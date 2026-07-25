<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Transaction boundary for the M3-02 SPMI document-version workflow.
 */
class Spmi_version_workflow_service
{
    protected $ci;
    protected $version_model;
    protected $file_asset_model;
    protected $organization_unit_model;
    protected $user_model;
    protected $audit_logger;

    protected $status_labels = [
        'draft' => 'Draft',
        'review' => 'Dalam Review',
        'approved' => 'Disetujui',
        'active' => 'Aktif',
        'retired' => 'Diarsipkan',
    ];

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Spmi_version_model');
        $this->ci->load->model('File_asset_model');
        $this->ci->load->model('Organization_unit_model');
        $this->ci->load->model('User_model');
        $this->ci->load->library('audit_logger');

        $this->version_model = $this->ci->Spmi_version_model;
        $this->file_asset_model = $this->ci->File_asset_model;
        $this->organization_unit_model = $this->ci->Organization_unit_model;
        $this->user_model = $this->ci->User_model;
        $this->audit_logger = $this->ci->audit_logger;
    }

    public function schema_ready()
    {
        return $this->version_model->schema_ready()
            && $this->file_asset_model->schema_ready()
            && $this->organization_unit_model->schema_ready();
    }

    public function status_labels()
    {
        return $this->status_labels;
    }

    public function find($id)
    {
        return $this->version_model->find((int) $id);
    }

    public function get_for_organization_unit($organization_unit_id)
    {
        return $this->version_model->get_for_organization_unit(
            (int) $organization_unit_id
        );
    }

    public function create_draft(array $input, $source_file_asset_id, $actor_user_id, $source_version_id = NULL)
    {
        $actor_user_id = (int) $actor_user_id;
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna pembuat tidak aktif atau tidak ditemukan.');
        }

        $normalized = $this->normalize_create($input);
        $validation = $this->validate_version_data($normalized, TRUE);
        if ($validation !== TRUE) {
            return $validation;
        }

        $this->ci->db->trans_begin();
        try {
            $this->version_model->lock_identity(
                $normalized['organization_unit_id'],
                $normalized['document_code']
            );
            if ($this->version_model->revision_exists(
                $normalized['organization_unit_id'],
                $normalized['document_code'],
                $normalized['revision_number']
            )) {
                return $this->rollback_failure('Nomor revisi sudah digunakan untuk dokumen dan unit ini.');
            }

            $asset = $this->file_asset_model->find_for_update(
                (int) $source_file_asset_id
            );
            $asset_validation = $this->validate_source_asset($asset, NULL);
            if ($asset_validation !== TRUE) {
                return $this->rollback_failure($asset_validation['message']);
            }

            $data = $normalized + [
                'source_file_asset_id' => (int) $asset->id,
                'source_file_path' => 'spmi_source/' . (string) $asset->stored_name,
                'source_file_sha256' => (string) $asset->sha256,
                'status' => 'draft',
                'created_by' => $actor_user_id,
                'approved_by' => NULL,
                'approved_at' => NULL,
            ];

            $id = $this->version_model->create($data);
            if (!$id || !$this->file_asset_model->assign_owner(
                (int) $asset->id,
                'spmi_version',
                (int) $id
            )) {
                return $this->rollback_failure('Draft versi SPMI gagal disimpan.');
            }

            if ($this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Draft versi SPMI gagal disimpan.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI draft create failed: ' . $exception->getMessage());
            return $this->rollback_failure('Draft versi SPMI gagal disimpan.');
        }

        $event_type = $source_version_id === NULL
            ? 'spmi_version_created'
            : 'spmi_version_cloned';
        $this->audit_logger->record(
            $event_type,
            'spmi_version',
            $id,
            $source_version_id === NULL ? 'create' : 'clone',
            NULL,
            $this->snapshot_array($data, $id),
            [
                'status_to' => 'draft',
                'file_asset_id' => (int) $asset->id,
                'source' => $source_version_id === NULL
                    ? 'upload'
                    : 'spmi_version_' . (int) $source_version_id,
                'scope' => 'organization_unit_' . $normalized['organization_unit_id'],
                'changed_fields' => [
                    'document_code',
                    'title',
                    'revision_number',
                    'effective_date',
                    'expires_at',
                    'source_file_asset_id',
                    'status',
                ],
            ],
            $actor_user_id
        );

        return [
            'success' => TRUE,
            'message' => $source_version_id === NULL
                ? 'Draft versi SPMI berhasil dibuat.'
                : 'Versi berhasil disalin menjadi draft baru.',
            'id' => (int) $id,
        ];
    }

    public function update_draft($id, array $input, $source_file_asset_id, $actor_user_id)
    {
        $id = (int) $id;
        $actor_user_id = (int) $actor_user_id;
        $existing = $this->version_model->find($id);
        if (!$existing) {
            return $this->failure('Versi SPMI tidak ditemukan.');
        }
        if ((string) $existing->status !== 'draft') {
            return $this->failure('Hanya versi berstatus draft yang dapat diubah.');
        }
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $data = $this->normalize_edit($input, $existing);
        $validation = $this->validate_version_data($data, FALSE);
        if ($validation !== TRUE) {
            return $validation;
        }

        $new_asset_id = (int) $source_file_asset_id;
        $before = $this->snapshot($existing);
        $old_stored_name = (string) $existing->source_file_stored_name;
        $changed_fields = [];
        foreach (['title', 'revision_number', 'effective_date', 'expires_at'] as $field) {
            $before_value = $existing->{$field} === NULL ? NULL : (string) $existing->{$field};
            if ((string) $before_value !== (string) $data[$field]) {
                $changed_fields[] = $field;
            }
        }

        $this->ci->db->trans_begin();
        try {
            $locked = $this->version_model->find_for_update($id);
            if (!$locked || (string) $locked->status !== 'draft') {
                return $this->rollback_failure('Draft sudah berubah dan tidak dapat diedit lagi.');
            }
            $this->version_model->lock_identity(
                (int) $existing->organization_unit_id,
                (string) $existing->document_code
            );
            if ($this->version_model->revision_exists(
                (int) $existing->organization_unit_id,
                (string) $existing->document_code,
                $data['revision_number'],
                $id
            )) {
                return $this->rollback_failure('Nomor revisi sudah digunakan untuk dokumen dan unit ini.');
            }

            if ($new_asset_id > 0) {
                $asset = $this->file_asset_model->find_for_update($new_asset_id);
                $asset_validation = $this->validate_source_asset($asset, $id);
                if ($asset_validation !== TRUE) {
                    return $this->rollback_failure($asset_validation['message']);
                }
                $data['source_file_asset_id'] = (int) $asset->id;
                $data['source_file_path'] = 'spmi_source/' . (string) $asset->stored_name;
                $data['source_file_sha256'] = (string) $asset->sha256;
                $changed_fields[] = 'source_file_asset_id';
            }

            if (empty($changed_fields)) {
                $this->ci->db->trans_rollback();
                return [
                    'success' => TRUE,
                    'message' => 'Tidak ada perubahan pada draft versi SPMI.',
                    'id' => $id,
                    'old_stored_name' => NULL,
                ];
            }

            if (!$this->version_model->update_draft($id, $data)) {
                return $this->rollback_failure('Draft versi SPMI gagal diperbarui.');
            }
            if ($new_asset_id > 0 && !$this->file_asset_model->assign_owner(
                $new_asset_id,
                'spmi_version',
                $id
            )) {
                return $this->rollback_failure('Kepemilikan file draft gagal diperbarui.');
            }
            if ($this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Draft versi SPMI gagal diperbarui.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI draft update failed: ' . $exception->getMessage());
            return $this->rollback_failure('Draft versi SPMI gagal diperbarui.');
        }

        $after = $data + ['id' => $id, 'status' => 'draft'];
        $this->audit_logger->record(
            'spmi_version_updated',
            'spmi_version',
            $id,
            'update',
            $before,
            $after,
            [
                'status_from' => 'draft',
                'status_to' => 'draft',
                'file_asset_id' => $new_asset_id > 0 ? $new_asset_id : (int) $existing->source_file_asset_id,
                'scope' => 'organization_unit_' . (int) $existing->organization_unit_id,
                'changed_fields' => $changed_fields,
            ],
            $actor_user_id
        );

        return [
            'success' => TRUE,
            'message' => 'Draft versi SPMI berhasil diperbarui.',
            'id' => $id,
            'old_stored_name' => $new_asset_id > 0 ? $old_stored_name : NULL,
        ];
    }

    public function submit_for_review($id, $actor_user_id)
    {
        return $this->transition(
            (int) $id,
            'draft',
            'review',
            [],
            (int) $actor_user_id,
            'spmi_version_submitted',
            'submit_review',
            'Versi berhasil dikirim untuk review.'
        );
    }

    public function approve($id, $actor_user_id)
    {
        $id = (int) $id;
        $actor_user_id = (int) $actor_user_id;
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna approver tidak aktif atau tidak ditemukan.');
        }

        $this->ci->db->trans_begin();
        try {
            $locked = $this->version_model->find_for_update($id);
            if (!$locked) {
                return $this->rollback_failure('Versi SPMI tidak ditemukan.');
            }
            if ((string) $locked->status !== 'review') {
                return $this->rollback_failure('Hanya versi dalam review yang dapat disetujui.');
            }
            if ((int) $locked->created_by === $actor_user_id) {
                return $this->rollback_failure(
                    'Pembuat versi tidak boleh menjadi satu-satunya approver.'
                );
            }

            $approved_at = $this->now();
            if (!$this->version_model->transition($id, 'review', 'approved', [
                'approved_by' => $actor_user_id,
                'approved_at' => $approved_at,
            ])) {
                return $this->rollback_failure('Versi SPMI gagal disetujui.');
            }
            if ($this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Versi SPMI gagal disetujui.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI approval failed: ' . $exception->getMessage());
            return $this->rollback_failure('Versi SPMI gagal disetujui.');
        }

        $this->audit_transition(
            $locked,
            'review',
            'approved',
            $actor_user_id,
            'spmi_version_approved',
            'approve',
            ['approved_by', 'approved_at', 'status'],
            [
                'approved_by' => $actor_user_id,
                'approved_at' => $approved_at,
            ]
        );

        return ['success' => TRUE, 'message' => 'Versi SPMI berhasil disetujui.', 'id' => $id];
    }

    public function activate($id, $actor_user_id, $on_date = NULL)
    {
        $id = (int) $id;
        $actor_user_id = (int) $actor_user_id;
        $existing = $this->version_model->find($id);
        if (!$existing) {
            return $this->failure('Versi SPMI tidak ditemukan.');
        }
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $on_date = $on_date === NULL ? date('Y-m-d') : (string) $on_date;
        if (!$this->valid_date($on_date)) {
            return $this->failure('Tanggal aktivasi tidak valid.');
        }

        $retired_version = NULL;
        $this->ci->db->trans_begin();
        try {
            $rows = $this->version_model->lock_identity(
                (int) $existing->organization_unit_id,
                (string) $existing->document_code
            );
            $target = NULL;
            foreach ($rows as $row) {
                if ((int) $row->id === $id) {
                    $target = $row;
                } elseif ((string) $row->status === 'active') {
                    $retired_version = $row;
                }
            }

            if (!$target || (string) $target->status !== 'approved') {
                return $this->rollback_failure('Hanya versi yang sudah disetujui yang dapat diaktifkan.');
            }
            if ((string) $target->effective_date > $on_date) {
                return $this->rollback_failure(
                    'Versi baru dapat diaktifkan pada atau setelah tanggal efektifnya.'
                );
            }
            if ($target->expires_at !== NULL
                && (string) $target->expires_at < $on_date) {
                return $this->rollback_failure(
                    'Versi yang masa berlakunya sudah berakhir tidak dapat diaktifkan.'
                );
            }
            if ($retired_version
                && (string) $retired_version->effective_date >= (string) $target->effective_date) {
                return $this->rollback_failure(
                    'Tanggal efektif versi baru harus setelah versi aktif saat ini.'
                );
            }

            if ($retired_version && !$this->version_model->retire_active_for_identity(
                (int) $target->organization_unit_id,
                (string) $target->document_code,
                $id,
                (string) $target->effective_date
            )) {
                return $this->rollback_failure('Versi aktif sebelumnya gagal diarsipkan.');
            }
            if (!$this->version_model->transition($id, 'approved', 'active')) {
                return $this->rollback_failure('Versi SPMI gagal diaktifkan.');
            }
            if ($this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Versi SPMI gagal diaktifkan.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI activation failed: ' . $exception->getMessage());
            return $this->rollback_failure('Versi SPMI gagal diaktifkan.');
        }

        if ($retired_version) {
            $this->audit_transition(
                $retired_version,
                'active',
                'retired',
                $actor_user_id,
                'spmi_version_retired',
                'retire',
                ['status', 'expires_at'],
                ['expires_at' => (string) $target->effective_date]
            );
        }
        $this->audit_transition(
            $target,
            'approved',
            'active',
            $actor_user_id,
            'spmi_version_activated',
            'activate',
            ['status']
        );

        return [
            'success' => TRUE,
            'message' => $retired_version
                ? 'Versi berhasil diaktifkan dan versi aktif sebelumnya diarsipkan.'
                : 'Versi SPMI berhasil diaktifkan.',
            'id' => $id,
        ];
    }

    public function retire($id, $actor_user_id, $on_date = NULL)
    {
        $id = (int) $id;
        $actor_user_id = (int) $actor_user_id;
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $on_date = $on_date === NULL ? date('Y-m-d') : (string) $on_date;
        if (!$this->valid_date($on_date)) {
            return $this->failure('Tanggal pengarsipan tidak valid.');
        }

        $this->ci->db->trans_begin();
        try {
            $locked = $this->version_model->find_for_update($id);
            if (!$locked || (string) $locked->status !== 'active') {
                return $this->rollback_failure('Hanya versi aktif yang dapat diarsipkan.');
            }
            $expires_at = max((string) $locked->effective_date, $on_date);
            if (!$this->version_model->transition($id, 'active', 'retired', [
                'expires_at' => $expires_at,
            ])) {
                return $this->rollback_failure('Versi SPMI gagal diarsipkan.');
            }
            if ($this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Versi SPMI gagal diarsipkan.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI retirement failed: ' . $exception->getMessage());
            return $this->rollback_failure('Versi SPMI gagal diarsipkan.');
        }

        $this->audit_transition(
            $locked,
            'active',
            'retired',
            $actor_user_id,
            'spmi_version_retired',
            'retire',
            ['status', 'expires_at'],
            ['expires_at' => $expires_at]
        );

        return ['success' => TRUE, 'message' => 'Versi SPMI berhasil diarsipkan.', 'id' => $id];
    }

    public function clone_to_draft($source_id, array $input, $source_file_asset_id, $actor_user_id)
    {
        $source = $this->version_model->find((int) $source_id);
        if (!$source) {
            return $this->failure('Versi sumber tidak ditemukan.');
        }
        if (!in_array((string) $source->status, ['approved', 'active', 'retired'], TRUE)) {
            return $this->failure('Hanya versi yang disetujui, aktif, atau diarsipkan yang dapat disalin.');
        }

        return $this->create_draft([
            'organization_unit_id' => (int) $source->organization_unit_id,
            'document_code' => (string) $source->document_code,
            'title' => isset($input['title']) ? $input['title'] : (string) $source->title,
            'revision_number' => isset($input['revision_number']) ? $input['revision_number'] : '',
            'effective_date' => isset($input['effective_date']) ? $input['effective_date'] : '',
            'expires_at' => isset($input['expires_at']) ? $input['expires_at'] : '',
        ], $source_file_asset_id, $actor_user_id, (int) $source->id);
    }

    protected function transition(
        $id,
        $from,
        $to,
        array $data,
        $actor_user_id,
        $event_type,
        $action,
        $message
    ) {
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $this->ci->db->trans_begin();
        try {
            $locked = $this->version_model->find_for_update($id);
            if (!$locked) {
                return $this->rollback_failure('Versi SPMI tidak ditemukan.');
            }
            if ((string) $locked->status !== $from) {
                return $this->rollback_failure(
                    'Transisi status tidak valid. Status saat ini: '
                    . (isset($this->status_labels[$locked->status])
                        ? $this->status_labels[$locked->status]
                        : (string) $locked->status)
                    . '.'
                );
            }
            if (!$this->version_model->transition($id, $from, $to, $data)) {
                return $this->rollback_failure('Status versi SPMI gagal diperbarui.');
            }
            if ($this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Status versi SPMI gagal diperbarui.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI transition failed: ' . $exception->getMessage());
            return $this->rollback_failure('Status versi SPMI gagal diperbarui.');
        }

        $this->audit_transition(
            $locked,
            $from,
            $to,
            $actor_user_id,
            $event_type,
            $action,
            array_merge(['status'], array_keys($data))
        );

        return ['success' => TRUE, 'message' => $message, 'id' => (int) $id];
    }

    protected function normalize_create(array $input)
    {
        return [
            'organization_unit_id' => isset($input['organization_unit_id'])
                ? (int) $input['organization_unit_id']
                : 0,
            'document_code' => strtoupper(trim(isset($input['document_code'])
                ? (string) $input['document_code']
                : '')),
            'title' => trim(isset($input['title']) ? (string) $input['title'] : ''),
            'revision_number' => trim(isset($input['revision_number'])
                ? (string) $input['revision_number']
                : ''),
            'effective_date' => trim(isset($input['effective_date'])
                ? (string) $input['effective_date']
                : ''),
            'expires_at' => $this->nullable_date(isset($input['expires_at'])
                ? $input['expires_at']
                : NULL),
        ];
    }

    protected function normalize_edit(array $input, $existing)
    {
        return [
            'organization_unit_id' => (int) $existing->organization_unit_id,
            'document_code' => (string) $existing->document_code,
            'title' => trim(isset($input['title']) ? (string) $input['title'] : ''),
            'revision_number' => trim(isset($input['revision_number'])
                ? (string) $input['revision_number']
                : ''),
            'effective_date' => trim(isset($input['effective_date'])
                ? (string) $input['effective_date']
                : ''),
            'expires_at' => $this->nullable_date(isset($input['expires_at'])
                ? $input['expires_at']
                : NULL),
        ];
    }

    protected function validate_version_data(array $data, $validate_unit)
    {
        if ((int) $data['organization_unit_id'] < 1) {
            return $this->failure('Unit organisasi wajib dipilih.');
        }
        if ($validate_unit) {
            $unit = $this->organization_unit_model->find(
                (int) $data['organization_unit_id']
            );
            if (!$unit || (int) $unit->active !== 1) {
                return $this->failure('Unit organisasi tidak aktif atau tidak ditemukan.');
            }
        }
        if ($data['document_code'] === ''
            || strlen($data['document_code']) > 64
            || preg_match('/^[A-Z0-9][A-Z0-9._\\/-]*$/', $data['document_code']) !== 1) {
            return $this->failure(
                'Kode dokumen wajib berupa huruf, angka, titik, garis bawah, garis miring, atau tanda hubung (maksimal 64 karakter).'
            );
        }
        if ($data['title'] === '' || $this->text_length($data['title']) > 255) {
            return $this->failure('Judul dokumen wajib diisi dan maksimal 255 karakter.');
        }
        if ($data['revision_number'] === ''
            || strlen($data['revision_number']) > 50
            || preg_match('/^[A-Za-z0-9][A-Za-z0-9._\\/-]*$/', $data['revision_number']) !== 1) {
            return $this->failure(
                'Nomor revisi wajib berupa huruf, angka, titik, garis bawah, garis miring, atau tanda hubung (maksimal 50 karakter).'
            );
        }
        if (!$this->valid_date($data['effective_date'])) {
            return $this->failure('Tanggal efektif wajib diisi dengan format yang valid.');
        }
        if ($data['expires_at'] !== NULL
            && (!$this->valid_date($data['expires_at'])
                || $data['expires_at'] < $data['effective_date'])) {
            return $this->failure('Tanggal berakhir harus sama atau setelah tanggal efektif.');
        }

        return TRUE;
    }

    protected function validate_source_asset($asset, $expected_owner_id)
    {
        $owner_valid = $asset
            && (string) $asset->owner_type === 'spmi_version'
            && ($asset->owner_id === NULL
                || ($expected_owner_id !== NULL
                    && (int) $asset->owner_id === (int) $expected_owner_id));
        if (!$owner_valid
            || (string) $asset->category !== 'spmi_source'
            || (string) $asset->storage_scope !== 'private'
            || (string) $asset->status !== 'active'
            || (string) $asset->extension !== 'pdf'
            || (string) $asset->mime_type !== 'application/pdf'
            || preg_match('/^[0-9a-f]{48}[.]pdf$/', (string) $asset->stored_name) !== 1
            || preg_match('/^[0-9a-f]{64}$/', (string) $asset->sha256) !== 1) {
            return $this->failure(
                'Aset PDF sumber tidak valid, tidak private, atau sudah dimiliki versi lain.'
            );
        }

        return TRUE;
    }

    protected function valid_actor($actor_user_id)
    {
        $actor = $this->user_model->find((int) $actor_user_id);
        return $actor && (int) $actor->is_active === 1;
    }

    protected function audit_transition(
        $row,
        $from,
        $to,
        $actor_user_id,
        $event_type,
        $action,
        array $changed_fields,
        array $after_overrides = []
    ) {
        $before = $this->snapshot($row);
        $after = $before;
        $after['status'] = $to;
        foreach ($after_overrides as $field => $value) {
            $after[$field] = $value;
        }

        $this->audit_logger->record(
            $event_type,
            'spmi_version',
            (int) $row->id,
            $action,
            $before,
            $after,
            [
                'status_from' => $from,
                'status_to' => $to,
                'file_asset_id' => (int) $row->source_file_asset_id,
                'scope' => 'organization_unit_' . (int) $row->organization_unit_id,
                'changed_fields' => $changed_fields,
            ],
            (int) $actor_user_id
        );
    }

    protected function snapshot($row)
    {
        return [
            'id' => (int) $row->id,
            'organization_unit_id' => (int) $row->organization_unit_id,
            'document_code' => (string) $row->document_code,
            'title' => (string) $row->title,
            'revision_number' => (string) $row->revision_number,
            'effective_date' => (string) $row->effective_date,
            'expires_at' => $row->expires_at === NULL ? NULL : (string) $row->expires_at,
            'source_file_asset_id' => (int) $row->source_file_asset_id,
            'status' => (string) $row->status,
            'created_by' => (int) $row->created_by,
            'approved_by' => $row->approved_by === NULL ? NULL : (int) $row->approved_by,
            'approved_at' => $row->approved_at === NULL ? NULL : (string) $row->approved_at,
        ];
    }

    protected function snapshot_array(array $data, $id)
    {
        return [
            'id' => (int) $id,
            'organization_unit_id' => (int) $data['organization_unit_id'],
            'document_code' => (string) $data['document_code'],
            'title' => (string) $data['title'],
            'revision_number' => (string) $data['revision_number'],
            'effective_date' => (string) $data['effective_date'],
            'expires_at' => $data['expires_at'],
            'source_file_asset_id' => (int) $data['source_file_asset_id'],
            'status' => (string) $data['status'],
            'created_by' => (int) $data['created_by'],
            'approved_by' => NULL,
            'approved_at' => NULL,
        ];
    }

    protected function rollback_failure($message)
    {
        $this->ci->db->trans_rollback();
        return $this->failure($message);
    }

    protected function failure($message)
    {
        return ['success' => FALSE, 'message' => (string) $message];
    }

    protected function nullable_date($value)
    {
        $value = trim((string) $value);
        return $value === '' ? NULL : $value;
    }

    protected function valid_date($value)
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        return $date !== FALSE && $date->format('Y-m-d') === (string) $value;
    }

    protected function now()
    {
        return (new DateTimeImmutable())->format('Y-m-d H:i:s.u');
    }

    protected function text_length($value)
    {
        return function_exists('mb_strlen')
            ? mb_strlen((string) $value, 'UTF-8')
            : strlen((string) $value);
    }
}

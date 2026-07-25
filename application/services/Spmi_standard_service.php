<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M3-03 mutation boundary for standards owned by one SPMI version.
 */
class Spmi_standard_service
{
    protected $ci;
    protected $standard_model;
    protected $version_model;
    protected $user_model;
    protected $audit_logger;

    protected $group_labels = [
        'education' => 'Pendidikan',
        'research' => 'Penelitian',
        'community_service' => 'Pengabdian kepada Masyarakat',
        'internal' => 'Standar Tambahan Perguruan Tinggi',
    ];

    protected $type_labels = [
        'sn_dikti' => 'SN Dikti',
        'internal' => 'Internal',
    ];

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Spmi_standard_model');
        $this->ci->load->model('Spmi_version_model');
        $this->ci->load->model('User_model');
        $this->ci->load->library('audit_logger');
        $this->ci->config->load('spmi_standard_seed', TRUE);

        $this->standard_model = $this->ci->Spmi_standard_model;
        $this->version_model = $this->ci->Spmi_version_model;
        $this->user_model = $this->ci->User_model;
        $this->audit_logger = $this->ci->audit_logger;
    }

    public function schema_ready()
    {
        return $this->standard_model->schema_ready()
            && $this->version_model->schema_ready();
    }

    public function group_labels()
    {
        return $this->group_labels;
    }

    public function type_labels()
    {
        return $this->type_labels;
    }

    public function find_version($id)
    {
        return $this->version_model->find((int) $id);
    }

    public function find($id)
    {
        return $this->standard_model->find((int) $id);
    }

    public function get_for_version($spmi_version_id)
    {
        return $this->standard_model->get_for_version((int) $spmi_version_id);
    }

    public function count_for_version($spmi_version_id)
    {
        return $this->standard_model->count_for_version((int) $spmi_version_id);
    }

    public function group_counts($spmi_version_id)
    {
        $counts = array_fill_keys(array_keys($this->group_labels), 0);
        foreach ($this->get_for_version((int) $spmi_version_id) as $standard) {
            if ((int) $standard->active === 1
                && isset($counts[(string) $standard->group_type])) {
                $counts[(string) $standard->group_type]++;
            }
        }
        return $counts;
    }

    public function seed_defaults($spmi_version_id, $actor_user_id)
    {
        $spmi_version_id = (int) $spmi_version_id;
        $actor_user_id = (int) $actor_user_id;
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $seed = $this->seed_rows($spmi_version_id);
        if ($seed === NULL) {
            return $this->failure('Konfigurasi seed 21 standar tidak valid.');
        }

        $this->ci->db->trans_begin();
        try {
            $version = $this->version_model->find_for_update($spmi_version_id);
            if (!$version) {
                return $this->rollback_failure('Versi SPMI tidak ditemukan.');
            }
            if ((string) $version->status !== 'draft') {
                return $this->rollback_failure(
                    'Struktur awal hanya dapat dimuat ke versi berstatus draft.'
                );
            }
            if ($this->standard_model->count_for_version($spmi_version_id) > 0) {
                return $this->rollback_failure(
                    'Versi ini sudah memiliki standar. Seed awal hanya dapat dijalankan sekali pada versi kosong.'
                );
            }
            if (!$this->standard_model->insert_batch($seed)
                || $this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Struktur 21 standar gagal dimuat.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI standard seed failed: ' . $exception->getMessage());
            return $this->rollback_failure('Struktur 21 standar gagal dimuat.');
        }

        $this->audit_logger->record(
            'spmi_standards_seeded',
            'spmi_version',
            $spmi_version_id,
            'seed',
            ['standard_count' => 0],
            ['standard_count' => count($seed)],
            [
                'row_count' => count($seed),
                'source' => 'business_requirements',
                'scope' => 'organization_unit_' . (int) $version->organization_unit_id,
                'changed_fields' => ['standards'],
            ],
            $actor_user_id
        );

        return [
            'success' => TRUE,
            'message' => 'Struktur awal 21 standar berhasil dimuat.',
            'row_count' => count($seed),
        ];
    }

    public function create($spmi_version_id, array $input, $actor_user_id)
    {
        $spmi_version_id = (int) $spmi_version_id;
        $actor_user_id = (int) $actor_user_id;
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $data = $this->normalize($input, $spmi_version_id, 1);
        $validation = $this->validate($data);
        if ($validation !== TRUE) {
            return $validation;
        }

        $this->ci->db->trans_begin();
        try {
            $version = $this->version_model->find_for_update($spmi_version_id);
            if (!$version) {
                return $this->rollback_failure('Versi SPMI tidak ditemukan.');
            }
            if ((string) $version->status !== 'draft') {
                return $this->rollback_failure(
                    'Standar hanya dapat ditambahkan pada versi berstatus draft.'
                );
            }
            if ($this->standard_model->code_exists(
                $spmi_version_id,
                $data['code']
            )) {
                return $this->rollback_failure(
                    'Kode standar sudah digunakan pada versi ini.'
                );
            }

            $id = $this->standard_model->create($data);
            if (!$id || $this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Standar SPMI gagal ditambahkan.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI standard create failed: ' . $exception->getMessage());
            return $this->rollback_failure('Standar SPMI gagal ditambahkan.');
        }

        $this->audit_logger->record(
            'spmi_standard_created',
            'spmi_standard',
            $id,
            'create',
            NULL,
            $this->snapshot_array($data, $id),
            [
                'status_to' => 'active',
                'scope' => 'organization_unit_' . (int) $version->organization_unit_id,
                'changed_fields' => array_keys($data),
            ],
            $actor_user_id
        );

        return [
            'success' => TRUE,
            'message' => 'Standar SPMI berhasil ditambahkan.',
            'id' => (int) $id,
        ];
    }

    public function update($id, array $input, $actor_user_id)
    {
        $id = (int) $id;
        $actor_user_id = (int) $actor_user_id;
        $existing = $this->standard_model->find($id);
        if (!$existing) {
            return $this->failure('Standar SPMI tidak ditemukan.');
        }
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $data = $this->normalize(
            $input,
            (int) $existing->spmi_version_id,
            (int) $existing->active
        );
        $validation = $this->validate($data);
        if ($validation !== TRUE) {
            return $validation;
        }

        $before = [];
        $changed_fields = [];
        $this->ci->db->trans_begin();
        try {
            $version = $this->version_model->find_for_update(
                (int) $existing->spmi_version_id
            );
            $locked = $this->standard_model->find_for_update($id);
            if (!$version || !$locked) {
                return $this->rollback_failure('Standar atau versi SPMI tidak ditemukan.');
            }
            if ((string) $version->status !== 'draft') {
                return $this->rollback_failure(
                    'Standar hanya dapat diubah pada versi berstatus draft.'
                );
            }
            $data['active'] = (int) $locked->active;
            $before = $this->snapshot($locked);
            $changed_fields = $this->changed_fields($before, $data);
            if (empty($changed_fields)) {
                $this->ci->db->trans_rollback();
                return [
                    'success' => TRUE,
                    'message' => 'Tidak ada perubahan pada standar SPMI.',
                    'id' => $id,
                ];
            }
            if ($this->standard_model->code_exists(
                (int) $existing->spmi_version_id,
                $data['code'],
                $id
            )) {
                return $this->rollback_failure(
                    'Kode standar sudah digunakan pada versi ini.'
                );
            }
            if (!$this->standard_model->update($id, $data)
                || $this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Standar SPMI gagal diperbarui.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI standard update failed: ' . $exception->getMessage());
            return $this->rollback_failure('Standar SPMI gagal diperbarui.');
        }

        $this->audit_logger->record(
            'spmi_standard_updated',
            'spmi_standard',
            $id,
            'update',
            $before,
            $this->snapshot_array($data, $id),
            [
                'scope' => 'organization_unit_' . (int) $version->organization_unit_id,
                'changed_fields' => $changed_fields,
            ],
            $actor_user_id
        );

        return [
            'success' => TRUE,
            'message' => 'Standar SPMI berhasil diperbarui.',
            'id' => $id,
        ];
    }

    public function toggle_active($id, $actor_user_id)
    {
        $id = (int) $id;
        $actor_user_id = (int) $actor_user_id;
        $existing = $this->standard_model->find($id);
        if (!$existing) {
            return $this->failure('Standar SPMI tidak ditemukan.');
        }
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $target = 0;
        $this->ci->db->trans_begin();
        try {
            $version = $this->version_model->find_for_update(
                (int) $existing->spmi_version_id
            );
            $locked = $this->standard_model->find_for_update($id);
            if (!$version || !$locked) {
                return $this->rollback_failure('Standar atau versi SPMI tidak ditemukan.');
            }
            if ((string) $version->status !== 'draft') {
                return $this->rollback_failure(
                    'Status standar hanya dapat diubah pada versi berstatus draft.'
                );
            }
            $target = (int) $locked->active === 1 ? 0 : 1;
            if (!$this->standard_model->update($id, ['active' => $target])
                || $this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Status standar gagal diperbarui.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI standard toggle failed: ' . $exception->getMessage());
            return $this->rollback_failure('Status standar gagal diperbarui.');
        }

        $this->audit_logger->record(
            $target ? 'spmi_standard_activated' : 'spmi_standard_deactivated',
            'spmi_standard',
            $id,
            $target ? 'activate' : 'deactivate',
            ['active' => (int) $locked->active],
            ['active' => $target],
            [
                'status_from' => (int) $locked->active ? 'active' : 'inactive',
                'status_to' => $target ? 'active' : 'inactive',
                'scope' => 'organization_unit_' . (int) $version->organization_unit_id,
                'changed_fields' => ['active'],
            ],
            $actor_user_id
        );

        return [
            'success' => TRUE,
            'message' => $target
                ? 'Standar berhasil diaktifkan.'
                : 'Standar berhasil dinonaktifkan tanpa menghapus histori.',
            'id' => $id,
        ];
    }

    public function reorder($spmi_version_id, array $orders, $actor_user_id)
    {
        $spmi_version_id = (int) $spmi_version_id;
        $actor_user_id = (int) $actor_user_id;
        if (!$this->valid_actor($actor_user_id)) {
            return $this->failure('Pengguna tidak aktif atau tidak ditemukan.');
        }

        $normalized = [];
        foreach ($orders as $id => $order) {
            $id = (int) $id;
            $order = (int) $order;
            if ($id < 1 || $order < 1 || $order > 100000) {
                return $this->failure('Urutan standar tidak valid.');
            }
            $normalized[$id] = $order;
        }
        if (empty($normalized)
            || count(array_unique(array_values($normalized))) !== count($normalized)) {
            return $this->failure('Setiap standar harus mempunyai urutan positif yang unik.');
        }

        $this->ci->db->trans_begin();
        try {
            $version = $this->version_model->find_for_update($spmi_version_id);
            $standards = $this->standard_model->lock_for_version($spmi_version_id);
            if (!$version) {
                return $this->rollback_failure('Versi SPMI tidak ditemukan.');
            }
            if ((string) $version->status !== 'draft') {
                return $this->rollback_failure(
                    'Urutan hanya dapat diubah pada versi berstatus draft.'
                );
            }

            $expected_ids = [];
            foreach ($standards as $standard) {
                $expected_ids[] = (int) $standard->id;
            }
            $submitted_ids = array_keys($normalized);
            sort($expected_ids, SORT_NUMERIC);
            sort($submitted_ids, SORT_NUMERIC);
            if ($expected_ids !== $submitted_ids) {
                return $this->rollback_failure(
                    'Daftar urutan harus mencakup seluruh standar pada versi ini.'
                );
            }

            foreach ($normalized as $id => $order) {
                if (!$this->standard_model->update($id, ['sort_order' => $order])) {
                    return $this->rollback_failure('Urutan standar gagal disimpan.');
                }
            }
            if ($this->ci->db->trans_status() === FALSE) {
                return $this->rollback_failure('Urutan standar gagal disimpan.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            log_message('error', 'SPMI standard reorder failed: ' . $exception->getMessage());
            return $this->rollback_failure('Urutan standar gagal disimpan.');
        }

        $this->audit_logger->record(
            'spmi_standards_reordered',
            'spmi_version',
            $spmi_version_id,
            'reorder',
            ['standard_count' => count($normalized)],
            ['standard_count' => count($normalized)],
            [
                'row_count' => count($normalized),
                'scope' => 'organization_unit_' . (int) $version->organization_unit_id,
                'changed_fields' => ['sort_order'],
            ],
            $actor_user_id
        );

        return [
            'success' => TRUE,
            'message' => 'Urutan standar berhasil disimpan.',
            'row_count' => count($normalized),
        ];
    }

    protected function seed_rows($spmi_version_id)
    {
        $configured = $this->ci->config->item(
            'standards',
            'spmi_standard_seed'
        );
        if (!is_array($configured) || count($configured) !== 21) {
            return NULL;
        }

        $rows = [];
        $codes = [];
        $group_counts = array_fill_keys(array_keys($this->group_labels), 0);
        foreach (array_values($configured) as $index => $seed) {
            $data = $this->normalize(
                $seed + [
                    'sort_order' => $index + 1,
                    'rationale' => NULL,
                    'definitions' => NULL,
                ],
                $spmi_version_id,
                1
            );
            if ($this->validate($data) !== TRUE || isset($codes[$data['code']])) {
                return NULL;
            }
            $codes[$data['code']] = TRUE;
            $group_counts[$data['group_type']]++;
            $rows[] = $data;
        }

        if ($group_counts !== [
            'education' => 8,
            'research' => 3,
            'community_service' => 3,
            'internal' => 7,
        ]) {
            return NULL;
        }

        return $rows;
    }

    protected function normalize(array $input, $spmi_version_id, $active)
    {
        return [
            'spmi_version_id' => (int) $spmi_version_id,
            'code' => strtoupper(trim(isset($input['code'])
                ? (string) $input['code']
                : '')),
            'name' => trim(isset($input['name']) ? (string) $input['name'] : ''),
            'group_type' => trim(isset($input['group_type'])
                ? (string) $input['group_type']
                : ''),
            'standard_type' => trim(isset($input['standard_type'])
                ? (string) $input['standard_type']
                : ''),
            'rationale' => $this->nullable_text(isset($input['rationale'])
                ? $input['rationale']
                : NULL),
            'definitions' => $this->nullable_text(isset($input['definitions'])
                ? $input['definitions']
                : NULL),
            'sort_order' => isset($input['sort_order'])
                ? (int) $input['sort_order']
                : 0,
            'active' => (int) $active === 1 ? 1 : 0,
        ];
    }

    protected function validate(array $data)
    {
        if ($data['spmi_version_id'] < 1) {
            return $this->failure('Versi SPMI wajib dipilih.');
        }
        if ($data['code'] === ''
            || strlen($data['code']) > 64
            || preg_match('/^[A-Z0-9][A-Z0-9._\\/-]*$/', $data['code']) !== 1) {
            return $this->failure(
                'Kode standar wajib berupa huruf, angka, titik, garis bawah, garis miring, atau tanda hubung (maksimal 64 karakter).'
            );
        }
        if ($data['name'] === '' || $this->text_length($data['name']) > 255) {
            return $this->failure('Nama standar wajib diisi dan maksimal 255 karakter.');
        }
        if (!isset($this->group_labels[$data['group_type']])
            || !isset($this->type_labels[$data['standard_type']])) {
            return $this->failure('Kelompok atau jenis standar tidak valid.');
        }
        if (($data['standard_type'] === 'internal'
                && $data['group_type'] !== 'internal')
            || ($data['standard_type'] === 'sn_dikti'
                && $data['group_type'] === 'internal')) {
            return $this->failure(
                'Jenis SN Dikti hanya untuk pendidikan, penelitian, atau pengabdian; kelompok internal wajib berjenis internal.'
            );
        }
        if ($data['sort_order'] < 1 || $data['sort_order'] > 100000) {
            return $this->failure('Urutan wajib berupa angka positif.');
        }
        foreach (['rationale', 'definitions'] as $field) {
            if ($data[$field] !== NULL && strlen($data[$field]) > 60000) {
                return $this->failure('Rasional dan definisi maksimal 60.000 byte.');
            }
        }
        return TRUE;
    }

    protected function changed_fields(array $before, array $after)
    {
        $fields = [];
        foreach ([
            'code',
            'name',
            'group_type',
            'standard_type',
            'rationale',
            'definitions',
            'sort_order',
            'active',
        ] as $field) {
            if ((string) $before[$field] !== (string) $after[$field]) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    protected function snapshot($standard)
    {
        return [
            'id' => (int) $standard->id,
            'spmi_version_id' => (int) $standard->spmi_version_id,
            'code' => (string) $standard->code,
            'name' => (string) $standard->name,
            'group_type' => (string) $standard->group_type,
            'standard_type' => (string) $standard->standard_type,
            'rationale' => $standard->rationale === NULL
                ? NULL
                : (string) $standard->rationale,
            'definitions' => $standard->definitions === NULL
                ? NULL
                : (string) $standard->definitions,
            'sort_order' => (int) $standard->sort_order,
            'active' => (int) $standard->active,
        ];
    }

    protected function snapshot_array(array $data, $id)
    {
        $snapshot = $data;
        $snapshot['id'] = (int) $id;
        return $snapshot;
    }

    protected function valid_actor($actor_user_id)
    {
        $actor = $this->user_model->find((int) $actor_user_id);
        return $actor && (int) $actor->is_active === 1;
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

    protected function nullable_text($value)
    {
        $value = trim((string) $value);
        return $value === '' ? NULL : $value;
    }

    protected function text_length($value)
    {
        return function_exists('mb_strlen')
            ? mb_strlen((string) $value, 'UTF-8')
            : strlen((string) $value);
    }
}

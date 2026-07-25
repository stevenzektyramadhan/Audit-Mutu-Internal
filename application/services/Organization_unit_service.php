<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organization_unit_service
{
    const TYPE_UNIVERSITY = 'university';
    const TYPE_FACULTY = 'faculty';
    const TYPE_STUDY_PROGRAM = 'study_program';
    const TYPE_INSTITUTE = 'institute';
    const TYPE_BUREAU = 'bureau';
    const TYPE_UNIT = 'unit';

    protected $ci;
    protected $organization_unit_model;
    protected $audit_logger;

    protected $type_labels = [
        self::TYPE_UNIVERSITY => 'Universitas',
        self::TYPE_FACULTY => 'Fakultas / UPPS',
        self::TYPE_STUDY_PROGRAM => 'Program Studi',
        self::TYPE_INSTITUTE => 'Lembaga',
        self::TYPE_BUREAU => 'Biro',
        self::TYPE_UNIT => 'Unit',
    ];

    protected $allowed_parent_types = [
        self::TYPE_UNIVERSITY => [],
        self::TYPE_FACULTY => [self::TYPE_UNIVERSITY],
        self::TYPE_STUDY_PROGRAM => [self::TYPE_FACULTY],
        self::TYPE_INSTITUTE => [self::TYPE_UNIVERSITY],
        self::TYPE_BUREAU => [self::TYPE_UNIVERSITY],
        self::TYPE_UNIT => [
            self::TYPE_UNIVERSITY,
            self::TYPE_FACULTY,
            self::TYPE_INSTITUTE,
            self::TYPE_BUREAU,
            self::TYPE_UNIT,
        ],
    ];

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Organization_unit_model');
        $this->ci->load->library('audit_logger');
        $this->organization_unit_model = $this->ci->Organization_unit_model;
        $this->audit_logger = $this->ci->audit_logger;
    }

    public function schema_ready()
    {
        return $this->organization_unit_model->schema_ready();
    }

    public function type_labels()
    {
        return $this->type_labels;
    }

    public function allowed_parent_types()
    {
        return $this->allowed_parent_types;
    }

    public function find($id)
    {
        return $this->organization_unit_model->find((int) $id);
    }

    public function get_tree()
    {
        return $this->flatten_tree($this->organization_unit_model->get_all());
    }

    public function get_parent_options($unit_id = NULL)
    {
        $excluded = [];
        if ((int) $unit_id > 0) {
            $excluded = $this->descendant_ids((int) $unit_id);
            $excluded[(int) $unit_id] = TRUE;
        }

        return array_values(array_filter($this->get_tree(), function ($unit) use ($excluded) {
            return !isset($excluded[(int) $unit->id]);
        }));
    }

    public function create($input)
    {
        $data = $this->normalize($input, TRUE);
        $validation = $this->validate($data);
        if ($validation !== TRUE) {
            return $validation;
        }

        $this->ci->db->trans_start();
        $id = $this->organization_unit_model->create($data);
        $this->ci->db->trans_complete();

        if ($id && $this->ci->db->trans_status()) {
            $this->audit_logger->record(
                'organization_unit_created',
                'organization_unit',
                $id,
                'create',
                NULL,
                $data,
                [
                    'status_to' => $data['active'] ? 'active' : 'inactive',
                    'changed_fields' => array_keys($data),
                ]
            );

            return [
                'success' => TRUE,
                'message' => 'Unit organisasi berhasil ditambahkan.',
                'id' => $id,
            ];
        }

        return ['success' => FALSE, 'message' => 'Unit organisasi gagal ditambahkan.'];
    }

    public function update($id, $input)
    {
        $id = (int) $id;
        $existing = $this->organization_unit_model->find($id);
        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Unit organisasi tidak ditemukan.'];
        }

        $data = $this->normalize($input, FALSE);
        $data['active'] = (int) $existing->active;
        $validation = $this->validate($data, $id);
        if ($validation !== TRUE) {
            return $validation;
        }

        $before = $this->snapshot($existing);
        $changed_fields = [];
        foreach ($data as $field => $value) {
            $existing_value = $field === 'parent_id'
                ? ($existing->parent_id === NULL ? NULL : (int) $existing->parent_id)
                : $existing->{$field};
            if ((string) $existing_value !== (string) $value) {
                $changed_fields[] = $field;
            }
        }

        if (empty($changed_fields)) {
            return ['success' => TRUE, 'message' => 'Tidak ada perubahan pada unit organisasi.'];
        }

        $this->ci->db->trans_start();
        $updated = $this->organization_unit_model->update($id, $data);
        $this->ci->db->trans_complete();

        if ($updated && $this->ci->db->trans_status()) {
            $this->audit_logger->record(
                'organization_unit_updated',
                'organization_unit',
                $id,
                'update',
                $before,
                $data,
                ['changed_fields' => $changed_fields]
            );

            return ['success' => TRUE, 'message' => 'Unit organisasi berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Unit organisasi gagal diperbarui.'];
    }

    public function toggle_active($id)
    {
        $id = (int) $id;
        $existing = $this->organization_unit_model->find($id);
        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Unit organisasi tidak ditemukan.'];
        }

        $target = (int) $existing->active === 1 ? 0 : 1;
        if ($target === 0 && $this->has_active_descendant($id)) {
            return [
                'success' => FALSE,
                'message' => 'Nonaktifkan seluruh unit turunan yang masih aktif terlebih dahulu.',
            ];
        }

        if ($target === 1 && !$this->ancestors_are_active($existing->parent_id)) {
            return [
                'success' => FALSE,
                'message' => 'Aktifkan unit induk terlebih dahulu.',
            ];
        }

        $updated = $this->organization_unit_model->update($id, ['active' => $target]);
        if (!$updated) {
            return ['success' => FALSE, 'message' => 'Status unit organisasi gagal diubah.'];
        }

        $this->audit_logger->record(
            $target ? 'organization_unit_activated' : 'organization_unit_deactivated',
            'organization_unit',
            $id,
            $target ? 'activate' : 'deactivate',
            ['active' => (int) $existing->active],
            ['active' => $target],
            [
                'status_from' => (int) $existing->active ? 'active' : 'inactive',
                'status_to' => $target ? 'active' : 'inactive',
                'changed_fields' => ['active'],
            ]
        );

        return [
            'success' => TRUE,
            'message' => $target
                ? 'Unit organisasi berhasil diaktifkan.'
                : 'Unit organisasi berhasil dinonaktifkan.',
        ];
    }

    protected function normalize($input, $include_active)
    {
        $parent_id = isset($input['parent_id']) ? (int) $input['parent_id'] : 0;
        $data = [
            'parent_id' => $parent_id > 0 ? $parent_id : NULL,
            'code' => strtoupper(trim(isset($input['code']) ? (string) $input['code'] : '')),
            'name' => trim(isset($input['name']) ? (string) $input['name'] : ''),
            'type' => trim(isset($input['type']) ? (string) $input['type'] : ''),
        ];

        if ($include_active) {
            $data['active'] = !empty($input['active']) ? 1 : 0;
            $data['metadata_json'] = NULL;
        }

        return $data;
    }

    protected function validate($data, $id = NULL)
    {
        if ($data['code'] === ''
            || strlen($data['code']) > 50
            || preg_match('/^[A-Z0-9][A-Z0-9._-]*$/', $data['code']) !== 1) {
            return [
                'success' => FALSE,
                'message' => 'Kode wajib diisi dengan huruf, angka, titik, garis bawah, atau tanda hubung (maksimal 50 karakter).',
            ];
        }

        if ($data['name'] === '' || $this->text_length($data['name']) > 200) {
            return [
                'success' => FALSE,
                'message' => 'Nama unit wajib diisi dan maksimal 200 karakter.',
            ];
        }

        if (!isset($this->allowed_parent_types[$data['type']])) {
            return ['success' => FALSE, 'message' => 'Jenis unit organisasi tidak valid.'];
        }

        if ($this->organization_unit_model->code_exists($data['code'], $id)) {
            return ['success' => FALSE, 'message' => 'Kode unit organisasi sudah digunakan.'];
        }

        if ($data['type'] === self::TYPE_UNIVERSITY) {
            if ($data['parent_id'] !== NULL) {
                return ['success' => FALSE, 'message' => 'Universitas harus menjadi unit root tanpa induk.'];
            }
        } else {
            if ($data['parent_id'] === NULL) {
                return ['success' => FALSE, 'message' => 'Unit selain universitas wajib memiliki induk.'];
            }

            $parent = $this->organization_unit_model->find($data['parent_id']);
            if (!$parent) {
                return ['success' => FALSE, 'message' => 'Unit induk tidak ditemukan.'];
            }

            if (!in_array($parent->type, $this->allowed_parent_types[$data['type']], TRUE)) {
                return [
                    'success' => FALSE,
                    'message' => $data['type'] === self::TYPE_STUDY_PROGRAM
                        ? 'Program studi wajib berada langsung di bawah fakultas/UPPS.'
                        : 'Jenis unit induk tidak sesuai dengan hierarki yang diizinkan.',
                ];
            }

            if (!empty($data['active']) && (int) $parent->active !== 1) {
                return ['success' => FALSE, 'message' => 'Unit aktif harus berada di bawah induk yang aktif.'];
            }
        }

        if ($id !== NULL && $data['parent_id'] !== NULL
            && $this->would_create_cycle((int) $id, (int) $data['parent_id'])) {
            return ['success' => FALSE, 'message' => 'Perubahan induk akan membentuk siklus hierarki.'];
        }

        if ($id !== NULL) {
            foreach ($this->organization_unit_model->get_children((int) $id) as $child) {
                $allowed = isset($this->allowed_parent_types[$child->type])
                    ? $this->allowed_parent_types[$child->type]
                    : [];
                if (!in_array($data['type'], $allowed, TRUE)) {
                    return [
                        'success' => FALSE,
                        'message' => 'Jenis unit tidak dapat diubah karena tidak sesuai dengan unit turunannya.',
                    ];
                }
            }
        }

        return TRUE;
    }

    protected function would_create_cycle($unit_id, $parent_id)
    {
        $seen = [];
        $cursor = $parent_id;
        while ($cursor > 0 && !isset($seen[$cursor])) {
            if ($cursor === $unit_id) {
                return TRUE;
            }
            $seen[$cursor] = TRUE;
            $parent = $this->organization_unit_model->find($cursor);
            $cursor = $parent && $parent->parent_id !== NULL
                ? (int) $parent->parent_id
                : 0;
        }

        return FALSE;
    }

    protected function ancestors_are_active($parent_id)
    {
        $seen = [];
        $cursor = $parent_id === NULL ? 0 : (int) $parent_id;
        while ($cursor > 0 && !isset($seen[$cursor])) {
            $seen[$cursor] = TRUE;
            $parent = $this->organization_unit_model->find($cursor);
            if (!$parent || (int) $parent->active !== 1) {
                return FALSE;
            }
            $cursor = $parent->parent_id === NULL ? 0 : (int) $parent->parent_id;
        }

        return $cursor === 0;
    }

    protected function has_active_descendant($unit_id)
    {
        $units = $this->organization_unit_model->get_all_plain();
        $children = [];
        foreach ($units as $unit) {
            if ($unit->parent_id !== NULL) {
                $children[(int) $unit->parent_id][] = $unit;
            }
        }

        $queue = isset($children[$unit_id]) ? $children[$unit_id] : [];
        $seen = [];
        while (!empty($queue)) {
            $unit = array_shift($queue);
            if (isset($seen[(int) $unit->id])) {
                continue;
            }
            $seen[(int) $unit->id] = TRUE;
            if ((int) $unit->active === 1) {
                return TRUE;
            }
            if (isset($children[(int) $unit->id])) {
                foreach ($children[(int) $unit->id] as $child) {
                    $queue[] = $child;
                }
            }
        }

        return FALSE;
    }

    protected function descendant_ids($unit_id)
    {
        $units = $this->organization_unit_model->get_all_plain();
        $children = [];
        foreach ($units as $unit) {
            if ($unit->parent_id !== NULL) {
                $children[(int) $unit->parent_id][] = (int) $unit->id;
            }
        }

        $found = [];
        $queue = isset($children[$unit_id]) ? $children[$unit_id] : [];
        while (!empty($queue)) {
            $id = array_shift($queue);
            if (isset($found[$id])) {
                continue;
            }
            $found[$id] = TRUE;
            if (isset($children[$id])) {
                foreach ($children[$id] as $child_id) {
                    $queue[] = $child_id;
                }
            }
        }

        return $found;
    }

    protected function flatten_tree($units)
    {
        $by_id = [];
        $children = [];
        foreach ($units as $unit) {
            $by_id[(int) $unit->id] = $unit;
            $parent_key = $unit->parent_id === NULL ? 0 : (int) $unit->parent_id;
            $children[$parent_key][] = $unit;
        }

        foreach ($children as &$siblings) {
            usort($siblings, function ($left, $right) {
                $name_compare = strcasecmp((string) $left->name, (string) $right->name);
                return $name_compare !== 0
                    ? $name_compare
                    : strcasecmp((string) $left->code, (string) $right->code);
            });
        }
        unset($siblings);

        $result = [];
        $visited = [];
        $append = function ($unit, $depth, $orphaned) use (&$append, &$children, &$result, &$visited) {
            $id = (int) $unit->id;
            if (isset($visited[$id])) {
                return;
            }
            $visited[$id] = TRUE;
            $unit->tree_depth = max(0, (int) $depth);
            $unit->tree_orphaned = (bool) $orphaned;
            $result[] = $unit;

            if (isset($children[$id])) {
                foreach ($children[$id] as $child) {
                    $append($child, $depth + 1, $orphaned);
                }
            }
        };

        foreach (isset($children[0]) ? $children[0] : [] as $root) {
            $append($root, 0, FALSE);
        }

        foreach ($units as $unit) {
            if (!isset($visited[(int) $unit->id])) {
                $missing_parent = $unit->parent_id !== NULL
                    && !isset($by_id[(int) $unit->parent_id]);
                $append($unit, 0, $missing_parent);
            }
        }

        return $result;
    }

    protected function snapshot($unit)
    {
        return [
            'parent_id' => $unit->parent_id === NULL ? NULL : (int) $unit->parent_id,
            'code' => (string) $unit->code,
            'name' => (string) $unit->name,
            'type' => (string) $unit->type,
            'active' => (int) $unit->active,
        ];
    }

    protected function text_length($value)
    {
        return function_exists('mb_strlen')
            ? mb_strlen((string) $value, 'UTF-8')
            : strlen((string) $value);
    }
}

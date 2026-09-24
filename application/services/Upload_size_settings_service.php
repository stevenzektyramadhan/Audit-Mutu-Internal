<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload_size_settings_service
{
    const APP_MAX_MIB = 10;
    const DEFAULT_LIMITS = [
        'spmi_evidence' => ['label' => 'Bukti SPMI', 'limit_mib' => 5],
        'ppepp_documents' => ['label' => 'Dokumen PPEPP', 'limit_mib' => 10],
        'profile_photos' => ['label' => 'Foto Profil', 'limit_mib' => 2],
        'spreadsheet_imports' => ['label' => 'Import Spreadsheet', 'limit_mib' => 2],
        'spmi_source_pdf' => ['label' => 'PDF Sumber SPMI', 'limit_mib' => 5],
        'institution_logo' => ['label' => 'Logo Lembaga', 'limit_mib' => 4],
    ];

    protected $ci;
    protected $model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Upload_size_settings_model');
        $this->model = $this->ci->Upload_size_settings_model;
    }

    public function settings()
    {
        $settings = [];
        foreach (self::DEFAULT_LIMITS as $category => $default) {
            $settings[$category] = [
                'label' => $default['label'],
                'limit_mib' => $this->clamp_mib((int) $default['limit_mib']),
            ];
        }
        foreach ($this->model->all() as $row) {
            if (isset($settings[$row->category])) {
                $settings[$row->category]['limit_mib'] = $this->clamp_mib((int) $row->limit_mib);
            }
        }
        return $settings;
    }

    public function update(array $data)
    {
        $ceiling = $this->php_ceiling_mib();
        if ($ceiling < 1) {
            return ['success' => FALSE, 'message' => 'Konfigurasi PHP upload saat ini terlalu kecil untuk menyimpan batas upload minimal 1 MiB.'];
        }

        $allowed_categories = array_keys(self::DEFAULT_LIMITS);
        $submitted_categories = array_keys($data);
        sort($allowed_categories);
        sort($submitted_categories);
        if ($submitted_categories !== $allowed_categories) {
            return ['success' => FALSE, 'message' => 'Kategori pengaturan upload tidak valid.'];
        }

        $rows = [];
        foreach (self::DEFAULT_LIMITS as $category => $default) {
            $value = trim((string) $data[$category]);
            if (!preg_match('/^[1-9][0-9]*$/', $value)) {
                return ['success' => FALSE, 'message' => 'Semua batas upload wajib berupa bilangan bulat MiB minimal 1.'];
            }
            if ((int) $value > $ceiling) {
                return ['success' => FALSE, 'message' => 'Batas upload tidak boleh melebihi ceiling aman server saat ini: ' . $ceiling . ' MiB.'];
            }

            $rows[] = [
                'category' => $category,
                'label' => $default['label'],
                'limit_mib' => (int) $value,
            ];
        }

        $this->ci->db->trans_begin();
        if (!$this->model->replace_all($rows)) return $this->rollback('Pengaturan ukuran upload gagal disimpan.');
        return $this->finish(['success' => TRUE, 'message' => 'Pengaturan ukuran upload berhasil disimpan.']);
    }

    public static function limit_bytes($category)
    {
        $mib = isset(self::DEFAULT_LIMITS[$category]) ? (int) self::DEFAULT_LIMITS[$category]['limit_mib'] : self::APP_MAX_MIB;
        $ci = &get_instance();
        if ($ci && isset($ci->db)) {
            $ci->load->model('Upload_size_settings_model');
            foreach ($ci->Upload_size_settings_model->all() as $row) {
                if ($row->category === $category) {
                    $mib = (int) $row->limit_mib;
                    break;
                }
            }
        }
        return self::clamp_static_mib($mib) * 1024 * 1024;
    }

    public function php_ceiling_mib()
    {
        return $this->bytes_to_mib_floor(min(self::APP_MAX_MIB * 1024 * 1024, $this->ini_bytes('upload_max_filesize'), $this->post_max_payload_bytes()));
    }

    protected function clamp_mib($value)
    {
        return max(1, min((int) $value, $this->php_ceiling_mib()));
    }

    protected function ini_mib($key)
    {
        return $this->bytes_to_mib_floor($this->ini_bytes($key));
    }

    protected function post_max_payload_bytes()
    {
        return $this->ini_bytes('post_max_size') - 1024 * 1024;
    }

    protected function ini_bytes($key)
    {
        return self::parse_ini_bytes(ini_get($key));
    }

    protected function bytes_to_mib_floor($bytes)
    {
        return (int) floor((int) $bytes / 1024 / 1024);
    }

    protected static function clamp_static_mib($value)
    {
        return max(1, min((int) $value, self::php_static_ceiling_mib()));
    }

    protected static function php_static_ceiling_mib()
    {
        return self::bytes_to_static_mib_floor(min(self::APP_MAX_MIB * 1024 * 1024, self::ini_static_bytes('upload_max_filesize'), self::post_max_static_payload_bytes()));
    }

    protected static function ini_static_mib($key)
    {
        return self::bytes_to_static_mib_floor(self::ini_static_bytes($key));
    }

    protected static function post_max_static_payload_bytes()
    {
        return self::ini_static_bytes('post_max_size') - 1024 * 1024;
    }

    protected static function ini_static_bytes($key)
    {
        return self::parse_ini_bytes(ini_get($key));
    }

    protected static function bytes_to_static_mib_floor($bytes)
    {
        return (int) floor((int) $bytes / 1024 / 1024);
    }

    protected static function parse_ini_bytes($value)
    {
        $raw = trim((string) $value);
        if ($raw === '') return self::APP_MAX_MIB * 1024 * 1024;
        $unit = strtolower(substr($raw, -1));
        $number = (float) $raw;
        if ($unit === 'g') $number *= 1024 * 1024 * 1024;
        elseif ($unit === 'm') $number *= 1024 * 1024;
        elseif ($unit === 'k') $number *= 1024;
        return (int) floor($number);
    }

    protected function rollback($message)
    {
        $this->ci->db->trans_rollback();
        return ['success' => FALSE, 'message' => $message];
    }

    protected function finish($result)
    {
        if ($this->ci->db->trans_status() === FALSE) {
            $this->ci->db->trans_rollback();
            return ['success' => FALSE, 'message' => 'Pengaturan ukuran upload gagal disimpan.'];
        }
        $this->ci->db->trans_commit();
        return $result;
    }
}

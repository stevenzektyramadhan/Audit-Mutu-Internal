<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_instruments_model extends CI_Model
{
    public function get_standards() { return $this->db->select('s.*, v.version_code, v.title AS version_title, v.status AS version_status')->from('spmi_standards s')->join('spmi_versions v', 'v.id = s.version_id')->order_by('v.id', 'DESC')->order_by('s.display_order', 'ASC')->get()->result(); }
    public function find_standard($id) { return $this->db->where('id', (int) $id)->get('spmi_standards')->row(); }
    public function find_version($id, $for_update = FALSE) { return $for_update ? $this->db->query('SELECT * FROM spmi_versions WHERE id = ' . (int) $id . ' FOR UPDATE')->row() : $this->db->where('id', (int) $id)->get('spmi_versions')->row(); }
    public function get_indicators($standard_id) { return $this->db->where('standard_id', (int) $standard_id)->order_by('indicator_code', 'ASC')->get('spmi_indicators')->result(); }
    public function get_packages($standard_id) { return $this->db->where('standard_id', (int) $standard_id)->order_by('display_order', 'ASC')->get('spmi_instrument_packages')->result(); }
    public function get_all_packages() { return $this->db->select('p.*, s.standard_code, s.title AS standard_title, v.version_code, v.status AS version_status')->from('spmi_instrument_packages p')->join('spmi_standards s', 's.id = p.standard_id')->join('spmi_versions v', 'v.id = s.version_id')->order_by('v.id', 'DESC')->order_by('s.display_order', 'ASC')->order_by('p.display_order', 'ASC')->get()->result(); }
    public function find_package($id) { return $this->db->select('p.*, s.standard_code, s.title AS standard_title, s.version_id, v.version_code, v.title AS version_title, v.status AS version_status')->from('spmi_instrument_packages p')->join('spmi_standards s', 's.id = p.standard_id')->join('spmi_versions v', 'v.id = s.version_id')->where('p.id', (int) $id)->get()->row(); }
    public function find_package_for_update($id) { return $this->db->where('id', (int) $id)->get('spmi_instrument_packages')->row(); }
    public function find_package_by_code($standard_id, $code, $exclude_id = 0) { $this->db->where(['standard_id' => (int) $standard_id, 'package_code' => $code]); if ($exclude_id) $this->db->where('id !=', (int) $exclude_id); return $this->db->get('spmi_instrument_packages')->row(); }
    public function find_package_by_order($standard_id, $order, $exclude_id = 0) { $this->db->where(['standard_id' => (int) $standard_id, 'display_order' => (int) $order]); if ($exclude_id) $this->db->where('id !=', (int) $exclude_id); return $this->db->get('spmi_instrument_packages')->row(); }
    public function count_questions($package_id) { return $this->db->where('package_id', (int) $package_id)->count_all_results('spmi_instrument_questions'); }
    public function get_questions($package_id) { return $this->db->select('q.*, i.indicator_code, i.title AS indicator_title')->from('spmi_instrument_questions q')->join('spmi_indicators i', 'i.id = q.indicator_id')->where('q.package_id', (int) $package_id)->order_by('q.display_order', 'ASC')->get()->result(); }
    public function find_question($id) { return $this->db->select('q.*, p.package_code, p.title AS package_title, p.standard_id, i.indicator_code, i.title AS indicator_title, s.standard_code, v.status AS version_status')->from('spmi_instrument_questions q')->join('spmi_instrument_packages p', 'p.id = q.package_id')->join('spmi_indicators i', 'i.id = q.indicator_id')->join('spmi_standards s', 's.id = p.standard_id')->join('spmi_versions v', 'v.id = s.version_id')->where('q.id', (int) $id)->get()->row(); }
    public function find_question_for_update($id) { return $this->db->where('id', (int) $id)->get('spmi_instrument_questions')->row(); }
    public function find_indicator($id) { return $this->db->where('id', (int) $id)->get('spmi_indicators')->row(); }
    public function find_question_by_code($package_id, $code, $exclude_id = 0) { $this->db->where(['package_id' => (int) $package_id, 'question_code' => $code]); if ($exclude_id) $this->db->where('id !=', (int) $exclude_id); return $this->db->get('spmi_instrument_questions')->row(); }
    public function find_question_by_order($package_id, $order, $exclude_id = 0) { $this->db->where(['package_id' => (int) $package_id, 'display_order' => (int) $order]); if ($exclude_id) $this->db->where('id !=', (int) $exclude_id); return $this->db->get('spmi_instrument_questions')->row(); }
    public function count_rubrics($question_id) { return $this->db->where('question_id', (int) $question_id)->count_all_results('spmi_instrument_rubrics'); }
    public function create($table, $data) { return $this->db->insert($table, $data); }
    public function update($table, $id, $data) { return $this->db->where('id', (int) $id)->update($table, $data); }
    public function delete($table, $id) { return $this->db->where('id', (int) $id)->delete($table); }
}

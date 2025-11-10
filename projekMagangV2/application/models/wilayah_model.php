<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wilayah_model extends CI_Model {

    private function _build_union_query($q = null)
    {
        $this->db->select("kode, nama, 'Provinsi' as level");
        $this->db->from('provinsi');
        if ($q) {
            if (is_string($q)) {
                $this->db->like('nama', $q);
            }
        }
        $q_prov = $this->db->get_compiled_select();

        $this->db->select("kode, nama, 'Kabupaten' as level");
        $this->db->from('kabupaten');
        if ($q) {
            if (is_string($q)) {
                $this->db->like('nama', $q);
            }
        }
        $q_kab = $this->db->get_compiled_select();

        $this->db->select("kode, nama, 'Kecamatan' as level");
        $this->db->from('kecamatan');
        if ($q) {
            if (is_string($q)) {
                $this->db->like('nama', $q);
            }
        }
        $q_kec = $this->db->get_compiled_select();

        $this->db->select("kode, nama, 'Kelurahan' as level");
        $this->db->from('kelurahan');
        if ($q) {
            if (is_string($q)) {
                $this->db->like('nama', $q);
            }
        }
        $q_kel = $this->db->get_compiled_select();

        return "$q_prov UNION ALL $q_kab UNION ALL $q_kec UNION ALL $q_kel";
    }

    public function get_paginated_all($limit, $offset, $q = null, $sort = 'kode', $order = 'DESC')
    {
        $union_query = $this->_build_union_query($q);
        $sql = "SELECT * FROM ($union_query) as data_gabungan
                ORDER BY $sort $order
                LIMIT $offset, $limit";
        return $this->db->query($sql)->result_array();
    }

    public function get_count_all($q = null)
    {
        $union_query = $this->_build_union_query($q);
        $sql = "SELECT COUNT(*) as total FROM ($union_query) as data_gabungan";
        $result = $this->db->query($sql)->row_array();
        return $result ? $result['total'] : 0;
    }
}
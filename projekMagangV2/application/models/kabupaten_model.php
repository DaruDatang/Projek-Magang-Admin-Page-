<?php
class Kabupaten_model extends CI_Model {
    protected $table = 'kabupaten';

    public function get_all($q = null, $sort = 'kode', $order = 'DESC')
    {
        $this->db->select('kabupaten.*, provinsi.nama AS provinsi_nama');
        $this->db->from('kabupaten');
        $this->db->join('provinsi', 'provinsi.kode = kabupaten.kode_provinsi', 'left');
        
        if ($q) {
            $this->db->like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
        }

        if ($sort == 'provinsi_nama') {
            $this->db->order_by($sort, $order);
        } else {
            $this->db->order_by("kabupaten.$sort", $order);
        }
        return $this->db->get()->result_array();
    }

    public function get_count($q = null, $kode_provinsi = null) {
        $this->db->from($this->table);
        $this->db->join('provinsi', 'provinsi.kode = kabupaten.kode_provinsi', 'left');

        if (!empty($kode_provinsi)) {
            $this->db->where('kabupaten.kode_provinsi', $kode_provinsi);
        }

        if ($q) {
            $this->db->group_start();
            $this->db->like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
            $this->db->group_end();
        }
        
        return $this->db->count_all_results();
    }

    public function get_paginated($limit, $offset, $q = null, $sort = 'kode', $order = 'DESC', $kode_provinsi = null)
    {
        $this->db->select('kabupaten.*, provinsi.nama AS provinsi_nama');
        $this->db->from('kabupaten');
        $this->db->join('provinsi', 'provinsi.kode = kabupaten.kode_provinsi', 'left');
        
        if (!empty($kode_provinsi)) {
            $this->db->where('kabupaten.kode_provinsi', $kode_provinsi);
        }

        if ($q) {
            $this->db->group_start();
            $this->db->like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
            $this->db->group_end();
        }

        if ($sort == 'provinsi_nama') {
            $this->db->order_by($sort, $order);
        } else {
            $this->db->order_by("kabupaten.$sort", $order);
        }
        
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }

    public function insert($nama, $kode_provinsi) {
        $kode = $this->generate_code($kode_provinsi);
        $this->db->insert($this->table, [
            'kode' => $kode,
            'nama' => $nama,
            'kode_provinsi' => $kode_provinsi
        ]);
    }

    public function update($kode, $nama, $kode_provinsi) {
        $this->db->where('kode', $kode)->update($this->table, [
            'nama' => $nama,
            'kode_provinsi' => $kode_provinsi
        ]);
    }

    public function delete($kode) {
        $this->db->where('kode', $kode)->delete($this->table);
    }

    public function generate_code($kode_provinsi) {
        $this->db->like('kode', $kode_provinsi . '.', 'after');
        $this->db->order_by('kode', 'DESC')->limit(1);
        $row = $this->db->get($this->table)->row();

        $next_num = 1;
        if ($row && isset($row->kode)) {
            $parts = explode('.', $row->kode);
            $next_num = intval(end($parts)) + 1;
        }
        return $kode_provinsi . '.' . str_pad($next_num, 2, '0', STR_PAD_LEFT);
    }

    public function get_with_provinsi($limit, $offset, $q = null, $sort = 'kode', $order = 'DESC')
    {
        $this->db->select('kabupaten.*, provinsi.nama AS provinsi_nama');
        $this->db->from('kabupaten');
        $this->db->join('provinsi', 'provinsi.kode = LEFT(kabupaten.kode, 2)', 'left');
        if ($q) $this->db->like('kabupaten.nama', $q);
        
        if ($sort == 'provinsi_nama') {
            $this->db->order_by($sort, $order);
        } else {
            $this->db->order_by("kabupaten.$sort", $order);
        }

        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }

    public function get_by_provinsi($kode_provinsi) {
        return $this->db->where('kode_provinsi', $kode_provinsi)
                        ->order_by('nama', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }
}
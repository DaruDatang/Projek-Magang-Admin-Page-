<?php
class Kelurahan_model extends CI_Model {
    protected $table = 'kelurahan';

    public function get_all($q = null, $sort = 'kode', $order = 'DESC') {
        $this->db->select('kelurahan.*, kecamatan.nama as kecamatan_nama, kabupaten.nama as kabupaten_nama, provinsi.nama as provinsi_nama');
        $this->db->from($this->table);
        $this->db->join('kecamatan', 'kelurahan.kode_kecamatan = kecamatan.kode', 'left');
        $this->db->join('kabupaten', 'kecamatan.kode_kabupaten = kabupaten.kode', 'left');
        $this->db->join('provinsi', 'kabupaten.kode_provinsi = provinsi.kode', 'left');

        if ($q) {
            $this->db->group_start();
            $this->db->like('kelurahan.nama', $q);
            $this->db->or_like('kecamatan.nama', $q);
            $this->db->or_like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
            $this->db->group_end();
        }
        
        if ($sort == 'provinsi_nama' || $sort == 'kabupaten_nama' || $sort == 'kecamatan_nama') {
            $this->db->order_by($sort, $order);
        } else {
            $this->db->order_by("kelurahan.$sort", $order);
        }
        
        return $this->db->get()->result_array();
    }

    public function get_count($q = null, $kode_provinsi = null, $kode_kabupaten = null, $kode_kecamatan = null) {
        $this->db->from($this->table);
        $this->db->join('kecamatan', 'kelurahan.kode_kecamatan = kecamatan.kode', 'left');
        $this->db->join('kabupaten', 'kecamatan.kode_kabupaten = kabupaten.kode', 'left');
        $this->db->join('provinsi', 'kabupaten.kode_provinsi = provinsi.kode', 'left');

        if (!empty($kode_kecamatan)) {
            $this->db->where('kelurahan.kode_kecamatan', $kode_kecamatan);
        } else if (!empty($kode_kabupaten)) {
            $this->db->where('kecamatan.kode_kabupaten', $kode_kabupaten);
        } else if (!empty($kode_provinsi)) {
            $this->db->where('kabupaten.kode_provinsi', $kode_provinsi);
        }

        if ($q) {
            $this->db->group_start();
            $this->db->like('kelurahan.nama', $q);
            $this->db->or_like('kecamatan.nama', $q);
            $this->db->or_like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
            $this->db->group_end();
        }
        return $this->db->count_all_results();
    }

    public function get_paginated($limit, $offset, $q = null, $sort = 'kode', $order = 'DESC', $kode_provinsi = null, $kode_kabupaten = null, $kode_kecamatan = null) {
        $this->db->select('kelurahan.*, kecamatan.nama as kecamatan_nama, kabupaten.nama as kabupaten_nama, provinsi.nama as provinsi_nama');
        $this->db->from($this->table);
        $this->db->join('kecamatan', 'kelurahan.kode_kecamatan = kecamatan.kode', 'left');
        $this->db->join('kabupaten', 'kecamatan.kode_kabupaten = kabupaten.kode', 'left');
        $this->db->join('provinsi', 'kabupaten.kode_provinsi = provinsi.kode', 'left');

        if (!empty($kode_kecamatan)) {
            $this->db->where('kelurahan.kode_kecamatan', $kode_kecamatan);
        } else if (!empty($kode_kabupaten)) {
            $this->db->where('kecamatan.kode_kabupaten', $kode_kabupaten);
        } else if (!empty($kode_provinsi)) {
            $this->db->where('kabupaten.kode_provinsi', $kode_provinsi);
        }

        if ($q) {
            $this->db->group_start();
            $this->db->like('kelurahan.nama', $q);
            $this->db->or_like('kecamatan.nama', $q);
            $this->db->or_like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
            $this->db->group_end();
        }
        
        if ($sort == 'provinsi_nama' || $sort == 'kabupaten_nama' || $sort == 'kecamatan_nama') {
            $this->db->order_by($sort, $order);
        } else {
            $this->db->order_by("kelurahan.$sort", $order);
        }
        
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }

    public function insert($nama, $kode_kecamatan) {
        $kode = $this->generate_code($kode_kecamatan);
        $this->db->insert($this->table, [
            'kode' => $kode,
            'nama' => $nama,
            'kode_kecamatan' => $kode_kecamatan
        ]);
    }

    public function update($kode, $nama, $kode_kecamatan) {
        $this->db->where('kode', $kode)->update($this->table, [
            'nama' => $nama,
            'kode_kecamatan' => $kode_kecamatan
        ]);
    }

    public function delete($kode) {
        $this->db->where('kode', $kode)->delete($this->table);
    }

    public function generate_code($kode_kecamatan) {
        $this->db->like('kode', $kode_kecamatan . '.', 'after');
        $this->db->order_by('kode', 'DESC')->limit(1);
        $row = $this->db->get($this->table)->row();

        $next_num = 1;
        if ($row && isset($row->kode)) {
            $parts = explode('.', $row->kode);
            $next_num = intval(end($parts)) + 1;
        }
        return $kode_kecamatan . '.' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    }

    public function get_by_kecamatan($kode_kecamatan) {
        return $this->db->where('kode_kecamatan', $kode_kecamatan)
                        ->order_by('nama', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }
}
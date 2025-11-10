<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kecamatan_model extends CI_Model {
    protected $table = 'kecamatan';

    public function get_all($q = null, $sort = 'kode', $order = 'DESC')
    {
        $this->db->select('kecamatan.*, kabupaten.nama as kabupaten_nama, provinsi.nama as provinsi_nama');
        $this->db->from($this->table);
        $this->db->join('kabupaten', 'kecamatan.kode_kabupaten = kabupaten.kode', 'left');
        $this->db->join('provinsi', 'kabupaten.kode_provinsi = provinsi.kode', 'left');
        
        if ($q) {
            $this->db->group_start();
            $this->db->like('kecamatan.nama', $q);
            $this->db->or_like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
            $this->db->group_end();
        }
        
        if ($sort == 'provinsi_nama' || $sort == 'kabupaten_nama') {
            $this->db->order_by($sort, $order);
        } else {
            $this->db->order_by("kecamatan.$sort", $order);
        }
        return $this->db->get()->result_array();
    }

    public function get_count($q = null, $kode_provinsi = null, $kode_kabupaten = null)
    {
        $this->db->from($this->table);
        $this->db->join('kabupaten', 'kecamatan.kode_kabupaten = kabupaten.kode', 'left');
        $this->db->join('provinsi', 'kabupaten.kode_provinsi = provinsi.kode', 'left');

        if (!empty($kode_kabupaten)) {
            $this->db->where('kecamatan.kode_kabupaten', $kode_kabupaten);
        } else if (!empty($kode_provinsi)) {
            $this->db->where('kabupaten.kode_provinsi', $kode_provinsi);
        }

        if ($q) {
            $this->db->group_start();
            $this->db->like('kecamatan.nama', $q);
            $this->db->or_like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
            $this->db->group_end();
        }
        return $this->db->count_all_results();
    }

    public function get_paginated($limit, $offset, $q = null, $sort = 'kode', $order = 'DESC', $kode_provinsi = null, $kode_kabupaten = null)
    {
        $this->db->select('kecamatan.*, kabupaten.nama as kabupaten_nama, provinsi.nama as provinsi_nama');
        $this->db->from($this->table);
        $this->db->join('kabupaten', 'kecamatan.kode_kabupaten = kabupaten.kode', 'left');
        $this->db->join('provinsi', 'kabupaten.kode_provinsi = provinsi.kode', 'left');

        if (!empty($kode_kabupaten)) {
            $this->db->where('kecamatan.kode_kabupaten', $kode_kabupaten);
        } else if (!empty($kode_provinsi)) {
            $this->db->where('kabupaten.kode_provinsi', $kode_provinsi);
        }

        if ($q) {
            $this->db->group_start();
            $this->db->like('kecamatan.nama', $q);
            $this->db->or_like('kabupaten.nama', $q);
            $this->db->or_like('provinsi.nama', $q);
            $this->db->group_end();
        }
        
        if ($sort == 'provinsi_nama' || $sort == 'kabupaten_nama') {
            $this->db->order_by($sort, $order);
        } else {
            $this->db->order_by("kecamatan.$sort", $order);
        }
        
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }

    public function insert($nama, $kode_kabupaten)
    {
        $kode = $this->generate_code($kode_kabupaten);
        $this->db->insert($this->table, [
            'kode' => $kode,
            'nama' => $nama,
            'kode_kabupaten' => $kode_kabupaten
        ]);
    }

    public function update($kode, $nama, $kode_kabupaten)
    {
        $this_data = [
            'nama' => $nama,
            'kode_kabupaten' => $kode_kabupaten
        ];
        $this->db->where('kode', $kode)->update($this->table, $this_data);
    }

    public function delete($kode)
    {
        $this->db->where('kode', $kode)->delete($this->table);
    }

    public function generate_code($kode_kabupaten)
    {
        $this->db->like('kode', $kode_kabupaten . '.', 'after');
        $this->db->order_by('kode', 'DESC')->limit(1);
        $row = $this->db->get($this->table)->row();
        if ($row) {
            $parts = explode('.', $row->kode);
            $next = str_pad(((int)end($parts) + 1), 2, '0', STR_PAD_LEFT);
        } else {
            $next = '01';
        }
        return $kode_kabupaten . '.' . $next;
    }

    public function get_by_kabupaten($kode_kabupaten)
    {
        return $this->db->where('kode_kabupaten', $kode_kabupaten)
                        ->order_by('nama', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }
}
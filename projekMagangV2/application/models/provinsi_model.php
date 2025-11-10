<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Provinsi_model extends CI_Model {
    protected $table = 'provinsi';

    // --- Ambil semua data (tanpa pagination) ---
    public function get_all($q = null, $sort = 'kode', $order = 'DESC') {
        if ($q) {
            $this->db->group_start();
            $this->db->like('nama', $q);
            $this->db->or_like('kode', $q);
            $this->db->group_end();
        }
        $this->db->order_by($sort, $order);
        return $this->db->get($this->table)->result_array();
    }

    // --- Hitung total data (untuk pagination) ---
    public function get_count($q = null)
    {
        if ($q) {
            $this->db->group_start();
            $this->db->like('nama', $q);
            $this->db->or_like('kode', $q);
            $this->db->group_end();
        }
        return $this->db->count_all_results($this->table);
    }

    // --- Ambil data per halaman ---
    public function get_paginated($limit, $start, $q = null, $sort = 'kode', $order = 'DESC')
    {
        if ($q) {
            $this->db->group_start();
            $this->db->like('nama', $q);
            $this->db->or_like('kode', $q);
            $this->db->group_end();
        }
        $this->db->order_by($sort, $order);
        return $this->db->get($this->table, $limit, $start)->result_array();
    }

    // --- CRUD dasar ---
    public function insert($nama)
    {
        $kode = $this->generate_code();
        $this->db->insert($this->table, ['kode' => $kode, 'nama' => $nama]);
    }

    public function update($kode, $nama)
    {
        $this->db->where('kode', $kode)->update($this->table, ['nama' => $nama]);
    }

    public function delete($kode)
    {
        $this->db->where('kode', $kode)->delete($this->table);
    }

    // --- Generate kode otomatis ---
    public function generate_code()
    {
        $this->db->select('kode')->order_by('kode', 'DESC')->limit(1);
        $row = $this->db->get($this->table)->row();
        if (!$row) return '11';
        $next = str_pad((int)$row->kode + 1, 2, '0', STR_PAD_LEFT);
        return $next;
    }
}
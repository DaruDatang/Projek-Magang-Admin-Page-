<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kecamatan extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Kecamatan_model', 'Kabupaten_model', 'Provinsi_model']);
        $this->load->library('pagination');
        $this->load->helper(['url', 'form']);
    }

    public function index()
    {
        $q = $this->input->get('q');
        $sort = $this->input->get('sort') ?: 'kode';
        $order = $this->input->get('order') ?: 'DESC';
        $kode_provinsi = $this->input->get('kode_provinsi');
        $kode_kabupaten = $this->input->get('kode_kabupaten');

        $config['base_url'] = base_url('kecamatan');
        $config['total_rows'] = $this->Kecamatan_model->get_count($q, $kode_provinsi, $kode_kabupaten);
        $config['per_page'] = 10;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;

        $config['full_tag_open']   = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        $config['full_tag_close']  = '</ul></nav>';
        $config['first_link']      = 'First';
        $config['first_tag_open']  = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_link']       = 'Last';
        $config['last_tag_open']   = '<li class="page-item">';
        $config['last_tag_close']  = '</li>';
        $config['next_link']       = '&raquo;';
        $config['next_tag_open']   = '<li class="page-item">';
        $config['next_tag_close']  = '</li>';
        $config['prev_link']       = '&laquo;';
        $config['prev_tag_open']   = '<li class="page-item">';
        $config['prev_tag_close']  = '</li>';
        $config['cur_tag_open']    = '<li class="page-item active" aria-current="page"><a class="page-link" href="#">';
        $config['cur_tag_close']   = '</a></li>';
        $config['num_tag_open']    = '<li class="page-item">';
        $config['num_tag_close']   = '</li>';
        $config['attributes']      = ['class' => 'page-link'];

        $this->pagination->initialize($config);
        $page = $this->input->get('page') ?: 0;

        $data = [
            'kecamatan' => $this->Kecamatan_model->get_paginated($config['per_page'], $page, $q, $sort, $order, $kode_provinsi, $kode_kabupaten),
            'links'     => $this->pagination->create_links(),
            'title'     => 'Data Kecamatan',
            'active_menu' => 'kecamatan',
            'total_data' => $config['total_rows'],
            'provinsi_list' => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'kabupaten_list' => $kode_provinsi ? $this->Kabupaten_model->get_by_provinsi($kode_provinsi) : [],
            'selected_provinsi' => $kode_provinsi,
            'selected_kabupaten' => $kode_kabupaten,
            'sort' => $sort,
            'order' => $order
        ];

        $this->load->view('partials/header', $data);
        $this->load->view('kecamatan/index', $data);
        $this->load->view('partials/footer');
    }

    public function add()
    {
        $data = [
            'provinsi' => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'kabupaten' => [],
            'mode' => 'add',
            'title' => 'Tambah Kecamatan',
            'active_menu' => 'kecamatan'
        ];

        if ($this->input->post()) {
            $this->Kecamatan_model->insert(
                $this->input->post('nama'),
                $this->input->post('kode_kabupaten')
            );
            redirect('kecamatan');
        }

        $this->load->view('partials/header', $data);
        $this->load->view('kecamatan/form', $data);
        $this->load->view('partials/footer');
    }

    public function edit($kode)
    {
        $item = $this->db->get_where('kecamatan', ['kode' => $kode])->row_array();
        $kabupaten_row = $this->db->get_where('kabupaten', ['kode' => $item['kode_kabupaten']])->row_array();
        $kode_provinsi = $kabupaten_row ? $kabupaten_row['kode_provinsi'] : null;

        $data = [
            'provinsi' => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'kabupaten' => $kode_provinsi ? $this->db->get_where('kabupaten', ['kode_provinsi' => $kode_provinsi])->result_array() : [],
            'item' => $item,
            'selected_provinsi' => $kode_provinsi,
            'selected_kabupaten' => $item['kode_kabupaten'],
            'mode' => 'edit',
            'title' => 'Edit Kecamatan',
            'active_menu' => 'kecamatan'
        ];

        if ($this->input->post()) {
            $this->Kecamatan_model->update(
                $kode,
                $this->input->post('nama'),
                $this->input->post('kode_kabupaten')
            );
            redirect('kecamatan');
        }

        $this->load->view('partials/header', $data);
        $this->load->view('kecamatan/form', $data);
        $this->load->view('partials/footer');
    }

    public function delete($kode)
    {
        $this->Kecamatan_model->delete($kode);
        redirect('kecamatan');
    }
}
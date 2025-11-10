<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kabupaten extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Kabupaten_model', 'Provinsi_model']);
        $this->load->library('pagination');
        $this->load->helper(['url', 'form']);
    }

    public function index()
    {
        $q = $this->input->get('q');
        $sort = $this->input->get('sort') ?: 'kode';
        $order = $this->input->get('order') ?: 'DESC';
        $kode_provinsi = $this->input->get('kode_provinsi');

        $config['base_url'] = base_url('kabupaten');
        $config['total_rows'] = $this->Kabupaten_model->get_count($q, $kode_provinsi);
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

        $data['kabupaten'] = $this->Kabupaten_model->get_paginated($config['per_page'], $page, $q, $sort, $order, $kode_provinsi);
        $data['links'] = $this->pagination->create_links();
        $data['title'] = 'Data Kabupaten';
        $data['active_menu'] = 'kabupaten';
        $data['total_data'] = $config['total_rows'];
        $data['provinsi_list'] = $this->Provinsi_model->get_all(null, 'nama', 'ASC');
        $data['selected_provinsi'] = $kode_provinsi;
        $data['sort'] = $sort;
        $data['order'] = $order;

        $this->load->view('partials/header', $data);
        $this->load->view('kabupaten/index', $data);
        $this->load->view('partials/footer');
    }

    public function add()
    {
        $data = [
            'provinsi' => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'mode' => 'add',
            'title' => 'Tambah Kabupaten',
            'active_menu' => 'kabupaten'
        ];

        if ($this->input->post()) {
            $this->Kabupaten_model->insert(
                $this->input->post('nama'),
                $this->input->post('kode_provinsi')
            );
            redirect('kabupaten');
        }

        $this->load->view('partials/header', $data);
        $this->load->view('kabupaten/form', $data);
        $this->load->view('partials/footer');
    }

    public function edit($kode)
    {
        $item = $this->db->get_where('kabupaten', ['kode' => $kode])->row_array();
        
        $data = [
            'provinsi' => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'item'     => $item,
            'selected_provinsi' => $item['kode_provinsi'],
            'mode'     => 'edit',
            'title'    => 'Edit Kabupaten',
            'active_menu' => 'kabupaten'
        ];

        if ($this->input->post()) {
            $this->Kabupaten_model->update(
                $kode, 
                $this->input->post('nama'),
                $this->input->post('kode_provinsi')
            );
            redirect('kabupaten');
        }

        $this->load->view('partials/header', $data);
        $this->load->view('kabupaten/form', $data);
        $this->load->view('partials/footer');
    }

    public function delete($kode)
    {
        $this->Kabupaten_model->delete($kode);
        redirect('kabupaten');
    }
}
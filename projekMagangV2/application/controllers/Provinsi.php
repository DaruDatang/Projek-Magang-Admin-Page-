<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Provinsi extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Provinsi_model');
        $this->load->library('pagination');
        $this->load->helper(['url', 'form']);
    }

    public function index()
    {
        $q = $this->input->get('q');
        $sort = $this->input->get('sort') ?: 'kode';
        $order = $this->input->get('order') ?: 'DESC';

        $config['base_url'] = base_url('provinsi');
        $config['total_rows'] = $this->Provinsi_model->get_count($q);
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
            'provinsi' => $this->Provinsi_model->get_paginated($config['per_page'], $page, $q, $sort, $order),
            'links'    => $this->pagination->create_links(),
            'q'        => $q,
            'sort'     => $sort,
            'order'    => $order,
            'title'    => 'Data Provinsi',
            'active_menu' => 'provinsi',
            'total_data' => $config['total_rows']
        ];

        $this->load->view('partials/header', $data);
        $this->load->view('provinsi/index', $data);
        $this->load->view('partials/footer');
    }

    public function add()
    {
        if ($this->input->post()) {
            $this->Provinsi_model->insert($this->input->post('nama'));
            redirect('provinsi');
        }

        $data = [
            'mode' => 'add',
            'title' => 'Tambah Provinsi',
            'active_menu' => 'provinsi'
        ];

        $this->load->view('partials/header', $data);
        $this->load->view('provinsi/form', $data);
        $this->load->view('partials/footer');
    }

    public function edit($kode)
    {
        $data['item'] = $this->db->get_where('provinsi', ['kode' => $kode])->row_array();

        if ($this->input->post()) {
            $this->Provinsi_model->update($kode, $this->input->post('nama'));
            redirect('provinsi');
        }

        $data['mode'] = 'edit';
        $data['title'] = 'Edit Provinsi';
        $data['active_menu'] = 'provinsi';

        $this->load->view('partials/header', $data);
        $this->load->view('provinsi/form', $data);
        $this->load->view('partials/footer');
    }

    public function delete($kode)
    {
        $this->Provinsi_model->delete($kode);
        redirect('provinsi');
    }
}
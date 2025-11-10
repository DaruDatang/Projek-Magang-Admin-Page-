<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wilayah extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            'Provinsi_model',
            'Kabupaten_model',
            'Kecamatan_model',
            'Kelurahan_model',
            'Wilayah_model'
        ]);
        $this->load->helper(['url', 'form']);
        $this->load->library(['session', 'pagination']);
    }

    public function index()
    {
        $q = trim($this->input->get('q'));
        $sort = $this->input->get('sort') ?: 'kode';
        $order = $this->input->get('order') ?: 'DESC';
        $limit = 10;
        $page = $this->input->get('page') ?: 0;
        $offset = (int)$page;

        $total_data = $this->Wilayah_model->get_count_all($q);
        $semua_data = $this->Wilayah_model->get_paginated_all($limit, $offset, $q, $sort, $order);

        $config['base_url'] = base_url('wilayah');
        $config['total_rows'] = $total_data;
        $config['per_page'] = $limit;
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

        $data['wilayah'] = $semua_data;
        $data['links'] = $this->pagination->create_links();
        $data['title'] = 'Data Wilayah Indonesia';
        $data['active_menu'] = 'wilayah';
        $data['total_data'] = $total_data;
        $data['sort'] = $sort;
        $data['order'] = $order;

        $this->load->view('partials/header', $data);
        $this->load->view('wilayah/index', $data);
        $this->load->view('partials/footer');
    }

    public function form()
    {
        $data['wilayah'] = [
            'provinsi'  => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'kabupaten' => [],
            'kecamatan' => []
        ];
        $data['title'] = 'Tambah Data Wilayah';
        $data['active_menu'] = 'wilayah';

        if ($this->input->post()) {
            $level = $this->input->post('level');

            switch ($level) {
                case 'provinsi':
                    $nama = $this->input->post('nama_provinsi');
                    $this->Provinsi_model->insert($nama);
                    break;

                case 'kabupaten':
                    $nama = $this->input->post('nama_kabupaten');
                    $kode_provinsi = $this->input->post('kode_provinsi');
                    $this->Kabupaten_model->insert($nama, $kode_provinsi);
                    break;

                case 'kecamatan':
                    $nama = $this->input->post('nama_kecamatan');
                    $kode_kabupaten = $this->input->post('kode_kabupaten_kec');
                    $this->Kecamatan_model->insert($nama, $kode_kabupaten);
                    break;

                case 'kelurahan':
                    $nama = $this->input->post('nama_kelurahan');
                    $kode_kecamatan = $this->input->post('kode_kecamatan_kel');
                    $this->Kelurahan_model->insert($nama, $kode_kecamatan);
                    break;
            }

            $this->session->set_flashdata('success', ucfirst($level) . ' berhasil ditambahkan');
            redirect('wilayah');
        }

        $this->load->view('partials/header', $data);
        $this->load->view('wilayah/form', $data);
        $this->load->view('partials/footer');
    }

    public function api_kabupaten_by_provinsi()
    {
        if (!$this->input->is_ajax_request()) { exit('No direct script access allowed'); }
        
        $kode_provinsi = $this->input->post('kode_provinsi');
        $data = $this->Kabupaten_model->get_by_provinsi($kode_provinsi);
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function api_kecamatan_by_kabupaten()
    {
        if (!$this->input->is_ajax_request()) { exit('No direct script access allowed'); }

        $kode_kabupaten = $this->input->post('kode_kabupaten');
        $data = $this->Kecamatan_model->get_by_kabupaten($kode_kabupaten);
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function api_kelurahan_by_kecamatan()
    {
        if (!$this->input->is_ajax_request()) { exit('No direct script access allowed'); }

        $kode_kecamatan = $this->input->post('kode_kecamatan');
        $data = $this->Kelurahan_model->get_by_kecamatan($kode_kecamatan);
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
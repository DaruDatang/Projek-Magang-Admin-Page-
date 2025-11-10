<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kelurahan extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Kelurahan_model', 'Kecamatan_model', 'Kabupaten_model', 'Provinsi_model']);
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
        $kode_kecamatan = $this->input->get('kode_kecamatan');

        $config['base_url'] = base_url('kelurahan');
        $config['total_rows'] = $this->Kelurahan_model->get_count($q, $kode_provinsi, $kode_kabupaten, $kode_kecamatan);
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

        $kelurahan_data = $this->Kelurahan_model->get_paginated($config['per_page'], $page, $q, $sort, $order, $kode_provinsi, $kode_kabupaten, $kode_kecamatan);

        $data = [
            'kelurahan' => $kelurahan_data,
            'links' => $this->pagination->create_links(),
            'q' => $q,
            'sort' => $sort,
            'order' => $order,
            'title' => 'Data Kelurahan',
            'active_menu' => 'kelurahan',
            'total_data' => $config['total_rows'],
            'provinsi_list' => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'kabupaten_list' => $kode_provinsi ? $this->Kabupaten_model->get_by_provinsi($kode_provinsi) : [],
            'kecamatan_list' => $kode_kabupaten ? $this->Kecamatan_model->get_by_kabupaten($kode_kabupaten) : [],
            'selected_provinsi' => $kode_provinsi,
            'selected_kabupaten' => $kode_kabupaten,
            'selected_kecamatan' => $kode_kecamatan
        ];

        $this->load->view('partials/header', $data);
        $this->load->view('kelurahan/index', $data);
        $this->load->view('partials/footer');
    }

    public function add()
    {
        $data = [
            'provinsi' => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'kabupaten' => [], 
            'kecamatan' => [], 
            'mode' => 'add',
            'title' => 'Tambah Kelurahan',
            'active_menu' => 'kelurahan'
        ];

        if ($this->input->post()) {
            $this->Kelurahan_model->insert(
                $this->input->post('nama'),
                $this->input->post('kode_kecamatan')
            );
            redirect('kelurahan');
        }

        $this->load->view('partials/header', $data);
        $this->load->view('kelurahan/form', $data);
        $this->load->view('partials/footer');
    }

    public function edit($kode)
    {
        $item = $this->db->get_where('kelurahan', ['kode' => $kode])->row_array();
        $kec_row = $this->db->get_where('kecamatan', ['kode' => $item['kode_kecamatan']])->row_array();
        $kab_row = $this->db->get_where('kabupaten', ['kode' => isset($kec_row['kode_kabupaten']) ? $kec_row['kode_kabupaten'] : ''])->row_array();
        
        $kode_provinsi = isset($kab_row['kode_provinsi']) ? $kab_row['kode_provinsi'] : null;
        $kode_kabupaten = isset($kec_row['kode_kabupaten']) ? $kec_row['kode_kabupaten'] : null;

        $data = [
            'provinsi' => $this->Provinsi_model->get_all(null, 'nama', 'ASC'),
            'kabupaten' => $kode_provinsi ? $this->db->get_where('kabupaten', ['kode_provinsi' => $kode_provinsi])->result_array() : [],
            'kecamatan' => $kode_kabupaten ? $this->db->get_where('kecamatan', ['kode_kabupaten' => $kode_kabupaten])->result_array() : [],
            'item' => $item,
            'selected_provinsi' => $kode_provinsi,
            'selected_kabupaten' => $kode_kabupaten,
            'selected_kecamatan' => $item['kode_kecamatan'],
            'mode' => 'edit',
            'title' => 'Edit Kelurahan',
            'active_menu' => 'kelurahan'
        ];

        if ($this->input->post()) {
            $this->Kelurahan_model->update(
                $kode,
                $this->input->post('nama'),
                $this->input->post('kode_kecamatan')
            );
            redirect('kelurahan');
        }

        $this->load->view('partials/header', $data);
        $this->load->view('kelurahan/form', $data);
        $this->load->view('partials/footer');
    }

    public function delete($kode)
    {
        $this->Kelurahan_model->delete($kode);
        redirect('kelurahan');
    }

    public function get_kabupaten_by_provinsi()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $kode_provinsi = $this->input->post('kode_provinsi');
        $kabupaten = $this->db->get_where('kabupaten', [
            'kode_provinsi' => $kode_provinsi
        ])->result_array();

        header('Content-Type: application/json');
        echo json_encode($kabupaten);
    }

    public function get_kecamatan_by_kabupaten()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        
        $kode_kabupaten = $this->input->post('kode_kabupaten');
        $kecamatan = $this->db->get_where('kecamatan', [
            'kode_kabupaten' => $kode_kabupaten
        ])->result_array();

        header('Content-Type: application/json');
        echo json_encode($kecamatan);
    }
}
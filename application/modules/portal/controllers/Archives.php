<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Archives extends MX_Controller{
    function __construct(){
        parent::__construct();

        $this->load->model('archives_m', 'archive');
    }

    function index(){
        $this->core->addJs('app/archive.js', true);

        $this->load->view("templates/header");
        $this->load->view('archives/index');
        $this->load->view("templates/footer");
    }

    function get_datatable(){
        $data = $this->archive->getDataTable();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function restore(){
        $data = $this->archive->restoreArchive();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }
}
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_logs extends MX_Controller{
    function __construct(){
        parent::__construct();

        $this->load->model('activity_m', 'activity');
    }

    function index(){
        $this->core->addJs('app/activity.js', true);

        $this->load->view("templates/header");
        $this->load->view('activity/index');
        $this->load->view("templates/footer");
    }

    function get_datatable(){
        $data = $this->activity->getDataTable();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }
}
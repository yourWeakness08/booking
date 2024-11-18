<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Facility extends MX_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('facility_m', 'facility');
    }

    function index(){
        $this->core->addJs('app/facility.js', true);

        $this->load->view("templates/header");
        $this->load->view('facilities/index');
        $this->load->view("templates/footer");
    }

    function get_datatable(){
        $data = $this->facility->getDatatableRequest();
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function create_facility(){
        $data = $this->facility->save();
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function update_facility(){
        $data = $this->facility->update();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function update_facility_status($id){
        $data = $this->facility->update_status($id);
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function archive_facility($id){
        $data = $this->facility->archive($id);
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function get_facility(){
        $data = $this->facility->getFacility();
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function getLegends(){
        $data = $this->facility->getLegendFacility();
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }
}
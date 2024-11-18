<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MX_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('users_m', 'users');
    }

    function index(){
        $this->core->addJs('app/users.js', true);

        $this->load->view("templates/header");
        $this->load->view('users/index');
        $this->load->view("templates/footer");
    }

    function create_user(){
        $data = $this->users->save();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function update_user(){
        $data = $this->users->update();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function archive_user($id){
        $data = $this->users->archive($id);
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function get_datatable(){
        $data = $this->users->getDatatableRequest();
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function get_user($id){
        $data = $this->users->getUser($id);
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function get_users(){
        $data = $this->users->getUsers();
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function suspend_user(){
        $data = $this->users->suspend();
		$this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }
}
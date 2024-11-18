<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MX_Controller{
    function __construct(){
        parent::__construct();

        $this->load->model('login_m', 'login');
    }

    function login(){
        $this->load->view('index');
    }

    function processLogin(){
        $result = array();
        $data = $this->login->processLogin();

        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function logout(){
        session_destroy();
        redirect('/');
    }
}
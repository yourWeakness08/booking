<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MX_Controller{
    function __construct(){
        parent::__construct();

        $this->load->model('dashboard_m', 'event');
        $this->load->model('core_m', 'core');
    }

    function index(){
        $this->core->addJs('app/dashboard.js', true);

        $this->load->view("templates/header");
        $this->load->view('dashboard/index');
        $this->load->view("templates/footer");
    }

    function test(){
        $this->core->addJs('app/dashboard.js', true);
        $this->core->addJs('app/activity.js', true);
        echo $this->core->getStoredJs();
    }

    function get_event(){
        $data = $this->event->getEvent();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function add_event(){
        $data = $this->event->addEvent();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function edit_event(){
        $data = $this->event->editEvent();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function get_datatable(){
        $data = $this->event->getDatatable();
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function get_events($id){
        $data = $this->event->getEvents($id);
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function archive_event($id){
        $data = $this->event->archive($id);
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }

    function test1(){
        // $post = array(
        //     'description' => 1,
        //     'facility' => 4,
        //     'recur_sched' => true,
        //     'frequency' => 'Weekly',
        //     'weekly' => array('Tuesday'),
        //     'daterange' => '2023-09-12 14:00 - 2023-09-26 15:00',
        //     'recur_from' => '2023-09-12 14:00',
        //     'recur_to' => '2023-09-26 15:00',
        //     'meeting_time_type' => '',
        //     'wholeday' => '',
        //     'halfday' => '',
        //     'halfdayindicator' => 'am',
        //     'date_from' => '',
        //     'date_to' => '',
        //     'contact' => 1,
        //     'remarks' => 1
        // );

        $post = array(
            'description' => 1,
            'facility' => 2,
            // 'frequency' => 'Daily',
            'daterange' => '',
            'meeting_time_type' => 'others',
            'wholeday' => '',
            'halfday' => '',
            'halfdayindicator' => 'am',
            'date_from' => '2023-08-17 08:00',
            'date_to' => '2023-08-17 08:30',
            'contact' => 1,
            'remarks' => 1
        );

        $data = $this->event->addEvent1($post);
        $this->output
        ->set_content_type('json')
        ->set_output(json_encode($data));
    }
}
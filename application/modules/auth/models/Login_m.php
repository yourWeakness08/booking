<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Login_m extends CI_Model {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Manila");
    }

    function processLogin(){
        $result = array();
        $post = $this->input->post();
        $username = $post['username'];
        $password = md5($post['password']);

        $this->db->from('tbl_users');
        $this->db->where("username", $username);
        $this->db->where('password', $password);
        $this->db->where('is_active', 1);
        $query = $this->db->get();

        if($query->num_rows() > 0){
            $row = $query->row();
            $data = array(
                'id' => $row->id,
                'name' => $row->firstname . ' ' . $row->lastname,
                'email' => $row->email,
                'role_id' => $row->role_id,
                'telegram' => $row->telegram_chat_id,
                'user_logged' => true
            );

            $this->session->set_userdata($data);

            $result['state'] = 'success';
            $result['msg'] = 'Loggin in! Please wait...';
        }else{
            $result['state'] = 'error';
            $result['msg'] = "Credentials doesn't match!";
        }

        return $result;

    }
}
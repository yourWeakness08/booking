<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_m extends CI_Model {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Manila");
        $this->load->model('Core_m', 'core');
    }

    function getDatatableRequest(){
        $request = $this->input->post();
        $search = (isset($request["search"]['value']) && $request["search"]['value']) ? $request["search"]['value'] : false;
        $limit = (isset($request["length"]) && $request["length"]) ? $request["length"] : 10;
        $offset = (isset($request["start"]) && $request["start"]) ? $request["start"] : 0;
        $sortBy = (isset($request["columns"]) && $request["columns"]) ? $request["columns"] : 1;
        $sortOrder = (isset($request["order"]) && $request["order"]) ? $request["order"] : null;

        $rowData = $this->tableRequestData($search, $limit, $offset, $sortBy, $sortOrder);
        $rowCount = $this->tableRequestCount($search);

        $response = array(
            "data" => $rowData,
            "recordsTotal" => $rowCount,
            "recordsFiltered" => $rowCount
        );
        return $response;
    }

    function tableRequestData($search = null, $limit, $offset, $sortBy, $sortOrder){
        $arrData = array();
        $result = array();
        $filterFields = array("firstname", "lastname", "email", 'username');

        $this->db->select("*, CONCAT(firstname, ' ', lastname) as name");
        $this->db->from('tbl_users');
        $this->db->where('is_archived', 0);

        if($limit != -1){
            $this->db->limit($limit, $offset);
        }

        if($search){
            foreach ($filterFields as $key => $field) {
                if ($key == 0) {
                    $this->db->like($field, $search, "both");
                } else {
                    $this->db->or_like($field, $search, "both");
                }
            }
        }

        $i = $sortOrder[0]['column'];
        $this->db->order_by($sortBy[$i]['data'], $sortOrder[0]['dir']);
        $query = $this->db->get();

        if($query->num_rows() > 0){
            foreach($query->result() as $key => $rs){
                $rs->name = ucwords($rs->name);
                $rs->role = ($rs->role_id == 1) ? 'Admin' : ($rs->role_id == 0 ? 'Superadmin' : "Guest");
                $arrData[$key] = $rs;
            }
            foreach ($arrData as $k => $v) {
                $result[] = $v;
            }

            return $result;
        }

        return $result;
    }

    function tableRequestCount($search = null){
        $this->db->from('tbl_users');
        $this->db->where('is_archived', 0);

        $filterFields = array("firstname", "lastname", "email", 'username');
        if($search){
            foreach ($filterFields as $key => $field) {
                if ($key == 0) {
                    $this->db->like($field, $search, "both");
                } else {
                    $this->db->or_like($field, $search, "both");
                }
            }
        }

        $query = $this->db->get();
        return $query->num_rows();

    }

    function save(){
        $result = array();
        $post = $this->input->post();
        $date = date('Y-m-d');
        $user_id = $_SESSION['id'];
        $data = array(
            "firstname" => ucwords($post['fname']),
            "lastname" => ucwords($post['lname']),
            "email" => $post['email'],
            "username" => $post['username'],
            "password" => md5($post['password']),
            "original_password" => $post['password'],
            "role_id" => $post['role'],
            "telegram_chat_id" => $post['telegram'],
            "created_date" => $date,
        );

        $check_if_exists = $this->checkUser($post['fname'], $post['lname'], $post['email']);
        if($check_if_exists){
            $query = $this->db->insert('tbl_users', $data);

            if($query){
                $id = $this->db->insert_id();
                $result['state'] = true;
                $result['msg'] = 'User Successfully Added!';
                $this->core->logs($user_id, $id, 'users', 'User successfully added user '.ucwords($post['fname']). ' '.ucwords($post['lname']), 'success');
            }else{
                $id = $this->db->insert_id();
                $result['state'] = false;
                $result['msg'] = 'Failed to save user!';
                $this->core->logs($user_id, $id, 'users', 'User failed to add user '.ucwords($post['fname']). ' '.ucwords($post['lname']), 'error');
            }
        }else{
            $result['state'] = false;
            $result['msg'] = 'User already exists!';
        }

        return $result;
    }

    function archive($id){
        $result = array();
        $user_id = $_SESSION['id'];

        $data = array(
            'archived_by' => $user_id,
            'is_archived' => 1,
            'archived_dt' => date('Y-m-d H:i:s')
        );

        $this->db->where("id", $id);
        $query = $this->db->update('tbl_users', $data);

        if($query){
            $result['state'] = true;
            $result['msg'] = 'Archived Sucessfully!';
            $this->core->logs($user_id, $id, 'users', 'User successfully archive item!', 'success');
        }else{
            $result['state'] = false;
            $result['msg'] = 'Failed to archive user!';
            $this->core->logs($user_id, $id, 'users', 'User failed to restore archive item', 'error');
        }

        return $result;
    }

    function getUser($id){
        $this->db->from('tbl_users');
        $this->db->where('id', $id);
        $query = $this->db->get();

        return $query->row();
    }

    function update(){
        $result = array();
        $post = $this->input->post();
        $user_id = $_SESSION['id'];

        if(!empty($post['password']) && isset($post['password'])){
            $data = array(
                'firstname' => ucwords($post['fname']),
                'lastname' => ucwords($post['lname']),
                'username' => $post['username'],
                'password' => md5($post['password']),
                'original_password' => $post['password'],
                'email' => $post['email'],
                'role_id' => $post['role'],
                'telegram_chat_id' => $post['telegram']
            );
        }else{
            $data = array(
                'firstname' => ucwords($post['fname']),
                'lastname' => ucwords($post['lname']),
                'username' => $post['username'],
                'email' => $post['email'],
                'role_id' => $post['role'],
                'telegram_chat_id' => $post['telegram']
            );
        }

        $this->db->where('id', $post['id']);
        $query = $this->db->update('tbl_users', $data);

        if($query){
            $result['state'] = true;
            $result['msg'] = 'User Successfully Updated!';
            $this->core->logs($user_id, $post['id'], 'users', 'User successfully update user '.ucwords($post['fname']). ' '.ucwords($post['lname']), 'success');
        }else{
            $result['state'] = false;
            $result['msg'] = 'Failed to update User!';
            $this->core->logs($user_id, $post['id'], 'users', 'User failed to update user '.ucwords($post['fname']). ' '.ucwords($post['lname']), 'error');
        }

        return $result;
    }

    function getUsers(){
        $get = $this->input->get();
        $filterFields = array("firstname", "lastname");

        $sql = "id as id, UPPER(CONCAT(firstname, ' ', lastname)) as text";
        $this->db->select($sql);
        $this->db->from('tbl_users');
        
        if(isset($get['term'])){
            foreach ($filterFields as $key => $field) {
                if ($key == 0) {
                    $this->db->like($field, $get['term'], "both");
                }
            }
        }
        
        $this->db->where("is_archived", 0);
        $this->db->where("role_id !=", 0);
        $query = $this->db->get();

        return $query->result();
    }

    function checkUser($firstname, $lastname, $email){
        $data = array(
            'firstname' => ucwords($firstname),
            'lastname' => ucwords($lastname),
            'email' => $email
        );

        $query = $this->db->get_where('tbl_users', $data);
        if($query->num_rows() > 0){
            return false;
        }else{
            return true;
        }
    }

    function suspend(){
        $post = $this->input->post();
        $user_id = $_SESSION['id'];
        $result = array();

        $status = $post['type'] == 'active' ? 0 : 1;
        $data = array(
            'is_active' => $status
        );
        
        $this->db->where('id', $post['id']);
        $query = $this->db->update('tbl_users', $data);

        if($query){
            $_status = $post['type'] == 'active' ? 'Suspended' : 'Reactivated';
            $result['state'] = true;
            $result['msg'] = 'User Successfully '.$_status.'!';
            $this->core->logs($user_id, $post['id'], 'users', 'User successfully `'.$_status.'` user with userid of `'.$post['id'].'`.', 'success');
        }else{
            $_status = $post['type'] == 'active' ? 'Suspend' : 'Reactivate';
            $result['state'] = false;
            $result['msg'] = 'Failed to '.$_status.' User!';
            $this->core->logs($user_id, $post['id'], 'users', 'User failed to `'.$_status.'` user with userid of `'.$post['id'].'`.', 'error');
        }

        return $result;
    }

}
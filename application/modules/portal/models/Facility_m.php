<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facility_m extends CI_Model {

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
        $filterFields = array("name", "status", "facility_color");

        $this->db->from('tbl_facility');
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
                $rs->name = $rs->name;
                $rs->status = $rs->status;
                $rs->color = $rs->facility_color;
                $arrData[$key] = $rs;
            }
            foreach ($arrData as $k => $v) {
                $result[] = $v;
            }
        }

        return $result;
    }

    function tableRequestCount($search = null){
        $this->db->from('tbl_facility');
        $this->db->where('is_archived', 0);

        $filterFields = array("name", "status", "facility_color");
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
        $user_id = $_SESSION['id'];
        $post = $this->input->post();
        $date = date('Y-m-d');
        $result = array();

        $this->db->from('tbl_facility');
        $this->db->where('name', ucwords($post['name']));
        $check = $this->db->get();

        if($check->num_rows() > 0){
            $result['state'] = false;
            $result['msg'] = 'Facility already exist';
        }else{
            $data = array(
                'name' => ucwords($post['name']),
                'facility_color' => $post['facility_color'],
                'created_by' => $user_id,
                'created_dt' => $date
            );
    
            $query = $this->db->insert('tbl_facility', $data);
            $id = $this->db->insert_id();
            if($query){
                $result['state'] = true;
                $result['msg'] = 'Facility Succefully Added!';
                $this->core->logs($user_id, $id, 'facility', 'User successfully added facility '. $post['name'], 'success');
            }else{
                $result['state'] = false;
                $result['msg'] = 'Failed to save facility!';
                $this->core->logs($user_id, $id, 'facility', 'User failed to add facility '. $post['name'], 'error');
            }
        }

        return $result;
    }

    function update(){
        $result = array();
        $post = $this->input->post();
        $user_id = $_SESSION['id'];

        $data = array(
            'name' => ucwords($post['name']),
            'facility_color' => $post['facility_color']
        );

        $this->db->where("id", $post['id']);
        $query = $this->db->update('tbl_facility', $data);

        if($query){
            $result['state'] = true;
            $result['msg'] = 'Facility Succefully Updated!';
            $this->core->logs($user_id, $post['id'], 'facility', 'User successfully updated facility '. $post['name'], 'success');
        }else{
            $result['state'] = false;
            $result['msg'] = 'Failed to update facility!';
            $this->core->logs($user_id, $post['id'], 'facility', 'User failed to update facility '. $post['name'], 'error');
        }

        return $result;
    }

    function update_status($id){
        $result = array();
        $post = $this->input->post();
        $user_id = $_SESSION['id'];

        $data = array(
            'status' => $post['status']
        );

        $this->db->where("id", $id);
        $query = $this->db->update('tbl_facility', $data);

        if($query){
            $result['state'] = true;
            $result['msg'] = 'Status Updated!';
            $this->core->logs($user_id, $id, 'facility', 'User updated facility status!', 'success');
        }else{
            $result['state'] = false;
            $result['msg'] = 'Failed to update status!';
            $this->core->logs($user_id, $id, 'facility', 'User failed to update facility status!', 'error');
        }

        return $result;
    }

    function archive($id){
        $result = array();
        $user_id = $_SESSION['id'];

        $data = array(
            'is_archived' => 1,
            'archived_by' => $user_id,
            'archived_dt' => date('Y-m-d H:i:s')
        );

        $this->db->where("id", $id);
        $query = $this->db->update('tbl_facility', $data);

        if($query){
            $result['state'] = true;
            $result['msg'] = 'Archived Sucessfully!';
            $this->core->logs($user_id, $id, 'facility', 'User successfully archive facility!', 'success');
        }else{
            $result['state'] = false;
            $result['msg'] = 'Failed to archive facility!';
            $this->core->logs($user_id, $id, 'facility', 'User failed to archive facility!', 'error');
        }

        return $result;
    }

    function getFacility(){
        $sql = "id as id, name as text";
        $this->db->select($sql);
        $this->db->from('tbl_facility');
        $this->db->where("is_archived", 0);
        $this->db->where('status', 'Active');
        $query = $this->db->get();

        return $query->result();
    }

    function getLegendFacility(){
        $sql = "facility_color as color, name";
        $this->db->select($sql);
        $this->db->from('tbl_facility');
        $this->db->where("is_archived", 0);
        $this->db->where('status', 'Active');
        $query = $this->db->get();

        return $query->result();
    }


}
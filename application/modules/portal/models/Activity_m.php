<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_m extends CI_Model {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Manila");

        $this->load->model('Core_m', 'core');
    }

    function getDataTable(){
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
        $filterFields = array("tbl_name", "firstname", "lastname", "message");

        $this->db->from('tbl_activity_logs as a');
        $this->db->join('tbl_users as b', 'b.id = a.user_id', 'left');

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
                $rs->name = ucwords($rs->firstname). ' ' .ucwords($rs->lastname);
                $rs->tbl_name = ucwords($rs->tbl_name);
                $rs->type = $rs->type;
                $rs->message = $rs->message;
                $rs->added_dt = date('F, d Y h:i A', strtotime($rs->added_dt));

                $arrData[$key] = $rs;
            }
            foreach ($arrData as $k => $v) {
                $result[] = $v;
            }
        }

        return $result;
    }

    function tableRequestCount($search = null){
        $this->db->from('tbl_activity_logs as a');
        $this->db->join('tbl_users as b', 'b.id = a.user_id', 'left');

        $filterFields = array("tbl_name", "firstname", "lastname", "message");
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
}
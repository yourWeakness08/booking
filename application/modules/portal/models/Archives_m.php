<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Archives_m extends CI_Model {

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

        if($search){
            $like = " AND name LIKE '%$search%'";
        }else{
            $like = '';
        }

        if($limit != -1){
            $_limit = " LIMIT ".$limit." OFFSET ".$offset;
        }

        $i = $sortOrder[0]['column'];
        // var_dump($i);
        $orderBy = 'ORDER BY '.$sortBy[$i]['data'].' '.$sortOrder[0]['dir'];
        // var_dump($orderBy);
        // $this->db->order_by($sortBy[$i]['data'], $sortOrder[0]['dir']);

        $query = $this->db->query(
            "SELECT * FROM ( 
                SELECT CONCAT(a.firstname, ' ', a.lastname) as name, CONCAT(a1.firstname, ' ', a1.lastname) as archived_user, DATE_FORMAT(a.archived_dt, '%b, %d %Y %h:%i %p') as date_archived, 'tbl_users' as tblname, a.is_archived as is_archived, a.id as id FROM tbl_users as a
                LEFT JOIN
                tbl_users as a1 ON a1.id = a.archived_by
            UNION ALL
                SELECT b.name, CONCAT(a2.firstname, ' ', a2.lastname) as archived_user, DATE_FORMAT(b.archived_dt, '%b, %d %Y %h:%i %p') as date_archived, 'tbl_facility' as tblname, b.is_archived as is_archived, b.id as id FROM tbl_facility as b
                LEFT JOIN
                tbl_users as a2 ON a2.id = b.archived_by
            UNION ALL
                SELECT c.description as name, CONCAT(a3.firstname, ' ', a3.lastname) as archived_user, DATE_FORMAT(c.archived_dt, '%b, %d %Y %h:%i %p') as date_archived, 'tbl_events' as tblname, c.is_archived as is_archived, c.id as id FROM tbl_events as c
                LEFT JOIN
                tbl_users as a3 ON a3.id = c.archived_by
            ) as table1 WHERE is_archived = 1 $like $orderBy $_limit");

        if($search){
            $filterFields1 = array("firstname", "lastname");
            foreach ($filterFields1 as $key => $field1) {
                if ($key == 0) {
                    $this->db->like($field1, $search, "both");
                } else {
                    $this->db->or_like($field1, $search, "both");
                }   
            }
        }
        
        $q = $query->result_array();

        return $q;
    }

    function tableRequestCount($search = null){
        $arrData = array();
        $result = array();

        if($search){
            $like = " AND name LIKE '%$search%'";
        }else{
            $like = '';
        }

        $query = $this->db->query(
            "SELECT * FROM ( 
                SELECT CONCAT(a.firstname, ' ', a.lastname) as name, CONCAT(a1.firstname, ' ', a1.lastname) as archived_user, DATE_FORMAT(a.archived_dt, '%b, %d %Y %h:%i %p') as date_archived, 'tbl_users' as tblname, a.is_archived as is_archived FROM tbl_users as a
                LEFT JOIN
                tbl_users as a1 ON a1.id = a.archived_by
            UNION ALL
                SELECT b.name, CONCAT(a2.firstname, ' ', a2.lastname) as archived_user, DATE_FORMAT(b.archived_dt, '%b, %d %Y %h:%i %p') as date_archived, 'tbl_facility' as tblname, b.is_archived as is_archived FROM tbl_facility as b
                LEFT JOIN
                tbl_users as a2 ON a2.id = b.archived_by
            UNION ALL
                SELECT c.description as name, CONCAT(a3.firstname, ' ', a3.lastname) as archived_user, DATE_FORMAT(c.archived_dt, '%b, %d %Y %h:%i %p') as date_archived, 'tbl_events' as tblname, c.is_archived as is_archived FROM tbl_events as c
                LEFT JOIN
                tbl_users as a3 ON a3.id = c.archived_by
            ) as table1 WHERE is_archived = 1 $like");

        if($search){
            $filterFields1 = array("firstname", "lastname");
            foreach ($filterFields1 as $key => $field1) {
                if ($key == 0) {
                    $this->db->like($field1, $search, "both");
                } else {
                    $this->db->or_like($field1, $search, "both");
                }   
            }
        }
        
        $q = $query->num_rows();

        return $q;
    }

    function restoreArchive(){
        $result = array();
        $post = $this->input->post();
        $user_id = $_SESSION['id'];
        $data = array(
            'is_archived' => 0
        );

        $this->db->where('id', $post['id']);
        $query = $this->db->update($post['tbl'], $data);

        if($query){
            $result['state'] = true;
            $result['msg'] = 'Archived item Restored!';
            $this->core->logs($user_id, $post['id'], trim($post['tbl'], 'tbl_'), 'User successfully restored archive item!', 'success');
        }else{
            $result['state'] = false;
            $result['msg'] = 'Failed to restore archived item!';
            $this->core->logs($user_id, $post['id'], trim('tbl_', $post['tbl']), 'User failed to restore archive item', 'error');
        }

        return $result;
    }
}
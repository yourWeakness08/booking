<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Core_m extends CI_Model {
    private $jsList = array(), $isFooterJs = array();

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Manila");
    }

    function logs($user_id, $tbl_id, $tbl_name, $msg, $type){
        $data = array(
            'tbl_name' => $tbl_name,
            'tbl_id' => $tbl_id,
            'user_id' => $user_id,
            'message' => $msg,
            'type' => $type,
            'added_dt' => date('Y-m-d H:i:s')
        );

        $query = $this->db->insert('tbl_activity_logs', $data);
        if($query){
            return 1;
        }else{
            return 0;
        }
    }

    function addJs($path = null, $footer = false){
        $arr = array();
        if($path){
            $this->jsList[] = $path;
            $this->isFooterJs[] = $footer;
        }

        return $this;
    }

    function getStoredJs(){
        $html = "";
        if($this->jsList){
            foreach($this->jsList as $key => $row){
                $filePath = realpath("./assets/js/{$row}");
                if (file_exists($filePath)) {
                    $currentUrl = base_url("assets/js/{$row}");
                    if ($this->isFooterJs[$key] == true) {
                        $html .= "<script type='text/javascript' src='{$currentUrl}'></script>\n";
                    }
                }
            }
        }
        return $html;
    }
}
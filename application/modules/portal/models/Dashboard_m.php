<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_m extends CI_Model {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Manila");
        $this->load->model('Core_m', 'core');
    }

    function getEvent(){
        $post = $this->input->get();

        $search = (isset($post['filter']) && $post['filter'] != 'null') ? $post['filter'] : null;
        $user = (isset($post['filterUser']) && $post['filterUser'] != 'null') ? $post['filterUser'] : null;
        $facility = (isset($post['filterFacility']) && $post['filterFacility'] != 'null') ? $post['filterFacility'] : null;
        $meeting = (isset($post['filterMeeting']) && $post['filterMeeting'] != 'null') ? $post['filterMeeting'] : null;
        $start = (isset($post['start']) && $post['start'] != 'null') ? date('Y-m-d H:i', strtotime($post['start'])) : null;
        $end = (isset($post['end']) && $post['end'] != 'null') ? date('Y-m-d H:i', strtotime($post['end'])) : null;
        
        
        $nonRecur = $this->getNonRecurring($search, $user, $facility, $meeting, $start, $end);
        $recur = $this->getRecurring($search, $user, $facility, $meeting, $start, $end);

        return array_merge($nonRecur, $recur);
    }

    function getNonRecurring($search, $user, $facility, $meeting, $start, $end){
        
        $result = array();
        $arrdata = array();

        $sql = "a.description as title, a.remarks as description, a.date_from as start_date, a.date_to as end_date, a.contact_number, a.id as event_id, a.meeting_time_type as meeting_type, a.recurring_schedule as recurring, a.recur_on as recur_on, ";
        $sql .= "b.firstname, b.lastname, b.id as user_id, b.role_id as role, ";
        $sql .= "c.name as facility_name, c.id as facility_id, c.facility_color as eventBackgroundColor";
        $this->db->select($sql);
        $this->db->from('tbl_events as a');
        $this->db->join('tbl_users as  b', 'b.id = a.reservation_from', 'left');
        $this->db->join('tbl_facility as  c', 'c.id = a.facility', 'left');
        $this->db->where('a.recurring_schedule', 0);
        $this->db->where('a.is_archived', 0);
        $this->db->where('a.date_from >= ', $start);
        $this->db->where('a.date_to <= ', $end);

        if($search){
            $this->db->like('description', ucwords($search), 'both');
            $this->db->or_like('reference_no', ucwords($search), 'both');
        }

        if($user){
            $this->db->where('reservation_from', $user);
        }

        if($facility){
            $this->db->where('facility', $facility);
        }

        if($meeting){
            $this->db->where('meeting_time_type', $meeting);
        }

        $this->db->group_by('a.id');
        $query = $this->db->get();

        if($query->num_rows() > 0){
            foreach ($query->result() as $key => $rs) {
                $rs->title = ucfirst($rs->title);
                $rs->description = ucfirst($rs->description);
                $rs->fullname = ucwords($rs->firstname . ' ' . $rs->lastname);
                $rs->startDate = date('Y-m-d', strtotime($rs->start_date));
                $rs->endDate = date('Y-m-d', strtotime($rs->end_date));
                $rs->start = date("Y-m-d H:i", strtotime($rs->start_date));
                $rs->end = date("Y-m-d H:i", strtotime($rs->end_date));
                $rs->meridiem = date('a', strtotime($rs->start_date));
                $rs->backgroundColor = $rs->eventBackgroundColor;
                $rs->date_range = '';
                $rs->frequency = '';
                $rs->textColor = '#FFFF';
                
                $arrData[$key] = $rs;
            }

            foreach ($arrData as $k => $v) {
                $result[] = $v;
            }
        }

        return $result;
    }

    function getRecurring($search, $user, $facility, $meeting, $startDate, $endDate){
        $result = array();
        $arrData = array();
        $recurring_date = array();
        $frequency = array();

        $sql = "a.description as title, a.remarks as description, a.date_from as start_date, a.date_to as end_date, a.contact_number, a.recurring_schedule, a.frequency, a.id as event_id, a.meeting_time_type as meeting_type, a.recurring_schedule as recurring, a.recur_on as recur_on, ";
        $sql .= "b.firstname, b.lastname, b.id as user_id, b.role_id as role, ";
        $sql .= "c.name as facility_name, c.id as facility_id, c.facility_color as backgroundColor";
        $this->db->select($sql);
        $this->db->from('tbl_events as a');
        $this->db->join('tbl_users as  b', 'b.id = a.reservation_from', 'left');
        $this->db->join('tbl_facility as  c', 'c.id = a.facility', 'left');
        $this->db->where('a.recurring_schedule', 1);
        $this->db->where('a.is_archived', 0);

        if($search){
            $this->db->like('description', ucwords($search), 'both');
            $this->db->or_like('reference_no', ucwords($search), 'both');
        }

        if($user){
            $this->db->where('reservation_from', $user);
        }

        if($facility){
            $this->db->where('facility', $facility);
        }

        if($meeting){
            $this->db->where('meeting_time_type', $meeting);
        }

        $this->db->group_by('a.id');
        $query = $this->db->get();

        if($query->num_rows() > 0){
            $count = 0;
            foreach ($query->result() as $key => $rs) {
                $tempResource = unserialize($rs->frequency);
                $start = date('Y-m-d', strtotime($rs->start_date));
                $end = date('Y-m-d', strtotime($rs->end_date));

                $starttime = date('H:i', strtotime($rs->start_date));
                $endtime = date('H:i', strtotime($rs->end_date));

                $recursive = $this->getReccurringSched($tempResource, $start, $end);
                foreach($recursive as $x => $row){
                    
                    $start = date('Y-m-d H:i', strtotime($row . ' ' . $starttime));
                    $end = date('Y-m-d H:i', strtotime($row . ' ' . $endtime));
                    
                    if($startDate <= $start && $endDate >= $end){
                        $recurring_date = array(
                            'title' => ucfirst($rs->title), 
                            'description' => ucfirst($rs->description), 
                            'start' => $start, 
                            'end' => $end, 
                            'contact_number' => $rs->contact_number,
                            'fullname' => ucwords($rs->firstname . ' ' . $rs->lastname),
                            'date_from' => ($rs->start_date),
                            'date_to' => ($rs->end_date),
                            'event_id' => $rs->event_id,
                            'role' => $rs->role,
                            'recurring' => $rs->recurring,
                            'meeting_type' => $rs->meeting_type,
                            'recur_on' => $rs->recur_on,
                            'frequency' => $tempResource,
                            'facility_id' => $rs->facility_id,
                            'facility_name' => $rs->facility_name,
                            'date_range' => date('Y-m-d H:i', strtotime($rs->start_date)) . ' - ' . date('Y-m-d H:i', strtotime($rs->end_date)),
                            'user_id' => $rs->user_id,
                            'backgroundColor' => $rs->backgroundColor,
                            'textColor' => '#FFFF',
                            'startDate' => date('Y-m-d', strtotime($rs->start_date)),
                            'endDate' => date('Y-m-d', strtotime($rs->end_date)),
                        );
    
                        $arrData[] = $recurring_date;
                    }
                }
            }

            foreach ($arrData as $k => $v) {
                $result[] = $v;
            }
        }

        return $result;
    }

    function addEvent(){
        $result = array();
        $post = $this->input->post();
        $userId = $_SESSION['id'];
        $date = date('Y-m-d H:i:s');
        $date_start = null;
        $date_end = null;
        $recur_sched = 0;
        $frequency = null;
        $recur_on = null;
        $time_start = null;
        $time_end = null;
        $check_if_recurring = 1;
        $_check_if_recurring = array();
        $meeting_type = null;
        $ref = 'ER'.date('YmdHis');

        if($post['meeting_time_type'] == 'whole day'){
            $tempWeek = strtolower(date('l', strtotime($post['wholeday'])));
            $time_start = '08:00';
            $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';

            $date_start = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 08:00'));
            $date_end = date('Y-m-d H:i', strtotime($post['wholeday'] . ' '.$time_end));
            
            $meeting_type = 'whole day';
        }else if($post['meeting_time_type'] == 'half day'){
            if($post['halfdayindicator'] == 'am'){
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 08:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 12:00'));
                $time_start = '08:00';
                $time_end = '12:00';
            }else{
                $tempWeek = strtolower(date('l', strtotime($post['halfday'])));

                $time_start = '13:00';
                $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';

                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 13:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' '.$time_end));
                
            }
            $meeting_type = 'half day';
        }else{
            $tempWeek = strtolower(date('l', strtotime($post['date_to'])));
            $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';
            $get_time = date('H:i', strtotime($post['date_to']));
            $date_start = $post['date_from'];

            $date_end =$post['date_to'];
            $meeting_type = 'others';
        }

        if(isset($post['recur_sched']) && $post['recur_sched']){
            $recur_sched = 1;
            $get_time = date('H:i', strtotime($post['recur_to']));

            if($post['frequency'] == 'Daily'){
                $recur_start = new DateTime(date_create($post['recur_from'])->format('Y-m-d'));
                $recur_end = new DateTime(date_create($post['recur_to'])->format('Y-m-d'));
                $interval = $recur_start->diff($recur_end);

                $tempDates = array();
                for($i = 0; $i <= $interval->d; $i++){
                    $val = ucfirst(date('l', strtotime(date("Y-m-d", strtotime($post['recur_from']."$i days")))));
                    array_push($tempDates, $val);
                }
                $frequency = serialize($tempDates);
                
                $recur_on = 'Daily';
            }else{
                $frequency = serialize($post['weekly']);
                $recur_on = 'Weekly';
            }

            $date_start = $post['recur_from'];
            $date_end = $post['recur_to'];
            $meeting_type = 'recurring';
        }

        $data = array(
            'reference_no' => $ref,
            'reservation_from' => $userId,
            'description' => ucwords($post['description']),
            'recurring_schedule' => $recur_sched,
            'recur_on' => $recur_on,
            'frequency' => $frequency,
            'facility' => $post['facility'],
            'meeting_time_type' => $meeting_type,
            'date_from' => $date_start,
            'date_to' => $date_end,
            'contact_number' => $post['contact'],
            'remarks' => $post['remarks'],
            'created_by' => $userId,
            'created_dt' => $date
        );

        $dateonly_start = date("Y-m-d", strtotime($date_start));
        $dateonly_end = date("Y-m-d", strtotime($date_end));

        $this->db->from('tbl_events');
        $this->db->group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from <=', $dateonly_start);
        $this->db->where('date_to >=', $dateonly_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from >=', $dateonly_start);
        $this->db->where('date_to <=', $dateonly_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();

        $q = $this->db->get();

        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                if(isset($post['recur_sched']) && $post['recur_sched']){
                    if($row->frequency){                        
                        if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));

                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);

                            foreach($recursive as $recurred){
                                if(in_array($recurred, $on_recursive)){
                                    $to_check_end = $this->checkEndDay($recurred, $date_end);

                                    if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }else{
                                        $_check_if_recurring[] = 1;
                                    }
                                }
                            }
                        }else{
                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            
                            if(array_intersect($on_recursive, $recursive)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $start_date = date('Y-m-d', strtotime($row->date_from));
                                $end_date = date('Y-m-d', strtotime($row->date_to));
                                
                                $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                
                                $tempDay = strtolower(date('l', strtotime($end_date)));
                                $temp_get_time = date('H:i', strtotime($date_end));
                                $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
                                if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
                                    $get_time = date('H:i', strtotime($row->date_to));

                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                    $to_check_end = date('Y-m-d H:i', strtotime($date_end));

                                    if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                        if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }else{
                                        $_check_if_recurring[] = 1;
                                    }
                                }else{
                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                    $to_check_end = date('Y-m-d H:i', strtotime($date_end));

                                    if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                        if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }else{
                                        $_check_if_recurring[] = 1;
                                    }
                                }
                            }
                        }
                    }else{
                        $start_date = date('Y-m-d', strtotime($row->date_from));
                        $end_date = date('Y-m-d', strtotime($row->date_to));
                        
                        $to_check_start_date = date('Y-m-d', strtotime($date_start));
                        $to_check_end_date = date('Y-m-d', strtotime($date_end));
                        
                        $tempDay = strtolower(date('l', strtotime($date_end)));
                        $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
                        if($start_date == $to_check_start_date){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));

                            if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }else{
                                $_check_if_recurring[] = 1;
                            }
                        }else{
                            $_check_if_recurring[] = 0;
                        }
                    }
                }else{
                    $weekday = date('l', strtotime($date_start));
                    $_weekday = date('l', strtotime($row->date_from));
                    
                    if($row->frequency){
                        if(in_array($weekday, unserialize($row->frequency))){
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            foreach($recursive as $_recurred){
                                if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                    $start_time = date('H:i', strtotime($row->date_from));
                                    $end_time = date('H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('H:i', strtotime($date_start));
                                    $to_check_end = date('H:i', strtotime($date_end));

                                    if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }else{
                                        $_check_if_recurring[] = 1;
                                    }
                                }
                            }
                        }else{
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));

                            if(date('Y-m-d', strtotime($row->date_from)) == date('Y-m-d', strtotime($date_start))){
                                if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }else{
                                    $_check_if_recurring[] = 1;
                                }
                            }
                        }
                    }else{
                        if($weekday == $_weekday){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
    
                            if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }else{
                                $_check_if_recurring[] = 1;
                            }
                        }
                    }
                }
            }

            if(in_array(1, $_check_if_recurring)){
                $result['state'] = false;
                $result['msg'] = 'Facility unavailable!';
            }else{
                $query = $this->db->insert('tbl_events', $data);
                $id = $this->db->insert_id();
                if($query){
                    $result['state'] = true;
                    $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                    $result['msg'] = 'Event Sucessfully Added!';
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to add event!';
                    $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                }
            }
        }else{
            $_frq = unserialize($frequency);
            $this->db->from('tbl_events');
            $this->db->where('facility', $post['facility']);
            $this->db->where('date_from >=', $date_start);
            $this->db->where('is_archived', 0);
            $qs = $this->db->get();

            if($qs->num_rows() > 0){
                foreach($qs->result() as $row){
                    if(isset($post['recur_sched']) && $post['recur_sched']){
                        if($row->frequency){                        
                            if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
    
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
    
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
    
                                foreach($recursive as $recurred){
                                    if(in_array($recurred, $on_recursive)){
                                        if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                    $_check_if_recurring[] = 1;
                                                }else{
                                                    $_check_if_recurring[] = 0;
                                                }
                                            }
                                        }else{
                                            $_check_if_recurring[] = 1;
                                        }
                                    }
                                }
                                
                            }else{
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                
                                if(array_intersect($on_recursive, $recursive)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $start_date = date('Y-m-d', strtotime($row->date_from));
                                    $end_date = date('Y-m-d', strtotime($row->date_to));
                                    
                                    $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                    $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                    
                                    if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                        
                                        if($row->date_from <= $date_start && $date_start >= $row->date_to){
                                            if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }else{
                                            $_check_if_recurring[] = 1;
                                        }
                                    }else{
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
    
                                        if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                            if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }else{
                                            $_check_if_recurring[] = 1;
                                        }
                                    }
                                }
                            }
    
                        }else{
                            $start_date = date('Y-m-d', strtotime($row->date_from));
                            $end_date = date('Y-m-d', strtotime($row->date_to));
                            
                            $to_check_start_date = date('Y-m-d', strtotime($date_start));
                            $to_check_end_date = date('Y-m-d', strtotime($date_end));
                            
                            
                            if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
                                
                                if($row->date_from <= $date_start && $date_start >= $row->date_to){
                                    if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }else{
                                    $_check_if_recurring[] = 1;
                                }
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }
                    }else{
    
                        $weekday = date('l', strtotime($date_start));
                        $_weekday = date('l', strtotime($row->date_from));
                        
                        if($row->frequency){
                            if(in_array($weekday, unserialize($row->frequency))){
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                foreach($recursive as $_recurred){
                                    if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                        $start_time = date('H:i', strtotime($row->date_from));
                                        $end_time = date('H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('H:i', strtotime($date_start));
                                        $to_check_end = date('H:i', strtotime($date_end));
    
                                        if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                    $_check_if_recurring[] = 1;
                                                }else{
                                                    $_check_if_recurring[] = 0;
                                                }
                                            }
                                        }else{
                                            $_check_if_recurring[] = 1;
                                        }
                                    }
                                }
                            }
                        }else{
                            if($weekday == $_weekday){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }else{
                                    $_check_if_recurring[] = 1;
                                }
                            }
                        }
                    }
                }

                if(in_array(1, $_check_if_recurring)){
                    $result['state'] = false;
                    $result['msg'] = 'Facility unavailable!';
                }else{
                    $query = $this->db->insert('tbl_events', $data);
                    $id = $this->db->insert_id();
                    if($query){
                        $result['state'] = true;
                        $result['msg'] = 'Event Sucessfully Added!';
                        $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']), 'success');
                    }else{
                        $result['state'] = false;
                        $result['msg'] = 'Failed to add event!';
                        $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']), 'error');
                    }
                }
            }else{
                $query = $this->db->insert('tbl_events', $data);
                $id = $this->db->insert_id();
                if($query){
                    $result['state'] = true;
                    $result['msg'] = 'Event Sucessfully Added!';
                    $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']), 'success');
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to add event!';
                    $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']), 'error');
                }
            }
        }

        return $result;
    }

    function addEventv3(){
        $result = array();
        $post = $this->input->post();
        $userId = $_SESSION['id'];
        $date = date('Y-m-d H:i:s');
        $date_start = null;
        $date_end = null;
        $recur_sched = 0;
        $frequency = null;
        $recur_on = null;
        $time_start = null;
        $time_end = null;
        $check_if_recurring = 1;
        $_check_if_recurring = array();
        $meeting_type = null;
        $ref = 'ER'.date('YmdHis');

        if($post['meeting_time_type'] == 'whole day'){
            $tempWeek = strtolower(date('l', strtotime($post['wholeday'])));
            $time_start = '08:00';
            $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';

            $date_start = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 08:00'));
            $date_end = date('Y-m-d H:i', strtotime($post['wholeday'] . ' '.$time_end));
            
            $meeting_type = 'whole day';
        }else if($post['meeting_time_type'] == 'half day'){
            if($post['halfdayindicator'] == 'am'){
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 08:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 12:00'));
                $time_start = '08:00';
                $time_end = '12:00';
            }else{
                $tempWeek = strtolower(date('l', strtotime($post['halfday'])));

                $time_start = '13:00';
                $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';

                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 13:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' '.$time_end));
                
            }
            $meeting_type = 'half day';
        }else{
            $tempWeek = strtolower(date('l', strtotime($post['date_to'])));
            $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';
            $get_time = date('H:i', strtotime($post['date_to']));
            $_date_end = null;
            $date_start = $post['date_from'];

            if($tempWeek == 'friday' || $tempWeek == 'saturday'){
                if($get_time >= $time_end){
                    $tempDate = new DateTime($post['date_to']);
                    $tempDate->modify($time_end);
                    $_date_end = $tempDate->format('Y-m-d H:i');
                }else{
                    $_date_end = $post['date_to'];
                }
            }else{
                if($get_time >= $time_end){
                    $tempDate = new DateTime($post['date_to']);
                    $tempDate->modify($time_end);
                    $_date_end = $tempDate->format('Y-m-d H:i');
                }else{
                    $_date_end = $post['date_to'];
                }
            }

            $date_end = $_date_end;
            $meeting_type = 'others';
        }

        if(isset($post['recur_sched']) && $post['recur_sched']){
            $recur_sched = 1;
            $get_time = date('H:i', strtotime($post['recur_to']));
            $_date_end = null;

            if($post['frequency'] == 'Daily'){
                $frequency = serialize(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'));
                $recur_on = 'Daily';

                $_date_end = $post['recur_to'];
            }else{
                $frequency = serialize($post['weekly']);
                $recur_on = 'Weekly';

                $to_lower = array_map('strtolower', $post['weekly']);
                $time_end = in_array('friday', $to_lower) || in_array('saturday', $to_lower) ? '17:00' : '18:00';

                if($get_time >= $time_end){
                    $tempDate = new DateTime($post['recur_to']);
                    $tempDate->modify($time_end);
                    $_date_end = $tempDate->format('Y-m-d H:i');
                }else{
                    $_date_end = $post['recur_to'];
                }
            }

            $date_start = $post['recur_from'];
            $date_end = $_date_end;
            $meeting_type = 'recurring';
        }

        $data = array(
            'reference_no' => $ref,
            'reservation_from' => $userId,
            'description' => ucwords($post['description']),
            'recurring_schedule' => $recur_sched,
            'recur_on' => $recur_on,
            'frequency' => $frequency,
            'facility' => $post['facility'],
            'meeting_time_type' => $meeting_type,
            'date_from' => $date_start,
            'date_to' => $date_end,
            'contact_number' => $post['contact'],
            'remarks' => $post['remarks'],
            'created_by' => $userId,
            'created_dt' => $date
        );

        $this->db->from('tbl_events');
        $this->db->group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from <=', $date_start);
        $this->db->where('date_to >=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from >=', $date_start);
        $this->db->where('date_to <=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();

        $q = $this->db->get();

        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                if(isset($post['recur_sched']) && $post['recur_sched']){
                    if($row->frequency){                        
                        if(array_intersect(unserialize($frequency), unserialize($row->frequency))){

                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));

                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);

                            foreach($recursive as $recurred){
                                if(in_array($recurred, $on_recursive)){
                                    $to_check_end = $this->checkEndDay($recurred, $date_end);

                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                        }else{
                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            
                            if(array_intersect($on_recursive, $recursive)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $start_date = date('Y-m-d', strtotime($row->date_from));
                                $end_date = date('Y-m-d', strtotime($row->date_to));
                                
                                $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                
                                $tempDay = strtolower(date('l', strtotime($end_date)));
                                $temp_get_time = date('H:i', strtotime($date_end));
                                $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
                                if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
                                    $get_time = date('H:i', strtotime($row->date_to));

                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));

                                    if($temp_get_time >= $get_time){
                                        $tempDate = new DateTime($date_end);
                                        $tempDate->modify($time_end);
                                        $to_check_end = $tempDate->format('Y-m-d H:i');
                                    }else{
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                    }
                                    
                                    if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }else{
                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));

                                    if($temp_get_time >= $get_time){
                                        $tempDate = new DateTime($date_end);
                                        $tempDate->modify($time_end);
                                        $to_check_end = $tempDate->format('Y-m-d H:i');
                                    }else{
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                    }

                                    if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }else{
                        $start_date = date('Y-m-d', strtotime($row->date_from));
                        $end_date = date('Y-m-d', strtotime($row->date_to));
                        
                        $to_check_start_date = date('Y-m-d', strtotime($date_start));
                        $to_check_end_date = date('Y-m-d', strtotime($date_end));
                        
                        $tempDay = strtolower(date('l', strtotime($date_end)));
                        $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
                        if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));

                            if($to_check_end >= $end_time){
                                $tempDate = new DateTime($date_end);
                                $tempDate->modify($time_end);
                                $to_check_end = $tempDate->format('Y-m-d H:i');
                            }else{
                                $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                            }
                            
                            if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }else{
                            $_check_if_recurring[] = 0;
                        }
                    }
                }else{
                    $weekday = date('l', strtotime($date_start));
                    $_weekday = date('l', strtotime($row->date_from));
                    
                    if($row->frequency){
                        if(in_array($weekday, unserialize($row->frequency))){
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            foreach($recursive as $_recurred){
                                if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                    $start_time = date('H:i', strtotime($row->date_from));
                                    $end_time = date('H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('H:i', strtotime($date_start));
                                    $to_check_end = date('H:i', strtotime($date_end));

                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                        }else{
                            if(in_array($weekday, unserialize($row->frequency))){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }else{
                        if($weekday == $_weekday){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
    
                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                $_check_if_recurring[] = 1;
                            }else{
                                if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }
                        }
                    }
                }
            }

            if(in_array(1, $_check_if_recurring)){
                $result['state'] = false;
                $result['msg'] = 'Facility unavailable!';
            }else{
                $query = $this->db->insert('tbl_events', $data);
                $id = $this->db->insert_id();
                if($query){
                    $result['state'] = true;
                    $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                    $result['msg'] = 'Event Sucessfully Added!';
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to add event!';
                    $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                }
            }
        }else{
            $_frq = unserialize($frequency);
            $this->db->from('tbl_events');
            $this->db->where('facility', $post['facility']);
            $this->db->where('date_from >=', $date_start);
            $this->db->where('is_archived', 0);
            $qs = $this->db->get();

            if($qs->num_rows() > 0){
                foreach($qs->result() as $row){
                    if(isset($post['recur_sched']) && $post['recur_sched']){
                        if($row->frequency){                        
                            if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
    
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
    
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
    
                                foreach($recursive as $recurred){
                                    if(in_array($recurred, $on_recursive)){
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }
                                }
                                
                            }else{
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                
                                if(array_intersect($on_recursive, $recursive)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $start_date = date('Y-m-d', strtotime($row->date_from));
                                    $end_date = date('Y-m-d', strtotime($row->date_to));
                                    
                                    $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                    $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                    
                                    if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
    
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                        
                                        if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }else{
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
    
                                        if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
    
                        }else{
                            $start_date = date('Y-m-d', strtotime($row->date_from));
                            $end_date = date('Y-m-d', strtotime($row->date_to));
                            
                            $to_check_start_date = date('Y-m-d', strtotime($date_start));
                            $to_check_end_date = date('Y-m-d', strtotime($date_end));
                            
                            
                            if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
                                
                                if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }
                    }else{
    
                        $weekday = date('l', strtotime($date_start));
                        $_weekday = date('l', strtotime($row->date_from));
                        
                        if($row->frequency){
                            if(in_array($weekday, unserialize($row->frequency))){
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                foreach($recursive as $_recurred){
                                    if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                        $start_time = date('H:i', strtotime($row->date_from));
                                        $end_time = date('H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('H:i', strtotime($date_start));
                                        $to_check_end = date('H:i', strtotime($date_end));
    
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }
                                }
                            }
                        }else{
                            if($weekday == $_weekday){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }
                }

                if(in_array(1, $_check_if_recurring)){
                    $result['state'] = false;
                    $result['msg'] = 'Facility unavailable!';
                }else{
                    $query = $this->db->insert('tbl_events', $data);
                    $id = $this->db->insert_id();
                    if($query){
                        $result['state'] = true;
                        $result['msg'] = 'Event Sucessfully Added!';
                        $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']), 'success');
                    }else{
                        $result['state'] = false;
                        $result['msg'] = 'Failed to add event!';
                        $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']), 'error');
                    }
                }
            }else{
                $query = $this->db->insert('tbl_events', $data);
                $id = $this->db->insert_id();
                if($query){
                    $result['state'] = true;
                    $result['msg'] = 'Event Sucessfully Added!';
                    $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']), 'success');
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to add event!';
                    $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']), 'error');
                }
            }
        }

        return $result;
    }

    function addEventv2(){
        $result = array();
        $post = $this->input->post();
        $userId = $_SESSION['id'];
        $date = date('Y-m-d H:i:s');
        $date_start = null;
        $date_end = null;
        $recur_sched = 0;
        $frequency = null;
        $recur_on = null;
        $time_start = null;
        $time_end = null;
        $check_if_recurring = 1;
        $_check_if_recurring = array();
        $meeting_type = null;
        $ref = 'ER'.date('YmdHis');

        if($post['meeting_time_type'] == 'whole day'){
            $date_start = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 08:00'));
            $date_end = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 18:00'));
            $time_start = '08:00';
            $time_end = '18:00';
            $meeting_type = 'whole day';
        }else if($post['meeting_time_type'] == 'half day'){
            if($post['halfdayindicator'] == 'am'){
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 08:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 12:00'));
                $time_start = '08:00';
                $time_end = '12:00';
            }else{
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 13:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 18:00'));
                $time_start = '13:00';
                $time_end = '18:00';
            }
            $meeting_type = 'half day';
        }else{
            $date_start = $post['date_from'];
            $date_end = $post['date_to'];
            $meeting_type = 'others';
        }

        if(isset($post['recur_sched']) && $post['recur_sched']){
            $recur_sched = 1;

            if($post['frequency'] == 'Daily'){
                $frequency = serialize(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'));
                $recur_on = 'Daily';
            }else{
                $frequency = serialize($post['weekly']);
                $recur_on = 'Weekly';
            }

            $date_start = $post['recur_from'];
            $date_end = $post['recur_to'];
            $meeting_type = 'recurring';
        }

        $data = array(
            'reference_no' => $ref,
            'reservation_from' => $userId,
            'description' => ucwords($post['description']),
            'recurring_schedule' => $recur_sched,
            'recur_on' => $recur_on,
            'frequency' => $frequency,
            'facility' => $post['facility'],
            'meeting_time_type' => $meeting_type,
            'date_from' => $date_start,
            'date_to' => $date_end,
            'contact_number' => $post['contact'],
            'remarks' => $post['remarks'],
            'created_by' => $userId,
            'created_dt' => $date
        );

        $this->db->from('tbl_events');
        $this->db->group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from <=', $date_start);
        $this->db->where('date_to >=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from >=', $date_start);
        $this->db->where('date_to <=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();

        $q = $this->db->get();

        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                if(isset($post['recur_sched']) && $post['recur_sched']){
                    // if($row->frequency){
                    //     if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
                    //         $start_time = date('H:i', strtotime($row->date_from));
                    //         $end_time = date('H:i', strtotime($row->date_to));
                            
                    //         $to_check_start = date('H:i', strtotime($date_start));
                    //         $to_check_end = date('H:i', strtotime($date_end));
    
                    //         if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                    //             $_check_if_recurring[] = 1;
                    //         }else{
                    //             $_check_if_recurring[] = 0;
                    //         }
                    //     }else{
                    //         $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                    //         $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);

                    //         if(array_intersect($on_recursive, $recursive)){
                    //             $_check_if_recurring[] = 1;
                    //         }else{
                    //             $start_time = date('H:i', strtotime($row->date_from));
                    //             $end_time = date('H:i', strtotime($row->date_to));
                                
                    //             $to_check_start = date('H:i', strtotime($date_start));
                    //             $to_check_end = date('H:i', strtotime($date_end));

                    //             if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                    //                 $_check_if_recurring[] = 1;
                    //             }else{
                    //                 $_check_if_recurring[] = 0;
                    //             }
                    //         }
                    //     }

                    // }else{
                    //     $start_time = date('H:i', strtotime($row->date_from));
                    //     $end_time = date('H:i', strtotime($row->date_to));
                        
                    //     $to_check_start = date('H:i', strtotime($date_start));
                    //     $to_check_end = date('H:i', strtotime($date_end));
    
                    //     if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                    //         $_check_if_recurring[] = 1;
                    //     }else{
                    //         $_check_if_recurring[] = 0;
                    //     }
                    // }
                    if($row->frequency){                        
                        if(array_intersect(unserialize($frequency), unserialize($row->frequency))){

                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));

                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);

                            foreach($recursive as $recurred){
                                if(in_array($recurred, $on_recursive)){
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                            
                        }else{
                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            
                            if(array_intersect($on_recursive, $recursive)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $start_date = date('Y-m-d', strtotime($row->date_from));
                                $end_date = date('Y-m-d', strtotime($row->date_to));
                                
                                $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                
                                if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
                                    // $start_time = date('H:i', strtotime($row->date_from));
                                    // $end_time = date('H:i', strtotime($row->date_to));
                                    
                                    // $to_check_start = date('H:i', strtotime($date_start));
                                    // $to_check_end = date('H:i', strtotime($date_end));

                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                    $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                    
                                    // if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                    if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }else{
                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                    $to_check_end = date('Y-m-d H:i', strtotime($date_end));

                                    if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }

                    }else{
                        $start_date = date('Y-m-d', strtotime($row->date_from));
                        $end_date = date('Y-m-d', strtotime($row->date_to));
                        
                        $to_check_start_date = date('Y-m-d', strtotime($date_start));
                        $to_check_end_date = date('Y-m-d', strtotime($date_end));
                        
                        
                        if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
                            
                            if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }else{
                            $_check_if_recurring[] = 0;
                        }
                    }
                }else{
                    // $start_time = date('H:i', strtotime($row->date_from));
                    // $end_time = date('H:i', strtotime($row->date_to));
                    
                    // $to_check_start = date('H:i', strtotime($date_start));
                    // $to_check_end = date('H:i', strtotime($date_end));

                    // if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                    //     $_check_if_recurring[] = 1;
                    // }else{
                    //     $_check_if_recurring[] = 0;
                    // }
                    // $weekday = date('l', strtotime($date_start));
                    // $_weekday = date('l', strtotime($row->date_from));

                    // if($weekday == $_weekday){
                    //     $start_time = date('H:i', strtotime($row->date_from));
                    //     $end_time = date('H:i', strtotime($row->date_to));
                        
                    //     $to_check_start = date('H:i', strtotime($date_start));
                    //     $to_check_end = date('H:i', strtotime($date_end));

                    //     if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                    //         $_check_if_recurring[] = 1;
                    //     }else{
                    //         $_check_if_recurring[] = 0;
                    //     }
                    // }

                    $weekday = date('l', strtotime($date_start));
                    $_weekday = date('l', strtotime($row->date_from));
                    
                    if($row->frequency){
                        if(in_array($weekday, unserialize($row->frequency))){
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            foreach($recursive as $_recurred){
                                if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                    $start_time = date('H:i', strtotime($row->date_from));
                                    $end_time = date('H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('H:i', strtotime($date_start));
                                    $to_check_end = date('H:i', strtotime($date_end));

                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                        }else{
                            if(in_array($weekday, unserialize($row->frequency))){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }else{
                        if($weekday == $_weekday){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
    
                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                $_check_if_recurring[] = 1;
                            }else{
                                if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }
                        }
                    }
                }
            }

            if(in_array(1, $_check_if_recurring)){
                $result['state'] = false;
                $result['msg'] = 'Facility unavailable!';
            }else{
                $query = $this->db->insert('tbl_events', $data);
                $id = $this->db->insert_id();
                if($query){
                    $result['state'] = true;
                    $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                    $result['msg'] = 'Event Sucessfully Added!';
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to add event!';
                    $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                }
            }
        }else{
            $_frq = unserialize($frequency);
            $this->db->from('tbl_events');
            $this->db->where('facility', $post['facility']);
            $this->db->where('date_from >=', $date_start);
            $this->db->where('is_archived', 0);
            $qs = $this->db->get();

            if($qs->num_rows() > 0){
                foreach($qs->result() as $row){
                    if(isset($post['recur_sched']) && $post['recur_sched']){
                        if($row->frequency){                        
                            if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
    
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
    
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
    
                                foreach($recursive as $recurred){
                                    if(in_array($recurred, $on_recursive)){
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }
                                }
                                
                            }else{
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                
                                if(array_intersect($on_recursive, $recursive)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $start_date = date('Y-m-d', strtotime($row->date_from));
                                    $end_date = date('Y-m-d', strtotime($row->date_to));
                                    
                                    $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                    $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                    
                                    if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
    
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                        
                                        if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }else{
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
    
                                        if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
    
                        }else{
                            $start_date = date('Y-m-d', strtotime($row->date_from));
                            $end_date = date('Y-m-d', strtotime($row->date_to));
                            
                            $to_check_start_date = date('Y-m-d', strtotime($date_start));
                            $to_check_end_date = date('Y-m-d', strtotime($date_end));
                            
                            
                            if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
                                
                                if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }
                    }else{
    
                        $weekday = date('l', strtotime($date_start));
                        $_weekday = date('l', strtotime($row->date_from));
                        
                        if($row->frequency){
                            if(in_array($weekday, unserialize($row->frequency))){
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                foreach($recursive as $_recurred){
                                    if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                        $start_time = date('H:i', strtotime($row->date_from));
                                        $end_time = date('H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('H:i', strtotime($date_start));
                                        $to_check_end = date('H:i', strtotime($date_end));
    
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }
                                }
                            }
                        }else{
                            if($weekday == $_weekday){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }
                }
    
                if(in_array(1, $_check_if_recurring)){
                    $result['state'] = false;
                    $result['msg'] = 'Facility unavailable!';
                }else{
                    $query = $this->db->insert('tbl_events', $data);
                    $id = $this->db->insert_id();
                    if($query){
                        $result['state'] = true;
                        $result['msg'] = 'Event Sucessfully Added!';
                        $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']), 'success');
                    }else{
                        $result['state'] = false;
                        $result['msg'] = 'Failed to add event!';
                        $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']), 'error');
                    }
                }
            }else{
                $query = $this->db->insert('tbl_events', $data);
                $id = $this->db->insert_id();
                if($query){
                    $result['state'] = true;
                    $result['msg'] = 'Event Sucessfully Added!';
                    $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']), 'success');
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to add event!';
                    $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']), 'error');
                }
            }
        }

        return $result;
    }

    function addEventv1(){
        $result = array();
        $post = $this->input->post();
        $userId = $_SESSION['id'];
        $date = date('Y-m-d H:i:s');
        $date_start = null;
        $date_end = null;
        $recur_sched = 0;
        $frequency = null;
        $recur_on = null;
        $time_start = null;
        $time_end = null;
        $check_if_recurring = 1;
        $meeting_type = null;
        $ref = 'ER'.date('YmdHis');

        if($post['meeting_time_type'] == 'whole day'){
            $date_start = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 08:00'));
            $date_end = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 18:00'));
            $time_start = '08:00';
            $time_end = '18:00';
            $meeting_type = 'whole day';
        }else if($post['meeting_time_type'] == 'half day'){
            if($post['halfdayindicator'] == 'am'){
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 08:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 12:00'));
                $time_start = '08:00';
                $time_end = '12:00';
            }else{
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 13:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 18:00'));
                $time_start = '13:00';
                $time_end = '18:00';
            }
            $meeting_type = 'half day';
        }else{
           $date_start = $post['date_from'];
           $date_end = $post['date_to'];
           $meeting_type = 'others';
        }

        if(isset($post['recur_sched']) && $post['recur_sched']){
            $recur_sched = 1;

            if($post['frequency'] == 'Daily'){
                $frequency = serialize(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'));
                $recur_on = 'Daily';
            }else{
                $frequency = serialize($post['weekly']);
                $recur_on = 'Weekly';
            }

            $date_start = $post['recur_from'];
            $date_end = $post['recur_to'];
            $meeting_type = 'recurring';
        }

        $data = array(
            'reference_no' => $ref,
            'reservation_from' => $userId,
            'description' => ucwords($post['description']),
            'recurring_schedule' => $recur_sched,
            'recur_on' => $recur_on,
            'frequency' => $frequency,
            'facility' => $post['facility'],
            'meeting_time_type' => $meeting_type,
            'date_from' => $date_start,
            'date_to' => $date_end,
            'contact_number' => $post['contact'],
            'remarks' => $post['remarks'],
            'created_by' => $userId,
            'created_dt' => $date
        );

        $this->db->from('tbl_events');
        $this->db->group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from <=', $date_start);
        $this->db->where('date_to >=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from >=', $date_start);
        $this->db->where('date_to <=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();
        
        $q = $this->db->get();

        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $_frq = unserialize($row->frequency);
                if($row->frequency){
                    $weekday = date('l', strtotime($date_start));
                    
                    foreach($_frq as $rows){
                        if($rows != $weekday){
                            $check_if_recurring = 0;
                        }else{
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
                            
                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                $check_if_recurring = 1;
                            }else{
                                $check_if_recurring = 0;
                            }
                        }
                    }
                }else{
                    $check_if_recurring = 0;
                }
            }

            if($check_if_recurring == 1){
                $result['state'] = false;
                $result['msg'] = 'Facility unavailable!';
            }else{
                $query = $this->db->insert('tbl_events', $data);
                $id = $this->db->insert_id();
                if($query){
                    $result['state'] = true;
                    $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                    $result['msg'] = 'Event Sucessfully Added!';
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to add event!';
                    $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                }
            }
        }else{
            $_frq = unserialize($frequency);
            $this->db->from('tbl_events');
            $this->db->where('facility', $post['facility']);
            $this->db->where('date_from >=', $date_start);
            $this->db->where('is_archived', 0);
            $qs = $this->db->get();

            if($qs->num_rows() > 0){
                foreach($qs->result() as $row){
                    if($row->frequency && $row->frequency != null && $row->frequency != ''){
                        $recurs = unserialize($row->frequency);
                        $weekday = date('l', strtotime($date_start));
                        $row_weekday_start = date('l', strtotime($row->date_from));
                        $row_weekday_end = date('l', strtotime($row->date_to));
    
                        foreach($recurs as $rows){
                            if($weekday != $rows){
                                $check_if_recurring = 0;
                            }else{
                                $check_if_recurring = 1;
    
                            }
                        }
                    }else{
                        $check_if_recurring = 0;
                    }
                }
    
                if($check_if_recurring == 1){
                    $result['state'] = false;
                    $result['msg'] = 'Facility unavailable!';
                }else{
                    $query = $this->db->insert('tbl_events', $data);
                    $id = $this->db->insert_id();
                    if($query){
                        $result['state'] = true;
                        $result['msg'] = 'Event Sucessfully Added!';
                        $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']), 'success');
                    }else{
                        $result['state'] = false;
                        $result['msg'] = 'Failed to add event!';
                        $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']), 'error');
                    }
                }
            }else{
                $query = $this->db->insert('tbl_events', $data);
                $id = $this->db->insert_id();
                if($query){
                    $result['state'] = true;
                    $result['msg'] = 'Event Sucessfully Added!';
                    $this->core->logs($userId, $id, 'events', 'User successfully added event '. ucwords($post['description']), 'success');
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to add event!';
                    $this->core->logs($userId, $id, 'events', 'User failed to add event '. ucwords($post['description']), 'error');
                }
            }
        }

        return $result;
    }

    function displayDates($date1, $date2, $format = 'Y-m-d' ) {
        $dates = array();
        $current = strtotime($date1);
        $date2 = strtotime($date2);
        $stepVal = '+1 day';
        while( $current <= $date2 ) {
           $dates[] = date($format, $current);
           $current = strtotime($stepVal, $current);
        }
        return $dates;
    }

    function getReccurringSched($days, $start, $end){
        $result = array();
        $arrData = array();
        $day = array('Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday');

        if($days){
            $date1=date_create($start);
            $date2=date_create($end);
            $diff=date_diff($date1,$date2);
            $diff = $diff->format("%a");
            
            $dates = $this->displayDates($start, $end);
            foreach($dates as $date){
                foreach($days as $key => $row){
                    $added_date = date('Y-m-d', strtotime($row, strtotime($date)));

                    if($added_date <= $end){
                        $arrData[] = $added_date;
                    }
                }
            }
        }

        return array_unique($arrData);
    }

    function editEvent(){
        $result = array();
        $post = $this->input->post();
        $date_start = null;
        $date_end = null;
        $recur_sched = 0;
        $frequency = null;
        $user_id = $_SESSION['id'];
        $_check_if_recurring = array();

        if($post['meeting_time_type'] == 'whole day'){
            $tempWeek = strtolower(date('l', strtotime($post['wholeday'])));
            $time_start = '08:00';
            $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';

            $date_start = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 08:00'));
            $date_end = date('Y-m-d H:i', strtotime($post['wholeday'] . ' '.$time_end));
        }else if($post['meeting_time_type'] == 'half day'){
            if($post['halfdayindicator'] == 'am'){
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 08:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 12:00'));
            }else{
                $tempWeek = strtolower(date('l', strtotime($post['halfday'])));

                $time_start = '13:00';
                $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';

                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 13:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' '.$time_end));
            }
        }else{
            $tempWeek = strtolower(date('l', strtotime($post['date_to'])));
            $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';
            $get_time = date('H:i', strtotime($post['date_to']));
            $_date_end = null;

            $date_start = $post['date_from'];
            $date_end = $post['date_to'];
        }

        if(isset($post['recur_sched_view']) && $post['recur_sched_view']){
            $recur_sched = 1;
            $_date_end = null;

            if($post['frequency'] == 'Daily'){
                $recur_start = new DateTime(date_create($post['recur_from_view'])->format('Y-m-d'));
                $recur_end = new DateTime(date_create($post['recur_to_view'])->format('Y-m-d'));
                $interval = $recur_start->diff($recur_end);

                $tempDates = array();
                for($i = 0; $i <= $interval->d; $i++){
                    $val = ucfirst(date('l', strtotime(date("Y-m-d", strtotime($post['recur_from_view']."$i days")))));
                    array_push($tempDates, $val);
                }
                $frequency = serialize($tempDates);
            }else{
                $frequency = serialize($post['weekly_view']);
            }

            $date_start = $post['recur_from_view'];
            $date_end = $post['recur_to_view'];
        }

        $data = array(
            'description' => ucwords($post['title']),
            'recurring_schedule' => $recur_sched,
            'recur_on' => ($recur_sched == 1) ? $post['frequency'] : null,
            'frequency' => ($recur_sched == 1) ? $frequency : null,
            'facility' => $post['facility'],
            'meeting_time_type' => ($recur_sched == 1) ? 'recurring' : $post['meeting_time_type'],
            'date_from' => $date_start,
            'date_to' => $date_end,
            'contact_number' => $post['contact'],
            'remarks' => $post['remarks'],
        );

        $dateonly_start = date("Y-m-d", strtotime($date_start));
        $dateonly_end = date("Y-m-d", strtotime($date_end));

        $this->db->from('tbl_events');
        $this->db->group_start();
        $this->db->where("id !=", $post['id']);
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from <=', $dateonly_start);
        $this->db->where('date_to >=', $dateonly_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where("id !=", $post['id']);
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from >=', $dateonly_start);
        $this->db->where('date_to <=', $dateonly_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();

        $q = $this->db->get();
        
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                if(isset($post['recur_sched_view']) && $post['recur_sched_view']){
                    if($row->frequency){     
                        if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
    
                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
    
                            foreach($recursive as $recurred){
                                if(in_array($recurred, $on_recursive)){
                                    $to_check_end = $this->checkEndDay($recurred, $date_end);
    
                                    if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }else{
                                        $_check_if_recurring[] = 1;
                                    }
                                }
                            }
                        }else{
                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            
                            if(array_intersect($on_recursive, $recursive)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $start_date = date('Y-m-d', strtotime($row->date_from));
                                $end_date = date('Y-m-d', strtotime($row->date_to));
                                
                                $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                
                                $tempDay = strtolower(date('l', strtotime($end_date)));
                                $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
                                $temp_get_time = date('H:i', strtotime($date_end));

                                if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
                                    $get_time = date('H:i', strtotime($row->date_to));
    
                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                    $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                    
                                    if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                        if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }else{
                                        $_check_if_recurring[] = 1;
                                    }
                                }else{
                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                    $to_check_end = date('Y-m-d H:i', strtotime($date_end));
    
                                    if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                        if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }else{
                                        $_check_if_recurring[] = 1;
                                    }
                                }
                            }
                        }
                    }else{
                        $start_date = date('Y-m-d', strtotime($row->date_from));
                        $end_date = date('Y-m-d', strtotime($row->date_to));
                        
                        $to_check_start_date = date('Y-m-d', strtotime($date_start));
                        $to_check_end_date = date('Y-m-d', strtotime($date_end));
                        
                        $tempDay = strtolower(date('l', strtotime($date_end)));
                        $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
                        if($start_date == $to_check_start_date){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
                            
                            if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }else{
                                $_check_if_recurring[] = 1;
                            }
                        }else{
                            $_check_if_recurring[] = 0;
                        }
                    }
                }else{
                    $weekday = date('l', strtotime($date_start));
                    $_weekday = date('l', strtotime($row->date_from));
                    
                    if($row->frequency){
                        if(in_array($weekday, unserialize($row->frequency))){
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            foreach($recursive as $_recurred){
                                if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                    $start_time = date('H:i', strtotime($row->date_from));
                                    $end_time = date('H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('H:i', strtotime($date_start));
                                    $to_check_end = date('H:i', strtotime($date_end));
    
                                    if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }else{
                                        $_check_if_recurring[] = 1;
                                    }
                                }
                            }
                        }else{
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
    
                            if(date('Y-m-d', strtotime($row->date_from)) == date('Y-m-d', strtotime($date_start))){
                                if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }else{
                                    $_check_if_recurring[] = 1;
                                }
                            }
                        }
                    }else{
                        if($weekday == $_weekday){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
    
                            if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }else{
                                $_check_if_recurring[] = 1;
                            }
                        }
                    }
                }
            }

            if(in_array(1, $_check_if_recurring)){
                $result['state'] = false;
                $result['msg'] = 'Facility unavailable!';
            }else{
                $this->db->where('id', $post['id']);
                $query = $this->db->update('tbl_events', $data);
                if($query){
                    $result['state'] = true;
                    $result['msg'] = 'Event Sucessfully Updated!';
                    $this->core->logs($user_id, $post['id'], 'events', 'User successfully updated event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to update event!';
                    $this->core->logs($user_id, $post['id'], 'events', 'User failed to update event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                }
            }
        }else{
            $_frq = unserialize($frequency);
            $this->db->from('tbl_events');
            $this->db->where('facility', $post['facility']);
            $this->db->where('date_from >=', $date_start);
            $this->db->where('is_archived', 0);
            $this->db->where("id !=", $post['id']);
            $qs = $this->db->get();

            if($qs->num_rows() > 0){
                foreach($qs->result() as $row){
                    if(isset($post['recur_sched_view']) && $post['recur_sched_view']){

                        if($row->frequency){                        
                            if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
    
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
    
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
    
                                foreach($recursive as $recurred){
                                    if(in_array($recurred, $on_recursive)){
                                        if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                    $_check_if_recurring[] = 1;
                                                }else{
                                                    $_check_if_recurring[] = 0;
                                                }
                                            }
                                        }else{
                                            $_check_if_recurring[] = 1;
                                        }
                                    }
                                }
                                
                            }else{
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                
                                if(array_intersect($on_recursive, $recursive)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $start_date = date('Y-m-d', strtotime($row->date_from));
                                    $end_date = date('Y-m-d', strtotime($row->date_to));
                                    
                                    $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                    $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                    
                                    if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
    
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                        
                                        if($row->date_from <= $date_start && $date_start >= $row->date_to){
                                            if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }else{
                                            $_check_if_recurring[] = 1;
                                        }
                                    }else{
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
    
                                        if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                            if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }else{
                                            $_check_if_recurring[] = 1;
                                        }
                                    }
                                }
                            }
    
                        }else{
                            $start_date = date('Y-m-d', strtotime($row->date_from));
                            $end_date = date('Y-m-d', strtotime($row->date_to));
                            
                            $to_check_start_date = date('Y-m-d', strtotime($date_start));
                            $to_check_end_date = date('Y-m-d', strtotime($date_end));
                            
                            
                            if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
                                
                                if($row->date_from <= $date_start && $date_start >= $row->date_to){
                                    if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }else{
                                    $_check_if_recurring[] = 1;
                                }
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }
                    }else{
    
                        $weekday = date('l', strtotime($date_start));
                        $_weekday = date('l', strtotime($row->date_from));
                        
                        if($row->frequency){
                            if(in_array($weekday, unserialize($row->frequency))){
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                foreach($recursive as $_recurred){
                                    if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                        $start_time = date('H:i', strtotime($row->date_from));
                                        $end_time = date('H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('H:i', strtotime($date_start));
                                        $to_check_end = date('H:i', strtotime($date_end));
    
                                        if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                    $_check_if_recurring[] = 1;
                                                }else{
                                                    $_check_if_recurring[] = 0;
                                                }
                                            }
                                        }else{
                                            $_check_if_recurring[] = 1;
                                        }
                                    }
                                }
                            }
                        }else{
                            if($weekday == $_weekday){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if($start_time <= $to_check_start && $to_check_start >= $end_time){
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }else{
                                    $_check_if_recurring[] = 1;
                                }
                            }
                        }
                    }
                }
                if(in_array(1, $_check_if_recurring)){
                    $result['state'] = false;
                    $result['msg'] = 'Facility unavailable!';
                }else{
                    $this->db->where('id', $post['id']);
                    $query = $this->db->update('tbl_events', $data);
                    if($query){
                        $result['state'] = true;
                        $result['msg'] = 'Event Sucessfully Updated!';
                        $this->core->logs($user_id, $post['id'], 'events', 'User successfully updated event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                    }else{
                        $result['state'] = false;
                        $result['msg'] = 'Failed to update event!';
                        $this->core->logs($user_id, $post['id'], 'events', 'User failed to update event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                    }
                }
            }else{
                $this->db->where('id', $post['id']);
                $query = $this->db->update('tbl_events', $data);
                if($query){
                    $result['state'] = true;
                    $result['msg'] = 'Event Sucessfully Updated!';
                    $this->core->logs($user_id, $post['id'], 'events', 'User successfully updated event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to update event!';
                    $this->core->logs($user_id, $post['id'], 'events', 'User failed to update event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                }
            }
        }

        return $result;
    }
    
    function editEventv2(){
        $result = array();
        $post = $this->input->post();
        $date_start = null;
        $date_end = null;
        $recur_sched = 0;
        $frequency = null;
        $user_id = $_SESSION['id'];
        $_check_if_recurring = array();

        if($post['meeting_time_type'] == 'whole day'){
            $tempWeek = strtolower(date('l', strtotime($post['wholeday'])));
            $time_start = '08:00';
            $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';

            $date_start = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 08:00'));
            $date_end = date('Y-m-d H:i', strtotime($post['wholeday'] . ' '.$time_end));
        }else if($post['meeting_time_type'] == 'half day'){
            if($post['halfdayindicator'] == 'am'){
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 08:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 12:00'));
            }else{
                $tempWeek = strtolower(date('l', strtotime($post['halfday'])));

                $time_start = '13:00';
                $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';

                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 13:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' '.$time_end));
            }
        }else{
            $tempWeek = strtolower(date('l', strtotime($post['date_to'])));
            $time_end = ($tempWeek == 'friday' || $tempWeek == 'saturday') ? '17:00' : '18:00';
            $get_time = date('H:i', strtotime($post['date_to']));
            $_date_end = null;

            if($tempWeek == 'friday' || $tempWeek == 'saturday'){
                if($get_time >= $time_end){
                    $tempDate = new DateTime($post['date_to']);
                    $tempDate->modify($time_end);
                    $_date_end = $tempDate->format('Y-m-d H:i');
                }else{
                    $_date_end = $post['date_to'];
                }
            }else{
                if($get_time >= $time_end){
                    $tempDate = new DateTime($post['date_to']);
                    $tempDate->modify($time_end);
                    $_date_end = $tempDate->format('Y-m-d H:i');
                }else{
                    $_date_end = $post['date_to'];
                }
            }

            $date_start = $post['date_from'];
            $date_end = $_date_end;
        }

        if(isset($post['recur_sched_view']) && $post['recur_sched_view']){
            $recur_sched = 1;
            $_date_end = null;

            if($post['frequency'] == 'Daily'){
                $frequency = serialize(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'));

                $_date_end = $post['recur_to_view'];
            }else{
                $frequency = serialize($post['weekly_view']);

                $to_lower = array_map('strtolower', $post['weekly_view']);
                $time_end = in_array('friday', $to_lower) || in_array('saturday', $to_lower) ? '17:00' : '18:00';

                if($get_time >= $time_end){
                    $tempDate = new DateTime($post['recur_to_view']);
                    $tempDate->modify($time_end);
                    $_date_end = $tempDate->format('Y-m-d H:i');
                }else{
                    $_date_end = $post['recur_to_view'];
                }
            }

            $date_start = $post['recur_from_view'];
            $date_end = $_date_end;
        }

        $data = array(
            'description' => ucwords($post['title']),
            'recurring_schedule' => $recur_sched,
            'recur_on' => ($recur_sched == 1) ? $post['frequency'] : null,
            'frequency' => ($recur_sched == 1) ? $frequency : null,
            'facility' => $post['facility'],
            'meeting_time_type' => ($recur_sched == 1) ? 'recurring' : $post['meeting_time_type'],
            'date_from' => $date_start,
            'date_to' => $date_end,
            'contact_number' => $post['contact'],
            'remarks' => $post['remarks'],
        );

        $this->db->from('tbl_events');
        $this->db->group_start();
        $this->db->where("id !=", $post['id']);
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from <=', $date_start);
        $this->db->where('date_to >=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where("id !=", $post['id']);
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from >=', $date_start);
        $this->db->where('date_to <=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();

        $q = $this->db->get();
        
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                if(isset($post['recur_sched_view']) && $post['recur_sched_view']){
                    if($row->frequency){         
                        if(array_intersect(unserialize($frequency), unserialize($row->frequency))){           
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
    
                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
    
                            foreach($recursive as $recurred){
                                if(in_array($recurred, $on_recursive)){
                                    $to_check_end = $this->checkEndDay($recurred, $date_end);
    
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                        }else{
                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            
                            if(array_intersect($on_recursive, $recursive)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $start_date = date('Y-m-d', strtotime($row->date_from));
                                $end_date = date('Y-m-d', strtotime($row->date_to));
                                
                                $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                
                                $tempDay = strtolower(date('l', strtotime($end_date)));
                                $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
                                $temp_get_time = date('H:i', strtotime($date_end));

                                if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
                                    $get_time = date('H:i', strtotime($row->date_to));
    
                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
    
                                    if($temp_get_time >= $get_time){
                                        $tempDate = new DateTime($date_end);
                                        $tempDate->modify($time_end);
                                        $to_check_end = $tempDate->format('Y-m-d H:i');
                                    }else{
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                    }
                                    
                                    if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }else{
                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
    
                                    if($temp_get_time >= $get_time){
                                        $tempDate = new DateTime($date_end);
                                        $tempDate->modify($time_end);
                                        $to_check_end = $tempDate->format('Y-m-d H:i');
                                    }else{
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                    }
    
                                    if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }else{
                        $start_date = date('Y-m-d', strtotime($row->date_from));
                        $end_date = date('Y-m-d', strtotime($row->date_to));
                        
                        $to_check_start_date = date('Y-m-d', strtotime($date_start));
                        $to_check_end_date = date('Y-m-d', strtotime($date_end));
                        
                        $tempDay = strtolower(date('l', strtotime($date_end)));
                        $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
                        if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
    
                            if($to_check_end >= $end_time){
                                $tempDate = new DateTime($date_end);
                                $tempDate->modify($time_end);
                                $to_check_end = $tempDate->format('Y-m-d H:i');
                            }else{
                                $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                            }
                            
                            if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }else{
                            $_check_if_recurring[] = 0;
                        }
                    }
                }else{
                    $weekday = date('l', strtotime($date_start));
                    $_weekday = date('l', strtotime($row->date_from));
                    
                    if($row->frequency){
                        if(in_array($weekday, unserialize($row->frequency))){
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            foreach($recursive as $_recurred){
                                if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                    $start_time = date('H:i', strtotime($row->date_from));
                                    $end_time = date('H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('H:i', strtotime($date_start));
                                    $to_check_end = date('H:i', strtotime($date_end));
    
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                        }else{
                            if(in_array($weekday, unserialize($row->frequency))){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }else{
                        if($weekday == $_weekday){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
    
                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                $_check_if_recurring[] = 1;
                            }else{
                                if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }
                        }
                    }
                }
            }

            if(in_array(1, $_check_if_recurring)){
                $result['state'] = false;
                $result['msg'] = 'Facility unavailable!';
            }else{
                $this->db->where('id', $post['id']);
                $query = $this->db->update('tbl_events', $data);
                if($query){
                    $result['state'] = true;
                    $result['msg'] = 'Event Sucessfully Updated!';
                    $this->core->logs($user_id, $post['id'], 'events', 'User successfully updated event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to update event!';
                    $this->core->logs($user_id, $post['id'], 'events', 'User failed to update event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                }
            }
        }else{
            $_frq = unserialize($frequency);
            $this->db->from('tbl_events');
            $this->db->where('facility', $post['facility']);
            $this->db->where('date_from >=', $date_start);
            $this->db->where('is_archived', 0);
            $this->db->where("id !=", $post['id']);
            $qs = $this->db->get();

            if($qs->num_rows() > 0){
                foreach($qs->result() as $row){
                    if(isset($post['recur_sched']) && $post['recur_sched']){
                        if($row->frequency){                        
                            if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
    
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
    
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
    
                                foreach($recursive as $recurred){
                                    if(in_array($recurred, $on_recursive)){
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }
                                }
                                
                            }else{
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                
                                if(array_intersect($on_recursive, $recursive)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $start_date = date('Y-m-d', strtotime($row->date_from));
                                    $end_date = date('Y-m-d', strtotime($row->date_to));
                                    
                                    $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                    $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                    
                                    if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
    
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                        
                                        if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }else{
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
    
                                        if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
    
                        }else{
                            $start_date = date('Y-m-d', strtotime($row->date_from));
                            $end_date = date('Y-m-d', strtotime($row->date_to));
                            
                            $to_check_start_date = date('Y-m-d', strtotime($date_start));
                            $to_check_end_date = date('Y-m-d', strtotime($date_end));
                            
                            
                            if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
                                
                                if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }
                    }else{
    
                        $weekday = date('l', strtotime($date_start));
                        $_weekday = date('l', strtotime($row->date_from));
                        
                        if($row->frequency){
                            if(in_array($weekday, unserialize($row->frequency))){
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                foreach($recursive as $_recurred){
                                    if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                        $start_time = date('H:i', strtotime($row->date_from));
                                        $end_time = date('H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('H:i', strtotime($date_start));
                                        $to_check_end = date('H:i', strtotime($date_end));
    
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }
                                }
                            }
                        }else{
                            if($weekday == $_weekday){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }
                }

                if(in_array(1, $_check_if_recurring)){
                    $result['state'] = false;
                    $result['msg'] = 'Facility unavailable!';
                }else{
                    $this->db->where('id', $post['id']);
                    $query = $this->db->update('tbl_events', $data);
                    if($query){
                        $result['state'] = true;
                        $result['msg'] = 'Event Sucessfully Updated!';
                        $this->core->logs($user_id, $post['id'], 'events', 'User successfully updated event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                    }else{
                        $result['state'] = false;
                        $result['msg'] = 'Failed to update event!';
                        $this->core->logs($user_id, $post['id'], 'events', 'User failed to update event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                    }
                }
            }else{
                $this->db->where('id', $post['id']);
                $query = $this->db->update('tbl_events', $data);
                if($query){
                    $result['state'] = true;
                    $result['msg'] = 'Event Sucessfully Updated!';
                    $this->core->logs($user_id, $post['id'], 'events', 'User successfully updated event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
                }else{
                    $result['state'] = false;
                    $result['msg'] = 'Failed to update event!';
                    $this->core->logs($user_id, $post['id'], 'events', 'User failed to update event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
                }
            }
        }

        return $result;
    }

    function editEventv1(){
        $result = array();
        $post = $this->input->post();
        $recur_sched = 0;
        $frequency = null;
        $user_id = $_SESSION['id'];

        if($post['meeting_time_type'] == 'whole day'){
            $date_start = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 08:00'));
            $date_end = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 18:00'));
        }else if($post['meeting_time_type'] == 'half day'){
            if($post['halfdayindicator'] == 'am'){
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 08:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 12:00'));
            }else{
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 13:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 18:00'));
            }
        }else{
           $date_start = $post['date_from'];
           $date_end = $post['date_to'];
        }
        
        if(isset($post['recur_sched_view']) && $post['recur_sched_view']){
            $recur_sched = 1;

            if($post['frequency'] == 'Daily'){
                $frequency = serialize(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'));
            }else{
                $frequency = serialize($post['weekly_view']);
            }

            $date_start = $post['recur_from_view'];
            $date_end = $post['recur_to_view'];
        }
        
        $data = array(
            'description' => ucwords($post['title']),
            'recurring_schedule' => $recur_sched,
            'recur_on' => ($recur_sched == 1) ? $post['frequency'] : null,
            'frequency' => ($recur_sched == 1) ? $frequency : null,
            'facility' => $post['facility'],
            'meeting_time_type' => ($recur_sched == 1) ? 'recurring' : $post['meeting_time_type'],
            'date_from' => $date_start,
            'date_to' => $date_end,
            'contact_number' => $post['contact'],
            'remarks' => $post['remarks'],
        );

        $this->db->where('id', $post['id']);
        $query = $this->db->update('tbl_events', $data);
        if($query){
            $result['state'] = true;
            $result['msg'] = 'Event Sucessfully Updated!';
            $this->core->logs($user_id, $post['id'], 'events', 'User successfully updated event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'success');
        }else{
            $result['state'] = false;
            $result['msg'] = 'Failed to update event!';
            $this->core->logs($user_id, $post['id'], 'events', 'User failed to update event '. ucwords($post['title']). ' scheduled on '.$date_start.' - '.$date_end, 'error');
        }

        return $result;
    }

    function getDatatable(){
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
        $filterFields = array("description", "reference_no", "name", "firstname", 'lastname', "meeting_time_type");

        $sql = 'a.id as event_id, a.description, a.recurring_schedule, recur_on, a.frequency, a.meeting_time_type, a.date_from as start_date, a.date_to as end_date, a.contact_number, a.remarks, a.recurring_schedule as recurring, a.recur_on as recur_on, a.created_dt, a.reference_no, ';
        $sql .= "b.firstname, b.lastname, b.id as user_id, b.role_id as role, ";
        $sql .= "c.name as facility_name, c.id as facility_id";

        $this->db->select($sql);
        $this->db->from('tbl_events as a');
        $this->db->join('tbl_users as  b', 'b.id = a.reservation_from', 'left');
        $this->db->join('tbl_facility as  c', 'c.id = a.facility', 'left');
        $this->db->where('a.is_archived', 0);

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
                $tempResource = unserialize($rs->frequency);
                $rs->reference_no = $rs->reference_no;
                $rs->description = ucfirst($rs->description);
                $rs->meeting_type = ucwords($rs->meeting_time_type);
                $rs->fullname = ucwords($rs->firstname. ' ' . $rs->lastname);
                $rs->date_range = date('Y-m-d h:i A', strtotime($rs->start_date)) . ' - ' . date('Y-m-d h:i A', strtotime($rs->end_date));
                $rs->recurring = ($rs->recurring_schedule == 1) ? unserialize($rs->frequency) : 0;
                $rs->meridiem = date('a', strtotime($rs->start_date));
                $rs->frequency = $tempResource;
                $rs->created_dt = date('Y-m-d h:i A', strtotime($rs->created_dt));
                $arrData[$key] = $rs; 
            }
            foreach ($arrData as $k => $v) {
                $result[] = $v;
            }
        }

        return $result;
    }

    function tableRequestCount($search = null){
        $this->db->from('tbl_events as a');
        $this->db->join('tbl_users as  b', 'b.id = a.reservation_from', 'left');
        $this->db->join('tbl_facility as  c', 'c.id = a.facility', 'left');
        $this->db->where('a.is_archived', 0);

        $filterFields = array("description","reference_no", "name", "firstname", 'lastname', "meeting_time_type");
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

    function getEvents($id){
        $result = array();
        $arrdata = array();
        $recurring_date = array();
        $frequency = array();

        $sql = "a.description as title, a.remarks as description, a.date_from as start_date, a.date_to as end_date, a.contact_number, a.recurring_schedule, a.frequency, a.id as event_id, a.meeting_time_type as meeting_type, a.recurring_schedule as recurring, a.recur_on as recur_on, ";
        $sql .= "b.firstname, b.lastname, b.id as user_id, b.role_id as role, ";
        $sql .= "c.name as facility_name, c.id as facility_id, c.facility_color as backgroundColor";
        $this->db->select($sql);
        $this->db->from('tbl_events as a');
        $this->db->join('tbl_users as  b', 'b.id = a.reservation_from', 'left');
        $this->db->join('tbl_facility as  c', 'c.id = a.facility', 'left');
        $this->db->where('a.id', $id);

        $query = $this->db->get();

        if($query->num_rows() > 0){
            $count = 0;
            $rs = $query->row();

            $tempResource = unserialize($rs->frequency);
            $start = date('Y-m-d', strtotime($rs->start_date));
            $end = date('Y-m-d', strtotime($rs->end_date));

            $starttime = date('H:i', strtotime($rs->start_date));
            $endtime = date('H:i', strtotime($rs->end_date));

            $recurring_date = array(
                'title' => ucfirst($rs->title), 
                'description' => ucfirst($rs->description), 
                'contact_number' => $rs->contact_number,
                'fullname' => ucwords($rs->firstname . ' ' . $rs->lastname),
                'date_from' => ($rs->start_date),
                'date_to' => ($rs->end_date),
                'start_date' => $rs->start_date,
                'end_date' => $rs->end_date,
                'event_id' => $rs->event_id,
                'role' => $rs->role,
                'recurring' => $rs->recurring,
                'meeting_type' => $rs->meeting_type,
                'recur_on' => $rs->recur_on,
                'frequency' => $tempResource,
                'facility_id' => $rs->facility_id,
                'facility_name' => $rs->facility_name,
                'date_range' => date('Y-m-d H:i', strtotime($rs->start_date)) . ' - ' . date('Y-m-d H:i', strtotime($rs->end_date)),
                'user_id' => $rs->user_id,
                'backgroundColor' => $rs->backgroundColor,
                'textColor' => '#FFFF',
                'startDate' => date('Y-m-d', strtotime($rs->start_date)),
                'endDate' => date('Y-m-d', strtotime($rs->start_date))
            );
            $result = $recurring_date;
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
        $query = $this->db->update('tbl_events', $data);

        if($query){
            $result['state'] = true;
            $result['msg'] = 'Archived Sucessfully!';
            $this->core->logs($user_id, $id, 'events', 'User successfully archive item!', 'success');
        }else{
            $result['state'] = false;
            $result['msg'] = 'Failed to archive facility!';
            $this->core->logs($user_id, $id, 'events', 'User failed to restore archive item', 'error');
        }

        return $result;
    }

    function addEvent1($post){
        $result = array();
        // $post = $this->input->post();
        $userId = $_SESSION['id'];
        $date = date('Y-m-d H:i:s');
        $date_start = null;
        $date_end = null;
        $recur_sched = 0;
        $frequency = null;
        $recur_on = null;
        $time_start = null;
        $time_end = null;
        $check_if_recurring = 1;
        $_check_if_recurring = array();
        $meeting_type = null;
        $ref = 'ER'.date('YmdHis');

        if($post['meeting_time_type'] == 'whole day'){
            $date_start = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 08:00'));
            $date_end = date('Y-m-d H:i', strtotime($post['wholeday'] . ' 18:00'));
            $time_start = '08:00';
            $time_end = '18:00';
            $meeting_type = 'whole day';
        }else if($post['meeting_time_type'] == 'half day'){
            if($post['halfdayindicator'] == 'am'){
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 08:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 12:00'));
                $time_start = '08:00';
                $time_end = '12:00';
            }else{
                $date_start = date('Y-m-d H:i', strtotime($post['halfday'] . ' 13:00'));
                $date_end = date('Y-m-d H:i', strtotime($post['halfday'] . ' 18:00'));
                $time_start = '13:00';
                $time_end = '18:00';
            }
            $meeting_type = 'half day';
        }else{
            $date_start = $post['date_from'];
            $date_end = $post['date_to'];
            $meeting_type = 'others';
        }

        if(isset($post['recur_sched']) && $post['recur_sched']){
            $recur_sched = 1;

            if($post['frequency'] == 'Daily'){
                $frequency = serialize(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'));
                $recur_on = 'Daily';
            }else{
                $frequency = serialize($post['weekly']);
                $recur_on = 'Weekly';
            }

            $date_start = $post['recur_from'];
            $date_end = $post['recur_to'];
            $meeting_type = 'recurring';
        }

        $data = array(
            'reference_no' => $ref,
            'reservation_from' => $userId,
            'description' => ucwords($post['description']),
            'recurring_schedule' => $recur_sched,
            'recur_on' => $recur_on,
            'frequency' => $frequency,
            'facility' => $post['facility'],
            'meeting_time_type' => $meeting_type,
            'date_from' => $date_start,
            'date_to' => $date_end,
            'contact_number' => $post['contact'],
            'remarks' => $post['remarks'],
            'created_by' => $userId,
            'created_dt' => $date
        );

        $this->db->from('tbl_events');
        // if(isset($post['recur_sched']) && $post['recur_sched']){
        //     if($recur_on == 'Weekly'){
        //         foreach(unserialize($frequency) as $frq){
        //             $this->db->or_like('frequency', $frq, 'both');
        //         }
        //     }
        // }
        $this->db->group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from <=', $date_start);
        $this->db->where('date_to >=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('facility', $post['facility']);
        $this->db->where('date_from >=', $date_start);
        $this->db->where('date_to <=', $date_end);
        $this->db->where('is_archived', 0);
        $this->db->group_end();

        $q = $this->db->get();

        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                if(isset($post['recur_sched']) && $post['recur_sched']){
                    if($row->frequency){
                        if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
                            
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));

                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);

                            foreach($recursive as $recurred){
                                if(in_array($recurred, $on_recursive)){
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                            
                        }else{
                            $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);

                            if(array_intersect($on_recursive, $recursive)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $start_date = date('Y-m-d', strtotime($row->date_from));
                                $end_date = date('Y-m-d', strtotime($row->date_to));
                                
                                $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                
                                if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
                                    $start_time = date('H:i', strtotime($row->date_from));
                                    $end_time = date('H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('H:i', strtotime($date_start));
                                    $to_check_end = date('H:i', strtotime($date_end));

                                    // $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    // $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    // $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                    // $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                    
                                    if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }else{
                                    $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                    $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                    $to_check_end = date('Y-m-d H:i', strtotime($date_end));

                                    if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }

                    }else{
                        $start_date = date('Y-m-d', strtotime($row->date_from));
                        $end_date = date('Y-m-d', strtotime($row->date_to));
                        
                        $to_check_start_date = date('Y-m-d', strtotime($date_start));
                        $to_check_end_date = date('Y-m-d', strtotime($date_end));
                        
                        
                        if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
                            
                            if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                $_check_if_recurring[] = 1;
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }else{
                            $_check_if_recurring[] = 0;
                        }
                    }
                }else{
                    $weekday = date('l', strtotime($date_start));
                    $_weekday = date('l', strtotime($row->date_from));
                    
                    if($row->frequency){
                        
                        if(in_array($weekday, unserialize($row->frequency))){
                            $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                            foreach($recursive as $_recurred){
                                
                                if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                    $start_time = date('H:i', strtotime($row->date_from));
                                    $end_time = date('H:i', strtotime($row->date_to));
                                    
                                    $to_check_start = date('H:i', strtotime($date_start));
                                    $to_check_end = date('H:i', strtotime($date_end));
                                    
                                    if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                        }else{
                            if(in_array($weekday, unserialize($row->frequency))){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
        
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }else{
                        if($weekday == $_weekday){
                            $start_time = date('H:i', strtotime($row->date_from));
                            $end_time = date('H:i', strtotime($row->date_to));
                            
                            $to_check_start = date('H:i', strtotime($date_start));
                            $to_check_end = date('H:i', strtotime($date_end));
    
                            if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                $_check_if_recurring[] = 1;
                            }else{
                                if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }
                        }
                    }
                }
            }
            
        }else{
            $_frq = unserialize($frequency);
            $this->db->from('tbl_events');
            $this->db->where('facility', $post['facility']);
            $this->db->where('date_from >=', $date_start);
            $this->db->where('is_archived', 0);
            $qs = $this->db->get();


            if($qs->num_rows() > 0){
                foreach($qs->result() as $row){
                    if(isset($post['recur_sched']) && $post['recur_sched']){
                        if($row->frequency){
                            if(array_intersect(unserialize($frequency), unserialize($row->frequency))){
                                
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
                
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                
                                foreach($recursive as $recurred){
                                    if(in_array($recurred, $on_recursive)){
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }
                                }
                                
                            }else{
                                $on_recursive = $this->getReccurringSched(unserialize($frequency), $date_start, $date_end);
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                
                                if(array_intersect($on_recursive, $recursive)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $start_date = date('Y-m-d', strtotime($row->date_from));
                                    $end_date = date('Y-m-d', strtotime($row->date_to));
                                    
                                    $to_check_start_date = date('Y-m-d', strtotime($date_start));
                                    $to_check_end_date = date('Y-m-d', strtotime($date_end));
                                    
                                    if(($start_date <= $to_check_start_date && $end_date >= $to_check_end_date)){
                                        $start_time = date('H:i', strtotime($row->date_from));
                                        $end_time = date('H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('H:i', strtotime($date_start));
                                        $to_check_end = date('H:i', strtotime($date_end));
                
                
                                        // $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        // $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        // $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        // $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                                        
                                        if(($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }else{
                                        $start_time = date('Y-m-d H:i', strtotime($row->date_from));
                                        $end_time = date('Y-m-d H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('Y-m-d H:i', strtotime($date_start));
                                        $to_check_end = date('Y-m-d H:i', strtotime($date_end));
                
                                        if(($start_time <= $to_check_start && $end_time >= $date_end) && ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            $_check_if_recurring[] = 0;
                                        }
                                    }
                                }
                            }
                
                        }else{
                            $start_date = date('Y-m-d', strtotime($row->date_from));
                            $end_date = date('Y-m-d', strtotime($row->date_to));
                            
                            $to_check_start_date = date('Y-m-d', strtotime($date_start));
                            $to_check_end_date = date('Y-m-d', strtotime($date_end));
                            
                            
                            if(($start_date == $to_check_start_date && $end_date == $to_check_end_date)){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
                                
                                if(($row->date_from <= $date_start && $row->date_to >= $date_end) || ($row->date_from >= $date_start && $row->date_to <= $date_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    $_check_if_recurring[] = 0;
                                }
                            }else{
                                $_check_if_recurring[] = 0;
                            }
                        }
                    }else{
                        $weekday = date('l', strtotime($date_start));
                        $_weekday = date('l', strtotime($row->date_from));

                        if($row->frequency){
                            if(in_array($weekday, unserialize($row->frequency))){
                                $recursive = $this->getReccurringSched(unserialize($row->frequency), $row->date_from, $row->date_to);
                                foreach($recursive as $_recurred){
                                    if(date('Y-m-d', strtotime($date_start)) == $_recurred){
                                        $start_time = date('H:i', strtotime($row->date_from));
                                        $end_time = date('H:i', strtotime($row->date_to));
                                        
                                        $to_check_start = date('H:i', strtotime($date_start));
                                        $to_check_end = date('H:i', strtotime($date_end));
                
                                        if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                            $_check_if_recurring[] = 1;
                                        }else{
                                            if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                                $_check_if_recurring[] = 1;
                                            }else{
                                                $_check_if_recurring[] = 0;
                                            }
                                        }
                                    }
                                }
                            }
                        }else{
                            if($weekday == $_weekday){
                                $start_time = date('H:i', strtotime($row->date_from));
                                $end_time = date('H:i', strtotime($row->date_to));
                                
                                $to_check_start = date('H:i', strtotime($date_start));
                                $to_check_end = date('H:i', strtotime($date_end));
                
                                if(($start_time <= $to_check_start && $end_time >= $to_check_end) || ($start_time >= $to_check_start && $end_time <= $to_check_end)){
                                    $_check_if_recurring[] = 1;
                                }else{
                                    if($start_time <= $to_check_end && $to_check_end <= $end_time){
                                        $_check_if_recurring[] = 1;
                                    }else{
                                        $_check_if_recurring[] = 0;
                                    }
                                }
                            }
                        }
                    }
                }

               
            }
        }

        return $result;
    }

    function checkEndDay($date, $end){
        $tempDay = strtolower(date('l', strtotime($date)));
        $time_end = ($tempDay == 'friday' || $tempDay == 'saturday') ? '17:00' : '18:00';
        $tempDate = null;

        if($end >= $time_end){
            $tempDate = $time_end;
        }else{
            $tempDate = $end;
        }

        return $tempDate;
    }
}
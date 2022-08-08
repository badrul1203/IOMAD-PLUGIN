<?php

require_once("$CFG->libdir/externallib.php");

class local_userdetail_external extends external_api {

    
    
    public static function getcompanydetails_parameters() {
        return new external_function_parameters(
                
            array('id' => new external_value(PARAM_INT, 'Company ID'))
        );
    }

    public static function getcompanydetails_returns() {
        return    new external_single_structure(
                array(
                    'courses' => new external_value(PARAM_RAW, 'courses HTML options'),
                    'users' => new external_value(PARAM_RAW, 'settings content text'),
                )
            );

    }
        
    public static function getcompanydetails($id) {
        global $CFG, $DB;
		
        $params = self::validate_parameters(self::getcompanydetails_parameters(), 
                array('id'=>$id));
        
        $companyid=$params['id'];
		
		$getcompusers=$DB->get_records_sql("select u.* from {user} u join {company_users} cu on (cu.userid=u.id) where cu.companyid=$companyid");
		$getcompcourses=$DB->get_records_sql("select c.* from {course} c join {company_course} cu on (cu.courseid=c.id) where cu.companyid=$companyid");
		
		$useroptionhtml = '<option value="">'.get_string('selectuser', 'local_userdetail').'</option>';
		foreach($getcompusers as $compuser){
			$useroptionhtml .= "<option value=".$compuser->id .">".fullname($compuser)."</option>";
		}
		
		$courseoptionhtml = '<option value="">'.get_string('selectcourse', 'local_userdetail').'</option>';
		foreach($getcompcourses as $compcourse){
			$courseoptionhtml .= "<option value=".$compcourse->id .">".$compcourse->fullname."</option>";
		}

        
        return array('courses'=>$courseoptionhtml, 'users'=>$useroptionhtml);
    }    
}

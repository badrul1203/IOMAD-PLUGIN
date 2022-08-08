<?php
require_once('../../config.php');
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once($CFG->libdir.'/formslib.php');

global $CFG,$DB,$USER;
require_login();
$PAGE->set_context(context_system::instance());
$pagetitle = get_string('addcompletion','local_userdetail');
$PAGE->set_url(new moodle_url("/local/userdetail/addcompletion.php"));
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('standard');



$context=context_system::instance();

$PAGE->set_context(context_system::instance());

class filter_form extends moodleform {
    function definition() {
        
        global $CFG,$DB,$id,$USER,$context, $PAGE;
        
		$classform =& $this->_form;        
		$PAGE->requires->js_call_amd('local_userdetail/companydata', 'init');
        
		$getcompanies=$DB->get_records_menu('company', array(),$sort='name', $fields='id,name');
		$getcourses=$DB->get_records_select_menu('course','id>1', array(), $sort='fullname', $fields='id,fullname');
		$getusers=$DB->get_records_select_menu('user','id>1 and suspended=0 and deleted=0 and confirmed=1 ', array(), $sort='firstname', $fields="id,concat(firstname,' ',lastname) ");
        
        $classform->addElement('select','company',get_string('companyname','local_userdetail'), array(''=>get_string('selectcompany', 'local_userdetail')) + $getcompanies);
        $classform->addRule('company',get_string('err_required','local_userdetail'),'required',null,'client');
        $classform->setType('company',PARAM_TEXT);

        
        $classform->addElement('select','course',get_string('coursename','local_userdetail'),$getcourses);
        $classform->addRule('course',get_string('err_required','local_userdetail'),'required',null,'client');
        $classform->setType('course',PARAM_TEXT);

        $classform->addElement('select','user',get_string('username','local_userdetail'),$getusers);
        $classform->addRule('user',get_string('err_required','local_userdetail'),'required',null,'client');
        $classform->setType('user',PARAM_TEXT);


        $classform->addElement('text', 'finalscroe', get_string('finalscroe', 'local_userdetail'));
        $classform->setType('finalscroe', PARAM_TEXT);
		$classform->addRule('finalscroe', get_string('required'), 'required', '', 'client', false, false);
		$classform->addRule('finalscroe', get_string('err_numeric','local_userdetail'), 'numeric', '', 'client', false, false);
      
         
        $classform->addElement('date_time_selector','compleationdate', get_string('compleationdate','local_userdetail'));	
        $classform->addRule('compleationdate',get_string('err_required','local_userdetail'),'required',null,'client');

       
        $this->add_action_buttons($cancel = true, $submitlabel=get_string('submit'));
                      
    }
  

}

$filter_form= new filter_form();


if($id)
{
    $user=$DB->get_record('userdetail',array('id'=>$id));
    $filter_form->set_data($user);
}
if($filter_form->is_cancelled())
{
    redirect(new moodle_url("/local/userdetail/filterform.php"));   
}
else if($formdata = $filter_form->get_data())
{
     $notification='';
    $isexistuser=$DB->get_record('user',array('id'=>$formdata->user));
    $iscourse = $DB->get_record('course', array('id'=>$formdata->course));	
				
        $plugin = enrol_get_plugin('manual');
		$plugin_instance = $DB->get_record("enrol", array("courseid" =>$iscourse->id, "enrol" => "manual", "status" => 0), "*", MUST_EXIST);
				
        if($isexistuser && $iscourse){
        $context = context_course::instance($formdata->course);
			if(!is_enrolled($context,$formdata->user) && enrol_is_enabled('manual')) {
				
				if($plugin_instance){
													
					$plugin->enrol_user($plugin_instance, $formdata->user, 5, time(),0);
													
				}
			   
			}
			$get_enrollment = $DB->get_record("user_enrolments", array("userid" =>$formdata->user, "enrolid" => $plugin_instance->id), "*", MUST_EXIST);
		
			
			
			$record= new stdClass();
        	$record->userid	=$formdata->user;
			$record->course = $formdata->course;
			$record->timeenrolled = $get_enrollment->timestart;
			$record->timestarted = time();
			$record->timecompleted = time();
			$record->reaggregate= $formdata->finalscroe;
			
			
			$trackrecord= new stdClass();
        	$trackrecord->userid	=$formdata->user;
			$trackrecord->courseid = $formdata->course;
			$trackrecord->coursename = $iscourse->fullname;
			$trackrecord->timeenrolled = $get_enrollment->timestart;
			$trackrecord->timestarted = time();
			$trackrecord->timecompleted = time();
			$trackrecord->companyid = $formdata->company;
			$trackrecord->finalscore= $formdata->finalscroe;
			
			
			//insertion in local_iomad_track
			 if($isexists=$DB->get_record('local_iomad_track', array('userid'=>$formdata->user,'courseid'=>$formdata->course, 'companyid'=> $formdata->company))){
				$trackrecord->id	=$isexists->id;
				$update=$DB->update_record('local_iomad_track',$trackrecord);
				$notification=get_string('completionadded', 'local_userdetail');
			}else{
				$insert=$DB->insert_record('local_iomad_track',$trackrecord);
				$notification=get_string('completionupdated', 'local_userdetail');
			}
			
			
            if($isexists=$DB->get_record('course_completions', array('userid'=>$formdata->user,'course'=>$formdata->course))){
				$record->id	=$isexists->id;
				$update=$DB->update_record('course_completions',$record);
				 $notification=get_string('completionadded', 'local_userdetail');
			}else{
				$insert=$DB->insert_record('course_completions',$record);
				 $notification=get_string('completionupdated', 'local_userdetail');
			}
            	
		
                            
        }
    

   
    
    redirect(new moodle_url("/local/userdetail/addcompletion.php"),$notification, null, \core\output\notification::NOTIFY_SUCCESS);   
    
    
}
 

 {
    echo $OUTPUT->header();
	
    $filter_form->display();

    echo $OUTPUT->footer(); 
}



?>

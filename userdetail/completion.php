<?php
require_once('../../config.php');
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once($CFG->libdir.'/formslib.php');

global $CFG,$DB,$USER;
require_login();
$id = optional_param('id',0,PARAM_INT);
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
        if($id){
			$getusrs=$DB->get_records_sql("select * from {user} u join {company_users} cu on (cu.userid=u.id) where cu.companyid=$id");
	        $getcrs=$DB->get_records_sql("select * from {course} c join {company_course} cu on (cu.courseid=c.id) where c.id>1 and cu.companyid=$id");
			$getusers=array();
			$getusers[]=get_string('selectuser','local_userdetail');
			foreach($getusrs as $getusr){
				$getusers[$getusr->id]=$getusr->firstname .' '.$getusr->lastname;
			}
			
			$getcourses=array();
			$getcourses[]=get_string('selectcourse','local_userdetail');
			foreach($getcrs as $getcr){
				$getcourses[$getcr->id]=$getcr->fullname;
			}
		
		}else{
		
		$getcourses=$DB->get_records_select_menu('course','id>1', array(), $sort='fullname', $fields='id,fullname');
		$getusers=$DB->get_records_select_menu('user','id>1 and suspended=0 and deleted=0 and confirmed=1 ', array(), $sort='firstname', $fields="id,concat(firstname,' ',lastname) ");
        }
		$options = array(                                                                                                           
              'multiple' => false,                                                  
              'noselectionstring' => get_string('notselected','local_userdetail'),
              'onchange' => 'javascript:compchange("'.$CFG->wwwroot.'/local/userdetail/completion.php?id=",this.value);'		  
         ); 
		
        $classform->addElement('select','company',get_string('companyname','local_userdetail'),$getcompanies,$options);
        $classform->addRule('company',get_string('err_required','local_userdetail'),'required',null,'client');
        $classform->setType('company',PARAM_TEXT);
		 $classform->setDefault('company',$id);

        
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

        // $classform->addElement('date_selector','compleationdate', get_string('compleationdate','local_userdetail'));	
        // $classform->addRule('compleationdate',get_string('err_required','local_userdetail'),'required',null,'client');
          
         
        
       
        
        
                  
                 
        
        if($id){
            $this->add_action_buttons($cancel = true, $submitlabel=get_string('update'));
            //$success = $classform->save_file('userfile','local/admit_card/Admit-card/img');

        }else{
            $this->add_action_buttons($cancel = true, $submitlabel=get_string('submit'));
            }           
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
    $isexistuser=$DB->get_record('user',array('id'=>$formdata->company));
    $iscourse = $DB->get_record('course', array('id'=>$formdata->company));	
				
        $plugin = enrol_get_plugin('manual');
		$plugin_instance = $DB->get_record("enrol", array("courseid" =>$iscourse->id, "enrol" => "manual", "status" => 0), "*", MUST_EXIST);
				
        if($isexistuser && $iscourse){
        $context = context_course::instance($formdata->company);
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

	echo '<script type="text/javascript">

	 function compchange(a,b){
		 
		 window.location = a+b;
	 }

	</script>';



    echo $OUTPUT->footer(); 
}



?>

<?php
require_once('../../config.php');
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
require_once($CFG->libdir.'/formslib.php');

global $CFG,$DB,$USER;
require_login();
$PAGE->set_context(context_system::instance());
$pagetitle = get_string('adduser','local_userdetail');
$PAGE->set_url(new moodle_url("/local/userdetail/adduser.php"));
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagelayout('standard');
$context=context_system::instance();


class user_form extends moodleform {
    function definition() {
        
        global $CFG,$DB,$USER;
        
		$classform =& $this->_form;        
        
        

        $classform->addElement('text', 'firstname', get_string('firstname', 'local_userdetail'));
        $classform->setType('firstname', PARAM_TEXT);
		$classform->addRule('firstname', get_string('required'), 'required', '', 'client', false, false);

        $classform->addElement('text', 'lastname', get_string('lastname', 'local_userdetail'));
        $classform->setType('lastname', PARAM_TEXT);
		$classform->addRule('lastname', get_string('required'), 'required', '', 'client', false, false);

        $classform->addElement('text', 'username', get_string('username', 'local_userdetail'));
        $classform->setType('username', PARAM_TEXT);
        $classform->addRule('username', get_string('required'), 'required', '', 'client', false, false); 
		$classform->addRule('username', get_string('err_alphanumeric','local_userdetail'), 'alphanumeric', '', 'client', false, false);

      
        $classform->addElement('passwordunmask', 'password', get_string('password','local_userdetail'));
        $classform->setType('password', PARAM_TEXT);
        $classform->addRule('password', get_string('required'), 'required', '', 'client', false, false);

        $classform->addElement('text', 'email', get_string('email', 'local_userdetail'));
        $classform->setType('email', PARAM_TEXT);
	    $classform->addRule('email', get_string('required'), 'required', '', 'client', false, false);
         
        
        $this->add_action_buttons($cancel = true, $submitlabel=get_string('submit'));
                      
    }
  

}

$user_form= new user_form();


if($user_form->is_cancelled())
{
    redirect(new moodle_url("/local/userdetail/adduser.php"));   
}
else if($formdata = $user_form->get_data())
{
	 $notification='';
	if(is_object($formdata)){
		        if(!empty($formdata->email) && !empty($formdata->username) && !empty($formdata->firstname) && !empty($formdata->lastname) && !empty($formdata->password)){
                    $formdata->modifierid = $USER->id;
                    $formdata->timemodified = time();
                    $formdata->password = hash_internal_user_password($formdata->password);
                    $formdata->confirmed = 1;
					$formdata->mnethostid = 1;
					$formdata->username = strtolower($formdata->username);
					
					

                    if($isexist = $DB->get_record('user', array('email'=>$formdata->email))){
                        $formdata->id = $isexist->id;
                        $userupdate = user_update_user($formdata, false, false);
                        $notification =get_string('user_update_message','local_userdetail');
                    } else {
						$formdata->timecreated = time();
                        $formdata->id = user_create_user($formdata, false, false);
                        $notification =get_string('user_add_message','local_userdetail');
                    }
				}
    }				
    
    
  
    redirect(new moodle_url("/local/userdetail/adduser.php"),$notification, null, \core\output\notification::NOTIFY_SUCCESS);   
    
    
}
 

 {
    echo $OUTPUT->header();
	
    $user_form->display();
    echo $OUTPUT->footer(); 
}



?>
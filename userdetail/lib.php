<?php
function local_userdetail_extend_navigation(global_navigation $navigation)
{
    
    global $CFG, $PAGE, $USER,$DB;
    
    if(isloggedin()){
		$link1= $navigation->add(get_string('addcompletion','local_userdetail'),new moodle_url('/local/userdetail/addcompletion.php'), navigation_node::TYPE_SETTING, null, null, new pix_icon('i/courseevent', ''));
		$link1->display=true;
		$link1->showinflatnavigation=true;
		
		$link2= $navigation->add(get_string('adduser','local_userdetail'),new moodle_url('/local/userdetail/adduser.php'), navigation_node::TYPE_SETTING, null, null, new pix_icon('i/completion_self', ''));
		$link2->display=true;
		$link2->showinflatnavigation=true;
	}
    
}


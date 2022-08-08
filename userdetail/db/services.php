<?php

$services = array(
      'mypluginservice' => array(                      //the name of the web service
          'functions' => array ('local_userdetail_get_company_details'), 
          'requiredcapability' => '',                //if set, the web service user need this capability to access 
                                                     //any function of this service. For example: 'some/capability:specified'                 
          'restrictedusers' =>0,                      //if enabled, the Moodle administrator must link some user to this service
                                                      //into the administration
          'enabled'=>1,                               //if enabled, the service can be reachable on a default installation
          'shortname'=>'companydataservice' //the short name used to refer to this service from elsewhere including when fetching a token
       )
  );

$functions = array(
    'local_userdetail_get_company_details' => array(
        'classname' => 'local_userdetail_external',
        'methodname' => 'getcompanydetails',
        'classpath' => 'local/userdetail/externallib.php',
        'description' => 'Get company data',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    )
    
);

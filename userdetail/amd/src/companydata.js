define(['jquery', 'core/str','core/ajax'], function($, mdlstrings, Ajax) {
    var preassessment = {

        init : function() {
			$( document ).ready(function() {
				 Ajax.call([{
					   methodname: 'local_userdetail_get_company_details',
					   args: {id: 0},
					   done: function(response) {
						   $("#id_user").html(response.users);
						   $("#id_course").html(response.courses);
					   },
				   }]);
				   
				$('#id_company').on('change', function(e) {
					var company = $(this).val();
				  
					Ajax.call([{
					   methodname: 'local_userdetail_get_company_details',
					   args: {id: company},
					   done: function(response) {
						   $("#id_user").html(response.users);
						   $("#id_course").html(response.courses);
					   },
				   }]);
					
				  });
			});
            
        },
        
    } 
    return preassessment;
});

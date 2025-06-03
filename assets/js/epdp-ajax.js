var epdp_vars;
jQuery(document).ready(function ($) {

	/* = Process demo button click
	====================================================================================== */
	$(document).on('click', '.epdp_launch_demo', function(e) {
		e.preventDefault();
		var demo_key  = $(this).data('demo-ref'),
            query_sep = epdp_vars.registration_page.indexOf( '?' ) > 0 ? '&' : '?',
			page      = epdp_vars.registration_page + query_sep + 'demo_ref=' + demo_key;

		window.location = page;
	});
});

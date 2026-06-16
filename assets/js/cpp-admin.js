
jQuery(document).ready(function($) {
	$('.notice.is-dismissible').on('click', '.notice-dismiss', function() {
		$.post(ajaxurl, {
			action: 'alg_wc_dismiss_cron_notice'
		});
	});
});

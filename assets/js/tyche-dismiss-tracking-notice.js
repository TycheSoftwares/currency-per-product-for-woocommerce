/**
 * Tyche Softwares.
 *
 * Tracking notice js.
 *
 * @package TycheSoftwares/PluginTracking
 * @since   6.8
*/
jQuery( document ).ready(
	function() {
		jQuery( '.notice.is-dismissible' ).each(
			function() {
				var $this   = jQuery( this ),
					$button = jQuery( '<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>' ),
					btnText = wp.i18n.dismiss || '';

				// Ensure plain text.
				$button.find( '.screen-reader-text' ).text( btnText );

				$this.append( $button );

				/**
				 * Event when close icon is clicked.
				 *
				 * @fires event:notice-dismiss
				 * @since 6.8
				*/
				$button.on(
					'click.notice-dismiss',
					function( event ) {
						event.preventDefault();
						$this.fadeTo(
							100,
							0,
							function() {
								jQuery( this ).slideUp(
									100,
									function() {
										jQuery( this ).remove();
										jQuery.post(
											cpp_ts_dismiss_notice.ts_admin_url,
											{
												action: cpp_ts_dismiss_notice.ts_prefix_of_plugin + "_tracker_dismiss_notice",
												tracking_notice : cpp_ts_dismiss_notice.tracking_notice
											},
											function( response ) {}
										);
									}
								);
							}
						);
					}
				);
			}
		);
	}
);

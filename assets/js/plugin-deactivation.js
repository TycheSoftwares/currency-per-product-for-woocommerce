var $cpp_tyche_plugin_deactivation_modal = {},
	$tyche_plugin_name                   = 'cpp_lite';

( function() {

	if ( 'undefined' === typeof tyche.plugin_deactivation || 'undefined' === typeof window[ `tyche_plugin_deactivation_${$tyche_plugin_name}_js` ] ) {
		return;
	}

	$cpp_tyche_plugin_deactivation_modal = tyche.plugin_deactivation.modal( $tyche_plugin_name, window[ `tyche_plugin_deactivation_${$tyche_plugin_name}_js` ] );

	if ( '' !== $cpp_tyche_plugin_deactivation_modal ) {
		tyche.plugin_deactivation.events.listeners( window[ `tyche_plugin_deactivation_${$tyche_plugin_name}_js` ], $cpp_tyche_plugin_deactivation_modal, $tyche_plugin_name );
	}
} )();

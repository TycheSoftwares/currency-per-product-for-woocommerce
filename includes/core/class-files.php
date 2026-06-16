<?php
/**
 * Currency Per Product - Admin Files Class
 *
 * Class for including files for the Admin.
 *
 * @author      Tyche Softwares
 * @package     CPP/Admin/Files
 * @category    Classes
 * @since       2.0
 */

namespace Tyche\CPP;

use Tyche\CPP\Admin\Admin_Scripts;
use Tyche\CPP\Admin\Product;
use Tyche\CPP\Admin\Widget;

defined( 'ABSPATH' ) || exit;

/**
 * CON Admin Files.
 *
 * @since 1.0
 */
class Files {

	/**
	 * Include files.
	 *
	 * @since 1.0
	 */
	public static function include_files() {

        CPP()::include_file( 'api/class-admin-api.php' );
        CPP()::include_file( 'api/class-general.php' );
        CPP()::include_file( 'api/class-behaviour.php' );
        CPP()::include_file( 'api/class-currencies.php' );
        CPP()::include_file( 'api/class-exchange-rates.php' );
        CPP()::include_file( 'api/class-advanced.php' );
        CPP()::include_file( 'api/class-store.php' );
        CPP()::include_file( 'api/class-tools.php' );

        $tyche_files = array(
            'class-tyche-cpp-tracking.php',
            'class-tyche-cpp-deactivation.php',
        );

        foreach ( $tyche_files as $tyche_file ) {
            if ( file_exists( CPP_PLUGIN_DIR_PATH . '/includes/' . $tyche_file ) ) {
               CPP()::include_file( $tyche_file );
            }
        }

		CPP()::include_file( 'functions/class-functions.php' );
		CPP()::include_file( 'functions/class-exchange-rates-functions.php' );

		CPP()::include_file( 'core/class-crons.php' );

		CPP()::include_file( 'admin/class-admin.php' );
		CPP()::include_file( 'admin/class-product.php' );
		
		// Scripts.
		CPP()::include_file( 'admin/class-admin-scripts.php' );
		new Admin_Scripts();

		CPP()::include_file( 'core/class-migration.php' );

		//Frontend
		CPP()::include_file( 'frontend/class-frontend.php' );

		CPP()::include_file( 'admin/class-currency-reports.php' );
	}

	/**
	 * Loads Dependency Files.
	 * If there are required files needed ( to be included before ) for the execution of the view file, those dependencies can be added here.
	 *
	 * @param string $section Section Directory.
	 * @param string $filename File in the section Directory to be loaded.
	 * @since 5.19.0
	 */
	public static function load_dependencies( $section, $filename ) {

		if ( '' === $section || '' === $filename ) {
			return;
		}
	}
}
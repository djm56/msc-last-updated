<?php
/**
 * Uninstall MSC Last Updated.
 *
 * @return void
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'msclu_options' );

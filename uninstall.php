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

if ( false ) {
	$hook = 'msc-last-updated_cron_event';
	$next = wp_next_scheduled( $hook );

	while ( $next ) {
		wp_unschedule_event( $next, $hook );
		$next = wp_next_scheduled( $hook );
	}
}

if ( false ) {
	global $wpdb;
	$pattern = $wpdb->esc_like( 'msc-last-updated_' ) . '%';
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
			$pattern
		)
	);
}

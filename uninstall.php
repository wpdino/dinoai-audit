<?php
/**
 * Uninstall DinoAI Audit.
 *
 * @package WPDINO\DinoAIAudit
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Deletes plugin options for the current site.
 *
 * @return void
 */
function dinoai_audit_delete_site_options() {
	delete_option( 'dinoai_audit_settings' );
	delete_option( 'dinoai_audit_usage' );
	delete_option( 'dinoai_audit_last_audit' );
	delete_option( 'dinoai_audit_version' );
	delete_transient( 'dinoai_audit_activation_redirect' );
}

if ( is_multisite() ) {
	$site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 500,
		)
	);

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		dinoai_audit_delete_site_options();
		restore_current_blog();
	}
} else {
	dinoai_audit_delete_site_options();
}

$users = get_users(
	array(
		'fields'       => 'ids',
		'meta_key'     => 'dinoai_audit_onboarding_dismissed', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- uninstall cleanup.
		'meta_compare' => 'EXISTS',
	)
);

foreach ( $users as $user_id ) {
	delete_user_meta( (int) $user_id, 'dinoai_audit_onboarding_dismissed' );
}

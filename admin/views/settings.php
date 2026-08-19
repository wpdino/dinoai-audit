<?php
/**
 * Settings screen.
 *
 * @package WPDINO\DinoAIAudit
 *
 * @var \WPDINO\DinoAIAudit\Settings $settings
 * @var array<string, mixed>    $usage
 * @var string                  $tab
 */

defined( 'ABSPATH' ) || exit;

$dinoai_audit_all           = $settings->all();
$dinoai_audit_modules       = $dinoai_audit_all['modules'];
$dinoai_audit_api_key       = $settings->get_api_key();
$dinoai_audit_masked        = $settings->get_masked_api_key();
$dinoai_audit_module_fields = array(
	'content' => array(
		'label'       => __( 'AI Content', 'dinoai-audit' ),
		'description' => __( 'Improve, rewrite, shorten, expand, or summarize selected text in the editor.', 'dinoai-audit' ),
		'icon'        => 'dashicons-edit',
	),
	'seo'     => array(
		'label'       => __( 'AI SEO', 'dinoai-audit' ),
		'description' => __( 'Generate an SEO title, meta description, and keyword suggestions.', 'dinoai-audit' ),
		'icon'        => 'dashicons-chart-area',
	),
	'images'  => array(
		'label'       => __( 'AI Image Alt Text', 'dinoai-audit' ),
		'description' => __( 'Generate accessibility-focused alt text for images.', 'dinoai-audit' ),
		'icon'        => 'dashicons-format-image',
	),
	'audit'   => array(
		'label'       => __( 'AI Site Audit', 'dinoai-audit' ),
		'description' => __( 'Run a basic WordPress site audit with a score and recommendations.', 'dinoai-audit' ),
		'icon'        => 'dashicons-chart-bar',
	),
);

$dinoai_audit_settings_tabs = array(
	'general'  => array(
		'title' => __( 'General', 'dinoai-audit' ),
		'icon'  => 'dashicons-admin-generic',
	),
	'provider' => array(
		'title' => __( 'AI Provider', 'dinoai-audit' ),
		'icon'  => 'dashicons-admin-network',
	),
	'usage'    => array(
		'title' => __( 'Usage', 'dinoai-audit' ),
		'icon'  => 'dashicons-chart-area',
	),
	'about'    => array(
		'title' => __( 'About', 'dinoai-audit' ),
		'icon'  => 'dashicons-info',
	),
);

/**
 * Filters DinoAI Audit settings tabs.
 *
 * @since 1.0.0
 *
 * @param array<string, array<string, string>> $dinoai_audit_settings_tabs Tab id => data.
 */
$dinoai_audit_settings_tabs = apply_filters( 'dinoai_audit_settings_tabs', $dinoai_audit_settings_tabs );

$dinoai_audit_header_title   = __( 'Welcome to DinoAI Audit!', 'dinoai-audit' );
$dinoai_audit_header_tagline = __( 'AI-powered WordPress website audits and optimization', 'dinoai-audit' );
?>
<div class="wpdino-settings-wrap">
	<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/header.php'; ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display flag after Settings API save.
	if ( isset( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) ) :
		?>
		<div class="wpdino-admin-notice wpdino-admin-notice-success">
			<span class="dashicons dashicons-yes-alt"></span>
			<span><?php esc_html_e( 'Settings saved successfully!', 'dinoai-audit' ); ?></span>
			<button type="button" class="wpdino-notice-dismiss">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
	<?php endif; ?>

	<div class="wpdino-main">
		<div class="wpdino-content">
			<div class="wpdino-card">
				<div class="wpdino-tabs-nav" role="tablist" aria-label="<?php esc_attr_e( 'Settings tabs', 'dinoai-audit' ); ?>">
					<?php foreach ( $dinoai_audit_settings_tabs as $dinoai_audit_tab_id => $dinoai_audit_tab_data ) : ?>
						<?php $dinoai_audit_is_active = ( $tab === $dinoai_audit_tab_id ); ?>
						<button type="button" class="wpdino-tab-btn<?php echo $dinoai_audit_is_active ? ' active' : ''; ?>" data-tab="<?php echo esc_attr( $dinoai_audit_tab_id ); ?>" role="tab" aria-selected="<?php echo $dinoai_audit_is_active ? 'true' : 'false'; ?>" aria-controls="tab-<?php echo esc_attr( $dinoai_audit_tab_id ); ?>" id="tab-<?php echo esc_attr( $dinoai_audit_tab_id ); ?>-btn">
							<span class="dashicons <?php echo esc_attr( $dinoai_audit_tab_data['icon'] ); ?>"></span>
							<?php echo esc_html( $dinoai_audit_tab_data['title'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<form method="post" action="options.php" class="wpdino-form" autocomplete="off">
					<?php settings_fields( 'dinoai_audit_settings_group' ); ?>

					<div class="wpdino-tab-content<?php echo 'general' === $tab ? ' active' : ''; ?>" id="tab-general" role="tabpanel" aria-labelledby="tab-general-btn">
						<div class="wpdino-section">
							<h3 class="wpdino-section-title">
								<span class="dashicons dashicons-admin-generic"></span>
								<?php esc_html_e( 'General', 'dinoai-audit' ); ?>
							</h3>
							<p class="wpdino-section-description"><?php esc_html_e( 'Disable modules you do not need. Settings and the AI provider remain available.', 'dinoai-audit' ); ?></p>
						</div>

						<div class="dinoai-audit-module-grid">
							<?php foreach ( $dinoai_audit_module_fields as $dinoai_audit_key => $dinoai_audit_field ) : ?>
								<div class="dinoai-audit-module-card">
									<input type="hidden" name="dinoai_audit_settings[modules][<?php echo esc_attr( $dinoai_audit_key ); ?>]" value="0" />
									<label>
										<span class="dinoai-audit-tile-icon" aria-hidden="true">
											<span class="dashicons <?php echo esc_attr( $dinoai_audit_field['icon'] ); ?>"></span>
										</span>
										<span class="dinoai-audit-module-copy">
											<span class="dinoai-audit-module-title"><?php echo esc_html( $dinoai_audit_field['label'] ); ?></span>
											<span class="dinoai-audit-module-desc"><?php echo esc_html( $dinoai_audit_field['description'] ); ?></span>
										</span>
										<span class="dinoai-audit-switch">
											<input type="checkbox" name="dinoai_audit_settings[modules][<?php echo esc_attr( $dinoai_audit_key ); ?>]" value="1" <?php checked( ! empty( $dinoai_audit_modules[ $dinoai_audit_key ] ) ); ?> />
											<span class="dinoai-audit-switch-ui" aria-hidden="true"></span>
										</span>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="wpdino-tab-content<?php echo 'provider' === $tab ? ' active' : ''; ?>" id="tab-provider" role="tabpanel" aria-labelledby="tab-provider-btn">
						<div class="wpdino-section">
							<h3 class="wpdino-section-title">
								<span class="dashicons dashicons-admin-network"></span>
								<?php esc_html_e( 'AI Provider', 'dinoai-audit' ); ?>
							</h3>
							<p class="wpdino-section-description"><?php esc_html_e( 'Connect your own OpenAI API key. The key is stored on this site and is never shown in full after saving.', 'dinoai-audit' ); ?></p>
						</div>

						<div class="dinoai-audit-provider-stack">
						<div class="wpdino-field-group">
							<label for="dinoai-audit-provider" class="wpdino-label"><?php esc_html_e( 'AI Provider', 'dinoai-audit' ); ?></label>
							<select name="dinoai_audit_settings[provider]" id="dinoai-audit-provider" class="wpdino-select">
								<?php foreach ( \WPDINO\DinoAIAudit\AI_Manager::available_providers() as $dinoai_audit_slug => $dinoai_audit_label ) : ?>
									<option value="<?php echo esc_attr( $dinoai_audit_slug ); ?>" <?php selected( $dinoai_audit_all['provider'], $dinoai_audit_slug ); ?>><?php echo esc_html( $dinoai_audit_label ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="wpdino-description"><?php esc_html_e( 'DinoAI Audit connects with OpenAI using your own API key.', 'dinoai-audit' ); ?></p>
						</div>

						<div class="wpdino-field-group">
							<label for="dinoai-audit-api-key" class="wpdino-label"><?php esc_html_e( 'OpenAI API Key', 'dinoai-audit' ); ?></label>
							<div class="wpdino-password-wrapper">
								<input
									type="text"
									id="dinoai-audit-api-key"
									name="dinoai_audit_settings[api_key]"
									value="<?php echo esc_attr( $dinoai_audit_masked ); ?>"
									class="wpdino-input wpdino-password-input"
									placeholder="sk-..."
									autocomplete="off"
									data-lpignore="true"
									data-form-type="other"
									data-actual-value="<?php echo esc_attr( $dinoai_audit_api_key ); ?>"
									data-masked="<?php echo '' !== $dinoai_audit_api_key ? 'true' : 'false'; ?>"
								/>
								<button type="button" class="wpdino-password-toggle" data-target="#dinoai-audit-api-key" aria-label="<?php esc_attr_e( 'Toggle API key visibility', 'dinoai-audit' ); ?>" aria-pressed="false">
									<span class="dashicons dashicons-visibility"></span>
									<span class="dashicons dashicons-hidden"></span>
								</button>
							</div>
							<p class="wpdino-description">
								<?php
								echo wp_kses(
									sprintf(
										/* translators: 1: OpenAI API keys URL, 2: OpenAI Terms of Use URL, 3: OpenAI Privacy Policy URL */
										__( 'Enter your OpenAI API key for DinoAI Audit. Get your key from %1$s. Using the key sends content you choose to OpenAI. Review their %2$s and %3$s.', 'dinoai-audit' ),
										'<a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer">https://platform.openai.com/api-keys</a>',
										'<a href="https://openai.com/policies/terms-of-use" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Terms of Use', 'dinoai-audit' ) . '</a>',
										'<a href="https://openai.com/policies/privacy-policy" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Privacy Policy', 'dinoai-audit' ) . '</a>'
									),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
											'rel'    => array(),
										),
									)
								);
								?>
							</p>
						</div>

						<div class="wpdino-field-group">
							<label for="dinoai-audit-model" class="wpdino-label"><?php esc_html_e( 'Default AI Model', 'dinoai-audit' ); ?></label>
							<select name="dinoai_audit_settings[model]" id="dinoai-audit-model" class="wpdino-select">
								<?php foreach ( \WPDINO\DinoAIAudit\AI_Manager::allowed_models( $dinoai_audit_all['provider'] ) as $dinoai_audit_model ) : ?>
									<option value="<?php echo esc_attr( $dinoai_audit_model ); ?>" <?php selected( $dinoai_audit_all['model'], $dinoai_audit_model ); ?>><?php echo esc_html( \WPDINO\DinoAIAudit\AI_Manager::model_label( $dinoai_audit_model ) ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="wpdino-description">
								<?php
								echo wp_kses(
									sprintf(
										/* translators: %s: OpenAI models documentation URL */
										__( 'Select the default AI model to use for content generation. Image alt text needs a vision-capable model such as GPT-4o mini. %s', 'dinoai-audit' ),
										'<a href="https://platform.openai.com/docs/models" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Check available models', 'dinoai-audit' ) . '</a>'
									),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
											'rel'    => array(),
										),
									)
								);
								?>
							</p>
						</div>

						<div class="wpdino-field-group">
							<label class="wpdino-label"><?php esc_html_e( 'Connection test', 'dinoai-audit' ); ?></label>
							<div class="dinoai-audit-inline-actions">
								<button type="button" class="wpdino-btn wpdino-btn-secondary" id="dinoai-audit-test-connection">
									<span class="dashicons dashicons-yes"></span>
									<?php esc_html_e( 'Test connection', 'dinoai-audit' ); ?>
								</button>
								<span class="dinoai-audit-test-status" id="dinoai-audit-test-status" aria-live="polite"></span>
							</div>
							<p class="wpdino-description"><?php esc_html_e( 'Saves are required before testing a newly entered API key.', 'dinoai-audit' ); ?></p>
						</div>
						</div>
					</div>

					<div class="wpdino-tab-content<?php echo 'usage' === $tab ? ' active' : ''; ?>" id="tab-usage" role="tabpanel" aria-labelledby="tab-usage-btn">
						<?php require DINOAI_AUDIT_PATH . 'admin/views/usage.php'; ?>
					</div>

					<div class="wpdino-tab-content<?php echo 'about' === $tab ? ' active' : ''; ?>" id="tab-about" role="tabpanel" aria-labelledby="tab-about-btn">
						<?php require DINOAI_AUDIT_PATH . 'admin/views/about-content.php'; ?>
					</div>

					<?php
					/**
					 * Fires after the built-in DinoAI Audit settings tab panels.
					 *
					 * @since 1.0.0
					 *
					 * @param string $tab Active tab id.
					 */
					do_action( 'dinoai_audit_settings_tab_panels', $tab );
					?>

					<div class="wpdino-form-footer">
						<div class="wpdino-form-actions-left">
							<button type="submit" class="wpdino-btn wpdino-btn-primary">
								<span class="dashicons dashicons-yes"></span>
								<?php esc_html_e( 'Save Changes', 'dinoai-audit' ); ?>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>

		<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/sidebar.php'; ?>
	</div>

	<?php require DINOAI_AUDIT_PATH . 'admin/views/partials/footer.php'; ?>
</div>

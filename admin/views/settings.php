<?php
/**
 * Settings screen.
 *
 * @package WPDINO\DinoAI
 *
 * @var \WPDINO\DinoAI\Settings $settings
 * @var array<string, mixed>    $usage
 * @var string                  $tab
 */

defined( 'ABSPATH' ) || exit;

$all     = $settings->all();
$modules = $all['modules'];
$api_key = $settings->get_api_key();
$masked  = $settings->get_masked_api_key();

$module_fields = array(
	'content' => array(
		'label'       => __( 'AI Content', 'dinoai' ),
		'description' => __( 'Improve, rewrite, shorten, expand, or summarize selected text in the editor.', 'dinoai' ),
		'icon'        => 'dashicons-edit',
	),
	'seo'     => array(
		'label'       => __( 'AI SEO', 'dinoai' ),
		'description' => __( 'Generate an SEO title, meta description, and keyword suggestions.', 'dinoai' ),
		'icon'        => 'dashicons-chart-area',
	),
	'images'  => array(
		'label'       => __( 'AI Image Alt Text', 'dinoai' ),
		'description' => __( 'Generate accessibility-focused alt text for images.', 'dinoai' ),
		'icon'        => 'dashicons-format-image',
	),
	'audit'   => array(
		'label'       => __( 'AI Site Audit', 'dinoai' ),
		'description' => __( 'Run a basic WordPress site audit with a score and recommendations.', 'dinoai' ),
		'icon'        => 'dashicons-chart-bar',
	),
);

$settings_tabs = array(
	'general'  => array(
		'title' => __( 'General', 'dinoai' ),
		'icon'  => 'dashicons-admin-generic',
	),
	'provider' => array(
		'title' => __( 'AI Provider', 'dinoai' ),
		'icon'  => 'dashicons-admin-network',
	),
	'usage'    => array(
		'title' => __( 'Usage', 'dinoai' ),
		'icon'  => 'dashicons-chart-area',
	),
	'about'    => array(
		'title' => __( 'About', 'dinoai' ),
		'icon'  => 'dashicons-info',
	),
);

/**
 * Filters DinoAI settings tabs.
 *
 * @since 1.0.0
 *
 * @param array<string, array<string, string>> $settings_tabs Tab id => data.
 */
$settings_tabs = apply_filters( 'dinoai_settings_tabs', $settings_tabs );

$dinoai_header_title   = __( 'Welcome to DinoAI!', 'dinoai' );
$dinoai_header_tagline = __( 'AI Toolkit for WordPress', 'dinoai' );
?>
<div class="wpdino-settings-wrap">
	<?php require DINOAI_PATH . 'admin/views/partials/header.php'; ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display flag after Settings API save.
	if ( isset( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) ) :
		?>
		<div class="wpdino-admin-notice wpdino-admin-notice-success">
			<span class="dashicons dashicons-yes-alt"></span>
			<span><?php esc_html_e( 'Settings saved successfully!', 'dinoai' ); ?></span>
			<button type="button" class="wpdino-notice-dismiss">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
	<?php endif; ?>

	<div class="wpdino-main">
		<div class="wpdino-content">
			<div class="wpdino-card">
				<div class="wpdino-tabs-nav" role="tablist" aria-label="<?php esc_attr_e( 'Settings tabs', 'dinoai' ); ?>">
					<?php foreach ( $settings_tabs as $tab_id => $tab_data ) : ?>
						<?php $is_active = ( $tab === $tab_id ); ?>
						<button type="button" class="wpdino-tab-btn<?php echo $is_active ? ' active' : ''; ?>" data-tab="<?php echo esc_attr( $tab_id ); ?>" role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>" aria-controls="tab-<?php echo esc_attr( $tab_id ); ?>" id="tab-<?php echo esc_attr( $tab_id ); ?>-btn">
							<span class="dashicons <?php echo esc_attr( $tab_data['icon'] ); ?>"></span>
							<?php echo esc_html( $tab_data['title'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<form method="post" action="options.php" class="wpdino-form" autocomplete="off">
					<?php settings_fields( 'dinoai_settings_group' ); ?>

					<div class="wpdino-tab-content<?php echo 'general' === $tab ? ' active' : ''; ?>" id="tab-general" role="tabpanel" aria-labelledby="tab-general-btn">
						<div class="wpdino-section">
							<h3 class="wpdino-section-title">
								<span class="dashicons dashicons-admin-generic"></span>
								<?php esc_html_e( 'General', 'dinoai' ); ?>
							</h3>
							<p class="wpdino-section-description"><?php esc_html_e( 'Disable modules you do not need. Settings and the AI provider remain available.', 'dinoai' ); ?></p>
						</div>

						<div class="dinoai-module-grid">
							<?php foreach ( $module_fields as $key => $field ) : ?>
								<div class="dinoai-module-card">
									<input type="hidden" name="dinoai_settings[modules][<?php echo esc_attr( $key ); ?>]" value="0" />
									<label>
										<span class="dinoai-tile-icon" aria-hidden="true">
											<span class="dashicons <?php echo esc_attr( $field['icon'] ); ?>"></span>
										</span>
										<span class="dinoai-module-copy">
											<span class="dinoai-module-title"><?php echo esc_html( $field['label'] ); ?></span>
											<span class="dinoai-module-desc"><?php echo esc_html( $field['description'] ); ?></span>
										</span>
										<span class="dinoai-switch">
											<input type="checkbox" name="dinoai_settings[modules][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $modules[ $key ] ) ); ?> />
											<span class="dinoai-switch-ui" aria-hidden="true"></span>
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
								<?php esc_html_e( 'AI Provider', 'dinoai' ); ?>
							</h3>
							<p class="wpdino-section-description"><?php esc_html_e( 'Connect your own OpenAI API key. The key is stored on this site and is never shown in full after saving.', 'dinoai' ); ?></p>
						</div>

						<div class="dinoai-provider-stack">
						<div class="wpdino-field-group">
							<label for="dinoai-provider" class="wpdino-label"><?php esc_html_e( 'AI Provider', 'dinoai' ); ?></label>
							<select name="dinoai_settings[provider]" id="dinoai-provider" class="wpdino-select">
								<?php foreach ( \WPDINO\DinoAI\AI_Manager::available_providers() as $slug => $label ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $all['provider'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="wpdino-description"><?php esc_html_e( 'DinoAI Lite connects with OpenAI using your own API key.', 'dinoai' ); ?></p>
						</div>

						<div class="wpdino-field-group">
							<label for="dinoai-api-key" class="wpdino-label"><?php esc_html_e( 'OpenAI API Key', 'dinoai' ); ?></label>
							<div class="wpdino-password-wrapper">
								<input
									type="text"
									id="dinoai-api-key"
									name="dinoai_settings[api_key]"
									value="<?php echo esc_attr( $masked ); ?>"
									class="wpdino-input wpdino-password-input"
									placeholder="sk-..."
									autocomplete="off"
									data-lpignore="true"
									data-form-type="other"
									data-actual-value="<?php echo esc_attr( $api_key ); ?>"
									data-masked="<?php echo '' !== $api_key ? 'true' : 'false'; ?>"
								/>
								<button type="button" class="wpdino-password-toggle" data-target="#dinoai-api-key" aria-label="<?php esc_attr_e( 'Toggle API key visibility', 'dinoai' ); ?>" aria-pressed="false">
									<span class="dashicons dashicons-visibility"></span>
									<span class="dashicons dashicons-hidden"></span>
								</button>
							</div>
							<p class="wpdino-description">
								<?php
								echo wp_kses(
									sprintf(
										/* translators: %s: OpenAI API keys URL */
										__( 'Enter your OpenAI API key for DinoAI. Get your key from %s', 'dinoai' ),
										'<a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer">https://platform.openai.com/api-keys</a>'
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
							<label for="dinoai-model" class="wpdino-label"><?php esc_html_e( 'Default AI Model', 'dinoai' ); ?></label>
							<select name="dinoai_settings[model]" id="dinoai-model" class="wpdino-select">
								<?php foreach ( \WPDINO\DinoAI\AI_Manager::allowed_models( $all['provider'] ) as $model ) : ?>
									<option value="<?php echo esc_attr( $model ); ?>" <?php selected( $all['model'], $model ); ?>><?php echo esc_html( \WPDINO\DinoAI\AI_Manager::model_label( $model ) ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="wpdino-description">
								<?php
								echo wp_kses(
									sprintf(
										/* translators: %s: OpenAI models documentation URL */
										__( 'Select the default AI model to use for content generation. Image alt text needs a vision-capable model such as GPT-4o mini. %s', 'dinoai' ),
										'<a href="https://platform.openai.com/docs/models" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Check available models', 'dinoai' ) . '</a>'
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
							<label class="wpdino-label"><?php esc_html_e( 'Connection test', 'dinoai' ); ?></label>
							<div class="dinoai-inline-actions">
								<button type="button" class="wpdino-btn wpdino-btn-secondary" id="dinoai-test-connection">
									<span class="dashicons dashicons-yes"></span>
									<?php esc_html_e( 'Test connection', 'dinoai' ); ?>
								</button>
								<span class="dinoai-test-status" id="dinoai-test-status" aria-live="polite"></span>
							</div>
							<p class="wpdino-description"><?php esc_html_e( 'Saves are required before testing a newly entered API key.', 'dinoai' ); ?></p>
						</div>
						</div>
					</div>

					<div class="wpdino-tab-content<?php echo 'usage' === $tab ? ' active' : ''; ?>" id="tab-usage" role="tabpanel" aria-labelledby="tab-usage-btn">
						<?php require DINOAI_PATH . 'admin/views/usage.php'; ?>
					</div>

					<div class="wpdino-tab-content<?php echo 'about' === $tab ? ' active' : ''; ?>" id="tab-about" role="tabpanel" aria-labelledby="tab-about-btn">
						<?php require DINOAI_PATH . 'admin/views/about-content.php'; ?>
					</div>

					<?php
					/**
					 * Fires after the built-in DinoAI settings tab panels.
					 *
					 * @since 1.0.0
					 *
					 * @param string $tab Active tab id.
					 */
					do_action( 'dinoai_settings_tab_panels', $tab );
					?>

					<div class="wpdino-form-footer">
						<div class="wpdino-form-actions-left">
							<button type="submit" class="wpdino-btn wpdino-btn-primary">
								<span class="dashicons dashicons-yes"></span>
								<?php esc_html_e( 'Save Changes', 'dinoai' ); ?>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>

		<?php require DINOAI_PATH . 'admin/views/partials/sidebar.php'; ?>
	</div>

	<?php require DINOAI_PATH . 'admin/views/partials/footer.php'; ?>
</div>

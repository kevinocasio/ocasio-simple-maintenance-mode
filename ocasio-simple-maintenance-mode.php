<?php
/**
 * Plugin Name: Ocasio Simple Maintenance Mode
 * Plugin URI:  https://kevinocasio.com/wordpress-plugins/ocasio-simple-maintenance-mode/
 * Description: A clean, modern maintenance mode splash page. Take your site offline for visitors with one click while admins browse normally.
 * Version:     1.0.0
 * Author:      Kevin Ocasio
 * Author URI:  https://kevinocasio.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ocasio-simple-maintenance-mode
 */

if (!defined('ABSPATH')) {
	exit;
}

define('OCASIO_SMM_VERSION', '1.0.0');

/**
 * Smart Brand URL Helper (Local-first testing support)
 */
function ocasio_smm_brand_url($path = '/tools/') {
	if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.local') !== false) {
		return home_url($path);
	}
	return 'https://kevinocasio.com' . $path;
}

/**
 * 1. Activation Defaults
 */
function ocasio_smm_activate() {
	if (get_option('ocasio_smm_enabled') === false) {
		$old = get_option('ko_smm_enabled', '0');
		update_option('ocasio_smm_enabled', $old);
	}
	if (get_option('ocasio_smm_headline') === false) {
		$old = get_option('ko_smm_headline', "We're Building Something Great");
		update_option('ocasio_smm_headline', $old);
	}
	if (get_option('ocasio_smm_message') === false) {
		$old = get_option('ko_smm_message', "Our site is currently undergoing scheduled maintenance. Please check back soon.");
		update_option('ocasio_smm_message', $old);
	}
	if (get_option('ocasio_smm_logo') === false) {
		$old = get_option('ko_smm_logo', '');
		update_option('ocasio_smm_logo', $old);
	}
	if (get_option('ocasio_smm_logo_width') === false) {
		$old = get_option('ko_smm_logo_width', '260');
		update_option('ocasio_smm_logo_width', $old);
	}
}
register_activation_hook(__FILE__, 'ocasio_smm_activate');

/**
 * 2. Register Settings
 */
function ocasio_smm_register_settings() {
	register_setting('ocasio_smm_options_group', 'ocasio_smm_enabled', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '0',
	));
	register_setting('ocasio_smm_options_group', 'ocasio_smm_headline', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => "We're Building Something Great",
	));
	register_setting('ocasio_smm_options_group', 'ocasio_smm_message', array(
		'type'              => 'string',
		'sanitize_callback' => 'wp_kses_post',
		'default'           => "Our site is currently undergoing scheduled maintenance. Please check back soon.",
	));
	register_setting('ocasio_smm_options_group', 'ocasio_smm_logo', array(
		'type'              => 'string',
		'sanitize_callback' => 'esc_url_raw',
		'default'           => '',
	));
	register_setting('ocasio_smm_options_group', 'ocasio_smm_logo_width', array(
		'type'              => 'integer',
		'sanitize_callback' => 'absint',
		'default'           => 260,
	));
}
add_action('admin_init', 'ocasio_smm_register_settings');

/**
 * 3. Front-End Maintenance Interceptor
 */
function ocasio_smm_render_maintenance_page() {
	$is_preview = ((isset($_GET['ocasio_smm_preview']) && $_GET['ocasio_smm_preview'] === '1') || (isset($_GET['ko_smm_preview']) && $_GET['ko_smm_preview'] === '1')) && current_user_can('manage_options');

	if (!$is_preview) {
		$enabled = get_option('ocasio_smm_enabled', null);
		if ($enabled === null) {
			$enabled = get_option('ko_smm_enabled', '0');
		}
		if ((int) $enabled !== 1) {
			return;
		}

		if (current_user_can('manage_options') || is_user_logged_in()) {
			return;
		}

		$protocol = wp_get_server_protocol();
		header("$protocol 503 Service Unavailable", true, 503);
		header('Content-Type: text/html; charset=utf-8');
		header('Retry-After: 3600');
	}

	$headline = get_option('ocasio_smm_headline', null);
	if ($headline === null) {
		$headline = get_option('ko_smm_headline', "We're Building Something Great");
	}
	$message = get_option('ocasio_smm_message', null);
	if ($message === null) {
		$message = get_option('ko_smm_message', "Our site is currently undergoing scheduled maintenance. Please check back soon.");
	}
	$logo = get_option('ocasio_smm_logo', null);
	if ($logo === null) {
		$logo = get_option('ko_smm_logo', '');
	}
	$logo_width = get_option('ocasio_smm_logo_width', null);
	if ($logo_width === null) {
		$logo_width = (int) get_option('ko_smm_logo_width', 260);
	} else {
		$logo_width = (int) $logo_width;
	}
	if ($logo_width < 50 || $logo_width > 1000) {
		$logo_width = 260;
	}
	$card_max_width = max(680, min(1000, $logo_width + 96));
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo esc_html($headline); ?></title>
		<style>
			* {
				box-sizing: border-box;
			}
			body {
				margin: 0;
				padding: 20px;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				background-color: #0b1329;
				background-image: radial-gradient(at 50% 0%, #1e293b 0%, #0b1329 100%);
				color: #0f172a;
				min-height: 100vh;
				display: flex;
				align-items: center;
				justify-content: center;
			}
			.ko-smm-card {
				background: #ffffff;
				border: 1px solid #e2e8f0;
				border-radius: 16px;
				padding: 60px 48px;
				max-width: <?php echo esc_attr($card_max_width); ?>px;
				width: 100%;
				text-align: center;
				box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
			}
			.ko-smm-logo {
				width: 100%;
				height: auto;
				margin: 0 auto 28px auto;
				display: block;
				object-fit: contain;
			}}
			.ko-smm-headline {
				font-size: 32px;
				font-weight: 800;
				color: #0f172a;
				line-height: 1.25;
				margin: 0 0 16px 0;
				letter-spacing: -0.5px;
			}
			.ko-smm-message {
				font-size: 17px;
				line-height: 1.65;
				color: #64748b;
				margin: 0 auto;
				max-width: 540px;
			}
			.ko-smm-message a {
				color: #e11d48;
				text-decoration: underline;
				font-weight: 600;
				transition: color 0.15s ease;
			}
			.ko-smm-message a:hover {
				color: #be123c;
			}
			.ko-smm-preview-banner {
				position: fixed;
				top: 16px;
				left: 50%;
				transform: translateX(-50%);
				background: #0f172a;
				color: #ffffff;
				font-size: 13px;
				font-weight: 600;
				padding: 8px 18px;
				border-radius: 9999px;
				border: 1px solid rgba(255, 255, 255, 0.15);
				box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
				z-index: 99999;
				display: flex;
				align-items: center;
				gap: 8px;
			}
			.ko-smm-preview-pill {
				background: #e11d48;
				color: #ffffff;
				font-size: 10.5px;
				font-weight: 800;
				text-transform: uppercase;
				letter-spacing: 0.5px;
				padding: 2px 7px;
				border-radius: 4px;
			}
			@media (max-width: 600px) {
				.ko-smm-card {
					padding: 40px 24px;
				}
				.ko-smm-headline {
					font-size: 25px;
				}
				.ko-smm-message {
					font-size: 15px;
				}
			}
		</style>
	</head>
	<body>
		<?php if ($is_preview): ?>
			<div class="ko-smm-preview-banner">
				<span class="ko-smm-preview-pill">Preview</span>
				<span>Visible only to logged-in administrators</span>
			</div>
		<?php endif; ?>

		<div class="ko-smm-card">
			<?php if (!empty($logo)): ?>
				<img src="<?php echo esc_url($logo); ?>" alt="Site Logo" class="ko-smm-logo" style="max-width:<?php echo esc_attr($logo_width); ?>px;">
			<?php endif; ?>

			<h1 class="ko-smm-headline"><?php echo esc_html($headline); ?></h1>
			<p class="ko-smm-message"><?php echo wp_kses_post(nl2br($message)); ?></p>
		</div>
	</body>
	</html>
	<?php
	exit();
}
add_action('template_redirect', 'ocasio_smm_render_maintenance_page');

/**
 * 4. Settings Page Action Link
 */
function ocasio_smm_settings_action_link($links) {
	$settings_link = '<a href="' . esc_url(admin_url('admin.php?page=ocasio-simple-maintenance-mode')) . '">' . esc_html__('Settings', 'ocasio-simple-maintenance-mode') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ocasio_smm_settings_action_link');

/**
 * 5. Admin Menu Registration
 */
function ocasio_smm_admin_menu() {
	if (empty($GLOBALS['admin_page_hooks']['ocasio-plugins-main'])) {
		$icon_url = plugins_url('assets/favicon.svg', __FILE__);

		add_menu_page(
			'Ocasio Plugins',
			'Ocasio Plugins',
			'manage_options',
			'ocasio-plugins-main',
			'ocasio_plugins_suite_dashboard_html',
			$icon_url,
			65
		);

		add_submenu_page(
			'ocasio-plugins-main',
			'Ocasio Plugins Suite',
			'Dashboard',
			'manage_options',
			'ocasio-plugins-main',
			'ocasio_plugins_suite_dashboard_html'
		);
	}

	add_submenu_page(
		'ocasio-plugins-main',
		'Maintenance Mode',
		'Maintenance',
		'manage_options',
		'ocasio-simple-maintenance-mode',
		'ocasio_smm_render_settings_page'
	);
}
add_action('admin_menu', 'ocasio_smm_admin_menu');

/**
 * 6. Enqueue Admin Assets
 */
function ocasio_smm_admin_assets($hook) {
	wp_add_inline_style('common', '#adminmenu .toplevel_page_ocasio-plugins-main .wp-menu-image img { width:20px!important; height:20px!important; padding:5px 0 0 0!important; opacity:1!important; }');

	if (strpos($hook, 'ocasio-simple-maintenance-mode') === false && $hook !== 'toplevel_page_ocasio-plugins-main' && strpos($hook, 'ocasio-plugins-main') === false) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'ocasio-smm-admin-css',
		plugins_url('assets/admin.css', __FILE__),
		array(),
		OCASIO_SMM_VERSION
	);

	wp_enqueue_script(
		'ocasio-smm-admin-js',
		plugins_url('assets/admin.js', __FILE__),
		array('jquery'),
		OCASIO_SMM_VERSION,
		true
	);

	wp_localize_script('ocasio-smm-admin-js', 'ocasioSmmVars', array(
		'ajax_url'    => admin_url('admin-ajax.php'),
		'nonce'       => wp_create_nonce('ocasio_smm_save_nonce'),
		'suite_nonce' => wp_create_nonce('ocasio_suite_toggle_nonce'),
	));
	wp_localize_script('ocasio-smm-admin-js', 'ocasio_vars', array(
		'ajaxurl'     => admin_url('admin-ajax.php'),
		'suite_nonce' => wp_create_nonce('ocasio_suite_toggle_nonce'),
	));
}
add_action('admin_enqueue_scripts', 'ocasio_smm_admin_assets');

/**
 * 7. AJAX Settings Save Handler
 */
function ocasio_smm_ajax_save_settings() {
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => 'Unauthorized access.'), 403);
	}

	check_ajax_referer('ocasio_smm_save_nonce', 'nonce');

	$enabled    = (isset($_POST['ocasio_smm_enabled']) && $_POST['ocasio_smm_enabled'] === '1') ? '1' : '0';
	$headline   = isset($_POST['ocasio_smm_headline']) ? sanitize_text_field(wp_unslash($_POST['ocasio_smm_headline'])) : "We're Building Something Great";
	$message    = isset($_POST['ocasio_smm_message']) ? wp_kses_post(wp_unslash($_POST['ocasio_smm_message'])) : "Our site is currently undergoing scheduled maintenance. Please check back soon.";
	$logo       = isset($_POST['ocasio_smm_logo']) ? esc_url_raw(wp_unslash($_POST['ocasio_smm_logo'])) : '';
	$logo_width = isset($_POST['ocasio_smm_logo_width']) ? absint($_POST['ocasio_smm_logo_width']) : 260;

	if ($logo_width < 50 || $logo_width > 1000) {
		$logo_width = 260;
	}

	update_option('ocasio_smm_enabled', $enabled);
	update_option('ocasio_smm_headline', $headline);
	update_option('ocasio_smm_message', $message);
	update_option('ocasio_smm_logo', $logo);
	update_option('ocasio_smm_logo_width', $logo_width);

	wp_send_json_success(array(
		'message'    => 'Settings saved successfully.',
		'enabled'    => $enabled,
		'logo_width' => $logo_width,
	));
}
add_action('wp_ajax_ocasio_smm_save_settings', 'ocasio_smm_ajax_save_settings');

/**
 * 8. Dedicated Settings Page HTML
 */
function ocasio_smm_render_settings_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$author_url     = ocasio_smm_brand_url('/');
	$hub_url        = ocasio_smm_brand_url('/wordpress-plugins/');
	$preview_url    = home_url('?ocasio_smm_preview=1');
	$logo_val       = get_option('ocasio_smm_logo', null);
	if ($logo_val === null) {
		$logo_val = get_option('ko_smm_logo', '');
	}
	$logo_width_val = get_option('ocasio_smm_logo_width', null);
	if ($logo_width_val === null) {
		$logo_width_val = get_option('ko_smm_logo_width', '260');
	}
	$enabled_val = get_option('ocasio_smm_enabled', null);
	if ($enabled_val === null) {
		$enabled_val = get_option('ko_smm_enabled', '0');
	}
	$is_enabled = ((int) $enabled_val === 1);

	$headline_val = get_option('ocasio_smm_headline', null);
	if ($headline_val === null) {
		$headline_val = get_option('ko_smm_headline', "We're Building Something Great");
	}

	$message_val = get_option('ocasio_smm_message', null);
	if ($message_val === null) {
		$message_val = get_option('ko_smm_message', "Our site is currently undergoing scheduled maintenance. Please check back soon.");
	}
	?>
	<div class="wrap ko-plugin-wrap">
		<div class="ko-plugin-card" style="max-width:680px;">
			<!-- Header (Rule 1: Red Bricolage + Crisp White Inter) -->
			<div class="ko-plugin-header">
				<h1 class="ko-plugin-header-title">
					<span class="ko-logo-ocasio">OCASIO</span>
					<span class="ko-title-text">SIMPLE MAINTENANCE MODE</span>
				</h1>
			</div>

			<!-- Body Stage -->
			<div class="ko-plugin-body">
				<p class="ko-plugin-intro">Take your site offline for visitors with a clean splash page. Admins can still browse the site normally.</p>

				<div id="ocasio-smm-active-banner" style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:10px 14px; border-radius:6px; margin-bottom:18px; font-size:13px; font-weight:600; display:<?php echo $is_enabled ? 'flex' : 'none'; ?>; align-items:center; gap:8px;">
					<span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#059669;"></span>
					<span>Maintenance Mode is active. The public site is currently hidden from visitors.</span>
				</div>

				<form method="post" action="options.php" id="ocasio-settings-form">
					<?php settings_fields('ocasio_smm_options_group'); ?>

					<div class="ko-setting-box">
						<!-- Row 1: Enable Toggle -->
						<div class="ko-setting-row" style="padding-top:0;">
							<div class="ko-setting-info">
								<strong>Enable Maintenance Mode</strong>
								<p>Turn this on to show the maintenance splash page to the public.</p>
							</div>
							<label class="ko-switch">
								<input type="hidden" name="ocasio_smm_enabled" value="0">
								<input type="checkbox" name="ocasio_smm_enabled" value="1" <?php checked($is_enabled, true); ?>>
								<span class="ko-slider"></span>
							</label>
						</div>

						<!-- Row 2: Headline -->
						<div class="ko-setting-row" style="flex-direction:column; align-items:flex-start; gap:8px;">
							<div class="ko-setting-info">
								<strong>Page Headline</strong>
								<p>The main title shown on the maintenance splash page.</p>
							</div>
							<input type="text" name="ocasio_smm_headline" value="<?php echo esc_attr($headline_val); ?>" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13.5px;">
						</div>

						<!-- Row 3: Message Body -->
						<div class="ko-setting-row" style="flex-direction:column; align-items:flex-start; gap:8px;">
							<div class="ko-setting-info">
								<strong>Message Body</strong>
								<p>Explain why the site is undergoing maintenance (HTML and links supported).</p>
							</div>
							<textarea name="ocasio_smm_message" rows="3" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13.5px; font-family:inherit;"><?php echo esc_textarea($message_val); ?></textarea>
						</div>

						<!-- Row 4: Logo Image -->
						<div class="ko-setting-row" style="flex-direction:column; align-items:flex-start; gap:8px;">
							<div class="ko-setting-info">
								<strong>Logo Image (Optional)</strong>
								<p>Select an image from your Media Library or enter an image URL.</p>
							</div>
							<div style="display:flex; gap:8px; width:100%; align-items:center;">
								<input type="text" name="ocasio_smm_logo" id="ocasio-smm-logo" value="<?php echo esc_attr($logo_val); ?>" placeholder="https://..." style="flex:1; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13.5px;">
								<button type="button" class="button" id="ocasio-smm-logo-btn" style="height:36px; line-height:1; font-size:13px; font-weight:600; background:#f1f5f9; border:1px solid #cbd5e1; color:#0f172a; padding:0 14px; border-radius:6px; cursor:pointer;">Choose Image</button>
								<button type="button" class="button" id="ocasio-smm-remove-logo-btn" style="height:36px; line-height:1; font-size:13px; font-weight:600; background:#fef2f2; border:1px solid #fecaca; color:#e11d48; padding:0 12px; border-radius:6px; cursor:pointer; <?php echo empty($logo_val) ? 'display:none;' : 'display:inline-flex;'; ?> align-items:center;">Remove</button>
							</div>

							<!-- Centered Logo Preview Stage -->
							<div id="ocasio-smm-logo-preview-wrap" style="box-sizing:border-box; width:100%; margin-top:14px; padding:20px; background:#ffffff; border:1px dashed #cbd5e1; border-radius:8px; <?php echo empty($logo_val) ? 'display:none;' : 'display:flex;'; ?> flex-direction:column; align-items:center; justify-content:center; gap:10px;">
								<img id="ocasio-smm-logo-preview" src="<?php echo esc_url($logo_val); ?>" alt="Logo Preview" style="max-width:100%; max-height:260px; height:auto; object-fit:contain; border-radius:4px;">
								<span style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Logo Preview</span>
							</div>
						</div>

						<!-- Row 5: Logo Width -->
						<div class="ko-setting-row" style="flex-direction:column; align-items:flex-start; gap:8px; border-bottom:none; padding-bottom:0;">
							<div class="ko-setting-info">
								<strong>Logo Width (px)</strong>
								<p>Set the display width for your logo or banner graphic on the splash page (default: 260px).</p>
							</div>
							<div style="display:flex; align-items:center; gap:8px;">
								<input type="number" name="ocasio_smm_logo_width" id="ocasio-smm-logo-width" value="<?php echo esc_attr($logo_width_val); ?>" min="50" max="1000" step="10" style="width:120px; padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-size:13.5px;">
								<span style="font-size:13px; color:#64748b; font-weight:600;">px</span>
							</div>
						</div>
					</div>

					<div class="ko-submit-wrap" style="display:flex; align-items:center; justify-content:center; gap:12px;">
						<?php
						$is_saved  = (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true');
						$btn_text  = $is_saved ? 'Settings Saved!' : 'Save Settings';
						$btn_class = 'ko-btn-submit' . ($is_saved ? ' ko-btn-saved' : '');
						?>
						<button type="submit" name="submit" id="ocasio-save-btn" class="<?php echo esc_attr($btn_class); ?>">
							<?php echo esc_html($btn_text); ?>
						</button>

						<a href="<?php echo esc_url($preview_url); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; border:1px solid #cbd5e1; color:#0f172a; font-size:14px; font-weight:600; padding:8px 18px; border-radius:6px; text-decoration:none; transition:all 0.15s ease;" onmouseover="this.style.background='#e2e8f0'; this.style.borderColor='#0f172a'; this.style.color='#e11d48';" onmouseout="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';">
							<span>Live Preview</span>
							<span class="dashicons dashicons-external" style="font-size:13px; width:13px; height:13px;"></span>
						</a>
					</div>

					<div style="text-align:center; margin-top:14px;">
						<a href="#" id="ocasio-smm-reset-btn" style="color:#94a3b8; font-size:12px; text-decoration:none; font-weight:600; transition:color 0.15s ease;" onmouseover="this.style.color='#e11d48'" onmouseout="this.style.color='#94a3b8'">
							Reset Default Text
						</a>
					</div>
				</form>
			</div>

			<!-- Card Footer -->
			<div class="ko-plugin-footer">
				<p><a href="<?php echo esc_url($author_url); ?>" target="_blank" rel="noopener noreferrer">Kevin Ocasio</a> built this and other <a href="<?php echo esc_url($hub_url); ?>" target="_blank" rel="noopener noreferrer">WordPress plugins</a>.</p>
			</div>
		</div>
	</div>
	<?php
}

// -------------------------------------------------------------------------
// 9. MASTER OCASIO PLUGINS SUITE DASHBOARD CALLBACK (VERBATIM RULE 13)
// -------------------------------------------------------------------------

if (!function_exists('ocasio_plugins_suite_dashboard_html')) {
	function ocasio_plugins_suite_dashboard_html() {
		$all_plugins = array(
			'ocasio-admin-bar-hider' => array(
				'title'         => 'Admin Bar Hider',
				'desc'          => 'Hides the front-end WordPress admin bar for all users with a single toggle.',
				'file'          => 'ocasio-admin-bar-hider/ocasio-admin-bar-hider.php',
				'fallback_file' => 'ko-admin-bar-hider/ko-admin-bar-hider.php',
				'opt_toggle'    => 'ocasio_abh_enabled',
				'fallback_opt'  => 'ko_abh_enabled',
				'has_options'   => false,
			),
			'ocasio-admin-username-changer' => array(
				'title'         => 'Admin Username Changer',
				'desc'          => 'Safely changes the primary administrator username directly without touching phpMyAdmin.',
				'file'          => 'ocasio-admin-username-changer/ocasio-admin-username-changer.php',
				'fallback_file' => 'ko-admin-username-changer/ko-admin-username-changer.php',
				'fallback_slug' => 'ko-admin-username-changer',
				'opt_toggle'    => null,
				'has_options'   => true,
			),
			'ocasio-auto-copyright-year' => array(
				'title'         => 'Auto Copyright Year',
				'desc'          => 'Displays the current year, symbol, or translated text via simple shortcodes.',
				'file'          => 'ocasio-auto-copyright-year/ocasio-auto-copyright-year.php',
				'fallback_file' => 'ko-auto-copyright-year/ko-auto-copyright-year.php',
				'fallback_slug' => 'ko-auto-copyright-year',
				'opt_toggle'    => null,
				'has_options'   => true,
			),
			'ocasio-clean-image-filenames' => array(
				'title'         => 'Clean Image Filenames',
				'desc'          => 'Sanitizes uploaded media filenames into clean, lowercase, URL-friendly slugs.',
				'file'          => 'ocasio-clean-image-filenames/ocasio-clean-image-filenames.php',
				'fallback_file' => 'ko-clean-image-filenames/ko-clean-image-filenames.php',
				'opt_toggle'    => 'ocasio_cif_enabled',
				'fallback_opt'  => 'ko_cif_enabled',
				'has_options'   => false,
			),
			'ocasio-comment-link-remover' => array(
				'title'         => 'Comment Link Remover',
				'desc'          => 'Strips hyperlinked website URLs from author comments to eliminate backlink spam.',
				'file'          => 'ocasio-comment-link-remover/ocasio-comment-link-remover.php',
				'fallback_file' => 'ko-comment-link-remover/ko-comment-link-remover.php',
				'opt_toggle'    => 'ocasio_clr_enabled',
				'fallback_opt'  => 'ko_clr_enabled',
				'has_options'   => false,
			),
			'ocasio-disable-comments-globally' => array(
				'title'         => 'Disable Comments Globally',
				'desc'          => 'Closes comments and trackbacks across the entire site, posts, and media.',
				'file'          => 'ocasio-disable-comments-globally/ocasio-disable-comments-globally.php',
				'fallback_file' => 'ko-disable-comments-globally/ko-disable-comments-globally.php',
				'opt_toggle'    => 'ocasio_dcg_enabled',
				'fallback_opt'  => 'ko_dcg_enabled',
				'has_options'   => false,
			),
			'ocasio-disable-emojis' => array(
				'title'         => 'Disable Emojis',
				'desc'          => 'Removes WordPress core emoji scripts, styles, and DNS prefetch requests to boost page speed.',
				'file'          => 'ocasio-disable-emojis/ocasio-disable-emojis.php',
				'fallback_file' => 'ko-disable-emojis/ko-disable-emojis.php',
				'opt_toggle'    => 'ocasio_de_enabled',
				'fallback_opt'  => 'ko_de_enabled',
				'has_options'   => false,
			),
			'ocasio-disable-gutenberg' => array(
				'title'         => 'Disable Gutenberg',
				'desc'          => 'Restores the Classic Editor and removes block library CSS for a cleaner authoring workflow.',
				'file'          => 'ocasio-disable-gutenberg/ocasio-disable-gutenberg.php',
				'fallback_file' => 'ko-disable-gutenberg/ko-disable-gutenberg.php',
				'opt_toggle'    => 'ocasio_dg_enabled',
				'fallback_opt'  => 'ko_dg_enabled',
				'has_options'   => false,
			),
			'ocasio-disable-xml-rpc' => array(
				'title'         => 'Disable XML-RPC',
				'desc'          => 'Blocks XML-RPC API access to protect your site against brute-force attacks.',
				'file'          => 'ocasio-disable-xml-rpc/ocasio-disable-xml-rpc.php',
				'fallback_file' => 'ko-disable-xml-rpc/ko-disable-xml-rpc.php',
				'opt_toggle'    => 'ocasio_dxml_enabled',
				'fallback_opt'  => 'ko_dxml_enabled',
				'has_options'   => false,
			),
			'ocasio-duplicate-post-button' => array(
				'title'         => 'Duplicate Post Button',
				'desc'          => 'Adds a one-click Clone action to duplicate any post or page into a new draft.',
				'file'          => 'ocasio-duplicate-post-button/ocasio-duplicate-post-button.php',
				'fallback_file' => 'ko-duplicate-post-button/ko-duplicate-post-button.php',
				'fallback_slug' => 'ko-duplicate-post-button',
				'opt_toggle'    => null,
				'has_options'   => true,
			),
			'ocasio-estimated-reading-time' => array(
				'title'         => 'Estimated Reading Time',
				'desc'          => 'Calculates and displays article read time above post content automatically.',
				'file'          => 'ocasio-estimated-reading-time/ocasio-estimated-reading-time.php',
				'fallback_file' => 'ko-estimated-reading-time/ko-estimated-reading-time.php',
				'opt_toggle'    => 'ocasio_ert_enabled',
				'fallback_opt'  => 'ko_ert_enabled',
				'has_options'   => false,
			),
			'ocasio-external-links-new-tab' => array(
				'title'         => 'External Links New Tab',
				'desc'          => 'Forces external links to open in a new tab with target="_blank" and rel="noopener".',
				'file'          => 'ocasio-external-links-new-tab/ocasio-external-links-new-tab.php',
				'fallback_file' => 'ko-external-links-new-tab/ko-external-links-new-tab.php',
				'opt_toggle'    => 'ocasio_elnt_enabled',
				'fallback_opt'  => 'ko_elnt_enabled',
				'has_options'   => false,
			),
			'ocasio-hide-version' => array(
				'title'         => 'Hide Version',
				'desc'          => 'Removes WordPress version generator tags and script query strings for security.',
				'file'          => 'ocasio-hide-version/ocasio-hide-version.php',
				'fallback_file' => 'ko-hide-version/ko-hide-version.php',
				'opt_toggle'    => 'ocasio_hv_enabled',
				'fallback_opt'  => 'ko_hv_enabled',
				'has_options'   => false,
			),
			'ocasio-limit-login-attempts' => array(
				'title'         => 'Limit Login Attempts',
				'desc'          => 'Throttles repeated failed login attempts by IP address to block brute-force attacks.',
				'file'          => 'ocasio-limit-login-attempts/ocasio-limit-login-attempts.php',
				'fallback_file' => 'ko-limit-login-attempts/ko-limit-login-attempts.php',
				'fallback_slug' => 'ko-limit-login-attempts',
				'opt_toggle'    => null,
				'has_options'   => true,
			),
			'ocasio-show-current-template' => array(
				'title'         => 'Show Current Template',
				'desc'          => 'Displays active template hierarchy filename in the admin bar for developers.',
				'file'          => 'ocasio-show-current-template/ocasio-show-current-template.php',
				'fallback_file' => 'ko-show-current-template/ko-show-current-template.php',
				'opt_toggle'    => 'ocasio_sct_enabled',
				'fallback_opt'  => 'ko_sct_enabled',
				'has_options'   => false,
			),
			'ocasio-301-redirect-manager' => array(
				'title'         => '301 Redirect Manager',
				'desc'          => 'Manages 301 permanent redirects and fixes broken links cleanly inside WordPress.',
				'file'          => 'ocasio-301-redirect-manager/ocasio-301-redirect-manager.php',
				'fallback_file' => 'ko-simple-301-redirects/ko-simple-301-redirects.php',
				'fallback_slug' => 'ko-simple-301-redirects',
				'opt_toggle'    => null,
				'has_options'   => true,
			),
			'ocasio-simple-maintenance-mode' => array(
				'title'         => 'Simple Maintenance Mode',
				'desc'          => 'Displays a clean splash page to visitors while admins work on the site.',
				'file'          => 'ocasio-simple-maintenance-mode/ocasio-simple-maintenance-mode.php',
				'fallback_file' => 'ko-simple-maintenance-mode/ko-simple-maintenance-mode.php',
				'fallback_slug' => 'ko-simple-maintenance-mode',
				'opt_toggle'    => null,
				'has_options'   => true,
			),
		);

		if (!function_exists('is_plugin_active')) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();
		$active_count      = 0;

		foreach ($all_plugins as $slug => $data) {
			$active_file = $data['file'];
			if (!is_plugin_active($active_file) && !empty($data['fallback_file']) && is_plugin_active($data['fallback_file'])) {
				$active_file = $data['fallback_file'];
			}
			if (is_plugin_active($active_file)) {
				$active_count++;
			}
		}

		$author_url = 'https://kevinocasio.com/';
		$hub_url    = 'https://kevinocasio.com/wordpress-plugins/';
		?>
		<div class="wrap ko-dash-wrap">
			<div class="ko-dash-hero">
				<div class="ko-dash-hero-left">
					<h1>
						<a href="<?php echo esc_url($hub_url); ?>" target="_blank" rel="noopener noreferrer" class="ko-dash-logo">
							<span class="ko-logo-ocasio">OCASIO</span>
							<span class="ko-logo-suite">PLUGINS SUITE</span>
						</a>
					</h1>
				</div>
				<div class="ko-dash-hero-right">
					<span class="ko-dash-count-pill"><?php echo esc_html($active_count); ?> of 17 Active</span>
				</div>
			</div>

			<div class="ko-dash-grid">
				<?php
				foreach ($all_plugins as $slug => $data):
					$active_file = $data['file'];
					if (!is_plugin_active($active_file) && !empty($data['fallback_file']) && is_plugin_active($data['fallback_file'])) {
						$active_file = $data['fallback_file'];
					}
					$is_installed = isset($installed_plugins[$active_file]) || isset($installed_plugins[$data['file']]) || (!empty($data['fallback_file']) && isset($installed_plugins[$data['fallback_file']]));
					$is_active    = is_plugin_active($active_file);
					$is_fallback  = ($active_file !== $data['file'] && !empty($data['fallback_file']));
					$page_slug    = ($is_fallback && !empty($data['fallback_slug'])) ? $data['fallback_slug'] : $slug;
					$settings_url = admin_url('admin.php?page=' . $page_slug);
					$activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . urlencode($data['file'])), 'activate-plugin_' . $data['file']);
					?>
					<div class="ko-dash-card">
						<div class="ko-dash-card-header">
							<h3 class="ko-dash-card-title"><?php echo esc_html($data['title']); ?></h3>
							<?php if ($is_active): ?>
								<?php if (!empty($data['opt_toggle'])):
									$opt_key      = (!empty($data['fallback_opt']) && get_option($data['opt_toggle'], null) === null) ? $data['fallback_opt'] : $data['opt_toggle'];
									$toggle_state = (int) get_option($opt_key, 1);
									$b_class      = ($toggle_state === 1) ? 'badge-active' : 'badge-paused';
									$b_label      = ($toggle_state === 1) ? 'Active' : 'Paused';
									?>
									<span class="ko-dash-badge <?php echo esc_attr($b_class); ?>" id="badge-<?php echo esc_attr($slug); ?>"><?php echo esc_html($b_label); ?></span>
								<?php else: ?>
									<span class="ko-dash-badge badge-active">Active</span>
								<?php endif; ?>
							<?php elseif ($is_installed): ?>
								<span class="ko-dash-badge badge-inactive">Inactive</span>
							<?php else: ?>
								<span class="ko-dash-badge badge-available">Available</span>
							<?php endif; ?>
						</div>

						<p class="ko-dash-card-desc"><?php echo esc_html($data['desc']); ?></p>

						<div class="ko-dash-card-footer">
							<?php if ($is_active): ?>
								<?php if ($data['has_options']): ?>
									<a href="<?php echo esc_url($settings_url); ?>" class="ko-dash-btn-primary">Manage Settings</a>
								<?php elseif (!empty($data['opt_toggle'])):
									$opt_key    = (!empty($data['fallback_opt']) && get_option($data['opt_toggle'], null) === null) ? $data['fallback_opt'] : $data['opt_toggle'];
									$toggle_val = (int) get_option($opt_key, 1);
									?>
									<div class="ko-dash-card-toggle-row">
										<span class="ko-dash-toggle-label">Active on Site</span>
										<div class="ko-dash-toggle-action">
											<span class="ko-dash-saved-pill" id="saved-<?php echo esc_attr($slug); ?>" style="display:none;">Saved</span>
											<label class="ko-switch">
												<input type="checkbox"
													class="ko-ajax-toggle"
													data-slug="<?php echo esc_attr($slug); ?>"
													data-option="<?php echo esc_attr($opt_key); ?>"
													value="1" <?php checked($toggle_val, 1); ?>>
												<span class="ko-slider"></span>
											</label>
										</div>
									</div>
								<?php else: ?>
									<span class="ko-dash-badge badge-active">Active</span>
								<?php endif; ?>
							<?php elseif ($is_installed): ?>
								<a href="<?php echo esc_url($activate_url); ?>" class="ko-dash-btn-activate">Activate</a>
							<?php else: ?>
								<a href="<?php echo esc_url($hub_url); ?>" target="_blank" rel="noopener noreferrer" class="ko-dash-btn-outline">Learn More</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ko-dash-global-footer">
				<p>Built with pride by <a href="<?php echo esc_url($author_url); ?>" target="_blank" rel="noopener noreferrer">Kevin Ocasio</a>. Explore all <a href="<?php echo esc_url($hub_url); ?>" target="_blank" rel="noopener noreferrer">17 lightweight WordPress tools</a>.</p>
			</div>
		</div>
		<?php
	}
}

// -------------------------------------------------------------------------
// 5. MASTER AJAX HANDLER FOR DASHBOARD GRID IN-CARD TOGGLES
// -------------------------------------------------------------------------

if (!function_exists('ocasio_suite_save_toggle_ajax_callback')) {
	function ocasio_suite_save_toggle_ajax_callback() {
		check_ajax_referer('ocasio_suite_toggle_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error('Unauthorized', 403);
		}

		$option_name  = isset($_POST['option_name']) ? sanitize_key($_POST['option_name']) : '';
		$option_value = isset($_POST['option_value']) ? absint($_POST['option_value']) : 0;

		$allowed_options = array(
			'ko_abh_enabled',
			'ko_cif_enabled',
			'ko_clr_enabled',
			'ko_dcg_enabled',
			'ko_de_enabled',
			'ko_dg_enabled',
			'ko_dxml_enabled',
			'ko_elnt_enabled',
			'ko_ert_enabled',
			'ko_hv_enabled',
			'ko_sct_enabled',
			'ocasio_abh_enabled',
			'ocasio_cif_enabled',
			'ocasio_clr_enabled',
			'ocasio_dcg_enabled',
			'ocasio_de_enabled',
			'ocasio_dg_enabled',
			'ocasio_dxml_enabled',
			'ocasio_elnt_enabled',
			'ocasio_ert_enabled',
			'ocasio_hv_enabled',
			'ocasio_sct_enabled',
		);

		if (in_array($option_name, $allowed_options, true)) {
			update_option($option_name, $option_value);
			wp_send_json_success();
		}

		wp_send_json_error('Invalid option key');
	}
	add_action('wp_ajax_ocasio_suite_save_toggle', 'ocasio_suite_save_toggle_ajax_callback');
}

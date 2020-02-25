<?php
/*
Plugin Name: CSS Reminder
Description: If you have switched themes but forgot to copy your Custom CSS, let CSS Reminder come to the rescue. This plugin finds and displays all the custom CSS from themes, including those that have been inactivated or uninstalled. It lets you copy the CSS so that you can add it to the Additional CSS option in the customizer.
Author: Poena
Version: 1.0
Text Domain: css-reminder
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Register a custom menu page.
 */
function css_reminder_register_menu_page() {
	add_menu_page(
		__( 'CSS Remider', 'css-reminder' ),
		__( 'CSS Reminder', 'css-reminder' ),
		'manage_options',
		'css_reminder',
		'css_reminder_menu_page',
		'dashicons-editor-code',
		80
	);
}
add_action( 'admin_menu', 'css_reminder_register_menu_page' );

/**
 * Enqueue scripts for copying the code.
 * jQuery is already loaded in the admin, so we don't need to require it here.
 */
function css_reminder_admin_scripts( $hook ) {
	if ( 'toplevel_page_css_reminder' !== $hook ) {
		return;
	}
	wp_enqueue_script( 'css-reminder-copy', plugins_url( 'js/copy.js', __FILE__ ), array( 'clipboard' ), true );
	wp_enqueue_style( 'css-reminder-style', plugins_url( 'css/css-reminder.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'css_reminder_admin_scripts' );

/**
 * Display a custom menu page
 */
function css_reminder_menu_page() {
	?>
	<div class="wrap">
	<h1><?php esc_html_e( 'CSS Reminder', 'css-reminder' ); ?></h1>
	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<p><?php esc_html_e( 'The CSS Reminder plugin lists all the custom CSS that is saved in the WordPress database, even if a theme has been deactivated.', 'css-reminder' ); ?></p>
			<h2><?php esc_html_e( 'How to use the plugin', 'css-reminder' ); ?> </h2>
			<ol>
				<li><?php esc_html_e( 'Locate the CSS you need in the table below.', 'css-reminder' ); ?><br>
					<?php esc_html_e( 'You can see the theme name, wether or not the theme is installed, and the date the CSS was edited.', 'css-reminder' ); ?><br>
					<?php esc_html_e( 'Click the button to copy the CSS.', 'css-reminder' ); ?>
				</li>
				<li><?php esc_html_e( 'Go to the Customizer and open the "Additional CSS" option.', 'css-reminder' ); ?></li>
				<li><?php esc_html_e( 'Paste the CSS into the option.', 'css-reminder' ); ?></li>
				<li><?php esc_html_e( 'Remember to publish your changes.', 'css-reminder' ); ?></li>
			</ol>
		</div>
	</div>

	<?php
	$args = array(
		'post_type'      => 'custom_css',
		'posts_per_page' => -1,
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		echo '<table class="wp-list-table widefat fixed striped posts">
		<thead><tr>
		<th scope="col" class="manage-column column-title column-primary">' . esc_html__( 'Theme Name', 'css-reminder' ) . '</th>
		<th scope="col" class="manage-column column-title column-primary">' . esc_html__( 'Theme Status', 'css-reminder' ) . '</th>
		<th scope="col" class="manage-column column-title column-primary">' . esc_html__( 'Custom CSS', 'css-reminder' ) . '</th>
		<th scope="col" class="manage-column column-title column-primary">' . esc_html__( 'Last edited', 'css-reminder' ) . '</th>
		<th scope="col" class="manage-column column-title column-primary">' . esc_html__( 'Database Post ID', 'css-reminder' ) . '</th>
		</tr></thead>';

		while ( $query->have_posts() ) {
			$query->the_post();
			$theme_slug = get_the_title();

			echo '<tr><td class="row-title">';
			echo css_reminder_get_name_from_slug( $theme_slug );
			if ( ! css_reminder_get_name_from_slug( $theme_slug ) ) {
				the_title();
			}
			echo '</td><td>';
			css_reminder_theme_status( $theme_slug );
			echo '</td><td>';
			echo '<span id="css-' . get_the_ID() . '">';
			the_content();
			echo '</span><br>';
			?>
			<div class="css-reminder-copy-buttons">
				<button type="button" class="button css-reminder-button" data-clipboard-target="#css-<?php echo get_the_ID(); ?>">
				<?php esc_html_e( 'Copy CSS to clipboard', 'css-reminder' ); ?>
				</button>
				<div class="success" aria-hidden="true"><?php esc_html_e( 'Copied!', 'css-reminder' ); ?></div>
			</div>
			<?php
			echo '</td><td>';
			echo get_the_modified_time( get_option( 'date_format' ) . ' H:i:s' );
			echo '<br>';
			esc_html_e( 'By ', 'css-reminder' );
			the_author();
			echo '</td><td>';
			the_ID();
			echo '</td></tr>';
		}
		wp_reset_postdata();
		echo '</table>';
	}
	?>
	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<h2><?php esc_html_e( 'CSS Reminder FAQ', 'css-reminder' ); ?></h2>
			<h3><?php esc_html_e( 'What is a Database Post ID?', 'css-reminder' ); ?></h3>
			<p>
			<?php esc_html_e( 'WordPress saves the Custom CSS as a post, using the "custom_css" post type.', 'css-reminder' ); ?><br>
			<?php esc_html_e( 'The data remains in the database even after a theme is uninstalled.', 'css-reminder' ); ?><br>
			<?php esc_html_e( 'The database post ID is provided for advanced users and webmasters who may want to clean up their database.', 'css-reminder' ); ?>
			</p>
			<br>
			<h2><?php esc_html_e( 'Rate this plugin', 'css-reminder' ); ?></h2>
			<p><?php esc_html_e( 'I hope you have found this plugin helpful. It has certainly saved me a lot of time when changing themes or migrating websites.', 'css-reminder' ); ?><br></p>
			<p><?php esc_html_e( 'If you like the plugin, please rate it on WordPress.org', 'css-reminder' ); ?></p>
		</div>
	</div>
	</div>
	<?php
}

/**
 * The custom CSS is saved with the theme slug, not the theme name, so we need to fetch the name.
 */
function css_reminder_get_name_from_slug( $theme_slug ) {
	$all_themes = wp_get_themes();
	foreach ( $all_themes as $theme ) {
		if ( $theme_slug === $theme->get( 'TextDomain' ) ) {
			return $theme->get( 'Name' );
		}
	}
}

/**
 *  Check if the theme is active or installed.
 */
function css_reminder_theme_status( $theme_slug ) {
	// Populate a list of all themes available in the install.
	$all_themes   = wp_get_themes();

	$active_theme = wp_get_theme();
	$active_theme_slug = $active_theme->get( 'TextDomain' );

	/* Check if the theme is active */
	if ( $theme_slug === $active_theme_slug ) {
		echo '<strong style="color:#40860a;">' . esc_html__( 'Active', 'css-reminder' ) . '</strong>';
	} else {
		/** If not, check if the theme is installed */
		foreach ( $all_themes as $theme ) {
			if ( $theme_slug === $theme->get( 'TextDomain' ) ) {
				esc_html_e( 'Installed', 'css-reminder' );
			}
		}
	}

}


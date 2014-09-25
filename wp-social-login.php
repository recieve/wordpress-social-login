<?php
/*
Plugin Name: WordPress Social Login
Plugin URI: http://wordpress.org/extend/plugins/wordpress-social-login/
Description: Allow your visitors to comment and login with social networks such as Twitter, Facebook, Google, Yahoo and more.
Version: 2.1.7
Author: Miled
Author URI: http://hybridauth.sourceforge.net
License: MIT License
Text Domain: wordpress-social-login
Domain Path: languages
*/

/*
*
*  Hi and thanks for taking the time to check out WSL code.
*
*  Please, don't hesitate to:
*
*   - Report bugs and issues.
* 	- Contribute: Code, Reviews, Ideas and Design.
*   - Point out stupidity, smells and inconsistencies in the code.
*   - Criticize.
*
*
*  If you want to contribute, please consider these general guide lines:
* 
*   - Don't hesitate to delete code that doesn't make sense or looks redundant.
*   - Feel free to create new functions and files when needed.
*   - Use 'if' and 'foreach' as little as possible.
*   - No 'switch'. No 'for'.
*   - Avoid over-commenting.
*
*
*  Coding Style :
*
*   - Readable code.
*   - Use tabs(8 spaces). As developers we read code 1/3 of the day and using clear indentations could
*     make life a bit easier.
*   - Same name convention of wordpress: those long long and self explanatory functions and variables.
*
*  If you want to translate this plugin and you are new to WP/i18n, then we recommend check out this
*  video https://youtube.com/watch?v=aGN-hbMCPMg (Localizing and Translating WordPress Plugins)
*
*  And PLEASE, if you have fixed, improved or translated something on WSL then consider sharing back 
*  by doping me an email or by submitting a PR on https://github.com/hybridauth/WordPress-Social-Login 
*
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// --------------------------------------------------------------------

@ session_start(); // shhhtt keept it a secret

$WORDPRESS_SOCIAL_LOGIN_VERSION = "2.1.7"; // I know

$_SESSION["wsl::plugin"] = "WordPress Social Login " . $WORDPRESS_SOCIAL_LOGIN_VERSION; // a useless piece of data we use to check some stuff

// -------------------------------------------------------------------- 

define( 'WORDPRESS_SOCIAL_LOGIN_ABS_PATH'				, WP_PLUGIN_DIR . '/wordpress-social-login'          );
define( 'WORDPRESS_SOCIAL_LOGIN_REL_PATH'				, dirname( plugin_basename( __FILE__ ) )             );
define( 'WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL'				, WP_PLUGIN_URL . '/wordpress-social-login'          );
define( 'WORDPRESS_SOCIAL_LOGIN_HYBRIDAUTH_ENDPOINT_URL', WORDPRESS_SOCIAL_LOGIN_PLUGIN_URL . '/hybridauth/' );

// --------------------------------------------------------------------

/**
* Check technical requirements before activating the plugin. 
*
* Wordpress 3.0 or newer required
*/
function wsl_activate()
{
	if ( ! function_exists ('register_post_status') ){
		deactivate_plugins (basename (dirname (__FILE__)) . '/' . basename (__FILE__));
		wp_die( __( "This plugin requires WordPress 3.0 or newer. Please update your WordPress installation to activate this plugin.", 'wordpress-social-login' ) );
	}

	do_action( 'wsl_activate' );
}

register_activation_hook( __FILE__, 'wsl_activate' );

// --------------------------------------------------------------------

/**
* Add a settings link to the Plugins page
*
* http://www.whypad.com/posts/wordpress-add-settings-link-to-plugins-page/785/
*/
function wsl_add_settings_link( $links, $file )
{
	static $this_plugin;

	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=wordpress-social-login">' . __( "Settings" ) . '</a>';

		array_unshift( $links, $settings_link );
	}

	return $links;
}

add_filter( 'plugin_action_links', 'wsl_add_settings_link', 10, 2 );

// --------------------------------------------------------------------

/**
* This file only need to be included for versions before 3.1.
*
* Deprecated since version 3.1, the functions are included by default
*/
if ( ! function_exists ('email_exists') ){
	require_once( ABSPATH . WPINC . '/registration.php' );
}

// --------------------------------------------------------------------

/**
* Loads the plugin's translated strings.
*
* http://codex.wordpress.org/Function_Reference/load_plugin_textdomain
*/
if ( function_exists ('load_plugin_textdomain') ){
	// B. Please. It's on purpose.
	load_plugin_textdomain ( 'wordpress-social-login', false, WORDPRESS_SOCIAL_LOGIN_REL_PATH . '/languages/' );
}

// --------------------------------------------------------------------

/**
* _e() wrapper
* 
* This function was used for the localization widget to generate translations per page. 
*
* kept for compatibility.
*/
function _wsl_e($text, $domain)
{
	echo __($text, $domain);
}

// --------------------------------------------------------------------

/**
* __() wrapper
* 
* This function was used for the localization widget to generate translations per page. 
*
* kept for compatibility.
*/
function _wsl__($text, $domain)
{
	return __($text, $domain);
}

// --------------------------------------------------------------------

/**
* Return the current used WSL version
*/
function wsl_version()
{
	global $WORDPRESS_SOCIAL_LOGIN_VERSION;

	return $WORDPRESS_SOCIAL_LOGIN_VERSION;
}

// -------------------------------------------------------------------- 

/* includes */

# Settings
require_once( dirname (__FILE__) . '/includes/settings/wsl.providers.php' 			 ); // List of provider supported by WSL (provided by hybridauth library) 
require_once( dirname (__FILE__) . '/includes/settings/wsl.database.php'             ); // Functions & utilities related to WSL database installation and migrations
require_once( dirname (__FILE__) . '/includes/settings/wsl.initialization.php'       ); // Check WSL requirements and register WSL settings
require_once( dirname (__FILE__) . '/includes/settings/wsl.compatibilities.php'      ); // Check and upgrade WSL database/settings (for older WSL versions)

# Services
require_once( dirname (__FILE__) . '/includes/services/wsl.authentication.php'       ); // Authenticate users via social networks. <- that's the most important script.
require_once( dirname (__FILE__) . '/includes/services/wsl.mail.notification.php'    ); // Emails and notifications.
require_once( dirname (__FILE__) . '/includes/services/wsl.user.avatar.php'          ); // Displaying the user avatar when available on the comment section
require_once( dirname (__FILE__) . '/includes/services/wsl.user.data.php'            ); // User data functions (database related)

# WSL Widget or so we call it
require_once( dirname (__FILE__) . '/includes/widgets/wsl.auth.widget.php'           ); // Authentication widget generators (where WSL widget/icons are displayed)
require_once( dirname (__FILE__) . '/includes/widgets/wsl.complete.registration.php' ); // Force users to complete their profile after they register.
require_once( dirname (__FILE__) . '/includes/widgets/wsl.notices.php'               ); // Kill WordPress and display HTML message with an error message. same as wp_die()

# WSL Admin UI. This will only kick in, if the current user is connected as admin
if( is_admin() ){
	require_once( dirname (__FILE__) . '/includes/admin/wsl.admin.ui.php'            ); // The entry point to WSL Admin interfaces 
}

// --------------------------------------------------------------------

/* hooks */

// registers wsl_database_migration_hook() to be run when the WSL is activated.
// this will create/update wslusersprofiles and wsluserscontacts
register_activation_hook( __FILE__, 'wsl_database_migration_hook' );

// --------------------------------------------------------------------

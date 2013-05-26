<?php
/*
Plugin Name: Disable WP_DEBUG
Plugin URI: http://aspenthemeworks.com/plugins
Description: Disable WP_DEBUG will disable WP_DEBUG output - if WP_DEBUG is true in wp-config.php.
Author: Bruce Wampler
Author URI: http://weavertheme.com/about
Version: 1.0
License: GPL

disable WP_DEBUG
Copyright (C) 2013, Bruce E. Wampler - aspen@aspenthemeworks.com

GPL License: http://www.opensource.org/licenses/gpl-license.php

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

define ('ASPEN_DISABLEWPD_VERSION','1.0');
define ('ASPEN_DISABLEWPD_MINIFY', '');

function aspen_disablewpd_installed() {
    return true;
}


//===============================================================
// connect plugin to WP

function aspen_disablewpd_load_styles() {
    // include any style sheet needed
    wp_enqueue_style('atw_disablewpd_Stylesheet', aspen_pi_plugins_url('/atw-admin-style', ASPEN_DISABLEWPD_MINIFY . '.css'));
}

function aspen_disablewpd_add_page() {
    // the 'aspen_switcher' is the ?page= name for forms - use different if not add_theme_page
    $page = add_management_page(
	'DisableWPD','Disable WP_DEBUG','manage_options','disable_wpdebug', 'aspen_disablewpd_admin');
    add_action('admin_print_styles-' . $page, 'aspen_disablewpd_load_styles');
}

add_action('admin_menu', 'aspen_disablewpd_add_page' ,1);



//===================================================================
//
function aspen_disablewpd_submitted($submit_name) {
    // do a nonce check for each form submit button
    // pairs 1:1 with aspen_disablewpd_nonce
    $nonce_act = $submit_name.'_act';
    $nonce_name = $submit_name.'_nonce';

    if (isset($_POST[$submit_name])) {
	if (isset($_POST[$nonce_name]) && wp_verify_nonce($_POST[$nonce_name],$nonce_act)) {
	    return true;
	} else {
	    die("WARNING: invalid form submit detected ($submit_name). Probably caused by session time-out, or, rarely, a failed security check.");
	}
    } else {
	return false;
    }
}

function aspen_disablewpd_nonce($submit_name) {
    // pairs 1:1 with aspen_disablewpd_sumbitted
    // will be one for each form submit button
    wp_nonce_field($submit_name.'_act',$submit_name.'_nonce');
}

function aspen_disablewpd_save_msg($msg) {
    echo '<div id="message" class="updated fade" style="width:80%;"><p><strong>' . $msg .
	    '</strong></p></div>';
}
function aspen_disablewpd_error_msg($msg) {
    echo '<div id="message" class="updated fade" style="background:#F88; width:80%;"><p><strong>' . $msg .
	    '</strong></p></div>';
}

//===================================================================
//
// Aspen plugin library - should be same from plugin to plugin, but keep in each theme top file
// all wrapped up in function_exists to avoid duplication if other aspen plugins active..

if (!function_exists('aspen_pi_plugins_url')) {
    function aspen_pi_plugins_url($file,$ext) {
    return plugins_url($file,__FILE__) . $ext;
}
}



//========================================================================
//  the work is done here...
//==============================================================
// process actions

function aspen_disablewpd_process() {
    // add a nonced form for each needed action

    // TAB 1 Options

    if (aspen_disablewpd_submitted('atw_disablewpd_save')) {				// enter a value into an input box
	if (isset($_POST['disablewpd_check']) && $_POST['disablewpd_check'] != '') {
	    update_option('aspen_wpdebug_disable',true);
	    aspen_disablewpd_save_msg('WP_DEBUG disabled.');
	    error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

	} else {
	    delete_option('aspen_wpdebug_disable',false);
	    aspen_disablewpd_save_msg('WP_DEBUG override disabled.');
	    error_reporting( E_ALL );
	}
    }

    if (aspen_disablewpd_submitted('atw_disablewpd_button3')) {
	$nothing[novalue] = 0;	// NOTE: This is a deliberate error to test PHP error message dispaly.
	if (WP_DEBUG && get_option('aspen_wpdebug_disable',false) == false) {
	    aspen_disablewpd_save_msg('You should have seen an "undefined constant" error.');
	} else {
	    aspen_disablewpd_save_msg('You should see no error message output.');
	}
    }

}

//==============================================================
// admin page

function aspen_disablewpd_admin() {
    if ( !current_user_can( 'manage_options' ) )  {
	wp_die('You do not have sufficient permissions to access this page.');
    }

    // process commands
    aspen_disablewpd_process();

    // display forms
?>
    <div class="atw-wrap">
	<div id="icon-themes" class="icon32"></div>
	<div style="float:left;padding-top:8px;"><h2>Disable WP_DEBUG</h2></div>
    <div style="clear:both;"></div>
	<h3>This plugin will disable WP_DEBUG output - if WP_DEBUG is already <em>true</em> in wp-config.php</h3>

<?php
	if (WP_DEBUG) {
	    echo '<p>WP_DEBUG is defined true in wp-config.php, so this plugin can help</p>';
	    if (get_option('aspen_wpdebug_disable',false) == true) {
		echo '<p>WP_DEBUG disable functionality is currently <strong>enabled</strong>.</p>';
	    } else {
		echo '<p>WP_DEBUG disable functionality is currently <strong>disabled</strong>.</p>';
	    }

	} else {
	    echo '<p>WP_DEBUG is <em>false</em> in wp-config.php, so you will not see debug output
	    with or without this plugin.
	    Edit wp-config.php and change the value of WP_DEBUG to true.</p>';
	}

?>
	<hr />
<?php
	aspen_disablewpd_admin_tab1();

}

//========================================================================
// Tab 1

function aspen_disablewpd_admin_tab1() {
?>
	<h3 style="color:blue;">Disable WP_DEBUG messages</h3>


<?php
	if (WP_DEBUG) {
?>
	<form id="atw_disablewpd_form1" name="atw_disablewpd_form1" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
	    <input type="checkbox" name="disablewpd_check" id="disablewpd_check" <?php checked(get_option('aspen_wpdebug_disable')); ?> />
    <small>Check this to disable WP_DEBUG messages</small>

	    <p class="submit">
	    <input type="submit" name="atw_disablewpd_save" value="Save Setting" />
	    </p>
<?php aspen_disablewpd_nonce('atw_disablewpd_save'); ?>
	</form>
<?php
	}
?>
	<hr />
	<form id="atw_disablewpd_form3" name="atw_disablewpd_form3" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post" >
		<span class="submit"><input type="submit" name="atw_disablewpd_button3" value="Test WP_DEBUG"/></span>
		-- Generate a PHP error to test if WP_DEBUG is working or not.
		<?php aspen_disablewpd_nonce('atw_disablewpd_button3'); ?>
	</form>
<?php
}

if (get_option('aspen_wpdebug_disable',false) == true) {
    error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
} else {
    error_reporting( E_ALL );
}

?>

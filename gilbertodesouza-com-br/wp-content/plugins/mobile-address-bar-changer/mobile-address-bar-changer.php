<?php
/*
Plugin Name: Mobile Address Bar Changer
Plugin URI: https://wordpress.org/plugins/mobile-address-bar-changer/
Description: A WordPress plugin to change Mobile Browser Address Bar Changer on Android Mobile.
Version: 3.0
Author: Anshul Labs
Author URI: http://anshullabs.xyz
textdomain: mobile-address-bar-changer
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
/*
Copyright 2012  Anshul Labs  (email : hello@anshullabs.xyz)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



/**
 * Loads the color picker javascript
 */
add_action( 'admin_enqueue_scripts', 'mabc_color_enqueues' );
function mabc_color_enqueues() {
    wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'meta-box-color-js', plugin_dir_url( __FILE__ ) . 'jquery.custom.js', array( 'wp-color-picker' ) );
}


// add color meta tag in header file.
add_action( 'wp_head', 'address_mobile_address_bar' );
function address_mobile_address_bar() {

	$mabc_options = get_option('mabc_setting');
	$color = $mabc_options['mabc_theme_color'];;
	//this is for Chrome, Firefox OS, Opera and Vivaldi
	echo '<meta name="theme-color" content="'.$color.'">';
	//Windows Phone **
	echo '<meta name="msapplication-navbutton-color" content="'.$color.'">';
	// iOS Safari
	echo '<meta name="apple-mobile-web-app-capable" content="yes">';
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black">';
}	

// add admin menu.
add_action( 'admin_menu', 'mabc_add_admin_menu' );
function mabc_add_admin_menu(){ 
	add_options_page( 'Mobile Address Bar Chnage Setting', 'Mobile Address Bar Chnage', 'manage_options', 'mobile_address_bar_chnage', 'mabc_options_page' );
}

// admin menu callback function. 
function mabc_options_page(){ 
	if (isset($_POST['mabc-submit'])) {
		$msg = 0;
		$mabc_setting = array();
		$mabc_setting['mabc_theme_color'] =	$_POST['mabc_theme_color'];
		
		$options = get_option('mabc_setting');
		if (empty($options)) {
			add_option('mabc_setting', $mabc_setting);
			$msg = 1;
		}
		else{
			update_option( 'mabc_setting', $mabc_setting );
			$msg = 1;
		}
	}
?>
<?php if ($msg==1) { ?>
	<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
		<p>
			<strong>Settings saved.</strong>
		</p>
	</div>
<?php } ?>
<div class="wrap">
    <div class="card pressthis">
    	<h2>Mobile Address Bar Changer</h2>
        <?php $mabc_options = get_option('mabc_setting'); ?>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top"><th scope="row">Select Color :</th>
                    <td>
                    	<input type="text" id="mabc_theme_color" name="mabc_theme_color" value="<?php echo $mabc_options['mabc_theme_color']; ?>" />
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" name="mabc-submit" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
		<br><br>
		<a href="http://www.paypal.me/anshulgangrade" rel="nofollow" target="_blank" style="font-size: 18px;border: 1px solid #0073aa;padding:  5px 8px;text-decoration:  none;">Donate Me</a>
		<br><br>
    </div>


</div>
	<?php
}

?>
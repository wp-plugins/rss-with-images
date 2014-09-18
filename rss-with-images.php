<?php 
 /**
  * @package RSS with Images
  *
  */
 /*
 Plugin Name: RSS with Images
 Plugin URI: www.endifmedia.com/rss-with-images
 Description: Seamlessly add featured images to your Mailchimp RSS to Email campaigns. 
 Author: ENDif Media
 Version: 1.1
 Author URI: endif.media
 License: GPLv2
 */

/*
	 This plugin is free software; you can redistribute it and/or modify 
	 it under the terms of the GNU General Public License as published by 
	 the Free software Foundation; either version 2 of the License, or 
	 (at your option) any later version.

	 This plugin is distributed in the hope that it will be useful, 
	 but WITHOUT ANY WARRANTY; without even the implied warranty of 
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
	 GNU General Public License for more details.

	 For a copy of the GNU General Public License write to the Free
	 Software Foundation, Inc., 51 Franklin St, Fifth Floor, 
	 Boston, Ma 02110-1301 USA
*/

/**
 * ADD LINKS UNDER PLUGIN TITLE
 *
 */
function rwi_em_add_under_title_links( $links ) {
	return array_merge(
		array(
			'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page=rss-with-images">Settings</a>'
		),
		$links
	);
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'rwi_em_add_under_title_links' );

/**
 * ADD LINKS UNDER PLUGIN DESCRIPTION
 *
 */
function rwi_em_add_under_description_links( $links, $file ) {
	$plugin = plugin_basename(__FILE__);
	// create link
	if ( $file == $plugin ) {
		return array_merge(
			$links,
				array( '<a href="http://wordpress.org/support/view/plugin-reviews/rss-with-images" target=_blank>Rate this plugin</a>' )
		);
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'rwi_em_add_under_description_links', 10, 2 );

/**
 * ADD CSS TO SETTINGS PAGE ONLY
 *
 */
function rwi_em_admin_css() {
 //get current screen
 $screen_page = get_current_screen();
	
 //add plugin css ONLY to settings page
 if( 'settings_page_rss-with-images' == $screen_page->id ){
   wp_enqueue_style( 'rwi-admin-css', plugins_url( 'css/plugin-styles.css', __FILE__ ),'20140605', false );
 }

}
add_action( 'admin_enqueue_scripts', 'rwi_em_admin_css' );

/** 
 * REGISTER SETTINGS PAGE
 *
 */
function rwi_em_create_menu(){
	add_options_page( 'Plugin Settings', 'RSS with Images', 'manage_options', 
	'rss-with-images', 'rwi_em_options_page' );
}
add_action( 'admin_menu', 'rwi_em_create_menu' );

/**
 * GENERATE SETTINGS PAGE
 *           
 */
function rwi_em_options_page(){

    if (!current_user_can('manage_options')) {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // See if the user has posted us some information
    if( isset($_POST['user-set-width']) || isset($_POST['user-set-height']) ){

    	check_admin_referer( 'rwi_em_save_form', 'rwi_em_name_of_nonce' );
 		 
    	$rwi_width = $_POST["user-set-width"];
    	$rwi_height = $_POST["user-set-height"];
    	$rwi_encoding = $_POST["user-set-charset"];
        
        // Check if input is a numeric value
        if(intval($rwi_width) && intval($rwi_height)){       	
        	$rwi_width = intval($rwi_width);
        	$rwi_height = intval($rwi_height);

        	if($rwi_width < 20 && $rwi_height > 192 || $rwi_height < 20 && $rwi_width > 192) {
        		$rwi_em_fail = 'What are you trying to do to me? Let\'s just start over, yeah?';
        	} else if( $rwi_width < 20 || $rwi_height < 20 ){
       		    $rwi_em_fail = 'Please choose a number GREATER THAN 20px';
        	} else if( $rwi_width > 192 || $rwi_height > 192 ) {
				$rwi_em_fail = 'Please choose a number LESS THAN 192px'; 
		    } else {
		     // update options	
			 update_option( 'rwi_width', $rwi_width ); 
			 update_option( 'rwi_height', $rwi_height );  
			 update_option( 'rwi_encoding', $rwi_encoding );

			 $rwi_em_success = true; //issue success variable 
	        }
	    // Fail if !is a numeric value
	    } else {
	      $rwi_em_fail = 'Please enter a NUMBER.'; 
	    }

// Output Message 
?>

<?php if(isset($rwi_em_fail)) { ?>
<div class="error">
	<p><strong><?php _e("$rwi_em_fail", 'rss-with-images' ); ?></strong></p>
</div>
<?php } ?>

<?php if(isset($rwi_em_success) && $rwi_em_success == true) { ?>
<div class="updated">
	<p><strong><?php _e('settings saved.', 'rss-with-images' ); ?></strong></p>
</div>
<?php } ?>

<?php }

    echo '<div class="wrap">';

    echo "<h2>" . __( 'RSS with Images - settings', '' ) . 
    "</h2><br><p>" . __( 'Here you can set the height and width of the images you want to appear in your RSS feed.<br>
      Log into Mailchimp, find your email template, view the image size requirements, and set them here. <br>
      The plugin will use these values to hard crop the uploaded image and get it ready for your feeds!<br>') ."</p>";

?>
		<form id="rss-with-images-options" method="post" action="">
		  <?php wp_nonce_field( 'rwi_em_save_form', 'rwi_em_name_of_nonce' ); ?>
		  <table class="form-table">		  	 
		    <tr valign="top">
		       <th scope="row">Image Width:</th>
			    <td>
			       <input type="number" min="20" max="192" id="user-set-width" name="user-set-width" value="<?php print get_option( 'rwi_width' ); ?>" />
			       px
			    </td>
		    </tr>
		    <tr valign="top">
		       <th scope="row">Image Height:</th>
			    <td>
			       <input type="number" min="20" max="192" id="user-set-height" name="user-set-height" value="<?php print get_option( 'rwi_height' ); ?>" />
			       px
			    </td>
		    </tr>
		     <tr valign="middle">
			   <th scope="row">
				  <label for="animation">XML Character Encoding: (<em>default is UTF-8</em>)</label>
			   </th>
			   <td>
		  	      <select name="user-set-charset">
					  <option value="UTF-8" <?php selected( get_option( 'rwi_encoding' ), 'UTF-8' ); ?>>UTF-8</option>
					  <option value="UTF-16" <?php selected( get_option( 'rwi_encoding' ), 'UTF-16' ); ?>>UTF-16</option>
					  <option value="ISO-8859-1" <?php selected( get_option( 'rwi_encoding' ), 'ISO-8859-1' ); ?>>ISO-8859-1</option>
				 	  <option value="ASCII" <?php selected( get_option( 'rwi_encoding' ), 'ASCII' ); ?>>ASCII</option>
				  </select>	
			   </td>
		  </tr>	  		         
		  </table>
		  <p class="submit">
		   <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		  </p>
		</form>
	</div>
<?php }
	
/**
 * SET NEW TEMPLATE FOR RSS EMAIL
 *
 * Remove WP Texturize
 * Add NEW rss template, leave the original intact.
 *  
 * @since Version 1.1  ( remove_filter('the_title', 'wptexturize'); )
 * @since Version 1.1  ( remove_filter('the_content', 'wptexturize'); )
 */
function rwi_em_feed_rss2(){
	//Added in Version 1.1 - Removes display bug in MS OFFICE
	remove_filter('the_title', 'wptexturize');

	//Added in Version 1.1 - Removes display bug in MS OFFICE
	remove_filter('the_content', 'wptexturize');

	if( $feed_template = locate_template( 'mailchimp-feed-rss2.php' ) ){
		load_template( $feed_template );
	} else {
    	load_template( dirname( __FILE__ ) . '/feeds/mailchimp-feed-rss2.php' );
    }
}
remove_all_actions( 'do_feed_rss2' );
add_action( 'do_feed_rss2', 'rwi_em_feed_rss2' );

/** 
 * SUPPORT FOR POST THUMBNAILS
 * 
 */
function rwi_em_add_thumbnail_support(){
	//If the current theme doesn't support post-thumbnails, let's add it now.
	if ( !current_theme_supports( 'post-thumbnails' ) ){
		add_theme_support( 'post-thumbnails' );
	}
	$rwi_image_width = get_option( 'rwi_width' ); // Get the image width from settings
	$rwi_image_height = get_option( 'rwi_height' );// Get the image height from settings

	// Add the width and height and hard crop the image
	add_image_size( 'rwi-featured-thumb', $rwi_image_width, $rwi_image_height, true );
}
add_action( 'init', 'rwi_em_add_thumbnail_support', 10, 1 );
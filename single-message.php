<?php
global $rightnow_title,
$welcome_title,
$plugins_title,
$wordpressblog_title,
$otherwordpressnews_title,
$recentcomments_title,
$recentcomments_title,
$incominglinks_title,
$quickpress_title,
$recentdrafts_title,
$wordpressblog_title,
$meta_boxes_global;

// Internationalization setup
$cdsm_plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'custom-dashboard-messages', false, $cdsm_plugin_dir );


/*****************************************************************************************/
								/*Widget removal arrays*/
/*****************************************************************************************/

// Network dashboard widget information array (used for checkboxes)
$meta_boxes_network = array(
	'network_dashboard_right_now' => $rightnow_title,
	'dashboard_plugins' => $plugins_title,
	'dashboard_primary' => $wordpressblog_title,
	'dashboard_secondary' => $otherwordpressnews_title
);

// Site dashboard widget information array (used for checkboxes)
$meta_boxes_site = array(
	'dashboard_welcome_widget' => $welcome_title,
	'dashboard_right_now' => $rightnow_title,
	'dashboard_recent_comments' => $recentcomments_title,
	'dashboard_incoming_links' => $incominglinks_title,
	'dashboard_quick_press' => $quickpress_title,
	'dashboard_recent_drafts' => $recentdrafts_title,
	'dashboard_primary' => $wordpressblog_title ,
	'dashboard_secondary' => $otherwordpressnews_title
);

// Global dashboard widget information array (used for checkboxes)
$meta_boxes_global = array(
	'dashboard_welcome_widget' => $welcome_title,
	'dashboard_primary' => $wordpressblog_title ,
	'dashboard_secondary' => $otherwordpressnews_title
);

/*****************************************************************************************/
/*****************************************************************************************/


// Hook for adding site level options menu in the settings menu bar
add_action( 'admin_menu', 'cdsm_options_menu' );

// Hook for setting up the settings section in network-wide settings tab (for creating network-wide welcome messages and removing widgets from any dashboard)
add_action( 'wpmu_options', 'cdsm_network_settings' );

// Hook for updating the network-wide welcome massage and widget removal data data
add_action( 'update_wpmu_options', 'cdsm_save_network_settings' ) ;

// Hook calls function for registering/adding settings when admin area is accessed
add_action( 'admin_init', 'cdsm_admin_init' );

// Remove widgets from site dashboard function
add_action( 'wp_dashboard_setup', 'cdsm_remove_site_dash_widgets' );

// Remove widgets from network dashboard function
add_action( 'wp_network_dashboard_setup', 'cdsm_remove_network_dash_widgets' );

// Remove widgets from global dashboard function
add_action( 'wp_user_dashboard_setup', 'cdsm_remove_global_dashboard_widgets' );


/** Function for registering/adding setings
 * cdsm_admin_init function.
 *
 * @access public
 * @return void
 */
function cdsm_admin_init() {

	// Adding the dash message widget to the site level dashboard
	$message_user_role = get_option( 'wp_dash_roles' );
	if( current_user_can($message_user_role) || $message_user_role == 'all') {
		add_action( 'wp_dashboard_setup', 'cdsm_add_dash_welcome_site' );
	}

	// Adding the dash message widget to the global level dashboard (this is the dashboard that new users that don't belong to a site see by default)
	add_action( 'wp_user_dashboard_setup', 'cdsm_add_dash_welcome_global' );

	// Site-level settings section
	add_settings_section(
		'cdsm_dash_settings_page_main',
		'',
		'cdsm_main_section_text',
		'cdsm_dash_settings_page'
	);

	// Site-level dashboard message text entry field
	add_settings_field(
		'cdsm_welcome_text',
		__( 'Message', 'custom-dashboard-messages' ),
		'cdsm_site_level_entry_field',
		'cdsm_dash_settings_page',
		'cdsm_dash_settings_page_main'
	);
	add_settings_field(
		'cdsm_logo',
		__( 'Logo in message bar', 'custom-dashboard-messages' ),
		'cdsm_site_level_entry_field_img',
		'cdsm_dash_settings_page',
		'cdsm_dash_settings_page_main'
	);
	add_settings_field(
		'cdsm_logo_height',
		__( 'Logo height', 'custom-dashboard-messages' ),
		'cdsm_site_level_entry_field_img_height',
		'cdsm_dash_settings_page',
		'cdsm_dash_settings_page_main'
	);

	add_settings_field(
		'cdsm_roles',
		__( 'Who can see this message?', 'custom-dashboard-messages' ),
		'cdsm_site_level_entry_field_roles',
		'cdsm_dash_settings_page',
		'cdsm_dash_settings_page_main'
	);

	add_settings_field(
		'cdsm_roles_normal',
		__( 'Who can write normal messages?', 'custom-dashboard-messages' ),
		'cdsm_site_level_entry_field_roles_normal',
		'cdsm_dash_settings_page',
		'cdsm_dash_settings_page_main'
	);


	// Dash message text entry option
	register_setting( 'cdsm_site_options', 'wp_dash_message', 'wp_dash_message_validate' );
	register_setting( 'cdsm_site_options', 'wp_dash_logo');
	register_setting( 'cdsm_site_options', 'wp_dash_logo_height');
	register_setting( 'cdsm_site_options', 'wp_dash_roles');
	register_setting( 'cdsm_site_options', 'wp_dash_roles_normal');

	// Adding the dash message widget into the widget removal arrays
	global $meta_boxes_site, $meta_boxes_global, $user_identity;
	$welcome_title = ' ' . __( 'Welcome', 'custom-dashboard-messages' ) . ', ' . $user_identity . ' ' . __( '(WP Dash Message)', 'custom-dashboard-messages' );
	$meta_boxes_site = array_merge( array( 'dashboard_welcome_widget' => $welcome_title ), $meta_boxes_site );
	$meta_boxes_global = array_merge( array( 'dashboard_welcome_widget' => $welcome_title ), $meta_boxes_global );
}

// Add the Dash Welcome widget and place it at the top of the SITE dashboard
/**
 * cdsm_add_dash_welcome_site function.
 *
 * @access public
 * @return void
 */
function cdsm_add_dash_welcome_site() {

	// Get user's data in order to display username in header
	global $user_identity;
	$message_logo = esc_attr(get_option( 'wp_dash_logo' ));
	$message_logo_height = esc_attr(get_option( 'wp_dash_logo_height' ));
	$pbfavicon = '<img
    src="'.$message_logo.'" height="'.$message_logo_height.'">';
	// Change second parameter to change the header of the widget
	wp_add_dashboard_widget(
		'dashboard_welcome_widget',
		$pbfavicon. ' '. __('Welcome', 'custom-dashboard-messages' ) . ', ' . $user_identity,
		'cdsm_dashboard_welcome_widget_function'
	);

	// Globalize the metaboxes array, this holds all the widgets for wp-admin
	global $wp_meta_boxes;

	// Get the regular dashboard widgets array
	// (which has our new widget already but at the end)
	$cdsm_normal_dashboard = $wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ];

	// Backup and delete our new dashbaord widget from the end of the array
	$cdsm_dashboard_widget_backup = array( 'dashboard_welcome_widget' => $cdsm_normal_dashboard[ 'dashboard_welcome_widget' ] );
	unset( $cdsm_normal_dashboard[ 'dashboard_welcome_widget' ] );

	// Merge the two arrays together so our widget is at the beginning
	$cdsm_sorted_dashboard = array_merge( $cdsm_dashboard_widget_backup, $cdsm_normal_dashboard );

	// Save the sorted array back into the original metaboxes
	$wp_meta_boxes[ 'dashboard' ][ 'normal' ][ 'core' ] = $cdsm_sorted_dashboard;

}


// Add the Dash Welcome widget and place it at the top of the GLOBAL dashboard
/**
 * cdsm_add_dash_welcome_global function.
 *
 * @access public
 * @return void
 */
function cdsm_add_dash_welcome_global() {

	// Get user's data in order to display username in header
	global $user_identity;

	// Change second parameter to change the header of the widget
	wp_add_dashboard_widget(
		'dashboard_welcome_widget',
		__('Welcome', 'custom-dashboard-messages' ) . ', ' . $user_identity,
		'cdsm_dashboard_welcome_widget_function'
	);

	// Globalize the metaboxes array, this holds all the widgets for wp-admin
	global $wp_meta_boxes;

	// Get the regular dashboard widgets array
	// (which has our new widget already but at the end)
	$cdsm_global_dashboard = $wp_meta_boxes[ 'dashboard-user' ][ 'normal' ][ 'core' ];

	// Backup and delete our new dashbaord widget from the end of the array
	$cdsm_dashboard_widget_backup = array( 'dashboard_welcome_widget' => $cdsm_global_dashboard[ 'dashboard_welcome_widget' ] );
	unset( $cdsm_global_dashboard[ 'dashboard_welcome_widget' ] );

	// Merge the two arrays together so our widget is at the beginning
	$cdsm_sorted_dashboard = array_merge( $cdsm_dashboard_widget_backup, $cdsm_global_dashboard );

	// Save the sorted array back into the original metaboxes
	$wp_meta_boxes[ 'dashboard-user' ][ 'normal' ][ 'core' ] = $cdsm_sorted_dashboard;

}


// Create the function to output the contents of the new Dashboard Widget
/**
 * cdsm_dashboard_welcome_widget_function function.
 *
 * @access public
 * @return void
 */
function cdsm_dashboard_welcome_widget_function() {

	// Display the site level widget entry first...
	$site_message = get_option( 'wp_dash_message' );
	$site_message_img_style = '<style>#dashboard_welcome_widget .hndle{justify-content: start;} #dashboard_welcome_widget .hndle img{margin-right: 5px;} #dashboard_welcome_widget .inside img{max-width: 100%;height: auto;}</style>';
	echo apply_filters( 'the_content', $site_message[ 'message' ].$site_message_img_style );

	// Display the network level widget entry second...
	$network_message = get_site_option( 'wp_dash_message_network', '', true );

	if( $network_message != '' ) {
		echo $network_message;
	}
}

/** Options page
 * cdsm_options_menu function.
 *
 * @access public
 * @return void
 */
function cdsm_options_menu() {
	// Parameters for options: 1. site header name 2. setting menu bar name
	// 3. capability (decides whether user has access) 4. menu slug 5. options page function
	$main_message_title = __( 'Main message', 'custom-dashboard-messages' );
    add_submenu_page('edit.php?post_type=cd_message_1', 'Custom Dashboard Message', $main_message_title, 'manage_options', 'cdsm_options', 'cdsm_dash_settings_page', null);
}

/** Site level options page set-up
 * cdsm_dash_settings_page function.
 * settings page
 * @access public
 * @return void
 */
function cdsm_dash_settings_page() {

	// Determines if user has permission to access options and if they don't error message is displayed
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have the permission to modify the custom dashboard message box.', 'custom-dashboard-messages' ) );
	}?>

<!-- Set up the page and populate it with the options section and save button -->
<div class="wrap">
  <h2><?php _e( 'Main message for users', 'custom-dashboard-messages' ) ?></h2>
  <form method="post" action="options.php"><?php
			settings_fields( 'cdsm_site_options' );
			do_settings_sections( 'cdsm_dash_settings_page' ); ?>
    <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
  </form>
</div>
<script>
/* Encode image to base64 */
function readFile() {

  if (this.files && this.files[0]) {

    var FR= new FileReader();

    FR.addEventListener("load", function(e) {
      document.getElementById("img").src       = e.target.result;
      document.getElementById("b64").value		 = e.target.result;
    });

    FR.readAsDataURL( this.files[0] );
  }

}

document.getElementById("inp").addEventListener("change", readFile);
</script>
<?php
}


// **Not used** (text function for text box area)
function cdsm_main_section_text() { }


// **Not used** (text function for text box area)
function cdsm_remove_widgets_text() { }


/** Sets up the site level entry field
 * cdsm_site_level_entry_field function.
 *
 * @access public
 * @return void
 */
function cdsm_site_level_entry_field() {

	// Get the site message entry
	$site_message = get_option( 'wp_dash_message' );

	// Get the widget removal options
	$WP_remove_option = get_option( 'wp_remove_site_widgets' );
	$WP_remove_site_option = get_site_option( 'wp_remove_site_widgets_N' ); ?>

<!-- Creates the site level entry field and populates it with whatever is currently displayed on the widget site message wise -->
<!-- The textarea is disabled if the widget is disabled on the site level -->
<?php wp_editor( $site_message[ 'message' ], 'cdsm_welcome_text', $settings = array('textarea_name' => 'wp_dash_message[message]') ); ?>
<br /><?php

	// Shows the "HTML allowed" message if the widget hasn't been disabled on the site level dashboard
	if( !isset( $WP_remove_option[ 'dashboard_welcome_widget' ] ) && !isset( $WP_remove_site_option[ 'dashboard_welcome_widget' ] ) ) { ?>
<br /><?php
	}
	// Show the following message instead if the dashboard message widget is disabled through the network administrator options
	elseif( isset( $WP_remove_site_option[ 'dashboard_welcome_widget' ] ) ) { ?>
<span class="description">
  <font color='FF6666'><?php
			_e( 'The network administrator has deactivated the dashboard message widget. Please
			contact your network administrator if you wish to have the dashboard message
			widget re-activated.', 'custom-dashboard-messages' ) ?>
  </font>
</span><?php
	}
	// Show the following message instead if the dashboard message widget is disabled through the site level administration options
	elseif( isset( $WP_remove_option[ 'dashboard_welcome_widget' ] ) ) { ?>
<span class="description">
  <font color='FF6666'><?php
			_e( 'One of the site administrators has deactivated the dashboard message widget. If
			you wish to re-activate the dashboard widget, simply deselect the appropriate checkbox
			in the section below and click on the "Save Changes" button.', 'custom-dashboard-messages' ) ?>
  </font>
</span><?php
	}

}

function cdsm_site_level_entry_field_img() {

	// Get the site message entry
	$message_logo = esc_attr(get_option( 'wp_dash_logo' ));
	// Show the following message instead if the dashboard message widget is disabled through the network administrator options
	if( isset( $message_logo ) ) { ?>
<img id="img" width="150" height="150" src="<?php echo $message_logo;?>"><br/>
<input type="hidden" name="wp_dash_logo" id="b64" value="<?php echo $message_logo;?>"><br/>
<input id="inp" type='file'>

<?php
}
}

function cdsm_site_level_entry_field_img_height() {

	// Get the site message entry
	$message_logo_height = esc_attr(get_option( 'wp_dash_logo_height' ));
	// Show the following message instead if the dashboard message widget is disabled through the network administrator options?>
<input type="text" name="wp_dash_logo_height" value="<?php echo $message_logo_height;?>">px
<?php
}

function cdsm_site_level_entry_field_roles(  ) {
	$options = get_option( 'wp_dash_roles' );
	?>
	<select name='wp_dash_roles'>
			<option value='manage_options' <?php if($options == 'manage_options') echo 'selected'; ?>>Administrator</option>
			<option value='publish_pages' <?php if($options == 'publish_pages') echo 'selected'; ?>>Editor</option>
			<option value='publish_posts' <?php if($options == 'publish_posts') echo 'selected'; ?>>Author</option>
			<option value='read' <?php if($options == 'read') echo 'selected'; ?>>Contributor</option>
			<option value='all' <?php if($options == 'all') echo 'selected'; ?>>Subscriber</option>
	</select> (minimum role)
<?php
}

function cdsm_site_level_entry_field_roles_normal(  ) {
	$options = get_option( 'wp_dash_roles_normal' );
	?>
	<select name='wp_dash_roles_normal'>
			<option value='manage_options' <?php if($options == 'manage_options') echo 'selected'; ?>>Administrator</option>
			<option value='publish_pages' <?php if($options == 'publish_pages') echo 'selected'; ?>>Editor</option>
			<option value='publish_posts' <?php if($options == 'publish_posts') echo 'selected'; ?>>Author</option>
			<option value='read' <?php if($options == 'read') echo 'selected'; ?>>Contributor</option>
			<option value='all' <?php if($options == 'all') echo 'selected'; ?>>Subscriber</option>
	</select> (minimum role)
<?php
}

/** Validation/clean-up of message. "trim" removes all spaces before and after text body. Returns the validated entry
 * wp_dash_message_validate function.
 *
 * @access public
 * @param mixed $input
 * @return $newinput -- Validated entry
 */
function wp_dash_message_validate($input) {
	$newinput[ 'message' ] =  trim( $input[ 'message' ] );
	return $newinput;
}


/** Set up network level entry field in settings tab and populate field with current network-wide dashboard widget message
 ** Set up the widget removal checkboxes
 * cdsm_network_settings function.
 *
 * @access public
 * @return void
 */
function cdsm_network_settings() {

	// Get the network level dashboard message entry
	$network_message = get_site_option( 'wp_dash_message_network', '', true );

	// Get the widget removal options
	$WP_remove_network_option = get_site_option( 'wp_remove_network_widgets' );
	$WP_remove_site_option = get_site_option( 'wp_remove_site_widgets_N' );
	$WP_remove_global_option = get_site_option( 'wp_remove_global_widgets' );

	// Globalize widget details
	global $meta_boxes_network, $meta_boxes_site, $meta_boxes_global; ?>


<!-- Set up textarea for dashboard message and notifications on its status -->
<h3><?php _e( 'Dashboard Message', 'custom-dashboard-messages' ) ?></h3>
<table class="form-table">
  <tr valign="top">
    <th scope="row"><?php _e( 'Network-Level Dashboard Message', 'custom-dashboard-messages' ) ?></th>
    <td>
      <!-- Network level entry field populated with whatever is currently displayed on the widget network message wise -->
      <!-- The textarea is disabled if the widget is disabled on the site level and global level dashboards -->
      <textarea class="large-text" cols="45" rows="5" id="wp_dash_message_network"
        name="wp_dash_message_network"><?php echo $network_message; ?></textarea><?php


				// Show the "HTML allowed" message if the site level or global level dashboard message widget is enabled
				if( !isset( $WP_remove_site_option[ 'dashboard_welcome_widget' ] ) || !isset( $WP_remove_global_option[ 'dashboard_welcome_widget' ] ) ) { ?>
      <span><?php _e( 'HTML allowed', 'custom-dashboard-messages' ) ?></span><?php
				} //end of if statement...
				?>
    </td>
  </tr>
</table>


<br />

<?php

}


/** Updates the network-wide dash message entry and the widget removal checkboxes status
 * cdsm_save_network_settings function.
 *
 * @access public
 * @return void
 */
 function cdsm_save_network_settings() {

 	// Apply filters to the dashboard network message
	$network_message = stripslashes( $_POST[ 'wp_dash_message_network' ] );
	$filtered_network_message = apply_filters( 'the_content', trim( $network_message ) );

	// Update the network dash message entry with the filtered version
	update_site_option( 'wp_dash_message_network', $filtered_network_message );

	// Update the network, site, and global dashboard  widget removal checkbox statuses
	update_site_option( 'wp_remove_network_widgets', isset( $_POST[ 'wp_remove_network_widgets' ] ) ? $_POST[ 'wp_remove_network_widgets' ] : NULL );
	update_site_option( 'wp_remove_site_widgets_N', isset( $_POST[ 'wp_remove_site_widgets_N' ] ) ? $_POST[ 'wp_remove_site_widgets_N' ] : NULL );
	update_site_option( 'wp_remove_global_widgets', isset( $_POST[ 'wp_remove_global_widgets' ] ) ? $_POST[ 'wp_remove_global_widgets' ] : NULL );

}


/** Removes all site level dashboard widgets that were checked off in the site and network level options
 * cdsm_remove_site_dash_widgets function.
 *
 * @access public
 * @return void
 */
function cdsm_remove_site_dash_widgets() {

	// Globalize the meta boxes array
	global $meta_boxes_site;

	// Get the site level and network level dashboard widget removal settings
	$WP_remove_option = get_option( 'wp_remove_site_widgets' );
	$WP_remove_site_option = get_site_option( 'wp_remove_site_widgets_N' );

	// Loop through all IDs
	foreach( $meta_boxes_site as $meta_box => $title )
	{
		// If the ID is marked as removed by site or network level setting...
		if( isset( $WP_remove_option[ $meta_box ] ) || isset( $WP_remove_site_option[$meta_box] ) ) {
			remove_meta_box( $meta_box, 'dashboard', 'normal' );
			remove_meta_box( $meta_box, 'dashboard', 'side' );
		}
	}
}


/** Removes all network level dashboard widgets that were checked off in the network level options
 * cdsm_remove_network_dash_widgets function.
 *
 * @access public
 * @return void
 */
function cdsm_remove_network_dash_widgets() {

	// Globalize the meta boxes array
	global $meta_boxes_network;

	// Get the network level dashboard widget removal settings
	$WP_remove_network_option = get_site_option( 'wp_remove_network_widgets' );

 	// Loop through all IDs
 	foreach( $meta_boxes_network as $meta_box => $title )
	{
		// If the ID is marked as removed by network level setting...
		if( isset( $WP_remove_network_option[ $meta_box ] ) ) {
			remove_meta_box( $meta_box, 'dashboard-network', 'normal' );
			remove_meta_box( $meta_box, 'dashboard-network', 'side' );
		}
	}
}


/** Removes all global level dashboard widgets that were checked off in the network level options
 * cdsm_remove_global_dashboard_widgets function.
 *
 * @access public
 * @return void
 */
function cdsm_remove_global_dashboard_widgets() {

	// Globalize the meta boxes array
	global $meta_boxes_global;

	// Get the network level dashboard widget removal settings
	$WP_remove_global_option = get_site_option( 'wp_remove_global_widgets' );

	// Loop through all IDs
	foreach( $meta_boxes_global as $meta_box => $title )
	{
		// If the ID is marked as removed by network level setting, remove it from the array
		if( isset( $WP_remove_global_option[$meta_box] ) ) {
			remove_meta_box( $meta_box, 'dashboard-user', 'normal' );
		}
  	}
}


/** Sets up the site level dashboard widget removal checkboxes
 * cdsm_site_level_dash_widget_options function.
 *
 * @access public
 * @return void
 */
function cdsm_site_level_dash_widget_options() {

	global $meta_boxes_site;

	// Get the site level option for checkbox status
	$WP_remove_option = get_option( 'wp_remove_site_widgets' );

	// Get the network level option
	$WP_remove_site_option = get_site_option( 'wp_remove_site_widgets_N' );

	// Set up site dashboard widget removal checkboxes
	foreach( $meta_boxes_site as $meta_box => $title ) {
		// If the given dashboard widget is disabled through the network settings, remove it from the checkbox options in the site level settings page
		if ( !isset( $WP_remove_site_option[ $meta_box ] ) ) { ?>
<input id='<?php echo $meta_box; ?>' type='checkbox' name='wp_remove_site_widgets[<?php echo $meta_box; ?>]'
  value='Removed'
  <?php isset( $WP_remove_option[$meta_box] ) ? checked( $WP_remove_option[ $meta_box ], 'Removed', true ) : NULL; ?> /><?php echo $title; ?><br><?php
		}
	} ?>

<!-- Short info sentence -->
<span class="description"><?php
		_e( 'Select all widgets you would like to remove from the site dashboard
		and click on the "Save Changes" button.', 'custom-dashboard-messages' ) ?>
</span><?php

	// Add another info message if multisite support is enabled
	if ( is_multisite() ) { ?>
<br />

<span class="description"><?php
			_e( 'NOTE: If widgets have been disabled through the "Network Settings"
			they can only be reactivated by a network admin and will not appear on
			this settings page.', 'custom-dashboard-messages' ) ?>
</span><?php
	}
}

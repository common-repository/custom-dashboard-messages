<?php
/*
Plugin Name: Zedna Custom Dashboard Messages
Plugin URI: https://profiles.wordpress.org/zedna/
Description: Allow admin to write messages on user dashboard.
Text Domain: custom-dashboard-messages
Domain Path: /languages
Version: 2.2.2
Author: Radek Mezulanik
Author URI: https://cz.linkedin.com/in/radekmezulanik
License: GPLv3
*/

/* Custom post types - Dashboard messages 1 */
add_action( 'init', 'create_post_type_cd_message_1' );
function create_post_type_cd_message_1() {
  register_post_type( 'cd_message_1',
    array(
      'labels' => array(
        'name' => __( 'Dashboard Messages', 'custom-dashboard-messages' ),
        'singular_name' => __( 'Dashboard Important!', 'custom-dashboard-messages' )
      ),
      'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields'),
      'public' => true,
      'has_archive' => true,
      'capability_type' => 'post',
      'rewrite' => array( 'slug' => 'cd_messages_1', 'with_front' => true)
    )
  ); 
}

//Add custom field
function cd_message_1_metaboxes( ) {
  global $wp_meta_boxes;
  add_meta_box('postfunctiondiv', __('Who can read this message?'), 'cd_message_1_metaboxes_html', 'cd_message_1', 'normal', 'high');
}
add_action( 'add_meta_boxes_cd_message_1', 'cd_message_1_metaboxes' );

function cd_message_1_metaboxes_html()
{
    global $post;
    $custom = get_post_custom($post->ID);
    $min_role = isset($custom["cd_min_role_to_see"][0])?$custom["cd_min_role_to_see"][0]:'all';
?>
    <label>Minimum role to see this message:</label>
    <select name='cd_min_role_to_see'>
			<option value='manage_options' <?php if($min_role == 'manage_options') echo 'selected'; ?>>Administrator</option>
			<option value='publish_pages' <?php if($min_role == 'publish_pages') echo 'selected'; ?>>Editor</option>
			<option value='publish_posts' <?php if($min_role == 'publish_posts') echo 'selected'; ?>>Author</option>
			<option value='read' <?php if($min_role == 'read') echo 'selected'; ?>>Contributor</option>
			<option value='all' <?php if($min_role == 'all') echo 'selected'; ?>>Subscriber</option>
	</select>
<?php
}

function cd_message_1_save_post()
{
    if(empty($_POST)) return; //why is cd_message_1_save_post triggered by add new? 
    global $post;
    update_post_meta($post->ID, "cd_min_role_to_see", $_POST["cd_min_role_to_see"]);
}   

add_action( 'save_post_cd_message_1', 'cd_message_1_save_post' ); 
/* // Custom post types - Dashboard messages 1 */

// remove unwanted dashboard widgets for relevant users
// function cd_remove_dashboard_widgets() {
//     $user = wp_get_current_user();
//     if ( ! $user->has_cap( 'manage_options' ) ) {
//         remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
//         remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
//         remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
//         remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
//         remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
//         remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
//         remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
//         remove_meta_box( 'dashboard_widget', 'dashboard', 'normal' );
//     }
// }
// add_action( 'wp_dashboard_setup', 'cd_remove_dashboard_widgets' );

// Hide Custom Dashboards menu for users without permissions
add_action('admin_menu','cd_remove_admin_menu');
function cd_remove_admin_menu()
{
  $message_user_role = get_option( 'wp_dash_roles' );
  if( !current_user_can('manage_options') && (!current_user_can($message_user_role) || $message_user_role != 'all')) {
    remove_menu_page('edit.php?post_type=cd_message_1');
  }
}

// Move the 'Right Now' dashboard widget to the right hand side
function cd_move_dashboard_widget() {
    $user = wp_get_current_user();
    if ( ! $user->has_cap( 'manage_options' ) ) {
        global $wp_meta_boxes;
        $widget = $wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'];
        unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'] );
        $wp_meta_boxes['dashboard']['side']['core']['dashboard_right_now'] = $widget;
    }
}
add_action( 'wp_dashboard_setup', 'cd_move_dashboard_widget' );

// add new dashboard widgets
function cd_add_dashboard_widgets() {
  $message_logo = esc_attr(get_option( 'wp_dash_logo' ));
  $message_logo_height = esc_attr(get_option( 'wp_dash_logo_height' ));
  $important_messages_title = __( 'Important messages', 'custom-dashboard-messages' );
  $pbfavicon = '<img
    src="'.$message_logo.'" height="'.$message_logo_height.'">';
    wp_add_dashboard_widget( 'cd_dashboard_messages_1', $pbfavicon.' '.$important_messages_title, 'cd_add_messages_1_widget' );
}

//Add column 1
function cd_add_messages_1_widget(){ ?>

<style>
#cd_dashboard_messages_1 .hndle{
  justify-content: start;
}
#cd_dashboard_messages_1 .hndle img{
  margin-right: 5px;
}
#cd_dashboard_messages_1 .inside img{
  max-width: 100%;
  height: auto;
}
</style>

<?php 

$args = array( 'post_type' => 'cd_message_1', 'posts_per_page' => '-1');
$loop = new WP_Query( $args );

$counter = 1;
if( $loop->have_posts() ):
          
  while( $loop->have_posts() ): $loop->the_post();
    global $post;
    $message_user_role = get_post_meta( get_the_ID(), 'cd_min_role_to_see' )[0];

    if( current_user_can($message_user_role) || $message_user_role == 'all' || empty($message_user_role)):

  ?>
  <div class="dashboard-message1">
    <h1><?php the_title(); ?></h1>
    <p><?php the_post_thumbnail('medium'); ?></p>
    <p><?php the_content(); ?></p>
  </div>
  <hr>
  <?php
    endif;
  endwhile;
else:
?>
<?php endif; ?>

<?php }
add_action( 'wp_dashboard_setup', 'cd_add_dashboard_widgets' );

require_once dirname( __FILE__ )  . '/single-message.php';

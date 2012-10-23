<?php /*
    Plugin Name: Mail Trail
    Plugin URI: https://github.com/dgrundel/mail-trail-wordpress
    Description: A WordPress plugin for sending and keeping track of messages sent by your WordPress site.
    Version: 1
    Author: Daniel Grundel, Web Presence Partners
    Author URI: http://www.webpresencepartners.com
*/
    
    class WebPres_Mail_Trail {
        
        public function __construct() {
            add_action('init', array(&$this, 'register_custom_post_types'));
            add_action('admin_menu', array(&$this, 'hide_add_new_menu_item'));
            add_action('admin_head', array(&$this, 'hide_add_new_header_button'));
            add_action('admin_menu', array(&$this, 'add_meta_box'));
            add_filter('manage_sent_mail_posts_columns', array(&$this, 'column_heads'));
            add_action('manage_sent_mail_posts_custom_column', array(&$this, 'column_contents'), 10, 2);
        }
        
        //register sent_mail post type
        function register_custom_post_types() {
            register_post_type(
                "sent_mail",
                array(
                    "labels" => array(
                        "name" => __( "Sent Mail" ),
                        "singular_name" => __( "Sent Mail" )
                    ),
                    "public" => false,
                    "has_archive" => false,
                    "show_ui" => true,
                    "capability_type" => "post",
                    "hierarchical" => false,
                    "rewrite" => false
                )
            );
            remove_post_type_support('sent_mail', 'editor');
            remove_post_type_support('sent_mail', 'title');
        }
        
        //hide the Add New menu item
        function hide_add_new_menu_item() {
            global $submenu;
            unset($submenu['edit.php?post_type=sent_mail'][10]);
        }
        
        //hide the Add New button in the header
        function hide_add_new_header_button() {
            global $pagenow;
            $post_id = intval($_GET['post']);
            $post_type = $post_id > 0 ? get_post_type($post_id) : $_GET['post_type'];
            
            if(is_admin() && $post_type == 'sent_mail' && ( $pagenow == 'edit.php' || $pagenow == 'post.php' )): ?>
                <style>
                    .add-new-h2 { display: none; }
                </style>
            <?php endif;
        }
        
        //add the To column and rename the Title column to Subject
        function column_heads($defaults) {  
            $defaults['sent_mail_to'] = 'To';
            $defaults['title'] = 'Subject';
            return $defaults;  
        }  
        
        //populate the To column
        function column_contents($column_name, $post_id) {
            if ($column_name == 'sent_mail_to') {
                echo get_post_meta($post_id, '_to', true);
            }
        }
        
        //add the info meta box to the sent_mail post type
        function add_meta_box() {
            add_meta_box('sent_mail_details', 'Sent Mail Details', array(&$this, 'show_meta_box'), 'sent_mail', 'normal', 'high');
        }
        
        //show the info meta box
        function show_meta_box() {
            require_once(plugin_dir_path(__FILE__).'mail-trail-meta-box.php');
        }
    }
    
    $webpres_mail_trail = new WebPres_Mail_Trail();
    
    require_once(plugin_dir_path(__FILE__).'mail-trail-wp_mail.php');
?>
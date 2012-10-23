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
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action('admin_menu', array(&$this, 'add_meta_box'));
            add_filter('manage_sent_mail_posts_columns', array(&$this, 'column_heads'));
            add_action('manage_sent_mail_posts_custom_column', array(&$this, 'column_contents'), 10, 2);
        }
        
        public function admin_menu() {
            add_management_page('Mail Trail', 'Mail Trail', 'manage_options', 'mail-trail', array(&$this, 'render_admin_action'));
        }
        
        public function render_admin_action() {
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'upload';
            require_once(plugin_dir_path(__FILE__).'mail-trail-common.php');
            require_once(plugin_dir_path(__FILE__)."mail-trail-{$action}.php");
        }
        
        function register_custom_post_types() {
            register_post_type(
                "sent_mail",
                array(
                    "labels" => array(
                        "name" => __( "Sent Mail" ),
                        "singular_name" => __( "Sent Mail" )
                    ),
                    "public" => true,
                    "has_archive" => true,
                    "show_ui" => true,
                    "capability_type" => "post",
                    "hierarchical" => false,
                    "rewrite" => true,
                    'supports' => array('custom-fields'),
                )
            );
        }
        
        function column_heads($defaults) {  
            $defaults['sent_mail_to'] = 'To';
            $defaults['title'] = 'Subject';
            return $defaults;  
        }  
        
        function column_contents($column_name, $post_id) {  
            if ($column_name == 'sent_mail_to') {  
                echo get_post_meta($post_id, '_to', true);
            }  
        }  

        
        function add_meta_box() {
            add_meta_box('sent_mail_details', 'Sent Mail Details', array(&$this, 'show_meta_box'), 'sent_mail', 'normal', 'high');
        }
        
        function show_meta_box() {
            require_once(plugin_dir_path(__FILE__).'mail-trail-meta-box.php');
        }
    }
    
    $webpres_mail_trail = new WebPres_Mail_Trail();
    
    require_once(plugin_dir_path(__FILE__).'mail-trail-wp_mail.php');
?>
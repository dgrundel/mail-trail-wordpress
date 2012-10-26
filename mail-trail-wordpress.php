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
            add_filter('admin_init', array(&$this , 'register_settings_fields'));
            add_action('admin_menu', array(&$this, 'hide_add_new_menu_item'));
            add_action('admin_menu', array(&$this, 'admin_menu'));
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
                    "rewrite" => false,
                    "menu_icon" => plugin_dir_url(__FILE__).'img/email.png'
                )
            );
            remove_post_type_support('sent_mail', 'editor');
            remove_post_type_support('sent_mail', 'title');
        }
        
        //add mail options page to admin menu
        function admin_menu() {
            add_submenu_page('edit.php?post_type=sent_mail', 'Mail Options', 'Mail Options', 'manage_options', 'mail_options', array(&$this, 'mail_options_html'));
        }
        
        //generate mail options page html
        function mail_options_html() { ?>  
            <div class="wrap">  
                <div id="icon-tools" class="icon32"></div>  
                <h2>Mail Options</h2>  
                <?php settings_errors(); ?>  
                <form method="post" action="options.php">  
                    <?php  
                        settings_fields( 'mail_options' );  
                        do_settings_sections( 'mail_options' );  
                        submit_button();  
                    ?>  
                </form>  
            </div>
        <?php }
        
        //register settings with WP
        function register_settings_fields() {
            add_settings_section('mail_options_tracking', 'Mail Tracking', array(&$this, 'tracking_section_description_html'), 'mail_options');
            
            register_setting('mail_options', 'mail_trail__enable_mail_save', 'intval');
            
            add_settings_field('mail_trail__enable_mail_save', '<label for="mail_trail__enable_mail_save">Save All Outgoing Mail</label>' , array(&$this, 'enable_mail_save_html') , 'mail_options', 'mail_options_tracking');
        }
        
        //echo out the mail tracking section description
        function tracking_section_description_html() {
            ?><p>Save all outgoing mail in the WordPress database. Viewable only by Administrators.</p><?php
        }
        
        //echo out the enable_mail_save field html
        function enable_mail_save_html() {
            $field_value = intval(get_option('mail_trail__enable_mail_save', ''));
            ?><input type="hidden" name="mail_trail__enable_mail_save" value="0">
            <input type="checkbox" id="mail_trail__enable_mail_save" name="mail_trail__enable_mail_save" value="1"<?php if($field_value) echo ' checked'; ?>><?php
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
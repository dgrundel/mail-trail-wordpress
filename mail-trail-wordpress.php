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
            register_activation_hook( __FILE__, array(&$this, 'activate'));
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
        
        function activate() {
            add_option('mail_trail__enable_mail_save', 1, '', 'yes');
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
            //General
            add_settings_section('mail_options_general_section', 'General Options', array(&$this, 'general_section_description_html'), 'mail_options');
            
            register_setting('mail_options', 'mail_trail__send_mail_from_name', 'trim');
            add_settings_field('mail_trail__send_mail_from_name', '<label for="mail_trail__send_mail_from_name">Send Mail From Name</label>' , array(&$this, 'send_mail_from_name_html') , 'mail_options', 'mail_options_general_section');
            
            register_setting('mail_options', 'mail_trail__send_mail_from_address', 'trim');
            add_settings_field('mail_trail__send_mail_from_address', '<label for="mail_trail__send_mail_from_address">Send Mail From Address</label>' , array(&$this, 'send_mail_from_address_html') , 'mail_options', 'mail_options_general_section');
            
            //Mail Tracking
            add_settings_section('mail_options_tracking_section', 'Mail Tracking', array(&$this, 'tracking_section_description_html'), 'mail_options');
            
            register_setting('mail_options', 'mail_trail__enable_mail_save', 'intval');
            add_settings_field('mail_trail__enable_mail_save', '<label for="mail_trail__enable_mail_save">Save All Outgoing Mail</label>' , array(&$this, 'enable_mail_save_html') , 'mail_options', 'mail_options_tracking_section');
            
            //Admin E-Mails
            add_settings_section('mail_options_admin_email_section', 'Admin E-Mails', array(&$this, 'admin_email_section_description_html'), 'mail_options');
            
            register_setting('mail_options', 'mail_trail__always_bcc_admin', 'intval');
            add_settings_field('mail_trail__always_bcc_admin', '<label for="mail_trail__always_bcc_admin">Always BCC Site Admin</label>' , array(&$this, 'always_bcc_admin_html') , 'mail_options', 'mail_options_admin_email_section');
            
            register_setting('mail_options', 'mail_trail__additional_admin_emails', 'trim');
            add_settings_field('mail_trail__additional_admin_emails', '<label for="mail_trail__additional_admin_emails">CC Admin E-Mails</label>' , array(&$this, 'additional_admin_emails_html') , 'mail_options', 'mail_options_admin_email_section');
            
            //Amazon SES
            add_settings_section('mail_options_ses_section', 'Amazon SES', array(&$this, 'ses_section_description_html'), 'mail_options');
            
            register_setting('mail_options', 'mail_trail__enable_ses', 'intval');
            add_settings_field('mail_trail__enable_ses', '<label for="mail_trail__enable_ses">Enable Amazon SES</label>' , array(&$this, 'enable_ses_html') , 'mail_options', 'mail_options_ses_section');
            
            register_setting('mail_options', 'mail_trail__ses_access_key', 'trim');
            add_settings_field('mail_trail__ses_access_key', '<label for="mail_trail__ses_access_key">Access Key</label>' , array(&$this, 'ses_access_key_html') , 'mail_options', 'mail_options_ses_section');
            
            register_setting('mail_options', 'mail_trail__ses_secret_key', 'trim');
            add_settings_field('mail_trail__ses_secret_key', '<label for="mail_trail__ses_secret_key">Secret Key</label>' , array(&$this, 'ses_secret_key_html') , 'mail_options', 'mail_options_ses_section');
        }
        
        //echo out the mail tracking section description
        function general_section_description_html() {
            return;
            ?><p></p><?php
        }
        
        function tracking_section_description_html() {
            ?><p>Save all outgoing mail in the WordPress database. Viewable only by Administrators.</p><?php
        }
        
        function admin_email_section_description_html() {
            ?><p>Control mail sent to the site admin.</p><?php
        }
        
        function ses_section_description_html() {
            ?><p>Use Amazon's Simple E-Mail Service to send messages.</p><?php
        }
        
        function send_mail_from_name_html() {
            $field_value = get_option('mail_trail__send_mail_from_name', '');
            ?><input type="text" id="mail_trail__send_mail_from_name" name="mail_trail__send_mail_from_name" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text ltr"><?php
        }
        
        function send_mail_from_address_html() {
            $field_value = get_option('mail_trail__send_mail_from_address', '');
            ?><input type="text" id="mail_trail__send_mail_from_address" name="mail_trail__send_mail_from_address" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text ltr"><?php
        }
        
        function enable_mail_save_html() {
            $field_value = intval(get_option('mail_trail__enable_mail_save', 1));
            ?><input type="hidden" name="mail_trail__enable_mail_save" value="0">
            <input type="checkbox" id="mail_trail__enable_mail_save" name="mail_trail__enable_mail_save" value="1"<?php if($field_value) echo ' checked'; ?>><?php
        }
        
        function always_bcc_admin_html() {
            $field_value = intval(get_option('mail_trail__always_bcc_admin', 0));
            ?><input type="hidden" name="mail_trail__always_bcc_admin" value="0">
            <input type="checkbox" id="mail_trail__always_bcc_admin" name="mail_trail__always_bcc_admin" value="1"<?php if($field_value) echo ' checked'; ?>>
            <p class="description">BCC the site admin on every outgoing message. Careful! This could be a lot of e-mail on a high traffic site.</p><?php
        }
        
        function additional_admin_emails_html() {
            $field_value = get_option('mail_trail__additional_admin_emails', '');
            ?><input type="text" id="mail_trail__additional_admin_emails" name="mail_trail__additional_admin_emails" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text ltr">
            <p class="description">Also CC these people on all messages sent to the site admin. Separate e-mail addresses with a comma.</p><?php
        }
        
        function enable_ses_html() {
            $field_value = intval(get_option('mail_trail__enable_ses', 0));
            ?><input type="hidden" name="mail_trail__enable_ses" value="0">
            <input type="checkbox" id="mail_trail__enable_ses" name="mail_trail__enable_ses" value="1"<?php if($field_value) echo ' checked'; ?>><?php
        }
        
        function ses_access_key_html() {
            $field_value = get_option('mail_trail__ses_access_key', '');
            ?><input type="text" id="mail_trail__ses_access_key" name="mail_trail__ses_access_key" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text code"><?php
        }
        
        function ses_secret_key_html() {
            $field_value = get_option('mail_trail__ses_secret_key', '');
            ?><input type="text" id="mail_trail__ses_secret_key" name="mail_trail__ses_secret_key" value="<?php echo htmlspecialchars($field_value); ?>" class="regular-text code"><?php
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
    require_once(plugin_dir_path(__FILE__).'mail-trail-message.php');
?>
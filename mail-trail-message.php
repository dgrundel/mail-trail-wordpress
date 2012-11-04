<?php
    class WebPres_Mail_Trail_Message {
        private $from;
        private $recipients;
        private $headers;
        private $attachments;
        private $charset;
        private $content_type;
        private $subject;
        private $body;
        
        public function __construct() {
            
            //set from name and address
            $from_address = get_option('mail_trail__send_mail_from_address', '');
            $from_name = get_option('mail_trail__send_mail_from_name', '');
            if(strlen($from_address) == 0) {
                // Get the site domain and get rid of www.
                $sitename = strtolower( $_SERVER['SERVER_NAME'] );
                if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                    $sitename = substr( $sitename, 4 );
                }
                $from_address = "wordpress@{$sitename}";
            }
            if(strlen($from_name) == 0) $from_name = 'WordPress';
            $this->set_from($from_address, $from_name);
            
            //set charset
            $this->set_charset(apply_filters( 'wp_mail_charset', get_bloginfo('charset') ));
            
            $content_type = 'text/plain';
            $content_type = apply_filters( 'wp_mail_content_type', $content_type );
            $this->set_content_type($content_type);
        }
        
        public static function validate_address($address) {
            //validate email address
            $email_regex = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/";
            return preg_match($email_regex, $sanitized_address) == 0 ? false : true;
        }
        
        public function set_from($address = null, $name = null) {
            
            if(!is_array($this->from)) $this->from = array();
            
            if($address !== null) {
                //not really sanitized. TODO later.
                $sanitized_address = trim($address);
                $this->from['address'] = $sanitized_address;
            }
            
            if($name !== null) {
                //not really sanitized. TODO later.
                $sanitized_name = trim($name);
                $this->from['name'] = $sanitized_name;
            }
        }
        
        public function get_from($part = null) {
            
            if(!is_array($this->from)) return null;
            
            if($part === null) {
                if(strlen($this->from['name']))
                    return $this->from['name'].' <'.$this->from['address'].'>';
                else
                    return $this->from['address'];
            } else {
                switch($part) {
                    case 'address':
                    case 'name':
                        return $this->from[$part];
                        break;
                    default:
                        throw new Exception("'{$part}' is not a valid from part.");
                }
            }
            
        }
        
        public function add_recipient($type = 'to', $address, $name = null) {
            
            switch($type) {
                case 'to':
                case 'cc':
                case 'bcc':
                    break;
                default:
                    throw new Exception("'{$type}' is not a valid recipient type.");
            }
            
            //initialize the arrays if need be.
            if(!is_array($this->recipients)) $this->recipients = array();
            if(!is_array($this->recipients[$type])) $this->recipients[$type] = array();
            
            //not really sanitized. TODO later.
            $sanitized_name = trim($name);
            $sanitized_address = trim($address);
            
            $this->recipients[$type][] = array(
                'address' => $sanitized_address,
                'name' => $sanitized_name);
        }
        
        public function add_to($address, $name = null) {
            $this->add_recipient('to', $address, $name);
        }
        public function add_cc($address, $name = null) {
            $this->add_recipient('cc', $address, $name);
        }
        public function add_bcc($address, $name = null) {
            $this->add_recipient('bcc', $address, $name);
        }
        
        public function get_recipients($type = null) {
            
            switch($type) {
                case 'to':
                case 'cc':
                case 'bcc':
                    return is_array($this->recipients[$type]) ? $this->recipients[$type] : array();
                    break;
                
                case null:
                    return is_array($this->recipients) ? $this->recipients : array();
                    break;
                
                default:
                    throw new Exception("'{$type}' is not a valid recipient type.");
            }
        }
        public function get_to() {
            return $this->get_recipients('to');
        }
        public function get_cc() {
            return $this->get_recipients('cc');
        }
        public function get_bcc() {
            return $this->get_recipients('bcc');
        }
        
        public function get_recipients_as_strings($type) {
            
            $to_return = array();
            
            switch($type) {
                case 'to':
                case 'cc':
                case 'bcc':
                    if(is_array($this->recipients[$type])) {
                        foreach($this->recipients[$type] as $recipient) {
                            if(strlen($recipient['name']) > 0)
                                $to_return[] = $recipient['name'].' <'.$recipient['address'].'>';
                            else
                                $to_return[] = $recipient['address'];
                        }
                        return $to_return;
                    } else {
                        return array();
                    }
                    break;
                
                default:
                    throw new Exception("'{$type}' is not a valid recipient type.");
            }
        }
        public function get_to_as_strings() {
            return $this->get_recipients_as_strings('to');
        }
        public function get_cc_as_strings() {
            return $this->get_recipients_as_strings('cc');
        }
        public function get_bcc_as_strings() {
            return $this->get_recipients_as_strings('bcc');
        }
        
        public function recipient_exists($address, $type = null) {
            
            switch($type) {
                case 'to':
                case 'cc':
                case 'bcc':
                    foreach($this->recipients[$type] as $key => $value) {
                        if($key == 'address' && $value == $address) return true;
                    }
                    break;
                
                case null:
                    foreach($this->recipients as $type => $recipients) {
                        foreach($recipients as $key => $value) {
                            if($key == 'address' && $value == $address) return true;
                        }
                    }
                    break;
                
                default:
                    throw new Exception("'{$type}' is not a valid recipient type.");
            }
            
            return false;
        }
        
        public function add_header($line) {
            //initialize the array if need be.
            if(!is_array($this->headers)) $this->headers = array();
            
            $this->headers[] = $line;
        }
        
        public function get_headers() {
            return is_array($this->headers) ? $this->headers() : array();
        }
        
        public function add_attachment($attachment) {
            //initialize the array if need be.
            if(!is_array($this->attachments)) $this->attachments = array();
            
            $this->attachments[] = $attachment;
        }
        
        public function get_attachments() {
            return is_array($this->attachments) ? $this->attachments : array();
        }
        
        public function set_charset($charset) {
            $this->charset = $charset;
        }
        
        public function get_charset() {
            return $this->charset;
        }
        
        public function set_content_type($content_type) {
            $this->content_type = $content_type;
        }
        
        public function get_content_type() {
            return $this->content_type;
        }
        
        public function set_subject($subject) {
            $this->subject = $subject;
        }
        
        public function get_subject() {
            return $this->subject;
        }
        
        public function set_body($content) {
            $this->body = $content;
            //if($html) $this->add_header("Content-type: text/html; charset={$this->charset}");
        }
        
        public function get_body() {
            return $this->body;
        }
        
        public function send_via_ses() {
            
            $this->pre_send_actions();
            
            require_once(plugin_dir_path(__FILE__).'ses.php');
            
            $access_key = get_option('mail_trail__ses_access_key', '');
            $secret_key = get_option('mail_trail__ses_secret_key', '');
            
            if(strlen($access_key) == 0)
                throw new Exception("SES Access Key not set.");
            if(strlen($secret_key) == 0)
                throw new Exception("SES Secret Key not set.");
            
            $ses = new SimpleEmailService($access_key, $secret_key);
            
            $message = new SimpleEmailServiceMessage();
            
            $message->setFrom($this->get_from());
            
            foreach($this->get_to_as_strings() as $recipient) {
                $message->addTo($recipient);
            }
            foreach($this->get_cc_as_strings() as $recipient) {
                $message->addCC($recipient);
            }
            foreach($this->get_bcc_as_strings() as $recipient) {
                $message->addBCC($recipient);
            }
            
            $message->setSubjectCharset($this->get_charset());
            $message->setSubject($this->get_subject());
            
            $message->setMessageCharset($this->get_charset());
            
            if($this->get_content_type() == 'text/html')
                $message->setMessageFromString(null, $this->get_body());
            else
                $message->setMessageFromString($this->get_body(), null);
            
            $send_status =  $ses->sendEmail($message);
            
            $this->post_send_actions($send_status);
            
            return $send_status;
        }
        
        public function send_via_phpmailer() {
            
            $this->pre_send_actions();
            
            global $phpmailer;
            
            // (Re)create it, if it's gone missing
            if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';
                $phpmailer = new PHPMailer( true );
            }
            
            // Empty out the values that may be set
            $phpmailer->ClearAddresses();
            $phpmailer->ClearAllRecipients();
            $phpmailer->ClearAttachments();
            $phpmailer->ClearBCCs();
            $phpmailer->ClearCCs();
            $phpmailer->ClearCustomHeaders();
            $phpmailer->ClearReplyTos();
            
            // Plugin authors can override the potentially troublesome default
            $phpmailer->From = $this->get_from('address');
            $phpmailer->FromName = $this->get_from('name');
            
            foreach($this->get_to() as $recipient) {
                $phpmailer->AddAddress($recipient['address'], $recipient['name']);
            }
            
            foreach($this->get_cc() as $recipient) {
                $phpmailer->AddCc($recipient['address'], $recipient['name']);
            }
            
            foreach($this->get_cc() as $recipient) {
                $phpmailer->AddBcc($recipient['address'], $recipient['name']);
            }
            
            $phpmailer->Subject = $this->get_subject();
            
            // Set to use PHP's mail()
            $phpmailer->IsMail();
            
            //Set content_type
            $content_type = $this->get_content_type();
            $phpmailer->ContentType = $content_type;
            
            // Set whether it's plaintext, depending on $content_type
            if ( 'text/html' == $content_type ) {
                $phpmailer->IsHTML( true );
            }
            
            $phpmailer->Body = $this->get_body();
            $phpmailer->CharSet = $this->get_charset();
            
            foreach($this->get_headers() as $header_line) {
                $phpmailer->AddCustomHeader($header_line);
            }
            
            foreach ( $this->get_attachments() as $attachment ) {
                try {
                    $phpmailer->AddAttachment($attachment);
                } catch ( phpmailerException $e ) {
                    continue;
                }
            }
            
            do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );
            
            try {
                $phpmailer->Send();
            } catch ( phpmailerException $e ) {
                $send_status = false;
            }
            $send_status = true;
            
            $this->post_send_actions($send_status);
            
            return $send_status;
        }
        
        public function save($send_status) {
            
            /* subject, body, and send_status are saved in both postmeta and the
            post itself.
            WP likes to strip things out of our post_content, though, so we use
            the postmeta to display the content to the user in WP admin.
            However, the post_content is searchable, so we still store the body
            content there also. */
            
            $new_post = array();
            $new_post['post_type'] = 'sent_mail';
            $new_post['post_title'] = $this->get_subject();
            $new_post['post_content'] = $this->get_body();
            $new_post['post_status'] = $send_status ? 'private' : 'draft';
            
            $new_post_meta = array();
            $new_post_meta['_to'] = implode(',', $this->get_to_as_strings());
            $new_post_meta['_cc'] = implode(',', $this->get_cc_as_strings());
            $new_post_meta['_bcc'] = implode(',', $this->get_bcc_as_strings());
            $new_post_meta['_headers'] = implode(',', $this->get_headers());
            $new_post_meta['_attachments'] = implode(',', $this->get_attachments());
            $new_post_meta['_created'] = time();
            $new_post_meta['_content_type'] = $this->get_content_type();
            $new_post_meta['_subject'] = $this->get_subject();
            $new_post_meta['_body'] = $this->get_body();
            $new_post_meta['_send_status'] = $send_status;
            
            $new_post_id = wp_insert_post($new_post, true);
            
            if(!is_wp_error($new_post_id) && $new_post_id > 0) {
                
                //set post_meta on inserted post
                foreach($new_post_meta as $meta_key => $meta_value) {
                    add_post_meta($new_post_id, $meta_key, $meta_value, true) or
                        update_post_meta($new_post_id, $meta_key, $meta_value);
                }
            }
        }
        
        private function pre_send_actions() {
            $admin_email = get_option('admin_email');
            
            //additional admin emails
            $additional_admin_emails = get_option('mail_trail__additional_admin_emails', '');
            if($this->recipient_exists($admin_email, 'to')) {
                foreach(explode(',', $additional_admin_emails) as $additional_email) {
                    $this->add_cc(trim($additional_email));
                }
            }
            
            //always bcc admin
            $always_bcc_admin = intval(get_option('mail_trail__always_bcc_admin', 0));
            if($always_bcc_admin) {
                if(!$this->recipient_exists($admin_email)) {
                    $this->add_bcc($admin_email);
                }
            }
            
        }
        
        private function post_send_actions($send_status) {
            $save_message = intval(get_option('mail_trail__enable_mail_save', 1));
            if($save_message) $this->save($send_status);
        }
    }
?>
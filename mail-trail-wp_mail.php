<?php
    if ( !function_exists( 'wp_mail' ) ) :
    /**
     * Send mail, similar to PHP's mail
     *
     * A true return value does not automatically mean that the user received the
     * email successfully. It just only means that the method used was able to
     * process the request without any errors.
     *
     * Using the two 'wp_mail_from' and 'wp_mail_from_name' hooks allow from
     * creating a from address like 'Name <email@address.com>' when both are set. If
     * just 'wp_mail_from' is set, then just the email address will be used with no
     * name.
     *
     * The default content type is 'text/plain' which does not allow using HTML.
     * However, you can set the content type of the email by using the
     * 'wp_mail_content_type' filter.
     *
     * The default charset is based on the charset used on the blog. The charset can
     * be set using the 'wp_mail_charset' filter.
     *
     * @since 1.2.1
     * @uses apply_filters() Calls 'wp_mail' hook on an array of all of the parameters.
     * @uses apply_filters() Calls 'wp_mail_from' hook to get the from email address.
     * @uses apply_filters() Calls 'wp_mail_from_name' hook to get the from address name.
     * @uses apply_filters() Calls 'wp_mail_content_type' hook to get the email content type.
     * @uses apply_filters() Calls 'wp_mail_charset' hook to get the email charset
     * @uses do_action_ref_array() Calls 'phpmailer_init' hook on the reference to
     *		phpmailer object.
     * @uses PHPMailer
     *
     * @param string|array $to Array or comma-separated list of email addresses to send message.
     * @param string $subject Email subject
     * @param string $message Message contents
     * @param string|array $headers Optional. Additional headers.
     * @param string|array $attachments Optional. Files to attach.
     * @return bool Whether the email contents were sent successfully.
     */
    function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
        
        // Compact the input, apply the filters, and extract them back out
        extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );
    
        if ( !is_array($attachments) )
            $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
        
        $mail_trail_message = new WebPres_Mail_Trail_Message();
        
        // Headers
        if ( empty( $headers ) ) {
            $headers = array();
        } else {
            if ( !is_array( $headers ) ) {
                // Explode the headers out, so this function can take both
                // string headers and an array of headers.
                $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
            } else {
                $tempheaders = $headers;
            }
            $headers = array();
            $cc = array();
            $bcc = array();
    
            // If it's actually got contents
            if ( !empty( $tempheaders ) ) {
                // Iterate through the raw headers
                foreach ( (array) $tempheaders as $header ) {
                    if ( strpos($header, ':') === false ) {
                        if ( false !== stripos( $header, 'boundary=' ) ) {
                            $parts = preg_split('/boundary=/i', trim( $header ) );
                            $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                        }
                        continue;
                    }
                    // Explode them out
                    list( $name, $content ) = explode( ':', trim( $header ), 2 );
    
                    // Cleanup crew
                    $name    = trim( $name    );
                    $content = trim( $content );
    
                    switch ( strtolower( $name ) ) {
                        // Mainly for legacy -- process a From: header if it's there
                        case 'from':
                            if ( strpos($content, '<' ) !== false ) {
                                // So... making my life hard again?
                                $from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
                                $from_name = str_replace( '"', '', $from_name );
                                $from_name = trim( $from_name );
    
                                $from_email = substr( $content, strpos( $content, '<' ) + 1 );
                                $from_email = str_replace( '>', '', $from_email );
                                $from_email = trim( $from_email );
                            } else {
                                $from_email = trim( $content );
                            }
                            break;
                        case 'content-type':
                            if ( strpos( $content, ';' ) !== false ) {
                                list( $type, $charset ) = explode( ';', $content );
                                $content_type = trim( $type );
                                if ( false !== stripos( $charset, 'charset=' ) ) {
                                    $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset ) );
                                } elseif ( false !== stripos( $charset, 'boundary=' ) ) {
                                    $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset ) );
                                    $charset = '';
                                }
                            } else {
                                $content_type = trim( $content );
                            }
                            break;
                        case 'cc':
                            $cc = array_merge( (array) $cc, explode( ',', $content ) );
                            break;
                        case 'bcc':
                            $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                            break;
                        default:
                            // Add it to our grand headers array
                            $headers[trim( $name )] = trim( $content );
                            break;
                    }
                }
            }
        }
    
        if ( isset( $from_name ) )
            $mail_trail_message->set_from(null, apply_filters('wp_mail_from_name', $from_name));
    
        if ( isset( $from_email ) )
            $mail_trail_message->set_from(apply_filters('wp_mail_from_email', $from_email), null);
        
        // Set destination addresses
        if ( !is_array( $to ) )
            $to = explode( ',', $to );
    
        foreach ( (array) $to as $recipient ) {
            try {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                $recipient_name = '';
                if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $recipient_name = $matches[1];
                        $recipient = $matches[2];
                    }
                }
                $mail_trail_message->add_to($recipient, $recipient_name);
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    
        // Set mail's subject and body
        $mail_trail_message->set_subject($subject);
        $mail_trail_message->set_body($message, $message);
        
        // Add any CC and BCC recipients
        if ( !empty( $cc ) ) {
            foreach ( (array) $cc as $recipient ) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    $recipient_name = '';
                    if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                        if ( count( $matches ) == 3 ) {
                            $recipient_name = $matches[1];
                            $recipient = $matches[2];
                        }
                    }
                    $mail_trail_message->add_cc($recipient, $recipient_name);
                } catch ( phpmailerException $e ) {
                    continue;
                }
            }
        }
    
        if ( !empty( $bcc ) ) {
            foreach ( (array) $bcc as $recipient) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    $recipient_name = '';
                    if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                        if ( count( $matches ) == 3 ) {
                            $recipient_name = $matches[1];
                            $recipient = $matches[2];
                        }
                    }
                    $mail_trail_message->add_bcc($recipient, $recipient_name);
                } catch ( phpmailerException $e ) {
                    continue;
                }
            }
        }
    
        // Set Content-Type and charset
        // If we don't have a content-type from the input headers
        if ( !isset( $content_type ) )
            $content_type = 'text/plain';
        
        $content_type = apply_filters( 'wp_mail_content_type', $content_type );
        $mail_trail_message->set_content_type($content_type);
        
        // Set the content-type and charset
        if ( isset( $charset ) )
            $mail_trail_message->set_charset(apply_filters( 'wp_mail_charset', $charset ));
    
        // Set custom headers
        if ( !empty( $headers ) ) {
            foreach( (array) $headers as $name => $content ) {
                $mail_trail_message->add_header(sprintf( '%1$s: %2$s', $name, $content ));
            }
    
            if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
                $mail_trail_message->add_header(sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ));
        }
    
        if ( !empty( $attachments ) ) {
            foreach ( $attachments as $attachment ) {
                $mail_trail_message->add_attachment($attachment);
            }
        }
    
        $send_via_ses = intval(get_option('mail_trail__enable_ses', 0));
        if($send_via_ses)
            return $mail_trail_message->send_via_ses();
        else
            return $mail_trail_message->send_via_phpmailer();
    }
    endif;
?>
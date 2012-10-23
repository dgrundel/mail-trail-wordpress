mail-trail-wordpress
====================

A WordPress plugin for sending and keeping track of messages sent by your WordPress site.

Creates a custom post type called *Sent Mail* and creates a post of this type every time a message is sent via the wp_mail function.

**Coming Soon**
- Send messages via Amazon SES
- BCC all messages to site Admin
- Send all admin messages to multiple addresses

SES functionality will probably make use of Dan Myers' Amazon SimpleEmailService PHP class
http://sourceforge.net/projects/php-aws-ses/

<?php global $post; ?>
<style>
    .sent_mail {
        font-size: 13px;
        line-height: 16px;
        color: #333;
    }
    .sent_mail table { width: 100%; }
    .sent_mail th,
    .sent_mail td {
        padding: 4px;
        font-size: 13px;
        line-height: 16px;
        color: #333;
    }
    .sent_mail th {
        width: 10%;
        text-align: right;
    }
    .sent_mail td {
        text-align: left;
        font-family: monospace;
    }
    .sent_mail .body {
        background-color: #fff;
        border: 1px solid #efefef;
        padding: 10px 4px;
        margin: 10px 0;
        text-align: left;
    }
    .sent_mail .body.other { font-family: monospace; }
</style>
<div class="sent_mail">
    <table>
        <thead>
            <tr>
                <th>To</th>
                <td><?php echo get_post_meta($post->ID, '_to', true); ?></td>
            </tr>
            <tr>
                <th>CC</th>
                <td><?php echo get_post_meta($post->ID, '_cc', true); ?></td>
            </tr>
            <tr>
                <th>BCC</th>
                <td><?php echo get_post_meta($post->ID, '_bcc', true); ?></td>
            </tr>
            <tr>
                <th>Subject</th>
                <td><?php echo get_post_meta($post->ID, '_subject', true); ?></td>
            </tr>
        </thead>
    </table><?php
    
    $content_type = get_post_meta($post->ID, '_content_type', true);
    
    if($content_type == 'text/html') {
        echo '<div class="body html">'.get_post_meta($post->ID, '_body', true).'</div>';
    } else {
        echo '<div class="body other">'.nl2br(htmlspecialchars(get_post_meta($post->ID, '_body', true))).'</div>';
    }
    
?></div>
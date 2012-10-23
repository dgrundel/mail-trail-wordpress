<?php global $post; ?>
<style>
    .sent_mail { width: 100%; }
    .sent_mail th,
    .sent_mail td {
        padding: 4px;
        font-size: 1.1em;
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
    .sent_mail td.body {
        width: 100%;
        background-color: #fff;
        border: 1px solid #efefef;
        padding: 10px 4px;
    }
</style>
<table class="sent_mail">
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
            <td><?php echo $post->post_title; ?></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2" class="body"><?php echo nl2br($post->post_content); ?></td>
        </tr>
    </tbody>
</table>
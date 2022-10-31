<html>

<body>
    <center>
        <table width="685" style="border:10px solid #49725B; padding:0; text-align:left; font-family:Verdana, Geneva, sans-serif; font-size:12px;" border="0" cellpadding="0" cellspacing="0" align="center">
            <tr style="background-color:#fff;">
                <td align="left" valign="middle" style=";padding:20px;text-align:center;">
                    <a href="<?php echo site_url() ?>" target="_blank" style="display: inline-block;">
                        <img src="<?php echo get_stylesheet_directory_uri() ?>/assets/images/Logo.png" alt="<?php echo get_bloginfo('name'); ?>" title="<?php echo get_bloginfo('name'); ?>" border="0" height="auto" width="auto" />
                    </a>
                </td>
            </tr>
            <tr>
                <td align="left" valign="top" style="padding:25px;">
                    <span style="color:#49725B; font-weight:bold;"><?php echo get_bloginfo('name'); ?> sent this message to you.</span><br><br>
                    Dear <b>Admin,</b><br><br>
                    You have received a new inquiry from <?php echo get_bloginfo('name'); ?> website.<br><br>
                    Please find the details below:<br>
                    <ul>
                        <li><strong>First Name:</strong> <?php echo $_REQUEST['firstname']; ?></li>
                        <li><strong>Last NAME:</strong> <?php echo $_REQUEST['lastname']; ?></li>
                        <li><strong>Company :</strong> <?php echo $_REQUEST['company']; ?></li>
                        <li><strong>Email:</strong> <?php echo $_REQUEST['email']; ?></li>
                        <?php if ('' != $_REQUEST['message']) :  echo '<li><strong>Message:</strong> ' . $_REQUEST['message'] . '</li>';
                        endif; ?>
                    </ul>
                    <br>
                    <b>Best Regards,</b> <br>
                    <a href="<?php echo site_url() ?>" target="_blank"><strong><?php echo get_bloginfo('name'); ?></strong></a>
                    <br />
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
<!DOCTYPE html>
<html>
        <head>
                <title><?php echo BRAND_PRODUCT; ?></title>
                <!-- Styles -->
                <style>
                /* Fonts */

                @import url(https://fonts.googleapis.com/css?family=Open+Sans:300,400);

                /* General */

                html, body {
                        font-family: 'Open Sans', Sans-serif;
                        font-size: 13px;
                        font-weight: 300;
                }

                table {
                        border-collapse: collapse;
                }

                .email {
                        width: 500px;
                }

                .email a {
                        color: #8db832;
                        text-decoration: none;
                }

                .email td {
                        vertical-align: top;
                }

                .email hr {
                        height: 1px;
                        color: #dddddd;
                }

                .email .logo {
                        text-align: right;
                }

                .email .hidden td {
                        color: #ffffff;
                }

                .email .hidden td::selection, .email .hidden td::-moz-selection {
                        color: #dddddd;
                }
                </style>
        </head>
        <body>
                <table class="email">
                        <tr>
                                <td><?php _e('Dear customer,'); ?></td>
                                <td class="logo">
                                        <img src="<?php echo URL_BASE; ?>/img/spotzi/logo.png" alt="">
                                </td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('Thank you for creating your personal  account! Below you will find details of your account.'); ?></td>
                        </tr>
                        <tr>
                                <td colspan="2"><hr></td>
                        </tr>
                        <tr>
                                <td><?php _e('User name'); ?></td>
                                <td><?php echo $this->userName; ?></td>
                        </tr>
                        <tr>
                                <td><?php _e('Email address'); ?></td>
                                <td><a href="mailto:<?php echo $this->userEmail; ?>"><?php echo $this->userEmail; ?></a></td>
                        </tr>
                        <tr>
                                <td colspan="2"><hr></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('You can log in using your user name or email address in combination with your password by <a href="http://www.spotzi.com/en/">clicking here</a>. Afterwards you will have the ability to order maps, edit maps and export data from maps.'); ?></td>
                        </tr>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('Best regards,'); ?></td>
                        </tr>
                        <tr>
                                <td colspan="2"><b><?php echo BRAND_NAME; ?></b></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('Veilingdreef 17'); ?></td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('4614RX, Bergen op Zoom'); ?></td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('The Netherlands'); ?></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('+31 164 24 00 00'); ?></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2">
                                        <a id="website" href="<?php _e('http://www.spotzi.com/en/'); ?>"><?php _e('Website'); ?></a>
                                        |
                                        <a id="contact" href="mailto:<?php _e('info@spotzi.com'); ?>"><?php _e('Contact'); ?></a>
                                        |
                                        <a id="problem" href="mailto:<?php echo EMAIL_FROM; ?>?subject=<?php echo _e('%s - problem with Spotzi Mapbuilder account', BRAND_PRODUCT); ?>"><?php _e('Report a problem'); ?></a>
                                </td>
                        </tr>
                </table>
        </body>
</html>
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
                                <td colspan="2"><?php _e('You are receiving this email because you indicated that you forgot the password for Spotzi Mapbuilder account %s. Click the link below to change your password. The link will expire in 2 days.', ucwords($this->user['Name'])); ?></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php_e('Does this account not belong to you or did you not request this change? Then you can ignore this email.'); ?></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><hr></td>
                        </tr>
                        <tr>
                                <td colspan="2"><a href="<?php echo $changePasswordUrl; ?>"><?php _e('Click here to change your password'); ?></a></td>
                        </tr>
                        <tr>
                                <td colspan="2"><hr></td>
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
                                        <a id="problem" href="mailto:<?php echo EMAIL_FROM; ?>?subject=<?php _e('%s - problem with Spotzi Mapbuilder account', BRAND_PRODUCT); ?>"><?php _e('Report a problem'); ?></a>
                                </td>
                        </tr>
                </table>
        </body>
</html>
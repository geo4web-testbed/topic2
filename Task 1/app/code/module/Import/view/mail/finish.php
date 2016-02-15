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
                                <td colspan="2"><?php _e('The import process of your Geonovum account was finished. Below you will find details of is results.'); ?></td>
                        </tr>
<?php if ($importsSuccess): ?>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('The following maps were <b style="color: #8db832;">successfully</b> imported:'); ?></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><hr></td>
                        </tr>
<?php   foreach ($importsSuccess as $import): ?>
<?php           if ($import['order']): ?>
                        <tr>
                                <td><?php _e('Order number'); ?></td>
                                <td><?php echo $import['order']; ?></td>
                        </tr>
<?php           endif; ?>
                        <tr>
                                <td><?php _e('Map'); ?></td>
                                <td><?php echo $import['name']; ?></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
<?php   endforeach; ?>
                        <tr>
                                <td colspan="2"><?php _e('You can view the maps by <a href="%s">clicking here</a>. Be aware: you may need to log in to your Geonovum account again in order to view the maps.', URL_BASE); ?></td>
                        </tr>
                        <tr>
                                <td colspan="2"><hr></td>
                        </tr>
<?php endif; ?>
<?php if ($importsError): ?>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><?php _e('The following maps could <b style="color: #fb0000;">not</b> be imported:'); ?></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
                        <tr>
                                <td colspan="2"><hr></td>
                        </tr>
<?php   foreach ($importsError as $import): ?>
<?php           if ($import['order']): ?>
                        <tr>
                                <td><?php _e('Order number'); ?></td>
                                <td><?php echo $import['order']; ?></td>
                        </tr>
<?php           endif; ?>
                        <tr>
                                <td><?php _e('Map'); ?></td>
                                <td><?php echo $import['name']; ?></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        </tr>
<?php   endforeach; ?>
                        <tr>
                                <td colspan="2"><?php _e('An import error can have several causes.<br>First, check <a href="http://spotzi.com/en/documentation/import-dataset/">this page</a> to see if the file type and the coordinate system of the data are supported by the Spotzi Geonovum mapviewer. If not, try to import a different file. Please contact us if the file does meet the requirements, we would love to help you solve the problem. To contact us, use the contact data below.'); ?></td>
                        </tr>
                        <tr>
                                <td colspan="2"><hr></td>
                        </tr>
<?php endif; ?>
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
<!DOCTYPE html>
<html>
        <head>
                <title><?php echo BRAND_PRODUCT; ?></title>
                <?php echo $this->getHeaderHTML(); ?>
        </head>
        <body>
                <noscript>
                        <style scoped>
                        body > * {
                                display: none;
                        }
                        </style>
                        <?php _e('This application uses Javascript. Please check if your browser supports Javascript and if it\'s enabled.'); ?>
                </noscript>
                <?php echo $result; ?>
        </body>
        <!-- Powered by Spotzi -->
</html>
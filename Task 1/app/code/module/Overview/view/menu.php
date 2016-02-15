<?php
$this->addHeaderJS('/js/remodal.min.js');
?>
<script>
var loggedIn = <?php echo ($loggedIn ? 'true' : 'false'); ?>;

$(document).ready(function() {
        $('select').chosen({
                disable_search_threshold: 10,
                no_results_text: '<?php _e('No results were found'); ?>',
                placeholder_text_single: '<?php _e('Select...'); ?>',
                placeholder_text_multiple: '<?php _e('Select...'); ?>'
        });

        // Trigger handlers
        $('body').addClass('menu').on('initializeVisualization', function() {
                $('#menuHome').show();

                if (visualization.map_enabled || visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?>) {
                        $('#menuMapUpdates, #menuMapSettings, #mapEdit')[visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?> ? 'show' : 'hide']();
                        $('#menuMapEdit')[visualization.edit_enabled || visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?> ? 'show' : 'hide']();

                        if (visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?>) {
                                $('#mapPrivacy').val(visualization.map_privacy).trigger('chosen:updated');
                                $('#mapUserInput').find('input').val(visualization.map_privacy_users);
                                $('#editPrivacy').val(visualization.edit_privacy).trigger('chosen:updated');
                                $('#editUserInput').find('input').val(visualization.edit_privacy_users);
                                $('#editModeCheckbox').prop('checked', visualization.edit_mode);

                                $('#mapPrivacy, #editPrivacy').change();
                        }
                } else {
                        $('#menuMapUpdates, #menuMapSettings, #menuMapEdit').hide();
                }
        }).on('clearVisualization', function() {
        }).on('initializeMenu', function() {
                var fragment = window.location.hash;
                var modalId = (fragment ? fragment.slice(1) : $.localStorage.getItem('modal'));
                if (modalId<?php if ($loggedIn): ?> && modalId !== 'login'<?php endif; ?>) openModal(modalId);

                $('#<?php echo ($loggedIn ? 'menuLogin' : 'menuCommunity'); ?>').click();
<?php if (!$model->visualizationSet): ?>
                showHomeScreen();
<?php else: ?>
                ($.localStorage.getItem('home') ? showHomeScreen() : hideHomeScreen());
<?php endif; ?>
        });

        // Login
        $('#loginForm').submit(login);
        $('#passwordForm').submit(sendPasswordMail);
<?php if ($loggedIn): ?>
        $('#menuUser').click(function() {
                checkLogin(function() {
                        openModal('account');
                });
        });
        $('#menuMaps').click(function() {
                checkLogin(function() {
                        $('#menuLogin').click();
                        showHomeScreen();
                });
        });
        $('#menuLogout').click(logout);
        $('#accountPasswordForm').submit(changeAccountPassword);

        $('#menuMapUpdates').click(function() {
                checkLogin(function() {
                        openModal('mapUpdates');

                        $('#menuProposed')[visualization.edit_mode ? 'show' : 'hide']();

                        // @todo: prepare update tabs
                });
        });
        $('#mapUpdates').find('#updatesMenu > li').click(function() {
                var menuItem = $(this);
                menuItem.parent().find('> li').removeClass('active');
                menuItem.addClass('active');

                // @todo: retrieve corresponding data
                $('#mapUpdates').find('#updatesOverview > div').slideUp();
                $('#' + menuItem.data('rel')).slideDown();
        });
        $('#mapUpdates').on('click', '.dataTable tr', showUpdate);
        $('#menuRecent').click();
        $('#menuMapSettings').click(function() {
                checkLogin(function() {
                        openModal('mapSettings');
                });
        });
        $('#settingsForm').submit(setMapSettings);
        $('#mapPrivacy').change(function() {
                var value = $(this).val();

                $('#mapUserInput')[value === 'link' ? 'slideDown' : 'slideUp']();
                $('#editPrivacyContent, #editModeContent')[value === 'private' ? 'slideUp' : 'slideDown']();
        });
        $('#editPrivacy').change(function() {
                var value = $(this).val();

                $('#editUserInput')[value === 'link' ? 'slideDown' : 'slideUp']();
                $('#editModeContent')[value === 'private' ? 'slideUp' : 'slideDown']();
        });

        setInterval(function() {
                loadUrl('<?php echo $this->getSessionStatusURL(); ?>', {}, function() {
                        loggedIn = true;
                }, function() {
                        loggedIn = false;
                }, function() {
                        if (!loggedIn) logoutSession();
                });
        }, 600000);
<?php else: ?>
        $('#menuUser, #menuLogin').click(function() {
                checkLogin(null, 'maps');
        });

        // Register
        $('#menuRegister').click(showRegister);
        $('#registerForm').submit(register);
<?php endif; ?>

        // Menu
        $(document).click(function() {
                $('.menuSub').removeClass('active');
        });
        $('.menuButton').has('.menuSub').click(function(e) {
                e.stopPropagation();

                var dropdown = $(this).find('.menuSub');
                var active = dropdown.hasClass('active');

                $('.menuSub').removeClass('active');
                if (!active) dropdown.addClass('active');
        });
        $('#menuHome').click(showHomeScreen);
        $('#menuModal').find('.back').click(function() {
                var modalId = $(this).data('remodal-rel');
                if (modalId) openModal(modalId);
        });
});

// Login
function showLogin() {
        openModal('login');
        $($('#loginUserName').hasValue() ? '#loginUserPassword' : '#loginUserName').focus();
}

function closeLogin() {
        closeModal('login');
}

function checkLogin(callback, showHome, modal) {
<?php if ($loggedIn): ?>
        if (loggedIn) {
                if ($.isFunction(callback)) callback();
        } else {
                $('#login').unbind('login').on('login', ($.isFunction(callback) ? callback : function() {}));

                showLogin();
        }
<?php else: ?>
        $.localStorage.setItem('home', showHome);
        $.localStorage.setItem('modal', modal);

        showLogin();
<?php endif; ?>
}

function login(e) {
        e.preventDefault();
        $('#loginError').hide();

        var form = $(this);
        form.find('input[type=submit]').attr('disabled', true).end().find('.loading').show();

        loadUrl(form.attr('action'), {
                data: form.serializeObject()
        }, function() {
<?php if ($loggedIn): ?>
                loggedIn = true;
                closeLogin();

                $('#login').trigger('login');
<?php else: ?>
                reloadTopLocation();
<?php endif; ?>
        }, function(err, errShow) {
                loggedIn = false;
                $('#loginError').html(errShow || '<?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?>').show();

                logError(err);
        }, function() {
                form.find('input[type=submit]').attr('disabled', false).end().find('.loading').hide();
        });
}

function sendPasswordMail(e) {
        e.preventDefault();
        $('#passwordSuccess, #passwordError').hide();

        var form = $(this);
        form.find('input[type=submit]').attr('disabled', true).end().find('.loading').show();

        loadUrl(form.attr('action'), {
                data: form.serializeObject()
        }, function() {
                $('#passwordSuccess').show();
        }, function(err, errShow) {
                $('#passwordError').html(errShow || '<?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?>').show();

                logError(err);
        }, function() {
                form.find('input[type=submit]').attr('disabled', false).end().find('.loading').hide();
        });
}

<?php if ($loggedIn): ?>
function logoutSession() {
        $('#logoutSessionFrame').attr('src', 'http://<?php echo $user['UserName']; ?>.<?php echo VISUALIZATION_DOMAIN; ?>/logout?response=0&time=' + Math.floor(Date.now() / 1000));
}

function logout(e) {
        e.preventDefault();

        var button = $(this);
        if (!button.attr('disabled')) {
                button.attr('disabled', true);

                loadUrl('<?php echo $this->getSessionInvalidateUrl(); ?>', {}, function() {
                        $('#logoutSessionFrame').unbind('load').load(function() {
                               reloadTopLocation();
                        });
                        logoutSession();
                }, null, function() {
                        button.attr('disabled', false);
                });
        }
}

function changeAccountPassword(e) {
        e.preventDefault();
        $('#accountPasswordSuccess, #accountPasswordError').hide();

        var form = $(this);
        form.find('input[type=submit]').attr('disabled', true).end().find('.loading').show();

        loadUrl(form.attr('action'), {
                data: form.serializeObject()
        }, function() {
                $('#accountPasswordSuccess').show();
                form.trigger('reset');
        }, function(err, errShow) {
                $('#accountPasswordError').html(errShow || '<?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?>').show();

                logError(err);
        }, function() {
                form.find('input[type=submit]').attr('disabled', false).end().find('.loading').hide();
        });
}

function setMapSettings(e) {
        e.preventDefault();
        $('#settingsSuccess, #settingsError').hide();

        var form = $(this);
        form.find('input[type=submit]').attr('disabled', true).end().find('.loading').show();

        loadUrl(form.attr('action'), {
                data: form.serializeObject()
        }, function() {
                $('#settingsSuccess').show();

                visualization.map_privacy = $('#mapPrivacy').val();
                visualization.map_privacy_users = $('#mapUserInput').find('input').val();
                visualization.edit_privacy = $('#editPrivacy').val();
                visualization.edit_privacy_users = $('#editUserInput').find('input').val();
                visualization.edit_mode = $('#editModeCheckbox').is(':checked');

                if (visualization.edit_mode) {
                        // @todo: show loading

                        loadUrl('<?php echo $this->getVisualizationPropositionUrl(); ?>', {}, function() {
                                // @todo: show success

                                mapReload(true);
                        }, function() {
                                // @todo: show error
                        }, function() {
                                // @todo: hide loading
                        });
                }
        }, function(err, errShow) {
                $('#settingsError').html(errShow || '<?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?>').show();

                logError(err);
        }, function() {
                form.find('input[type=submit]').attr('disabled', false).end().find('.loading').hide();
        });
}

// Updates
function showUpdate() {
        console.log('showing update');
        // @todo: get viz and data
        // @todo: load visualization
        // @todo: show feature window
        // @todo: show old vs. new
        if (visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?>) {
                // @todo: show buttons
        }
        // @todo: hide modal
}
<?php else: ?>
// Register
function showRegister() {
        openModal('register');
        $($('#registerUserName').hasValue() ? ($('#registerUserEmail').hasValue() ? '#registerUserPassword' : '#registerUserEmail') : '#registerUserName').focus();
}

function register(e) {
        e.preventDefault();
        $('#registerError').hide();

        var form = $(this);
        form.find('input[type=submit]').attr('disabled', true).end().find('.loading').show();

        loadUrl(form.attr('action'), {
                data: form.serializeObject()
        }, function() {
                reloadTopLocation(true);
        }, function(err, errShow) {
                $('#registerError').html(errShow || '<?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?>').show();

                logError(err);
        }, function() {
                form.find('input[type=submit]').attr('disabled', false).end().find('.loading').hide();
        });
}
<?php endif; ?>

// General
function openModal(id) {
        var modal = $('[data-remodal-id=' + id + ']');
        if (modal.length) modal.remodal().open();
}

function closeModal(id) {
        var modal = $('[data-remodal-id=' + id + ']');
        if (modal.length) modal.remodal().close();
}
</script>
<div id="menu">
        <div id="mainMenu">
                <div id="menuButtons">
                        <div id="menuHome" class="menuButton" title="<?php _e('Maps'); ?>">
                                <span class="fa-icon fa-globe"></span>
                                <span class="title"><?php _e('Maps'); ?></span>
                        </div>
<?php if ($loggedIn): ?>
                        <div id="menuMapUpdates" class="menuButton" title="<?php _e('Map updates'); ?>">
                                <span class="fa-icon fa-pencil-square-o"></span>
                                <span class="title"><?php _e('Map updates'); ?></span>
                        </div>
                        <div id="menuMapSettings" class="menuButton" title="<?php _e('Map settings'); ?>">
                                <span class="fa-icon fa-cog"></span>
                                <span class="title"><?php _e('Map settings'); ?></span>
                        </div>
                        <div id="menuMapEdit" class="menuButton" title="<?php _e('Edit'); ?>">
                                <span class="fa-icon fa-pencil"></span>
                                <span class="title"><?php _e('Edit'); ?></span>
                                <ul id="editMenu" class="menuSub">
                                        <li class="fa-icon fa-circle" title="<?php _e('Add point'); ?>"><?php _e('Add point'); ?></li>
                                        <li class="borderTop fa-icon fa-minus" title="<?php _e('Add line'); ?>"><?php _e('Add line'); ?></li>
                                        <li class="borderTop fa-icon fa-delicious" title="<?php _e('Add polygon'); ?>"><?php _e('Add polygon'); ?></li>
                                        <li id="mapEdit" class="borderTop fa-icon fa-pencil-square-o" title="<?php _e('Advanced editor'); ?>"><?php _e('Advanced editor'); ?></li>
                                </ul>
                        </div>
<?php endif; ?>
                        <div id="menuAccount" class="<?php if ($loggedIn): ?>menuUser <?php endif; ?>menuButton">
                                <span class="fa-icon fa-user" title="<?php _e('Account'); ?>"></span>
                                <ul class="menuSub">
<?php if ($loggedIn): ?>
                                        <li id="menuUser" class="menuUser fa-icon fa-user" title="<?php _e('Account'); ?>"><?php echo ucwords(isset($user['FullName']) ? $user['FullName'] : $user['UserName']); ?></li>
                                        <li id="menuMaps" class="borderTop fa-icon fa-globe" title="<?php _e('My Maps'); ?>"><?php _e('My Maps'); ?></li>
                                        <li id="menuLogout" class="borderTop fa-icon fa-sign-out" class="borderTop" title="<?php _e('Log out'); ?>"><?php _e('Log out'); ?></li>
<?php else: ?>
                                        <li id="menuUser" class="menuNoUser fa-icon fa-user" title="<?php _e('Log in'); ?>"><?php _e('Not logged in'); ?></li>
                                        <li id="menuLogin" class="fa-icon fa-sign-in" title="<?php _e('Log in'); ?>"><?php _e('Log in'); ?></li>
                                        <li id="menuRegister" class="borderTop fa-icon fa-user-plus" class="borderTop" title="<?php _e('Register'); ?>"><?php _e('Register'); ?></li>
<?php endif; ?>
                                </ul>
                        </div>
                </div>
        </div>
        <div id="menuModal">
                <div id="login" data-remodal-id="login">
                        <button data-remodal-action="close" class="remodal-close"></button>
                        <h4><?php _e('Log in'); ?></h4>
                        <div id="loginUser">
                                <p class="description"><?php _e('In order to gain access to this function you must log in to your account.'); ?></p>
                                <form id="loginForm" class="clear" method="post" enctype="multipart/form-data" action="<?php echo $this->getSessionValidateUrl(); ?>">
                                        <p id="loginError" class="spotziRed" style="display: none;"></p>
                                        <p><input id="loginUserName" type="text" name="<?php echo REQUEST_PARAMETER_USER_NAME; ?>"
                                                  placeholder="<?php _e('User name or email address'); ?>"<?php if ($loggedIn): ?> value="<?php echo $user['UserName']; ?>"<?php endif; ?> required></p>
                                        <p><input id="loginUserPassword" type="password" name="<?php echo REQUEST_PARAMETER_USER_PASSWORD; ?>" placeholder="<?php _e('Password'); ?>" required></p>
                                        <p class="note clear">
                                                <span><?php _e('Don\'t have an account? <a href="javascript:void(0);" onclick="showRegister();">Click here</a> to create one.'); ?></span>
                                                <input id="loginSubmit" type="submit" value="<?php _e('Log in'); ?>">
                                        </p>
                                        <div class="loading"><div class="fa-icon fa-spinner fa-pulse"></div></div>
                                </form>
                        </div>
                </div>
<?php if ($loggedIn): ?>
                <div id="logoutSession">
                        <iframe id="logoutSessionFrame" style="display: none;"></iframe>
                </div>
                <div id="account" data-remodal-id="account">
                        <button data-remodal-action="close" class="remodal-close"></button>
                        <h4><?php _e('My account'); ?></h4>
                        <div id="accountProfile">
                                <h5><?php _e('Profile'); ?></h5>
                                <table id="profileTable">
                                        <tr>
                                                <td><?php _e('User name'); ?></td>
                                                <td><?php echo ucwords(isset($user['FullName']) ? $user['FullName'] : $user['UserName']); ?></td>
                                        </tr>
                                        <tr>
                                                <td><?php _e('Email address'); ?></td>
                                                <td><?php echo $user['Email']; ?></td>
                                        </tr>
                                </table>
                        </div>
                        <div id="accountPassword">
                                <h5><?php _e('Change password'); ?></h5>
                                <form id="accountPasswordForm" method="post" enctype="multipart/form-data" action="<?php echo $this->getSessionUserUrl(); ?>">
                                        <p id="accountPasswordSuccess" class="spotziGreen" style="display: none;"><?php _e('Your password was successfully changed'); ?></p>
                                        <p id="accountPasswordError" class="spotziRed" style="display: none;"></p>
                                        <p><input id="accountPasswordOld" type="password" name="passwordOld" placeholder="<?php _e('Current password'); ?>" required></p>
                                        <p><input id="accountPasswordNew" type="password" name="passwordNew" placeholder="<?php _e('New password'); ?>" required></p>
                                        <p><input id="accountPasswordConfirm" type="password" name="passwordConfirm" placeholder="<?php _e('Confirm password'); ?>" required></p>
                                        <p class="clear"><input id="accountPasswordSubmit" type="submit" value="<?php _e('Change password'); ?>"></p>
                                        <div class="loading"><div class="fa-icon fa-spinner fa-pulse"></div></div>
                                </form>
                        </div>
                </div>
                <div id="mapUpdates" data-remodal-id="mapUpdates">
                        <button data-remodal-action="close" class="remodal-close"></button>
                        <h4><?php _e('Map updates'); ?></h4>
                        <div id="updatesContent">
                                <p>
                                        <ul id="updatesMenu" class="clear">
                                                <li id="menuRecent" data-rel="updatesRecent"><h5><?php _e('Recent'); ?></h5></li>
                                                <li id="menuProposed" data-rel="updatesProposed"><h5><?php _e('Proposed'); ?></h5></li>
                                                <li id="menuMy" data-rel="updatesMy"><h5><?php _e('My updates'); ?></h5></li>
                                        </ul>
                                </p>
                                <div id="updatesOverview">
                                        <div id="updatesRecent">
                                                <table class="dataTable">
                                                        <thead>
                                                                <tr>
                                                                        <th><h6><?php _e('Map'); ?></h6></th>
                                                                        <th><h6><?php _e('User'); ?></h6></th>
                                                                        <th><h6><?php _e('Status'); ?></h6></th>
                                                                        <th><h6><?php _e('Created at'); ?></h6></th>
                                                                </tr>
                                                        </thead>
                                                        <tbody>
                                                                <tr>
                                                                        <td>Agrarische import</td>
                                                                        <td>Ruben</td>
                                                                        <td><?php _e('Pending'); ?></td>
                                                                        <td>2015-12-23 14:40:24</td>
                                                                </tr>
                                                                <tr>
                                                                        <td>Akkerbouw</td>
                                                                        <td>Ruben</td>
                                                                        <td><?php _e('Pending'); ?></td>
                                                                        <td>2015-12-23 13:37:43</td>
                                                                </tr>
                                                                <tr>
                                                                        <td>Agrarische import</td>
                                                                        <td>Teun</td>
                                                                        <td><?php _e('<span class="spotziGreen">Accepted</span>'); ?></td>
                                                                        <td>2015-12-22 15:31:58</td>
                                                                </tr>
                                                        </tbody>
                                                </table>
                                        </div>
                                        <div id="updatesProposed"><?php _e('Proposed'); ?></div>
                                        <div id="updatesMy"><?php _e('My updates'); ?></div>
                                </div>
                                <div class="loading"><div class="fa-icon fa-spinner fa-pulse"></div></div>
                        </div>
                </div>
                <div id="mapSettings" data-remodal-id="mapSettings">
                        <button data-remodal-action="close" class="remodal-close"></button>
                        <h4><?php _e('Map settings'); ?></h4>
                        <div id="settingsContent">
                                <form id="settingsForm" method="post" enctype="multipart/form-data" action="<?php echo $this->getVisualizationUpdateUrl(); ?>">
                                        <p id="settingsSuccess" class="spotziGreen" style="display: none;"><?php _e('The settings were saved successfully.'); ?></p>
                                        <p id="settingsError" class="spotziRed" style="display: none;"><?php _e('The settings could not be saved.'); ?></p>
                                        <div id="mapPrivacyContent">
                                                <h5><?php _e('Map privacy'); ?></h5>
                                                <p>
                                                        <select id="mapPrivacy" name="mapPrivacy">
                                                                <option value="public"><?php _e('Public'); ?></option>
                                                                <option value="link"><?php _e('Restricted'); ?></option>
                                                                <option value="private"><?php _e('Private'); ?></option>
                                                        </select>
                                                        <div id="mapUserInput" class="userInput">
                                                                <input type="text" name="mapPrivacyUsers" placeholder="<?php _e('Allowed users'); ?>" value="">
                                                                <p class="note"><?php _e('Comma separated list of user names'); ?></p>
                                                        </div>
                                                </p>
                                        </div>
                                        <div id="editPrivacyContent">
                                                <h5><?php _e('Edit privacy'); ?></h5>
                                                <p>
                                                        <select id="editPrivacy" name="editPrivacy">
                                                                <option value="public"><?php _e('Public'); ?></option>
                                                                <option value="link"><?php _e('Restricted'); ?></option>
                                                                <option value="private"><?php _e('Private'); ?></option>
                                                        </select>
                                                        <div id="editUserInput" class="userInput">
                                                                <input type="text" name="editPrivacyUsers" placeholder="<?php _e('Allowed users'); ?>" value="">
                                                                <p class="note"><?php _e('Comma separated list of user names'); ?></p>
                                                        </div>
                                                </p>
                                        </div>
                                        <div id="editModeContent">
                                                <h5><?php _e('Edit mode'); ?></h5>
                                                <p>
                                                        <label for="editModeCheckbox" class="checkbox">
                                                                <input id="editModeCheckbox" type="checkbox" name="editMode">
                                                                <span></span>
                                                                <?php _e('Review community updates'); ?>
                                                        </label>
                                                </p>
                                        </div>
                                        <p class="note clear">
                                                <input id="doSettings" type="submit" value="<?php _e('Update settings'); ?>">
                                        </p>
                                        <div class="loading"><div class="fa-icon fa-spinner fa-pulse"></div></div>
                                </form>
                        </div>
                </div>
<?php else: ?>
                <div id="register" data-remodal-id="register">
                        <button data-remodal-action="close" class="remodal-close"></button>
                        <h4><?php _e('Register'); ?></h4>
                        <div id="registerContent">
                                <p class="description"><?php _e('In order to gain access to this function you must create an account.'); ?></p>
                                <form id="registerForm" method="post" enctype="multipart/form-data" action="<?php echo $this->getSessionRegisterUrl(); ?>">
                                        <p id="registerError" class="spotziRed" style="display: none;"></p>
                                        <p><input id="registerUserName" type="text" name="<?php echo REQUEST_PARAMETER_USER_NAME; ?>" placeholder="<?php _e('User name'); ?>" required></p>
                                        <p><input id="registerUserEmail" type="email" name="<?php echo REQUEST_PARAMETER_USER_EMAIL; ?>" placeholder="<?php _e('Email address'); ?>" required></p>
                                        <p><input id="registerUserPassword" type="password" name="<?php echo REQUEST_PARAMETER_USER_PASSWORD; ?>" placeholder="<?php _e('Password'); ?>" required></p>
                                        <p class="note clear">
                                                <span><?php _e('Already have an account? <a href="javascript:void(0);" onclick="showLogin();">Click here</a> to log in.'); ?></span>
                                                <input id="doRegister" type="submit" value="<?php _e('Register'); ?>">
                                        </p>
                                        <div class="loading"><div class="fa-icon fa-spinner fa-pulse"></div></div>
                                </form>
                                <p class="note"><?php _e('By registering you are accepting the <a href="http://www.spotzi.com/en/terms-of-service/" target="_blank">terms and conditions</a> of the service and the <a href="http://www.spotzi.com/en/privacy/" target="_blank">privacy policy</a>.'); ?></p>
                        </div>
                </div>
<?php endif; ?>
        </div>
</div>
<?php
if ($loggedIn) require_once('edit.php');
?>
<script>
var communityMaps = <?php echo json_encode($model->visualization['communityMaps']); ?>;
var myMaps = <?php echo json_encode($model->visualization['myMaps']); ?>;

$(document).ready(function() {
        // Home screen
        $('#homeMap, #homeClose').click(hideHomeScreen);
        $('#homeButton').click(function() {
                showHomeScreen();
        });

        // Map tab
        $('#maps').on('click', '.mapBlock', function() {
                var value = $(this).data('value');
                if (value) {
                        if (value.hasOwnProperty('Url') && value['Url']) {
                                openVisualization(value);
                        } else {
                                parentMapCategory = activeMapCategory;
                                activeMapCategory = value;
                                updateMapTab();
                        }
                }
        });
        $('#mapMenu').on('click', 'a[data-maps]', showMapTab);
        $('#mapBack').click(function() {
                if (activeMapCategory) {
                        activeMapCategory = (activeMapCategory['IsSubCategory'] === '0' ? null : parentMapCategory);
                        parentMapCategory = null;
                        updateMapTab();
                }
        });
<?php if ($loggedIn): ?>

        // Content
        $('#importForm').submit(importStart);
        $('#createForm').submit(createData);
        $('#mapsLogin').on('click', '.delete', deleteData);
<?php endif; ?>
});

// Home screen
function showHomeScreen() {
        if (!$('#home').is(':visible')) {
                $('#menu').slideUp();
                $('.leaflet-container').find('> :not(.leaflet-map-pane)').css('visibility', 'hidden');

                $('#homeClose')[map ? 'show' : 'hide']();
                $('#home').slideDown();
        }

        updateScrolls($('#home'));
}

function hideHomeScreen() {
        $('#home').slideUp();

        $('#menu').slideDown();
        $('.leaflet-container').find('> :not(.leaflet-map-pane)').css('visibility', 'visible');
}

// Map tab
var activeMapTab, activeMapCategory, parentMapCategory;

function updateMapTab() {
        if (activeMapTab) {
                activeMapTab.empty().hide();

                switch (activeMapTab.attr('id')) {
                        case 'mapsCommunity':
                                updateMapTabContent(communityMaps);
                                break;
                        case 'mapsLogin':
                                checkLogin(function() {
<?php if ($loggedIn): ?>
                                        myMaps.sort(function(a, b) {
                                                var aName = a['Title'].toLowerCase();
                                                var bName = b['Title'].toLowerCase();
                                                return (aName < bName ? -1 : (aName > bName ? 1 : 0));
                                        });
                                        updateMapTabContent(myMaps, function() {
                                                var elemNew = $('<li class="tableView mapBlock" title="<?php _e('Create map'); ?>">\n\
                                                                        <div class="tableCellView">\n\
                                                                                <span class="fa-icon fa-plus-circle"></span>\n\
                                                                                <h6 class=descriptionBlock><?php _e('Create map'); ?></h6>\n\
                                                                        </div>\n\
                                                                </li>').click(function() {
                                                        openModal('createData');
                                                        $('#createName').focus();
                                                });
                                                var elemImport = $('<li class="tableView mapBlock" title="<?php _e('Import data'); ?>">\n\
                                                                        <div class="tableCellView">\n\
                                                                                <span class="fa-icon fa-cloud-upload"></span>\n\
                                                                                <h6 class=descriptionBlock><?php _e('Import data'); ?></h6>\n\
                                                                        </div>\n\
                                                                </li>').click(function() {
                                                        openModal('importData');
                                                        $('#importName').focus();
                                                });
                                                activeMapTab.prepend(elemNew, elemImport);
                                        });
<?php endif; ?>
                                }, true);
                                break;
                }

                setImageError('<?php echo VISUALIZATION_PLACEHOLDER; ?>');
                activeMapTab.fadeIn();
                updateScrolls($('#home'));
        }
}

function updateMapTabContent(objects, callback) {
        var fragment = $(document.createDocumentFragment());
        $.each(objects, function(index, value) {
                if ($.isPlainObject(value)) {
                        var title = value['Title'].capitalize();
                        var titleHTML = (value.hasOwnProperty('CategoryId') ? title : '<div class="title">' + title + '</div><div class="fa-icon fa-times delete"></div>');

                        if (!value.hasOwnProperty('IsSubCategory')) {
                                var vizId = value['Url'].split('/')[6];
                                value['ImageUrl'] = '<?php echo VISUALIZATION_IMAGE_URL; ?>' + vizId + '.jpg';
                        }
                        var active = (visualization.<?php echo REQUEST_PARAMETER_VIZ_URL; ?>  === value['Url'] ? ' active' : '');

                        fragment.append($('<li class="tableView mapBlock' + active + '" title="' + title + '">\n\
                                                <div class="tableCellView">\n\
                                                        <img src="' + value['ImageUrl'] + '" alt="" />\n\
                                                        <h6 class="titleBlock">' + titleHTML + '</h6>\n\
                                                </div>\n\
                                        </li>').data('value', value));
                }
        });
        activeMapTab.empty().append(fragment);

        if ($.isFunction(callback)) callback();
}

function showMapTab() {
        var elem = $(this);

        $('#mapMenu').find('a').removeClass('active');
        elem.addClass('active');
        $('#maps').find('> ul').hide();

        activeMapTab = $('#' + elem.data('maps')).show();
        activeMapCategory = null, parentMapCategory = null;
        updateMapTab();
}

function openVisualization(value, callback) {
        initializeVisualization(value['Url'], {}, function() {
                ($.isFunction(callback) ? callback() : hideHomeScreen());

                updateMapTab();
        });
}
<?php if ($loggedIn): ?>

// Content
function importStart() {
        $('#importSuccess, #importError').hide();

        $('#importFrame').unbind('load').load(importFinish);
        $(this).find('input[type=submit]').attr('disabled', true).end().find('.loading').show();
}

function importFinish() {
        var response = $(this).contents().find('body').html();
        var responseJSON = false;
        try {
                responseJSON = $.parseJSON(response);
        } catch (err) {}

        if (responseJSON && responseJSON.hasOwnProperty('<?php echo REQUEST_RESULT; ?>') &&
            responseJSON.<?php echo REQUEST_RESULT; ?> === true) {
                $('#importSuccess').show();
                $('#importForm').trigger('reset');

                $.localStorage.setItem('home', true);
        } else {
                $('#importError').html(responseJSON.hasOwnProperty('<?php echo REQUEST_ERROR; ?>') ? responseJSON.<?php echo REQUEST_ERROR; ?> : '<?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?>').show();

                logError(response);
        }

        $('#importForm').find('input[type=submit]').attr('disabled', false).end().find('.loading').hide();
}

function createData(e) {
        e.preventDefault();
        $('#createError').hide();

        var form = $(this);
        form.find('input[type=submit]').attr('disabled', true).end().find('.loading').show();

        loadUrl(form.attr('action'), {
                data: form.serializeObject()
        }, function(response) {
                openVisualization(response, function() {
                        myMaps.push(response);
                        $('#menuLogin').click();

                        closeModal('createData');
                        hideHomeScreen();
                });
        }, function(err) {
                $('#createError').show();

                logError(err);
        }, function() {
                form.find('input[type=submit]').attr('disabled', false).end().find('.loading').hide();
        });
}

function deleteData(e) {
        e.preventDefault();
        e.stopPropagation();

        if (confirm('<?php _e('Are you sure you want to delete this map permanently?\nThis cannot be undone.'); ?>')) {
                var value = $(this).closest('.mapBlock').data('value');
                var visualizationId = value['Id'];

                if (value.hasOwnProperty('Url')) {
                        loadUrl('<?php echo $this->getVisualizationDeleteUrl(); ?>', {
                                data: {
                                        <?php echo REQUEST_PARAMETER_VIZ_ID; ?>: visualizationId,
                                        <?php echo REQUEST_PARAMETER_VIZ_URL; ?>: value['Url']
                                }
                        }, function() {
                                if (visualization.<?php echo REQUEST_PARAMETER_VIZ_ID ?> === visualizationId)
                                        openVisualization(myMaps[0]);

                                removeBySubValue(myMaps, 'Id', visualizationId);
                                $('#menuLogin').click();
                        });
                }
        }
}
<?php endif; ?>
</script>
<div id="home">
        <div id="homeMap"></div>
        <div id="homeWindow">
                <div id="homeClose" class="fa-icon fa-times"></div>
                <div id="homeSub" class="scroll">
                        <div id="mapMenu" class="clear">
                                <div id="mainMenu">
                                        <a id="menuCommunity" data-maps="mapsCommunity"><?php _e('Community Maps'); ?></a>
                                        <a id="menuLogin" data-maps="mapsLogin"><?php _e('My Maps'); ?></a>
                                </div>
                        </div>
                        <div id="maps" class="scroll">
                                <ul id="mapsCommunity"></ul>
                                <ul id="mapsLogin"></ul>
                        </div>
                </div>
        </div>
        <div id="homeModal">
<?php if ($loggedIn): ?>
                <div id="importData" data-remodal-id="importData">
                        <button data-remodal-action="close" class="remodal-close"></button>
                        <h4><?php _e('Import data'); ?></h4>
                        <div id="importDataContent">
                                <p class="description"><?php _e('Please choose the file to import.'); ?></p>
                                <form id="importForm" method="post" enctype="multipart/form-data" action="<?php echo $this->getImportVisualizationUrl(); ?>" target="importFrame">
                                        <p id="importSuccess" class="spotziGreen" style="display: none;"><?php _e('The file was uploaded successfully and is being added to your account, you will receive another e-mail once this process is finished. Afterwards you will have to refresh this page in order to find the map in My Maps.'); ?></p>
                                        <p id="importError" class="spotziRed" style="display: none;"></p>
                                        <p><input type="text" id="importName" name="importName" placeholder="<?php _e('Map name'); ?>"></p>
                                        <p><input type="file" id="importFile" name="importFile" accept=".csv,.xls,.xlsx,.geojson,.zip,.kml,.gpx" required></p>
                                        <p class="note clear">
                                                <?php $size = String::formatBytes(VISUALIZATION_IMPORT_SIZE, 'gB'); ?>
                                                <span><?php _e('Limit: %s, file types: CSV, XLS(X), GeoJSON, ZIP, KML, GPX', ($size ? $size . 'GB' : 'none')); ?></span>
                                                <input type="submit" value="<?php _e('Import data'); ?>">
                                                <iframe id="importFrame" name="importFrame" style="display: none;"></iframe>
                                        </p>
                                        <div class="loading"><div class="fa-icon fa-spinner fa-pulse"></div></div>
                                </form>
                        </div>
                </div>
                <div id="createData" data-remodal-id="createData">
                        <button data-remodal-action="close" class="remodal-close"></button>
                        <h4><?php _e('Create map'); ?></h4>
                        <div id="createDataContent">
                                <p class="description"><?php _e('Please pick a name for your new map.'); ?></p>
                                <form id="createForm" method="post" enctype="multipart/form-data" action="<?php echo $this->getVisualizationCreateUrl(); ?>" target="createFrame">
                                        <p id="createError" class="spotziRed" style="display: none;"><?php _e('An error occured while creating the map'); ?></p>
                                        <p><input type="text" id="createName" name="createName" placeholder="<?php _e('Map name'); ?>"></p>
                                        <p class="note clear">
                                                <span><?php _e('Afterwards you can add data to the map'); ?></span>
                                                <input type="submit" value="<?php _e('Create map'); ?>">
                                        </p>
                                        <div class="loading"><div class="fa-icon fa-spinner fa-pulse"></div></div>
                                </form>
                        </div>
                </div>
<?php endif; ?>
        </div>
</div>
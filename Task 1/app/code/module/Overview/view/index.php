<?php
// User related variables
$loggedIn = Session::getData(REQUEST_PARAMETER_LOGGEDIN);
$user = Session::getData(REQUEST_PARAMETER_USER_NAME);

// Map/data parameters
$x = $this->getParam(REQUEST_PARAMETER_X);
$y = $this->getParam(REQUEST_PARAMETER_Y);
$xyPresent = (is_numeric($x) && is_numeric($y));
$zoom = $this->getParam(REQUEST_PARAMETER_ZOOM);
?>
<script>
var visualization, map, mapLayers, mapLayer, propositionLayer, updateScheduler;

$(document).ready(function() {
        $('.noSelect').attr('unselectable', 'on');
        $('input.select').click(function() {
                this.select();
        });

        // Content
        var options = {};
        var x = <?php if ($xyPresent): echo $x; else: ?>$.localStorage.getItem('<?php echo REQUEST_PARAMETER_X; ?>')<?php endif; ?>;
        var y = <?php if ($xyPresent): echo $y; else: ?>$.localStorage.getItem('<?php echo REQUEST_PARAMETER_Y; ?>')<?php endif; ?>;
        if ((x !== undefined && x !== null && !isNaN(x)) && (y !== undefined && y !== null && !isNaN(y)))
                options.center = [y, x];

        var zoom = <?php if (is_numeric($zoom)): echo $zoom; else: ?>$.localStorage.getItem('<?php echo REQUEST_PARAMETER_ZOOM; ?>')<?php endif; ?>;
        if (zoom !== undefined && zoom !== null && !isNaN(zoom))
                options.zoom = zoom;

        initializeVisualization('<?php echo $model->visualization['defaultVisualization']['Url']; ?>', options, function () {
                $('body').trigger('initializeMenu');
        });

        initializeScrolls();
});

// Visualization
function inspectVisualization(vizUrl, data, callback) {
        loadUrl('<?php echo $this->getVisualizationInspectURL(); ?>', {
                data: {
                        <?php echo REQUEST_PARAMETER_VIZ_URL; ?>: vizUrl
                }
        }, function(response) {
                visualization = response;
                visualization.<?php echo REQUEST_PARAMETER_VIZ_TITLE; ?> = data.title || '<?php echo BRAND_PRODUCT ?>';
                visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?> = (mapLayers.length - 1);
                var layer = mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>];
                visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_TYPE; ?> = layer.type;

                if (visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_TYPE; ?> === 'layergroup') {
                        var subLayers = layer.options.layer_definition.layers;

                        var mapLayerSet = false;
                        var subLayersLength = subLayers.length;
                        for (var i = (subLayersLength - 1); i >= 0; i--) {
                                var table = subLayers[i].options.sql.substr(subLayers[i].options.sql.indexOf('from') + 5);
                                if (table.indexOf(' ') !== -1) table = table.substr(0, table.indexOf(' '));

                                var isPropositionLayer = /_(propose)_\d{3,8}_?\d{3,6}$/.test(table);
                                if (!mapLayerSet && !isPropositionLayer) {
                                        var isEditorLayer = /_(point|line|polygon)_\d{3,8}_?\d{3,6}$/.test(table);
                                        if (!isEditorLayer || !mapLayer) {
                                                mapLayer = subLayers[i];
                                                mapLayer.index = i;
                                        }
                                        if (!isEditorLayer) mapLayerSet = true;
                                }

                                if (!propositionLayer && isPropositionLayer) {
                                        propositionLayer = subLayers[i];
                                        propositionLayer.index = i;
                                }

                                if (mapLayerSet && propositionLayer) break;
                        }
                } else {
                        mapLayer = layer;
                }

                visualization.<?php echo REQUEST_PARAMETER_VIZ_QUERY; ?> = mapLayer.options.sql;

                if ($.isFunction(callback)) callback();
        }, mapError);
}

function initializeVisualization(vizUrl, options, callback) {
        if (typeof cartodb !== 'undefined') {
                clearVisualization();

                cdb.vis.Loader.get(vizUrl, function(data) {
                        var vizData = $.extend({}, data);
                        cartodb.createVis('map', data, $.extend({
                                no_cdn: true, search: false, share: false
                        }, options)).done(function(viz, vizLayers) {
                                map = viz.getNativeMap();
                                mapLayers = vizLayers;

                                inspectVisualization(vizUrl, vizData, function() {
                                        $('.contentTitle').html(visualization.<?php echo REQUEST_PARAMETER_VIZ_TITLE; ?>);

                                        $('body').trigger('initializeVisualization');
                                        if ($.isFunction(callback)) callback();

                                        map.on('movestart', clearMapUpdate).on('moveend', scheduleMapUpdate);

                                        $.localStorage.clear();
                                });
                        }).error(mapError);
                });
        } else {
                mapError();
        }
}

function clearVisualization() {
        visualization = {}, mapLayers = null, mapLayer = null, propositionLayer = null;

        if (map) {
                map.remove();
                $('#map').empty();
        }

        $('body').trigger('clearVisualization');
}

function loadUrl(url, options, success, failure, always) {
        options = options || {};

        $.ajax($.extend(true, {
                type: 'POST',
                url: url,
                data: {
                        <?php echo REQUEST_PARAMETER_VIZ_URL; ?>: visualization.<?php echo REQUEST_PARAMETER_VIZ_URL; ?>
                }
        }, options)).done(function(xhr) {
                var responseJSON = false;
                try {
                        responseJSON = $.parseJSON(xhr);
                } catch (err) {}

                if (responseJSON && responseJSON.hasOwnProperty('<?php echo REQUEST_RESULT; ?>') &&
                    responseJSON.<?php echo REQUEST_RESULT; ?> !== false) {
                        if ($.isFunction(success)) success(responseJSON.<?php echo REQUEST_RESULT; ?>);
                } else if ($.isFunction(failure)) {
                        failure(xhr, responseJSON.hasOwnProperty('<?php echo REQUEST_ERROR; ?>') ? responseJSON.<?php echo REQUEST_ERROR; ?> : '');
                }
        }).fail(failure).always(always);
}

function scheduleMapUpdate(timeout) {
        clearMapUpdate();
        updateScheduler = setTimeout(mapUpdate, (timeout === false ? 0 : 1000));
}

function clearMapUpdate() {
        clearTimeout(updateScheduler);
}

function mapUpdate() {
        if (map && mapLayers[1]._url) {
                var mapTestUrl = mapLayers[1]._url.replace('{x}', '0').replace('{y}', '0').replace('{z}', '0');
                $.ajax({
                        type: 'GET',
                        url: mapTestUrl
                }).fail(function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.error.search('Invalid or nonexistent map configuration token') === 0)
                                mapReload(false);
                });
        }

        var center = getMapCenter();
        $.localStorage.setItem('<?php echo REQUEST_PARAMETER_X; ?>', center.lng);
        $.localStorage.setItem('<?php echo REQUEST_PARAMETER_Y; ?>', center.lat);
        $.localStorage.setItem('<?php echo REQUEST_PARAMETER_ZOOM; ?>', map.getZoom());
}

function mapReload(fullReload) {
        if (map) {
                if (fullReload) {
                        initializeVisualization(visualization.<?php echo REQUEST_PARAMETER_VIZ_URL; ?>, {
                                center: [map.getCenter().lat, map.getCenter().lng],
                                zoom: map.getZoom()
                        });
                } else {
                        if (visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_TYPE; ?> === 'torque') {
                                var css = mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].options.cartocss;
                                css = (/\/\*spotzi_timestmp:[0-9]*\*\/$/.test(css) ? css.replace(/\/\*spotzi_timestmp:[0-9]*\*\/$/, '/*spotzi_timestmp:' + Date.now() + '*/') : css + ' /*spotzi_timestmp:' + Date.now() + '*/');
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].setCartoCSS(css);
                        } else {
                                var layerCount = mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers.length;
                                for (var mapLayerIndex = 0; mapLayerIndex < layerCount; mapLayerIndex++) {
                                        var css = mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[mapLayerIndex].sub.getCartoCSS();
                                        css = (/\/\*spotzi_timestmp:[0-9]*\*\/$/.test(css) ? css.replace(/\/\*spotzi_timestmp:[0-9]*\*\/$/, '/*spotzi_timestmp:' + Date.now() + '*/') : css + ' /*spotzi_timestmp:' + Date.now() + '*/');
                                        mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[mapLayerIndex].sub.setCartoCSS(css);
                                }
                        }
                }
        }
}

function mapError(err) {
        logError(err);
}

function createMarker(lon, lat, imageUrl, options) {
        return new L.Marker(new L.LatLng(lat, lon), $.extend({
                icon: L.icon({
                        iconUrl: imageUrl || '/img/map/spotziMarker.png',
                        iconSize: [24, 36],
                        iconAnchor: [12, 36]
                })
        }, options));
}

function getMapCenter() {
        return map.getCenter().wrap();
}

function setMapView(lon, lat, zoom) {
        map.setView(new L.LatLng(lat, lon), zoom || visualization.<?php echo REQUEST_PARAMETER_ZOOM; ?>);
}

function sendMapViewMessage(lon, lat, zoom) {
        setMapView(lon, lat, zoom);
}

function getMapBounds() {
        // Retrieve map state data
        var bounds = map.getBounds();
        var boundsSW = bounds.getSouthWest().wrap();
        var boundsNE = bounds.getNorthEast().wrap();

        // Just load the entire horizon when needed, 360 = difference between -180 and 180
        if ((bounds.getEast() - bounds.getWest()) > 360) {
                boundsSW.lng = -180;
                boundsNE.lng = 180;
        }

        return L.latLngBounds(boundsSW, boundsNE);
}

function setMapBounds(bounds) {
        if (typeof bounds !== 'object')  {
                bounds = bounds.split(',');
                bounds = L.latLngBounds([[bounds[1], bounds[0]], [bounds[3], bounds[2]]]);
        }

        map.fitBounds(bounds, {
                maxZoom: <?php echo VISUALIZATION_ZOOM_MAX; ?>
        });
}

// General
function logError(err) {
<?php if (debugMode()): ?>
        console.log(err || '<?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?>');
<?php endif; ?>
}

function setTopLocation(url, home, modal) {
        if (home === true) $.localStorage.setItem('home', true);
        if (modal) $.localStorage.setItem('modal', modal);

        setLocation(url, true);
}

function reloadTopLocation(home, modal) {
        if (home === true) $.localStorage.setItem('home', true);
        if (modal) $.localStorage.setItem('modal', modal);

        window.location.reload();
}

function setImageError(url, elem) {
        url = url || '/img/spotzi/placeholder.png';
        (elem || $('.tableCellView')).find('img').andSelf().unbind().error(function() {
                $(this).attr('src', url);
        });
}

function initializeScrolls(elem, options) {
        (elem || $('body')).find('.scroll').each(function() {
                createScroll($(this), options);
        });
}

function createScroll(elem, options) {
        (elem || $('body')).niceScroll($.extend({
                cursorwidth: 7,
                cursorminheight: 24,
                autohidemode: false,
                horizrailenabled: true
        }, options));
}

function updateScrolls(elem) {
        (elem || $('body')).find('.scroll').each(function() {
                $(this).getNiceScroll().resize().doScrollPos(0, 0);
        });
}

function clearScrolls(elem) {
        elem = elem || $('body');
        elem.find('.scroll').each(function() {
                $(this).getNiceScroll().remove();
        });
        elem.find('div[id^="ascrail"]').remove();
}
</script>
<div id="map"></div>
<?php
require_once('menu.php');
require_once('home.php');
?>
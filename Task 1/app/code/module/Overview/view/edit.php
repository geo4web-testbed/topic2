<script>
$(document).ready(function() {
        $('#editMenu').find('li').click(function() {
                var title = $(this).attr('title');
                mapEditor.addFeature(title);
        });

        $('body').bind('initializeVisualization', function() {
                if (visualization.edit_enabled && mapLayer) {
                        mapEditor.init();
                        mapLayer.sub.on('featureClick', mapEditor.getLayerListener(mapLayer.index));
                }
        });

        $('#mapEdit').unbind('click').click(function() {
                if (visualization.edit_enabled && visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?>) {
                        checkLogin(function() {
                                var url = 'http://<?php echo $user['UserName']; ?>.<?php echo VISUALIZATION_DOMAIN; ?>/login/viz?email=<?php echo $user['UserName']; ?>&api_key=<?php echo $user['ApiKey']; ?>&viz=' + visualization.<?php echo REQUEST_PARAMETER_VIZ_ID; ?>;

                                setTopLocation(url);
                        });
                }
        });

        $('#editMsg').click(function() {
                $(this).hide();
        });

        $('.menuButton').click(function() {
                if ($(this).attr('id') !== 'menuMapEdit') {
                        $('#editInfoContainer').hide();
                        mapEditor.stopEditFeature();
                }
        });
});

$('#createForm').submit(function() {
        $('#menu').on('custom', function() {
                $('#menu').off('custom');
                $('#menuMapEdit').trigger('click');
        });
});



var editFeature = (function() {
        var feature = null;
        function getFeature() {
                return feature;
        }

        var clicked_point = null;
        function getClickedPoint() {
                return clicked_point;
        }

        var feature_id = '';
        function getFeatureId() {
                switch(getAction()) {
                        case '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>':
                        case '':
                                return '';
                        case '<?php echo EDITOR_ACTION_EDIT_DATA; ?>':
                        case '<?php echo EDITOR_ACTION_EDIT_GEOM; ?>':
                        case '<?php echo EDITOR_ACTION_DELETE; ?>':
                                if (feature_id) {
                                        return feature_id;
                                } else {
                                        return '';
                                }
                }
        }

        var layer = null;
        function getLayer() {
                switch(getAction()) {
                        case '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>':
                        case '':
                                return '';
                        case '<?php echo EDITOR_ACTION_EDIT_DATA; ?>':
                        case '<?php echo EDITOR_ACTION_EDIT_GEOM; ?>':
                        case '<?php echo EDITOR_ACTION_DELETE; ?>':
                                if (layer) {
                                        return layer;
                                } else {
                                        return '';
                                }
                }
        }

        var geom_type = '';
        var geom_types = ['<?php echo EDITOR_POINT; ?>', '<?php echo EDITOR_LINE; ?>', '<?php echo EDITOR_POLYGON; ?>'];
        function getGeomType() {
                if ($.inArray(geom_type, geom_types) !== -1) {
                        return geom_type;
                } else {
                        return '';
                }
        }

        function setGeomType(newGeomType) {
                if ($.inArray(newGeomType, geom_types) !== -1) {
                        geom_type = newGeomType;
                        return true;
                } else {
                        return false;
                }
        }

        function getTheGeom() {
                if (feature && getGeomType()) {
                        switch (getGeomType()) {
                                case '<?php echo EDITOR_POINT; ?>':
                                        return feature.getLatLng();
                                case '<?php echo EDITOR_LINE; ?>':
                                case '<?php echo EDITOR_POLYGON; ?>':
                                        return feature.getLatLngs();
                        }
                }

                return null;
        }

        var style_options = null;
        function getStyleOptions() {
                return style_options;
        }

        var style_css = '';
        function getStyleCSS() {
                return style_css;
        }

        var info_fields = {name: '', imageurl: '', description: ''};
        function getInfoFields() {
                return info_fields;
        }

        var action = '';
        var permittedActions = ['<?php echo EDITOR_ACTION_NEW_FEATURE; ?>', '<?php echo EDITOR_ACTION_EDIT_DATA; ?>', '<?php echo EDITOR_ACTION_EDIT_GEOM; ?>', '<?php echo EDITOR_ACTION_DELETE; ?>'];
        function getAction() {
                if ($.inArray(action, permittedActions) !== -1) {
                        return action;
                } else {
                        return '';
                }
        }

        function setAction(newAction) {
                if ($.inArray(newAction, permittedActions) !== -1) {
                        action = newAction;
                        return true;
                }

                return false;
        }

        function checkAction() {
                if (getAction()) {
                        switch (action) {
                                case '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>':
                                        return (getFeature() && getGeomType() && getTheGeom() && getStyleOptions() && getInfoFields());
                                case '<?php echo EDITOR_ACTION_EDIT_DATA; ?>':
                                        return (getGeomType() && getStyleOptions() && getInfoFields() && getFeatureId() && getLayer());
                                case '<?php echo EDITOR_ACTION_EDIT_GEOM; ?>':
                                        return (getFeature() && getGeomType() && getTheGeom() && getFeatureId() && getLayer());
                                case '<?php echo EDITOR_ACTION_DELETE; ?>':
                                        return (getFeatureId() && getLayer());
                        }
                }

                return false;
        }

        function startAction(newAction, actionData) {
                var args = $.extend(true, actionData, {});
                if (setAction(newAction)) {
                        if (args.clicked_point) {
                                clicked_point = new L.CircleMarker(args.clicked_point);
                                if (args.geom_type === 'POINT' && args.the_geom)
                                        clicked_point = new L.CircleMarker(args.the_geom[0]);
                        } else {
                                return false;
                        }

                        switch (getAction()) {
                                case '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>':
                                case '<?php echo EDITOR_ACTION_EDIT_GEOM; ?>':
                                        if (args.geom_type && setGeomType(args.geom_type) && args.the_geom) {
                                                if (args.the_geom.length > 999) return true;
                                                switch (args.geom_type) {
                                                        case '<?php echo EDITOR_POINT; ?>':
                                                                makePoint(args.the_geom[0]);
                                                                if (getAction() === '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>') style_options = { marker_file: 'http://images.spotzi.com/mapbuilder/editor/icons/pin.svg', color: '#000000' };
                                                                break;
                                                        case '<?php echo EDITOR_LINE; ?>':
                                                                makeLine(args.the_geom);
                                                                if (getAction() === '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>') style_options = { color: '#000000' };
                                                                break;
                                                        case '<?php echo EDITOR_POLYGON; ?>':
                                                                makePolygon(args.the_geom);
                                                                if (getAction() === '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>') style_options = { color: '#000000' };
                                                                break;
                                                        default:
                                                                return false;
                                                }
                                                if (getAction() === '<?php echo EDITOR_ACTION_EDIT_GEOM; ?>') {
                                                        if (args.feature_id && args.layer) {
                                                                feature_id = args.feature_id;
                                                                layer = args.layer;
                                                        } else {
                                                                return false;
                                                        }
                                                }
                                        } else {
                                                return false;
                                        }
                                        break;
                                case '<?php echo EDITOR_ACTION_EDIT_DATA; ?>':
                                        if (args.geom_type && setGeomType(args.geom_type) && args.info_fields && args.feature_id && args.layer && args.style_options) {
                                                style_css = args.style_css ? args.style_css : '';
                                                info_fields = args.info_fields;
                                                feature_id = args.feature_id;
                                                layer = args.layer;
                                                style_options = args.style_options;
                                                if (style_options.description && style_options.description === ' ') style_options.description = '';
                                        } else {
                                                return false;
                                        }
                                        break;
                                case '<?php echo EDITOR_ACTION_DELETE; ?>':
                                        if (args.feature_id && args.layer) {
                                                feature_id = args.feature_id;
                                                layer = args.layer;
                                        } else {
                                                return false;
                                        }
                                        break;
                        }
                } else {
                        return false;
                }

                return checkAction();
        }

        function endAction() {
                if (getAction() === '<?php echo EDITOR_ACTION_EDIT_DATA; ?>') {
                        var regex = new RegExp('#\\w+?\\[\\s*cartodb_id\\s*=\\s*' + getFeatureId() + '\\s*\\].+?\\}');
                        getLayer().sub.setCartoCSS(getLayer().sub.getCartoCSS().replace(/\n/g, '/*line-brake*/').replace(regex, style_css).replace(/\/\*line-brake\*\//g, '\n'));
                }
                cancelFeature();
        }

        function makePoint(latlng) {
                feature = new L.CircleMarker(latlng, {
                        radius: 4,
                        color: '#FF0000',
                        fillColor: '#FFFFFF',
                        fillOpacity: 1,
                        pointerEvents: 'mousedown'
                }).addTo(map);
                clicked_point = feature;
                feature.on('click', function() {
                        mapEditor.showDataForm();
                        map.off('click');
                });
                feature.on('mousedown', function() {
                        map.on('mousemove', function(moveEvent) {
                                if (moveEvent.originalEvent.buttons !== 0) {
                                        feature.setLatLng([moveEvent.latlng.lat, moveEvent.latlng.lng]);
                                } else {
                                        map.off('mousemove');
                                        mapEditor.showDataForm();
                                }
                        });
                });
        }

        function makeLine(latlngs) {
                feature = new L.Polyline(latlngs).addTo(map);
                makeFeaturePoints();
        }

        function makePolygon(latlngs) {
                feature = new L.Polygon(latlngs).addTo(map);
                makeFeaturePoints();
        }

        function makeFeaturePoints() {
                if (feature && ($.inArray(geom_type, ['MULTILINESTRING', 'MULTIPOLYGON']) !== -1)) {
                        var featurePoints = feature.getLatLngs();
                        var featureCount = featurePoints.length;

                        feature.points = [];
                        for (var pointIndex = 0; pointIndex < featureCount; pointIndex++) {
                                feature.points.push(getFeaturePoint(featurePoints[pointIndex]));
                        }
                }
        }

        function addLatLng(latlng) {
                if (feature && ($.inArray(geom_type, ['MULTILINESTRING', 'MULTIPOLYGON']) !== -1)) {
                        feature.addLatLng(latlng);
                        feature.points.push(getFeaturePoint(latlng));
                }
        }

        function getFeaturePoint(latlng) {
                var featurePoint = new L.CircleMarker(latlng, { radius: 4, color: '#FF0000', fillColor: '#FFFFFF', fillOpacity: 1 }).addTo(map);
                featurePoint.bringToFront();
                featurePoint.on('click', function() {
                        clicked_point = featurePoint;
                        featureClickHandler();
                });
                featurePoint.on('mousedown', function() {
                        setTimeout(function(){
                                map.on('mousemove', function(moveEvent) {
                                        if (feature) {
                                                if (moveEvent.originalEvent.buttons !== 0) {
                                                        featurePoint.off('click');
                                                        map.off('click');
                                                        featurePoint.setLatLng([moveEvent.latlng.lat, moveEvent.latlng.lng]);

                                                        var newLatLngs = [];
                                                        var featureCount = feature.points.length;
                                                        for (var featureIndex = 0; featureIndex < featureCount; featureIndex++) {
                                                                newLatLngs.push(feature.points[featureIndex].getLatLng());
                                                        }
                                                        feature.setLatLngs(newLatLngs);
                                                } else {
                                                        map.off('mousemove');
                                                        if (getAction() === '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>') {
                                                                map.on('click', function(e) {
                                                                        addLatLng(e.latlng);
                                                                });
                                                        }
                                                        featurePoint.on('click', function() {
                                                                clicked_point = featurePoint;
                                                                featureClickHandler();
                                                        });
                                                }
                                        } else {
                                                map.off('mousemove');
                                        }
                                });
                        }, 150);
                });

                return featurePoint;
        }

        function featureClickHandler() {
                var minimum = 2;
                if (getGeomType() === '<?php echo EDITOR_POLYGON; ?>') minimum = 3;
                if (editFeature.get_the_geom().length >= minimum) {
                        hideFeaturePoints();
                        mapEditor.showDataForm();
                        map.off('click');
                } else {
                        $('#editMsg').empty().append('<?php _e('Place another point on the map to finish the feature'); ?>').show();
                }
        }

        function showFeaturePoints() {
                if (feature && feature.points) {
                        var featureCount = feature.points.length;
                        for (var i = 0; i < featureCount; i++) {
                                $(feature.points[i]._container).show();
                        }
                }
        }

        function hideFeaturePoints() {
                if (feature && feature.points) {
                        var featureCount = feature.points.length;
                        for (var i = 0; i < featureCount; i++) {
                                $(feature.points[i]._container).hide();
                        }
                }
        }

        function cancelFeature() {
                if (feature) {
                        hideFeaturePoints();
                        $(feature._container).hide();
                }
                feature = null;
                clicked_point = null;
                geom_type = '';
                style_options = null;
                info_fields = { name: '', imageurl: '', description: '' };
                action = '';
                feature_id = '';
                layer = null;
        }

        function fillInputForm(form) {
                if (checkAction()) {
                        switch (getAction()) {
                                case '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>':
                                case '<?php echo EDITOR_ACTION_EDIT_DATA; ?>':
                                        var imageInput = '<input id="featureImage" name="imageurl" type="file" placeholder="<?php _e('Image'); ?>" accept="image/*">';
                                        if (getInfoFields().imageurl) imageInput = '<input id="featureImage" class="userText" name="imageurl" type="text" placeholder="<?php _e('Image'); ?>" value="' + getInfoFields().imageurl + '">';
                                        form.append('<div class="editDatafield">\n' +
                                                                '<input required id="featureName" class="userText" name="name" type="text" placeholder="<?php _e('Name'); ?>" value="' + getInfoFields().name + '">\n' +
                                                        '</div>\n' +
                                                        '<div class="editDatafield">\n' +
                                                                '<textarea id="featureDescription" class="userText" name="description" placeholder="<?php _e('Description'); ?>" cols="40" rows="4">' + getInfoFields().description + '</textarea>\n' +
                                                        '</div>\n' +
                                                        '<div class="editDatafield">\n' +
                                                                imageInput + '\n' +
                                                        '</div>\n' +
                                                        '<div id="styleOptions" class="editDatafield">\n' +
                                                                getStyleForm() + '\n' +
                                                        '</div>\n');
                                        $('#featureName').on('input', function() {
                                                var name = $(this)[0].value;
                                                info_fields.name = name;
                                        });

                                        $('#featureDescription').on('input', function() {
                                                var description = $(this)[0].value;
                                                info_fields.description = description;
                                        });

                                        $('#featureImage').on('input', function() {
                                                info_fields.imageurl = ($(this)[0].files ? $(this)[0].files[0] : $(this)[0].value);
                                                if (getInfoFields().imageurl === '') {
                                                        $('<input id="featureImage" name="imageurl" type="file" placeholder="<?php _e('Image'); ?>" accept="image/*">').insertBefore(this);
                                                        $(this).remove();
                                                }
                                        });

                                        enableStyleForm();

                                        switch (getGeomType()) {
                                                case '<?php echo EDITOR_POINT; ?>':
                                                        if (getStyleOptions().marker_file !== '' && getStyleOptions().color !== '') {
                                                                enableStyleForm();
                                                                $('#markerSelector').find('img[src$="' + getStyleOptions().marker_file + '"]').parent().addClass('selected');
                                                                $('#featureColorPicker').find('li[style*="background-color:' + getStyleOptions().color + ';"]').addClass('selected');
                                                        } else {
                                                                disableStyleForm();
                                                        }
                                                        break;
                                                case '<?php echo EDITOR_LINE; ?>':
                                                case '<?php echo EDITOR_POLYGON; ?>':
                                                        if (getStyleOptions().color !== '') {
                                                                enableStyleForm();
                                                                $('#featureColorPicker').find('li[style*="background-color:' + getStyleOptions().color + ';"]').addClass('selected');
                                                        } else {
                                                                disableStyleForm();
                                                        }
                                                        break;
                                        }
                                break;
                                case '<?php echo EDITOR_ACTION_EDIT_GEOM; ?>':
                                        form.append('<div>\n' +
                                                        '<p><?php _e('Save edits?'); ?></p>\n' +
                                                    '</div>');
                                break;
                                case '<?php echo EDITOR_ACTION_DELETE; ?>':
                                        form.append('<div>\n' +
                                                        '<p><?php _e('Are you sure you want to delete this feature?'); ?></p>\n' +
                                                    '</div>');
                                break;
                        }

                        var submitText = '<?php _e('Save'); ?>';
                        if (getAction() === '<?php echo EDITOR_ACTION_DELETE; ?>') submitText = '<?php _e('Delete'); ?>';

                        form.append('<input type="button" id="cancelFeature" value="<?php _e('Cancel'); ?>">\n' +
                                    '<input type="submit" id="saveFeature" value="' + submitText + '">\n');
                } else {
                        form.append('<div><?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?></div>' +
                                    '<input type="button" id="cancelFeature" value="<?php _e('Ok'); ?>">');
                }

                $('#cancelFeature').click(function() {
                        mapEditor.stopEditFeature();
                });
        }

        function getStyleForm() {
                var styleForm = '<div><h5><?php _e('Styling'); ?></h5></div>';
                if (getGeomType() !== '') {
                        if (getGeomType() === '<?php echo EDITOR_POINT; ?>') {
                                styleForm += '<div class="styleOption">\n' +
                                                        '<ul id="markerSelector">\n' +
                                                                '<li class="markerOption"><img src="http://images.spotzi.com/mapbuilder/editor/icons/pin.svg"></li>\n' +
                                                                '<li class="markerOption"><img src="http://images.spotzi.com/mapbuilder/editor/icons/home.svg"></li>\n' +
                                                                '<li class="markerOption"><img src="http://images.spotzi.com/mapbuilder/editor/icons/restaurant.svg"></li>\n' +
                                                                '<li class="markerOption"><img src="http://images.spotzi.com/mapbuilder/editor/icons/shop.svg"></li>\n' +
                                                                '<li class="markerOption"><img src="http://images.spotzi.com/mapbuilder/editor/icons/fuel.svg"></li>\n' +
                                                        '</ul>\n' +
                                                '</div>\n';
                        }
                        styleForm += '<div class="styleOption">\n' +
                                                '<ul id="featureColorPicker">\n' +
                                                        '<li class="colorOption" style="background-color:#000000;"></li>\n' +
                                                        '<li class="colorOption" style="background-color:#FFFFFF;"></li>\n' +
                                                        '<li class="colorOption" style="background-color:#FF00FF;"></li>\n' +
                                                        '<li class="colorOption" style="background-color:#FF0000;"></li>\n' +
                                                        '<li class="colorOption" style="background-color:#FFFF00;"></li>\n' +
                                                        '<li class="colorOption" style="background-color:#00FF00;"></li>\n' +
                                                        '<li class="colorOption" style="background-color:#00FFFF;"></li>\n' +
                                                        '<li class="colorOption" style="background-color:#0000FF;"></li>\n' +
                                                '</ul>\n' +
                                        '</div>';
                }

                return styleForm;
        }

        function enableStyleForm() {
                $('#styleOptions').empty().append(getStyleForm());
                placeDataContainer();
                if (getGeomType() === '<?php echo EDITOR_POINT; ?>') {
                        $('.markerOption').off('click').click(function() {
                                $('#markerSelector').find('.selected').removeClass('selected');
                                $(this).addClass('selected');
                                var newUrl = $(this).find('img')[0].src;
                                style_options.marker_file = newUrl;
                                if (getAction() === '<?php echo EDITOR_ACTION_EDIT_DATA; ?>') {
                                        var layerCSS = getLayer().sub.getCartoCSS().replace(/\n/g, '/*line-brake*/');
                                        var regex = new RegExp('\\[\\s*cartodb_id\\s*=\\s*'+getFeatureId()+'\\s*\\].+?\\}');
                                        if (regex.test(layerCSS)) {
                                                var newCSS = layerCSS.match(regex)[0].replace(/marker-file\s*:.+?;/, 'marker-file: url(' + newUrl + ');');
                                                layerCSS = layerCSS.replace(regex, newCSS).replace(/\/\*line-brake\*\//g, '\n');
                                        } else {
                                                var color = (getStyleOptions().color ? getStyleOptions().color : '#000000');
                                                var layerName = getLayer().options.layer_name.replace(/ /g, '_');
                                                var newCSS = '\n\n#' + layerName + '[cartodb_id=' + getFeatureId() + '] {\n    marker-file: url(' + newUrl + ');\n    marker-fill: ' + color + ';\n    marker-width: 20;\n}';
                                                layerCSS += newCSS;
                                        }
                                        getLayer().sub.setCartoCSS(layerCSS);
                                }
                        });
                }

                $('.colorOption').off('click').click(function() {
                        $('#featureColorPicker').find('.selected').removeClass('selected');
                        $(this).addClass('selected');
                        var newColor = $(this)[0].outerHTML.match(/background-color:.+?;/)[0].split(':')[1].replace(';', '');
                        style_options.color = newColor;
                        if (getAction() === '<?php echo EDITOR_ACTION_EDIT_DATA; ?>') {
                                var layerCSS = getLayer().sub.getCartoCSS().replace(/\n/g, '/*line-brake*/');
                                var regex = new RegExp('\\[\\s*cartodb_id\\s*=\\s*' + getFeatureId() + '\\s*\\].+?\\}');
                                var newCSS = '';
                                switch (getGeomType()) {
                                        case '<?php echo EDITOR_POINT; ?>':
                                                if (regex.test(layerCSS)) {
                                                        newCSS = layerCSS.match(regex)[0].replace(/marker-fill\s*:.+?;/, 'marker-fill: ' + newColor + ';');
                                                        layerCSS = layerCSS.replace(regex, newCSS).replace(/\/\*line-brake\*\//g, '\n');
                                                } else {
                                                        var url = (getStyleOptions().marker_file ? getStyleOptions().marker_file : 'http://images.spotzi.com/mapbuilder/editor/icons/pin.svg');
                                                        var layerName = getLayer().options.layer_name.replace(/ /g, '_');
                                                        newCSS = '\n\n#' + layerName + '[cartodb_id=' + getFeatureId() + '] {\n    marker-file: url(' + url + ');\n    marker-fill: ' + newColor + ';\n    marker-width: 20;\n}';
                                                        layerCSS += newCSS;
                                                }
                                                break;
                                        case '<?php echo EDITOR_LINE; ?>':
                                                if (regex.test(layerCSS)) {
                                                        newCSS =  layerCSS.match(regex)[0].replace(/line-color\s*:.+?;/, 'line-color: ' + newColor + ';');
                                                        layerCSS = layerCSS.replace(regex, newCSS).replace(/\/\*line-brake\*\//g, '\n');
                                                } else {
                                                        var layerName = getLayer().options.layer_name.replace(/ /g, '_');
                                                        newCSS = '\n\n#' + layerName + '[cartodb_id=' + getFeatureId() + '] {\n    line-color: ' + newColor + ';\n}';
                                                        layerCSS += newCSS;
                                                }
                                                break;
                                        case '<?php echo EDITOR_POLYGON; ?>':
                                                if (regex.test(layerCSS)) {
                                                        newCSS = layerCSS.match(regex)[0].replace(/polygon-fill\s*:.+?;/, 'polygon-fill: ' + newColor + ';');
                                                        layerCSS = layerCSS.replace(regex, newCSS).replace(/\/\*line-brake\*\//g, '\n');
                                                } else {
                                                        var layerName = getLayer().options.layer_name.replace(/ /g, '_');
                                                        newCSS = '\n\n#' + layerName + '[cartodb_id=' + getFeatureId() + '] {\n    polygon-fill: ' + newColor + ';\n}';
                                                        layerCSS += newCSS;
                                                }
                                                break;
                                }
                                if (newCSS !== '') getLayer().sub.setCartoCSS(layerCSS);
                        }
                });
        }

        function disableStyleForm() {
                $('#styleOptions').empty().append('<div id="styleWarning">\n' +
                                                                '<span class="fa-icon fa-exclamation-triangle"><?php _e('Click here to use a simple style'); ?></span>' +
                                                        '</div>');
                $('#styleWarning').click(enableStyleForm);
        }

        function saveFeatureAction(form) {
                if (checkAction()) {
                        var hiddenFormFields =  '<input hidden name="<?php echo REQUEST_PARAMETER_VIZ_URL; ?>" value="' + visualization.<?php echo REQUEST_PARAMETER_VIZ_URL; ?> + '">\n';
                        switch (getAction()) {
                                case '<?php echo EDITOR_ACTION_NEW_FEATURE; ?>':
                                        var geometryJSON = '';
                                        switch (getGeomType()) {
                                                case '<?php echo EDITOR_POINT; ?>':
                                                case '<?php echo EDITOR_LINE; ?>':
                                                        geometryJSON = JSON.stringify(getTheGeom());
                                                        break;
                                                case '<?php echo EDITOR_POLYGON; ?>':
                                                        var geometry = getTheGeom();
                                                        geometry.push(geometry[0]);
                                                        geometryJSON = JSON.stringify(geometry);
                                                        break;
                                        }

                                        var styleJSON = JSON.stringify(getStyleOptions());

                                        hiddenFormFields += '<textarea hidden name="the_geom">' + geometryJSON + '</textarea>\n' +
                                                            '<textarea hidden id="featureStyle" name="featureStyle">' + styleJSON + '</textarea>\n' +
                                                            '<input hidden name="geom_type" value="' + getGeomType() + '">\n' +
                                                            '<input hidden name="featureAction" value="' + getAction() + '">\n';
                                        break;
                                case '<?php echo EDITOR_ACTION_EDIT_DATA; ?>':
                                        var styleJSON = JSON.stringify(getStyleOptions());

                                        hiddenFormFields += '<textarea hidden name="layerId">' + getLayer().id + '</textarea>\n' +
                                                            '<textarea hidden name="featureId">' + getFeatureId() + '</textarea>\n' +
                                                            '<textarea hidden id="featureStyle" name="featureStyle">' + styleJSON + '</textarea>\n' +
                                                            '<input hidden name="geom_type" value="' + getGeomType() + '">\n' +
                                                            '<input hidden name="featureAction" value="' + getAction() + '">\n';
                                        break;
                                case '<?php echo EDITOR_ACTION_EDIT_GEOM; ?>':
                                        var geometryJSON = '';
                                        switch (getGeomType()) {
                                                case '<?php echo EDITOR_POINT; ?>':
                                                case '<?php echo EDITOR_LINE; ?>':
                                                        geometryJSON = JSON.stringify(getTheGeom());
                                                        break;
                                                case '<?php echo EDITOR_POLYGON; ?>':
                                                        var geometry = getTheGeom();
                                                        geometry.push(geometry[0]);
                                                        geometryJSON = JSON.stringify(geometry);
                                                        break;
                                        }

                                        hiddenFormFields += '<textarea hidden name="layerId">' + getLayer().id + '</textarea>\n' +
                                                            '<textarea hidden name="featureId">' + getFeatureId() + '</textarea>\n' +
                                                            '<textarea hidden name="the_geom">' + geometryJSON + '</textarea>\n' +
                                                            '<input hidden name="geom_type" value="' + getGeomType() + '">\n' +
                                                            '<input hidden name="featureAction" value="' + getAction() + '">\n';
                                        break;
                                case '<?php echo EDITOR_ACTION_DELETE; ?>':
                                        hiddenFormFields += '<textarea hidden name="layerId">' + getLayer().id + '</textarea>\n' +
                                                            '<textarea hidden name="featureId">' + getFeatureId() + '</textarea>\n' +
                                                            '<input hidden name="featureAction" value="' + getAction() + '">\n';
                                        break;
                        }

                        form.append(hiddenFormFields);
                }
        }

        return {
                get_feature: getFeature,
                get_clicked_point: getClickedPoint,
                get_feature_id: getFeatureId,
                get_layer: getLayer,
                get_geom_type: getGeomType,
                get_the_geom: getTheGeom,
                add_point: addLatLng,
                get_style_options: getStyleOptions,
                get_info_fields: getInfoFields,
                get_action: getAction,
                new_action: startAction,
                save_action: saveFeatureAction,
                cancel_action: endAction,
                reset: cancelFeature,
                show_input: fillInputForm
        };
})();

var mapEditor = (function() {
        var advancedEditor = '';

        function initThis() {
                if (visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?>)
                        advancedEditor = 'http://<?php echo $user['UserName']; ?>.<?php echo VISUALIZATION_DOMAIN; ?>/login/viz?email=<?php echo $user['UserName']; ?>&api_key=<?php echo $user['ApiKey']; ?>&viz=' + visualization.<?php echo REQUEST_PARAMETER_VIZ_ID; ?>;

                resetInfowindowFeature();
        }

        function drawFeature(featureType) {
                editFeature.cancel_action();
                startEditing();
                switch (featureType) {
                        case '<?php _e('Add point'); ?>':
                                drawPoint();
                                break;
                        case '<?php _e('Add line'); ?>':
                                drawLine();
                                break;
                        case '<?php _e('Add polygon'); ?>':
                                drawPolygon();
                                break;
                }
        }

        function drawPoint() {
                $('#editMsg').empty().append('<?php _e('Click on the map to create a point'); ?>').show();
                map.off('click').on('click', function(e) {
                        editFeature.new_action('<?php echo EDITOR_ACTION_NEW_FEATURE; ?>', {
                                geom_type: '<?php echo EDITOR_POINT; ?>',
                                the_geom: [{ lng: e.latlng.lng, lat: e.latlng.lat }],
                                clicked_point: { lng: e.latlng.lng, lat: e.latlng.lat }
                        });
                        map.off('click');
                        showDataInput();
                });
        }

        function drawLine() {
                $('#editMsg').empty().append('<?php _e('Click on the map to start creating a line'); ?>').show();
                map.off('click').on('click', function(e) {
                        if (editFeature.get_feature()) {
                                editFeature.add_point(e.latlng);
                                var lineHint = '<?php _e('Click on a connection point to finish your line'); ?>';
                                if (editFeature.get_the_geom().length > 1 && $('#editMsg').text() !== lineHint) $('#editMsg').empty().append(lineHint).show();
                        } else {
                                $('#editMsg').empty().append('<?php _e('Place another point on the map to append the line'); ?>').show();
                                editFeature.new_action('<?php echo EDITOR_ACTION_NEW_FEATURE; ?>', {
                                        geom_type: 'MULTILINESTRING',
                                        the_geom: [{ lng: e.latlng.lng, lat: e.latlng.lat }],
                                        clicked_point: { lng: e.latlng.lng, lat: e.latlng.lat }
                                });
                        }
                });
        }

        function drawPolygon() {
                $('#editMsg').empty().append('<?php _e('Click on the map to start creating a polygon'); ?>').show();
                map.off('click').on('click', function(e) {
                        if (editFeature.get_feature()) {
                                editFeature.add_point(e.latlng);
                                var polyHint = '<?php _e('Click on a connection point to finish your polygon'); ?>';
                                if (editFeature.get_the_geom().length > 2 && $('#editMsg').text() !== polyHint) $('#editMsg').empty().append('<p>'+polyHint+'</p>').show();
                        } else {
                                $('#editMsg').empty().append('<?php _e('Place another point on the map to append the polygon'); ?>').show();
                                editFeature.new_action('<?php echo EDITOR_ACTION_NEW_FEATURE; ?>', {
                                        geom_type: 'MULTIPOLYGON',
                                        the_geom: [{ lng: e.latlng.lng, lat: e.latlng.lat }],
                                        clicked_point: { lng: e.latlng.lng, lat: e.latlng.lat }
                                });
                        }
                });
        }

        function startEditing(hideLayer){
                if (mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].hideDataLayer && hideLayer) mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].hideDataLayer();

                if (visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_TYPE; ?> === 'layergroup') {
                        var layerCount = mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers.length;
                        for (var i = 0; i < layerCount; i++) {
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub.setInteraction(false);
                        }
                }
        }

        function stopEditing() {
                map.off('click');
                if (mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].showDataLayer) mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].showDataLayer();

                if (visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_TYPE; ?> === 'layergroup') {
                        var layerCount = mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].interactionEnabled.length;
                        for (var i = 0; i < layerCount; i++) {
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub.setInteraction(true);
                        }
                }
        }

        function cancelFeature() {
                map.off('click');
                editFeature.cancel_action();
                $('#editDataContainer, #editMsg').hide();
                stopEditing();
        }

        var infowindowFeature = {};
        function resetInfowindowFeature() {
                infowindowFeature = { feature_id: null, layer: null, info_fields: { name: '', description: '', imageurl: '' }, geom_type: '', the_geom: null, style_options: null, style_css: '', clicked_point: null };
        }

        function createLayerListener(i) {
                if (mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i] && mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub && mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub.infowindow) {
                        var interactivityFields = ['cartodb_id'];

                        var attributeCount = 0;
                        if (mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub.infowindow.attributes.fields) attributeCount = mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub.infowindow.attributes.fields.length;
                        for (var fieldNum = 0; fieldNum < attributeCount; fieldNum++) {
                                interactivityFields.push(mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub.infowindow.attributes.fields[fieldNum].name);
                        }
                        mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub.setInteraction(interactivityFields);
                        mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].sub.setInteractivity(interactivityFields);

                        return function(e, latlng, pos, data) {
                                resetInfowindowFeature();
                                infowindowFeature.info_fields.name = (data.name ? data.name : '');
                                infowindowFeature.info_fields.description = (data.description ? data.description : '');
                                infowindowFeature.info_fields.imageurl = (data.imageurl ? data.imageurl : '');
                                infowindowFeature.feature_id = data.cartodb_id;
                                infowindowFeature.layer = mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i];
                                infowindowFeature.clicked_point = latlng;
                                if (mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow && mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].layers[i].infowindow.fields.length > 0) {
                                        $('#editInfoContainer').hide();
                                        map.off('move', 'pizza');
                                        mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow.bind('change:template', mapEditor.enableEdit, mapEditor);
                                } else {
                                        if (mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow) mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow.closeInfowindow();
                                        var info_text = (visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?> ? '<?php _e('You haven\\\'t selected any infowindow fields. Please visit the <a href="%the_url%">advanced editor</a> to set them.'); ?>'.replace('%the_url%', advancedEditor) : '<?php _e('There are no available infowindow fields.'); ?>');
                                        var infotemplate = '<div class="cartodb-popup header" data-cover="false">' +
                                                                '<span class="editInfoContainer-close-button"></span>' +
                                                                '<div class="spotzi-popup-header">' + info_text + '</div>' +
                                                                '<div id="infowindowEditButtons">\n' +
                                                                        '<div id="editFeatureData" class="editButton disabled" data-toggle="tooltip" title="<?php _e('Edit data'); ?>"></div>\n' +
                                                                        '<div id="editFeatureLocation" class="editButton disabled" data-toggle="tooltip" title="<?php _e('Edit geometry'); ?>"></div>\n' +
                                                                        '<div id="deleteFeature" class="editButton disabled" data-toggle="tooltip" title="<?php _e('Delete feature'); ?>"></div>\n' +
                                                                        '<div class="spotziLoader"><img src="http://images.spotzi.com/mapbuilder/spotzi-compass-82x82.png" alt=""></div>' +
                                                                '</div>\n' +
                                                                '<div class="cartodb-popup-tip-container"></div>' +
                                                           '</div>';
                                        mapEditor.enableEdit(false, infotemplate);
                                }
                        };
                }
        }

        function placeEditInfoContainer() {
                if (map) {
                        var placeOnScreen = map.latLngToContainerPoint(infowindowFeature.clicked_point);
                        var usedTop = (placeOnScreen.y - $('#editInfoContainer').height() - 25);
                        var usedLeft = (placeOnScreen.x - 25);

                        $('#editInfoContainer').css({
                                top: usedTop,
                                left: usedLeft
                        });
                }
        }

        function addInfowindowButtons(infowindow, template, changes){
                if (infowindow) {
                        if (template.search('id="infowindowEditButtons"') === -1) {
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow.attributes.template = template.replace(/<\/div>\s*<div class=\"cartodb-popup-tip-container\">\s*<\/div>/,
                                        '<div id="infowindowEditButtons">\n' +
                                                '<div id="editFeatureData" class="editButton disabled" data-toggle="tooltip" title="<?php _e('Edit data'); ?>"></div>\n' +
                                                '<div id="editFeatureLocation" class="editButton disabled" data-toggle="tooltip" title="<?php _e('Edit geometry'); ?>"></div>\n' +
                                                '<div id="deleteFeature" class="editButton disabled" data-toggle="tooltip" title="<?php echo _e('Delete feature'); ?>"></div>\n' +
                                                '<div class="spotziLoader"><img src="http://images.spotzi.com/mapbuilder/spotzi-compass-82x82.png" alt=""></div>' +
                                                '</div>\n' +
                                        '</div>\n' +
                                        '<div class="cartodb-popup-tip-container"></div>'
                                );
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow.setAlternativeName('description', '<?php _e('Description'); ?>');
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow.unbind('change:template', mapEditor.enableEdit, mapEditor);
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow.trigger('change:template');
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow.bind('change:content', getEditButtonClick);
                        }
                } else {
                        $('#editInfoContainer').empty().append(template);
                        map.on('move', placeEditInfoContainer, 'pizza');

                        $('.editInfoContainer-close-button').css({
                                top: -20,
                                right: -20
                        }).off('click').click(function() {
                                $('#editInfoContainer').hide();
                        });
                        getEditButtonClick();
                        $('#editInfoContainer').show();
                        placeEditInfoContainer();
                }

                loadUrl('<?php echo $this->getVisualizationGetFeatureUrl(); ?>', {
                        data:  {
                                featureId: infowindowFeature.feature_id,
                                layerId: infowindowFeature.layer.id
                        }
                }, function(response) {
                        if (response.id === infowindowFeature.feature_id && response.layer === infowindowFeature.layer.id) {
                                infowindowFeature.geom_type = response.geom.geom_type;
                                infowindowFeature.style_css = response.style[0];
                                var colors = ['#000000', '#FFFFFF', '#FF00FF', '#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF'];
                                switch (infowindowFeature.geom_type) {
                                        case '<?php echo EDITOR_POINT; ?>':
                                                var marker_file = '';
                                                if (/marker-file\s*:.+?;/.test(infowindowFeature.style_css)) {
                                                        try {
                                                                marker_file = infowindowFeature.style_css.match(/marker-file\s*:.+?;/)[0].replace(/\/\*line-brake\*\//g, '').match(/http:.+?(svg)/)[0];
                                                        } catch (err) {
                                                                marker_file = '';
                                                        }
                                                        if (/http:\/\/images\.spotzi\.com\/mapbuilder\/editor\/icons\/.+?(\.svg)/.test(marker_file) === false)
                                                                marker_file = '';
                                                }
                                                var marker_fill = '';
                                                if (/marker-fill\s*:.+?;/.test(infowindowFeature.style_css)) {
                                                        try {
                                                                marker_fill = infowindowFeature.style_css.match(/marker-fill\s*:.+?;/)[0].replace(/\/\*line-brake\*\//g, '').split(':')[1].replace(';', '').trim();
                                                        } catch (err) {
                                                                marker_fill = '';
                                                        }
                                                        if ($.inArray(marker_fill, colors) === -1) marker_fill = '';
                                                }
                                                var coordinates = response.geom.the_geom.match(/(\-?\d+(\.\d+)?)\s*(\-?\d+(\.\d+)?)/)[0].split(' ');
                                                infowindowFeature.the_geom = [{ lng: parseFloat(coordinates[0]), lat: parseFloat(coordinates[1]) }];
                                                infowindowFeature.style_options = { marker_file: marker_file, color: marker_fill };
                                                break;
                                        case '<?php echo EDITOR_LINE; ?>':
                                                var line_color = '';
                                                if (/line-color\s*:.+?;/.test(infowindowFeature.style_css)) {
                                                        try {
                                                                line_color = infowindowFeature.style_css.match(/line-color\s*:.+?;/)[0].replace(/\/\*line-brake\*\//g, '').split(':')[1].replace(';', '').trim();
                                                        } catch (err) {
                                                                line_color = '';
                                                        }
                                                        if ($.inArray(line_color, colors) === -1) line_color = '';
                                                }
                                                var coordinates = response.geom.the_geom.match(/(\-?\d+(\.\d+)?)\s*(\-?\d+(\.\d+)?)/g);

                                                var the_geom = [];
                                                var coordinateCount = coordinates.length;
                                                for (var coordNum = 0; coordNum < coordinateCount; coordNum++) {
                                                        the_geom.push({ lng: parseFloat(coordinates[coordNum].split(' ')[0]), lat: parseFloat(coordinates[coordNum].split(' ')[1]) });
                                                }
                                                infowindowFeature.the_geom = the_geom;
                                                infowindowFeature.style_options = { color: line_color };
                                                break;
                                        case '<?php echo EDITOR_POLYGON; ?>':
                                                var polygon_fill = '';
                                                if (/polygon-fill\s*:.+?;/.test(infowindowFeature.style_css)) {
                                                        try {
                                                                polygon_fill = infowindowFeature.style_css.match(/polygon-fill\s*:.+?;/)[0].replace(/\/\*line-brake\*\//g, '').split(':')[1].replace(';', '').trim();
                                                        } catch (err) {
                                                                polygon_fill = '';
                                                        }
                                                        if ($.inArray(polygon_fill, colors) === -1) polygon_fill = '';
                                                }
                                                var coordinates = response.geom.the_geom.match(/(\-?\d+(\.\d+)?)\s*(\-?\d+(\.\d+)?)/g);

                                                var the_geom = [];
                                                var coordinateCount = (coordinates.length - 1);
                                                for (var coordNum = 0; coordNum < coordinateCount; coordNum++) {
                                                        the_geom.push({ lng: parseFloat(coordinates[coordNum].split(' ')[0]), lat: parseFloat(coordinates[coordNum].split(' ')[1]) });
                                                }
                                                infowindowFeature.the_geom = the_geom;
                                                infowindowFeature.style_options = { color: polygon_fill };
                                                break;
                                }
                                infowindowFeature = $.extend(true, infowindowFeature, {});
                                $('#infowindowEditButtons').find('.spotziLoader').css('display', 'none').end()
                                        .find('.editButton').removeClass('disabled');
                        }
                });
        }

        function getEditButtonClick() {
                $('#infowindowEditButtons').find('.spotziLoader').css('display', 'block').find('img').css('animation', 'fa-spin 1.5s infinite steps(60)');
                $('.editButton').unbind('click').click(function() {
                        if (!infowindowFeature.geom_type) return;
                        $('#editInfoContainer').hide();
                        if (mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow)
                                mapLayers[visualization.<?php echo REQUEST_PARAMETER_VIZ_LAYER_INDEX; ?>].infowindow.closeInfowindow();
                        switch ($(this).context.id) {
                                case 'editFeatureData':
                                        if (editFeature.new_action('<?php echo EDITOR_ACTION_EDIT_DATA; ?>', infowindowFeature)) {
                                                startEditing(false);
                                                showDataInput();
                                        } else {
                                                $('#editDataContainer').empty().append('<div><?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?></div>');
                                                placeDataContainer();
                                        }
                                        break;
                                case 'editFeatureLocation':
                                        if (editFeature.new_action('<?php echo EDITOR_ACTION_EDIT_GEOM; ?>', infowindowFeature)) {
                                                if (!editFeature.get_feature()) {
                                                        var text = '<?php _e('The geometry you are trying to edit is too large for the simple editor.'); ?>';
                                                        if (visualization.<?php echo REQUEST_PARAMETER_MYMAP; ?>) {
                                                                text += ' <?php _e('Please visit the <a href="%the_url%">advanced editor</a> to edit.'); ?>'.replace('%the_url%', advancedEditor);
                                                                var advancedEditArray = advancedEditor.split('?');
                                                                var advancedMain = advancedEditArray[0];
                                                                var advancedInputs = '';
                                                                var inputsArray = advancedEditArray[1].split('&');

                                                                var inputCount = inputsArray.length;
                                                                for (var inputIndex = 0; inputIndex < inputCount; inputIndex++) {
                                                                        advancedInputs += '<input hidden name="' + inputsArray[inputIndex].split('=')[0] + '" value="' + inputsArray[inputIndex].split('=')[1] + '">';
                                                                }
                                                                $('#editDataContainer').empty().append('<div>' + text + '</div>' +
                                                                                                       '<form action="' + advancedMain + '/">' +
                                                                                                                advancedInputs +
                                                                                                                '<input type="button" id="cancelFeature" value="<?php _e('Cancel'); ?>">' +
                                                                                                                '<input type="submit" value="<?php _e('Ok'); ?>">' +
                                                                                                       '</form>').show();
                                                        } else {
                                                                $('#editDataContainer').empty().append('<div>' + text + '</div>' +
                                                                                                       '<input type="button" id="cancelFeature" value="<?php _e('Cancel'); ?>">').show();
                                                        }
                                                        placeDataContainer();
                                                } else {
                                                        var text = '<?php _e('Click or move the point to finish your marker'); ?>';
                                                        switch (editFeature.get_geom_type()) {
                                                                case '<?php echo EDITOR_LINE; ?>':
                                                                        text = '<?php _e('Click on a connection point to finish your line'); ?>';
                                                                        break;
                                                                case '<?php echo EDITOR_POLYGON; ?>':
                                                                        text = '<?php _e('Click on a connection point to finish your polygon'); ?>';
                                                                        break;
                                                        }
                                                        $('#editMsg').empty().append(text).show();
                                                        startEditing(true);
                                                }
                                        } else {
                                                $('#editDataContainer').empty().append('<div><?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?></div>');
                                                placeDataContainer();
                                        }
                                        break;
                                case 'deleteFeature':
                                        if (editFeature.new_action('<?php echo EDITOR_ACTION_DELETE; ?>', infowindowFeature)) {
                                                startEditing(false);
                                                showDataInput();
                                        } else {
                                                $('#editDataContainer').empty().append('<div><?php _e('An unexpected error has occured.<br>If this error keeps occuring, please contact our webmaster at info@spotzi.com for assistance.'); ?></div>');
                                                placeDataContainer();
                                        }
                                        break;
                        }
                });
        }

        function showDataInput() {
                $('#editMsg').hide();
                var featureMenu = '<form method="post" id="addFeatureForm" name="addFeatureForm" onsubmit="return mapEditor.submitFeature();"></form>' +
                                  '<div class="spotziLoader"><img src="http://images.spotzi.com/mapbuilder/spotzi-compass-82x82.png" alt=""></div>';

                $('#editDataContainer').empty().append(featureMenu);
                editFeature.show_input($('#addFeatureForm'));

                placeDataContainer();

                $('#editDataContainer').show();
                map.off('move', editFeature).on('move', placeDataContainer, editFeature);
        }

        function saveFeature() {
                var form = $('#addFeatureForm');

                editFeature.save_action(form);
                var formData = new FormData(form[0]);
                $('#editDataContainer').find('input, textarea').attr('disabled', true).end().find('.spotziLoader').css('display', 'block').find('img').css('animation', 'fa-spin 1.5s infinite steps(60)');

                loadUrl('<?php echo $this->getVisualizationAddFeatureUrl(); ?>', {
                        data: formData,
                        enctype: 'multipart/form-data',
                        processData: false,
                        contentType: false
                }, function(response) {
                        if (response.success) {
                                editFeature.reset();
                                $('#editDataContainer').hide();
                        } else {
                                $('#editDataContainer').empty().append('<div><p><?php _e('An error occured while uploading the geometry.'); ?></p></div>' +
                                                                       '<input type="button" id="cancelFeature" value="<?php _e('Ok'); ?>">');
                                placeDataContainer();
                        }

                        mapReload(response.reload);
                        stopEditing();
                }, function() {
                        $('#editDataContainer').empty().append('<div><p><?php _e('An error occured while uploading the geometry.'); ?></p></div>' +
                                                               '<input type="button" id="cancelFeature" value="<?php _e('Ok'); ?>">');
                        placeDataContainer();
                });

                return false;
        }

        return {
                init: initThis,
                addFeature: drawFeature,
                showDataForm: showDataInput,
                submitFeature: saveFeature,
                stopEditFeature: cancelFeature,
                enableEdit: addInfowindowButtons,
                getLayerListener: createLayerListener
        };
})();

// Page Functions
function placeDataContainer() {
        var container = $('#editDataContainer');
        if (map && editFeature && container.length && editFeature.get_clicked_point()) {
                var placeOnScreen = map.latLngToContainerPoint(editFeature.get_clicked_point().getLatLng());
                var usedTop = (placeOnScreen.y + 5);
                if (usedTop > (window.innerHeight - (container.outerHeight() + 50))) usedTop = (usedTop - container.outerHeight() - 5);
                var usedLeft = (placeOnScreen.x + 5);
                if (usedLeft > (window.innerWidth - (container.outerWidth() + 50))) usedLeft = (usedLeft - container.outerWidth() - 5);

                container.css({
                        top: usedTop,
                        left: usedLeft
                });

                $('#cancelFeature').off('click').click(mapEditor.stopEditFeature);
        }
}
</script>
<div id="edit">
        <div id="editMsg"></div>
        <div id="editDataContainer"></div>
        <div id="editInfoContainer" class="cartodb-popup v2"></div>
</div>
// synchroner Code
document.addEventListener('DOMContentLoaded', function () {
    
    let panel   = document.querySelector('#hs-list');
    let hs      = [];
    
    lyr_Hofstellen_2.getSource().forEachFeature(function (feature) {
        let f = {
            number: feature.get('number'),
            name:   feature.get('label'),
        }
        hs.push(f);
    });
    
    hs.sort((a, b) => a.number - b.number);
    
    hs.forEach(function (h) 
    {
        const hsli = document.createElement('div');
    
        hsli.classList.add(`hs-${h.number}`);
        hsli.innerHTML = 'Nr. ' + h.number + ' ' + h.name;
    
        if(panel) panel.appendChild(hsli)      
    });
});

// Funktionen

function m2px(m) {
    var centerLatLng = map.getView().getCenter();
    var pointC = map.getPixelFromCoordinate(centerLatLng);
    var pointX = [pointC[0] + 100, pointC[1]];
    var latLngC = map.getCoordinateFromPixel(pointC);
    var latLngX = map.getCoordinateFromPixel(pointX);
    var lineX = new ol.geom.LineString([latLngC, latLngX]);
    var distanceX = lineX.getLength() / 100;
    reciprocal = 1 / distanceX;
    px = Math.ceil(reciprocal);
    return px;
}

var createTextStyle = function(feature, resolution, labelText, labelFont,labelFill, placement, bufferColor,bufferWidth) 
{
    if (feature.hide || !labelText) {
        return; 
    } 

    if (bufferWidth == 0) {
        var bufferStyle = null;
    } else {
        var bufferStyle = new ol.style.Stroke({
            color: bufferColor,
            width: bufferWidth
        })
    }
    
    var textStyle = new ol.style.Text({
        font: labelFont,
        text: labelText,
        textBaseline: "middle",
        textAlign: "left",
        offsetX: 8,
        offsetY: 3,
        placement: placement,
        maxAngle: 0,
        fill: new ol.style.Fill({
          color: labelFill
        }),
        stroke: bufferStyle
    });

    return textStyle;
};

function stripe(stripeWidth, gapWidth, angle, color) {
    var canvas = document.createElement('canvas');
    var context = canvas.getContext('2d');
    canvas.width = screen.width;
    canvas.height = stripeWidth + gapWidth;
    context.fillStyle = color;
    context.lineWidth = stripeWidth;
    context.fillRect(0, 0, canvas.width, stripeWidth);
    innerPattern = context.createPattern(canvas, 'repeat');

    var outerCanvas = document.createElement('canvas');
    var outerContext = outerCanvas.getContext('2d');
    outerCanvas.width = screen.width;
    outerCanvas.height = screen.height;
    outerContext.rotate((Math.PI / 180) * angle);
    outerContext.translate(-(screen.width/2), -(screen.height/2));
    outerContext.fillStyle = innerPattern;
    outerContext.fillRect(0,0,screen.width,screen.height);

    return outerContext.createPattern(outerCanvas, 'no-repeat');
};

// begin layers.js

// end layers.js

var map = new ol.Map({
    layers: layersList,
    renderer: 'canvas',
    target: 'map',
    
    view: new ol.View({
        //maxZoom: 28, minZoom: 1,
        zoom: 18,
        //center: [51.42844665110317, 13.858214471317353],
    })
});

//initial view - epsg:3857 coordinates if not "Match project CRS"
map.getView().fit([1542151.208901, 6696831.973394, 1543390.308651, 6698068.387861], map.getSize());

//full zooms only
map.getView().setProperties({constrainResolution: true});

//change cursor on move
function pointerOnFeature(evt) {
    if (evt.dragging) {
        return;
    }
    var hasFeature = map.hasFeatureAtPixel(evt.pixel, {
        layerFilter: function (layer) {
            return layer && (layer.get("interactive"));
        }
    });
    map.getViewport().style.cursor = hasFeature ? "pointer" : "";
}

map.on('pointermove', pointerOnFeature);

function styleCursorMove() {
    map.on('pointerdrag', function () {
        map.getViewport().style.cursor = "move";
    });
    map.on('pointerup', function () {
        map.getViewport().style.cursor = "default";
    });
}

styleCursorMove();

////small screen definition
var hasTouchScreen = map.getViewport().classList.contains('ol-touch');
var isSmallScreen = window.innerWidth < 650;

// hilight and hover **************************

//popup
var container = document.getElementById('popup');
var content = document.getElementById('popup-content');
var closer = document.getElementById('popup-closer');
var sketch;

function stopMediaInPopup() {
    var mediaElements = container.querySelectorAll('audio, video');
    mediaElements.forEach(function (media) {
        media.pause();
        media.currentTime = 0;
    });
}

if(closer) {
    closer.onclick = function () {
        container.style.display = 'none';
        closer.blur();
        stopMediaInPopup();
        return false;
    };
}

var overlayPopup = new ol.Overlay({
    element: container,
    autoPan: false
});

map.addOverlay(overlayPopup)


var NO_POPUP = 0
var ALL_FIELDS = 1

/**
 * Returns either NO_POPUP, ALL_FIELDS or the name of a single field to use for
 * a given layer
 * @param layerList {Array} List of ol.Layer instances
 * @param layer {ol.Layer} Layer to find field info about
 */
function getPopupFields(layerList, layer) {
    // Determine the index that the layer will have in the popupLayers Array,
    // if the layersList contains more items than popupLayers then we need to
    // adjust the index to take into account the base maps group
    var idx = layersList.indexOf(layer) - (layersList.length - popupLayers.length);
    
    return popupLayers[idx];
}

//highligth collection
var collection = new ol.Collection();
var featureOverlay = new ol.layer.Vector({
    map: map,
    source: new ol.source.Vector({
        features: collection,
        useSpatialIndex: false // optional, might improve performance
    }),
    style: [new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: '#f00',
            width: 1
        }),
        fill: new ol.style.Fill({
            color: 'rgba(255,0,0,0.1)'
        }),
    })],
    updateWhileAnimating: true, // optional, for instant visual feedback
    updateWhileInteracting: true // optional, for instant visual feedback
});

var doHighlight = true;
var doHover = true;

function createPopupField(currentFeature, currentFeatureKeys, layer) 
{
    let popupText = '';
    
    console.log(layer.get('popuplayertitle')); // , currentFeature.get('number') + ' ' + currentFeature.get('label')
    
    return popupText;
};

function createPopupFieldA(currentFeature, currentFeatureKeys, layer) 
{
    var popupText = '';
    
    for (var i = 0; i < currentFeatureKeys.length; i++) 
    {

//console.log(layer.get('fieldLabels')[currentFeatureKeys[i]] + ': ' + currentFeature.get(currentFeatureKeys[i]));
//console.log(layer.get('fieldLabels'));
//console.log(currentFeature.get('number') + ' ' + currentFeature.get('label'));

        if (
            currentFeatureKeys[i] != 'geometry' && 
            currentFeatureKeys[i] != 'layerObject' && 
            currentFeatureKeys[i] != 'idO'
        ) {
            var popupField = '';
            
            if (layer.get('fieldLabels')[currentFeatureKeys[i]] == "hidden field") {
                continue;
            } else if (layer.get('fieldLabels')[currentFeatureKeys[i]] == "inline label - visible with data") {
                if (currentFeature.get(currentFeatureKeys[i]) == null) {
                    continue;
                }
            }
            
            if (layer.get('fieldLabels')[currentFeatureKeys[i]] == "inline label - always visible" ||
                layer.get('fieldLabels')[currentFeatureKeys[i]] == "inline label - visible with data") {
                popupField += '<th>' + layer.get('fieldAliases')[currentFeatureKeys[i]] + '</th><td>';
            } else {
                popupField += '<td colspan="2">';
            }
            
            if (layer.get('fieldLabels')[currentFeatureKeys[i]] == "header label - visible with data") {
                if (currentFeature.get(currentFeatureKeys[i]) == null) {
                    continue;
                }
            }
            
            if (layer.get('fieldLabels')[currentFeatureKeys[i]] == "header label - always visible" ||
                layer.get('fieldLabels')[currentFeatureKeys[i]] == "header label - visible with data") {
                popupField += '<strong>' + layer.get('fieldAliases')[currentFeatureKeys[i]] + '</strong><br />';
            }
            
            if (layer.get('fieldImages')[currentFeatureKeys[i]] != "ExternalResource") {
                popupField += (currentFeature.get(currentFeatureKeys[i]) != null ? autolinker.link(currentFeature.get(currentFeatureKeys[i]).toLocaleString()) + '</td>' : '');
            } else {
                var fieldValue = currentFeature.get(currentFeatureKeys[i]);
                if (/\.(gif|jpg|jpeg|tif|tiff|png|avif|webp|svg)$/i.test(fieldValue)) {
                    popupField += (fieldValue != null ? '<img src="images/' + fieldValue.replace(/[\\\/:]/g, '_').trim() + '" /></td>' : '');
                } else if (/\.(mp4|webm|ogg|avi|mov|flv)$/i.test(fieldValue)) {
                    popupField += (fieldValue != null ? '<video controls><source src="images/' + fieldValue.replace(/[\\\/:]/g, '_').trim() + '" type="video/mp4">Il tuo browser non supporta il tag video.</video></td>' : '');
                } else if (/\.(mp3|wav|ogg|aac|flac)$/i.test(fieldValue)) {
                    popupField += (fieldValue != null ? '<audio controls><source src="images/' + fieldValue.replace(/[\\\/:]/g, '_').trim() + '" type="audio/mpeg">Il tuo browser non supporta il tag audio.</audio></td>' : '');
                } else {
                    popupField += (fieldValue != null ? autolinker.link(fieldValue.toLocaleString()) + '</td>' : '');
                }
            }
            popupText += '<tr>' + popupField + '</tr>';
        }
    }
    return popupText;
}

var highlight;
var autolinker = new Autolinker({truncate: {length: 30, location: 'smart'}});

function onPointerMove(evt) {
    if (!doHover && !doHighlight) {
        return;
    }
    var pixel = map.getEventPixel(evt.originalEvent);
    var coord = evt.coordinate;
    var currentFeature;
    var currentLayer;
    var currentFeatureKeys;
    var clusteredFeatures;
    var clusterLength;
    var popupText = '<ul>';

    // Collect all features and their layers at the pixel
    var featuresAndLayers = [];
    
    map.forEachFeatureAtPixel(pixel, function (feature, layer) {
        if (layer && feature instanceof ol.Feature && (layer.get("interactive") || layer.get("interactive") === undefined)) {
            featuresAndLayers.push({feature, layer});
        }
    });

    // Iterate over the features and layers in reverse order
    for (var i = featuresAndLayers.length - 1; i >= 0; i--) {
        var feature = featuresAndLayers[i].feature;
        var layer = featuresAndLayers[i].layer;
        var doPopup = false;
        for (k in layer.get('fieldImages')) {
            if (layer.get('fieldImages')[k] != "Hidden") {
                doPopup = true;
            }
        }
        currentFeature = feature;
        currentLayer = layer;
        clusteredFeatures = feature.get("features");
        
        if (clusteredFeatures) {
            clusterLength = clusteredFeatures.length;
        }
        
        if (typeof clusteredFeatures !== "undefined") {
            if (doPopup) {
                for (var n = 0; n < clusteredFeatures.length; n++) {
                    currentFeature = clusteredFeatures[n];
                    currentFeatureKeys = currentFeature.getKeys();
                    
                    /*
                    popupText += '<li><table>'
                    popupText += '<a>' + '<b>' + layer.get('popuplayertitle') + '</b>' + '</a>';
                    popupText += createPopupField(currentFeature, currentFeatureKeys, layer);
                    popupText += '</table></li>';
                    */
                    popupText += 'clusteredFeatures 531'
                }
            }
        } else {
            currentFeatureKeys = currentFeature.getKeys();
            
            if (doPopup) {
                /*
                popupText += '<li><table>';
                popupText += '<a>' + '<b>' + layer.get('popuplayertitle') + '</b>' + '</a>';
                popupText += createPopupField(currentFeature, currentFeatureKeys, layer);
                popupText += '</table></li>';
                */
                let number = currentFeature.get('number');
                
                if(number) {
                    popupText = 'Hofstelle Nr. ' + currentFeature.get('number') + ' ' + currentFeature.get('label');
                } else {
                    popupText = currentFeature.get('label');
                }
                popupText = popupText.replace(/ /g, "&nbsp;");
            }
        }
    }

    if (popupText == '<ul>') {
        popupText = '';
    } else {
        popupText += '</ul>';
    }

    if (doHighlight) {
        if (currentFeature !== highlight) {
            if (highlight) {
                featureOverlay.getSource().removeFeature(highlight);
            }
            
            if (currentFeature) 
            {
                var featureStyle
                if (typeof clusteredFeatures == "undefined") {
                    var style = currentLayer.getStyle();
                    var styleFunction = typeof style === 'function' ? style : function () {
                        return style;
                    };
                    featureStyle = styleFunction(currentFeature)[0];
                } else {
                    featureStyle = currentLayer.getStyle().toString();
                }

                if (
                    currentFeature.getGeometry().getType() == 'Point' || 
                    currentFeature.getGeometry().getType() == 'MultiPoint'
                ) {
                    var radius
                    if (typeof clusteredFeatures == "undefined") {
                        radius = featureStyle.getImage().getRadius();
                    } else {
                        radius = parseFloat(featureStyle.split('radius')[1].split(' ')[1]) + clusterLength;
                    }

                    highlightStyle = new ol.style.Style({
                        image: new ol.style.Circle({
                            fill: new ol.style.Fill({
                                color: "rgba(255, 255, 0, 1.00)"
                            }),
                            radius: radius
                        })
                    })
                } 
                else if (
                    currentFeature.getGeometry().getType() == 'LineString' || 
                    currentFeature.getGeometry().getType() == 'MultiLineString'
                ) {
                    let featureWidth = featureStyle.getStroke().getWidth();

                    highlightStyle = new ol.style.Style({
                        stroke: new ol.style.Stroke({
                            color: 'rgba(255, 0, 0, 1.00)',
                            lineDash: null,
                            width: featureWidth
                        })
                    });

                } else {
                    highlightStyle = new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: 'rgba(255, 0, 0, 0.10)'
                        })
                    })
                }
                featureOverlay.getSource().addFeature(currentFeature);
                featureOverlay.setStyle(highlightStyle);
                
                let hs = document.querySelector(`.hs-${currentFeature.get('number')}`);
                
                //if(hs && !hs.classList.contains('highlight')) hs.classList.Add('highlight');
            }
            highlight = currentFeature;
        }
    }

    if (doHover) {
        if (popupText) {
            content.innerHTML = popupText;
            container.style.display = 'block';
            overlayPopup.setPosition(coord);
        } else {
            container.style.display = 'none';
            if(closer) closer.blur();
        }
    }
};

map.on('pointermove', onPointerMove);

// Add a layer switcher outside the map

var lswitcher = new ol.control.LayerSwitcher({
    target: $(".layerSwitcher").get(0), 
      // displayInLayerSwitcher: function (l) { return false; },
    show_progress:true,
    // reordering: false,
    extent: false,
    trash:  false,
    oninfo: function (l) { alert(l.get("title")); }    
});

map.addControl(lswitcher);

// The serach input
var search = $('<input>').attr('placeholder','filter');

function filterLayers(rex, layers) {
    var found = false;
    layers.forEach(function(l){
      // Layer Group
      if (l.getLayers) {
        if (filterLayers(rex, l.getLayers().getArray())) {
          l.set('noLayer', false);
          found = true;
        } else {
          l.set('noLayer', true);
        }
      } else {
        if (rex.test(l.get('title'))) {
          l.setVisible(true);
          found = true;
        } else {
          l.setVisible(false);
        }
      }
    });
    return found;
}
search.on('keyup change', function() {
    var rex = new RegExp(search.val(), 'i');
    filterLayers(rex, layersList);
    // Force layer switcher redraw
    lswitcher.drawPanel()
});

// Add search input in the switcher header
lswitcher.setHeader(search.get(0));

// When switcher is drawn hide/show the list item according to its visility
lswitcher.on('drawlist', function(e) 
{
    // Hide Layer Group with no layers visible
    if (e.layer.getLayers) {
      if (e.layer.get('noLayer')) {
        $(e.li).hide();
      } else {
        $(e.li).show();
      }
    } else {
      if (e.layer.getVisible()) {
        $(e.li).show();
      } else {
        $(e.li).hide();
      }
    }
});

// Compass control
var compass, compassBottom;

function setCompass() {
  if (compass) map.removeControl(compass);
  if (compassBottom) map.removeControl(compassBottom);
  
  //if ($("#top").prop("checked")) {
  if (true) {
    compass = new ol.control.Compass ({
      className: "top",
      src: $("#image").val(),
      // rotateWithView: $("#rotate").prop("checked"),
      style: new ol.style.Stroke ({ color: $("#color").val(), width: ($("#line").prop("checked") ? 1 : 0) })
    });
    map.addControl(compass);
  }
  else compass = false;
  compassBottom = new ol.control.Compass ({
    className: "bottom",
    //src: $("#image").val(),
    src: 'compact',
    rotateWithView: $("#rotate").prop("checked"),
    style: new ol.style.Stroke ({ color: $("#color").val(), width: ($("#line").prop("checked") ? 1 : 0) })
  });
  map.addControl(compassBottom);
}

setCompass();

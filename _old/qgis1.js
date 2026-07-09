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

var map = new ol.Map({
    layers: layersList,
    renderer: 'canvas',
    target: 'map',

    view: new ol.View({
        //maxZoom: 28, minZoom: 1,
        zoom: 18,
        center: [51.42844665110317, 13.858214471317353],
    })
});

//initial view - epsg:3857 coordinates if not "Match project CRS"
map.getView().fit([1542151.208901, 6696831.973394, 1543390.308651, 6698068.387861], map.getSize());

//full zooms only
map.getView().setProperties({constrainResolution: true});
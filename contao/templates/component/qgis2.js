// Funktionen

function mm2px(m) {
    const _map = map_1;

    var centerLatLng = _map.getView().getCenter();
    var pointC = _map.getPixelFromCoordinate(centerLatLng);
    var pointX = [pointC[0] + 100, pointC[1]];
    var latLngC = _map.getCoordinateFromPixel(pointC);
    var latLngX = _map.getCoordinateFromPixel(pointX);
    var lineX = new ol.geom.LineString([latLngC, latLngX]);
    var distanceX = lineX.getLength() / 100;
    reciprocal = 1 / distanceX;
    px = Math.ceil(reciprocal);
    return px;
}

const createTS = function(feature, resolution, labelText, labelFont,labelFill, placement, bufferColor,bufferWidth)
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

const map_1= new ol.Map({
    target: 'olmap-1',
    layers: ol_layers_1,
    renderer: 'canvas',

    view: new ol.View({
        zoom: 16,
        //minZoom: 13,
        //maxZoom: 19,
        //center: [51.42844665110317, 13.858214471317353],
    })
});

//initial view - epsg:3857 coordinates if not "Match project CRS"
map_1.getView().fit([1542151.208901, 6696831.973394, 1543390.308651, 6698068.387861], map_1.getSize());

//full zooms only
//map_1.getView().setProperties({constrainResolution: true});
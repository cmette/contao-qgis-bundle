function mm2px(m) {
    const _map = map_4;
    const centerCoord = _map.getView().getCenter();
    const centerPx = _map.getPixelFromCoordinate(centerCoord);
    const rightPx = [centerPx[0] + 100, centerPx[1]];
    const coordC = _map.getCoordinateFromPixel(centerPx);
    const coordR = _map.getCoordinateFromPixel(rightPx);
    const line = new ol.geom.LineString([coordC, coordR]);
    const unitsPer100px = line.getLength();
    if (!unitsPer100px || unitsPer100px === 0) return 0;
    const unitsPerPx = unitsPer100px / 100;
    return Math.ceil(m / unitsPerPx)
}

map_4_styleCache = {
    style_1: (feature) => new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: 'rgba(72,187,120,1.000)',
            lineDash: [10, 10],
            lineCap: 'butt',
            lineJoin: 'miter',
            width: 1,
        }),
        fill: new ol.style.Fill({color: 'rgba(245,101,101,0.600)'}),
        image: new ol.style.Circle({radius: 6, fill: new ol.style.Fill({color: '#ff0'})}),
        text: new ol.style.Text({
            font: '12px Calibri,sans-serif',
            text: feature.get('name'),
            textAlign: 'center',
            textBaseline: 'top',
            offsetX: 0,
            offsetY: 0,
            rotation: 0,
            placement: 'point',
        }),
    }),
    style_4: (feature) => new ol.style.Style({
        stroke: new ol.style.Stroke({
            color: 'rgba(72,187,120,1.000)',
            lineDash: [],
            lineCap: 'butt',
            lineJoin: 'miter',
            width: 1,
        }),
        fill: new ol.style.Fill({color: 'rgba(159,122,234,0.500)'}),
        image: new ol.style.Circle({radius: 6, fill: new ol.style.Fill({color: '#ff0'})}),
        text: new ol.style.Text({
            font: '12px Calibri,sans-serif',
            text: feature.get('name'),
            textAlign: 'center',
            textBaseline: 'top',
            offsetX: 0,
            offsetY: 0,
            rotation: 0,
            placement: 'point',
        }),
    }),
};
map_4_styleFunction = (feature, resolution) => {
    const styleId = feature.get('styleId');
    if (resolution > 100) {
        return null
    }
    return map_4_styleCache[`style_${styleId}`](feature)
};
var ol_layers_4 = [];

var source_Tile_1 = new ol.source.XYZ({attributions: 'OpenStreetMap',});
source_Tile_1.setUrl('https://tile.openstreetmap.org/{z}/{x}/{y}.png');
var layer_1 = new ol.layer.Tile({
    title: 'OSM via XYZ',
    type: 'base',
    opacity: 1.000000,
    source: source_Tile_1,
    interactive: !0,
});
layer_1.setVisible(!0);
ol_layers_4.push(layer_1);

var source_Image_6 = new ol.source.Image({
    attributions: 'JPG',
    url: 'https://arnsdorf.ddev.site/files/content-arnsdorf/karten/hofstellen/24HK14GUEF-Hofstellen-min.jpg',
    projection: 'EPSG:3857',
    imageExtent: [1542103.46463, 6696699.56956, 1543500.30577, 6698158.00370],
});
var layer_6 = new ol.layer.Image({
    title: 'Test Layer Hofstellen ImageStatic',
    type: 'base',
    opacity: 1.000000,
    source: source_Image_6,
    interactive: !0,
});
layer_6.setVisible(!0);
ol_layers_4.push(layer_6);

map_4 = new ol.Map({
    target: 'olmap-4',
    layers: ol_layers_4,
    renderer: 'canvas',
    view: new ol.View({projection: 'EPSG:3857', zoom: 13.713690000000000, center: [1543135.21683, 6697433.31208],})
})
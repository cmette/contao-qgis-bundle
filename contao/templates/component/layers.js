var wms_layers = [];

var lyr_OpenStreetMap_0 = new ol.layer.Tile({
    'title': 'OpenStreetMap',
    'type': 'base',
    'opacity': 1.000000,

    source: new ol.source.XYZ({
        attributions: ' ',
        url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
    })
});


var format_VektorLayer_1 = new ol.format.GeoJSON();
var features_VektorLayer_1 = format_VektorLayer_1.readFeatures(json_VektorLayer_1,{dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_VektorLayer_1 = new ol.source.Vector({attributions: ' ',});

jsonSource_VektorLayer_1.addFeatures(features_VektorLayer_1);

var lyr_VektorLayer_1 = new ol.layer.Vector({
    declutter: false,
    source: jsonSource_VektorLayer_1,
    style: style_VektorLayer_1,
    popuplayertitle: 'Vektor-Layer',
    interactive: true,
    title: '<img src="files/content-arnsdorf/hoyk/styles/legend/VektorLayer_1.png" /> Vektor-Layer'
});


var format_Hofstellen_2 = new ol.format.GeoJSON();
var features_Hofstellen_2 = format_Hofstellen_2.readFeatures(json_Hofstellen_2,
    {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_Hofstellen_2 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_Hofstellen_2.addFeatures(features_Hofstellen_2);

var lyr_Hofstellen_2 = new ol.layer.Vector({
    declutter: false,
    source: jsonSource_Hofstellen_2,
    style: style_Hofstellen_2,
    popuplayertitle: 'Hofstellen',
    interactive: true,
    title: '<img src="files/content-arnsdorf/hoyk/styles/legend/Hofstellen_2.png" /> Hofstellen'
});
//----------------------------------------------------------------------------------------------
var format_Schwarzwasserunbegradigt_3 = new ol.format.GeoJSON();
var features_Schwarzwasserunbegradigt_3 = format_Schwarzwasserunbegradigt_3.readFeatures(json_Schwarzwasserunbegradigt_3,
    {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_Schwarzwasserunbegradigt_3 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_Schwarzwasserunbegradigt_3.addFeatures(features_Schwarzwasserunbegradigt_3);
var lyr_Schwarzwasserunbegradigt_3 = new ol.layer.Vector({
    declutter: false,
    source: jsonSource_Schwarzwasserunbegradigt_3,
    style: style_Schwarzwasserunbegradigt_3,
    popuplayertitle: 'Schwarzwasser (unbegradigt)',
    interactive: true,
    title: '<img src="files/content-arnsdorf/hoyk/styles/legend/Schwarzwasserunbegradigt_3.png" /> Schwarzwasser (unbegradigt)'
});
//----------------------------------------------------------------------------------------------
var format_Sieggrabenunbegradigt_4 = new ol.format.GeoJSON();
var features_Sieggrabenunbegradigt_4 = format_Sieggrabenunbegradigt_4.readFeatures(json_Sieggrabenunbegradigt_4,
    {dataProjection: 'EPSG:4326', featureProjection: 'EPSG:3857'});
var jsonSource_Sieggrabenunbegradigt_4 = new ol.source.Vector({
    attributions: ' ',
});
jsonSource_Sieggrabenunbegradigt_4.addFeatures(features_Sieggrabenunbegradigt_4);
var lyr_Sieggrabenunbegradigt_4 = new ol.layer.Vector({
    declutter: false,
    source: jsonSource_Sieggrabenunbegradigt_4,
    style: style_Sieggrabenunbegradigt_4,
    popuplayertitle: 'Sieggraben (unbegradigt)',
    interactive: true,
    title: '<img src="files/content-arnsdorf/hoyk/styles/legend/Sieggrabenunbegradigt_4.png" /> Sieggraben (unbegradigt)'
});
//----------------------------------------------------------------------------------------------
lyr_OpenStreetMap_0.setVisible(true);
lyr_VektorLayer_1.setVisible(true);
lyr_Hofstellen_2.setVisible(true);
lyr_Schwarzwasserunbegradigt_3.setVisible(true);
lyr_Sieggrabenunbegradigt_4.setVisible(true);

var layersList = [lyr_OpenStreetMap_0, lyr_VektorLayer_1, lyr_Hofstellen_2, lyr_Schwarzwasserunbegradigt_3, lyr_Sieggrabenunbegradigt_4];

lyr_VektorLayer_1.set('fieldAliases', {'id': 'id', 'label': 'label',});
lyr_Hofstellen_2.set('fieldAliases', {'id': 'id', 'number': 'number', 'label': 'label',});
lyr_Schwarzwasserunbegradigt_3.set('fieldAliases', {'id': 'id', 'label': 'label',});
lyr_Sieggrabenunbegradigt_4.set('fieldAliases', {'id': 'id', 'label': 'label',});
lyr_VektorLayer_1.set('fieldImages', {'id': 'TextEdit', 'label': 'TextEdit',});
lyr_Hofstellen_2.set('fieldImages', {'id': '', 'number': '', 'label': '',});
lyr_Schwarzwasserunbegradigt_3.set('fieldImages', {'id': '', 'label': '',});
lyr_Sieggrabenunbegradigt_4.set('fieldImages', {'id': '', 'label': '',});
lyr_VektorLayer_1.set('fieldLabels', {'id': 'no label', 'label': 'no label',});
lyr_Hofstellen_2.set('fieldLabels', {
    'id': 'no label',
    'number': 'inline label - always visible',
    'label': 'inline label - always visible',
});
lyr_Schwarzwasserunbegradigt_3.set('fieldLabels', {'id': 'no label', 'label': 'no label',});
lyr_Sieggrabenunbegradigt_4.set('fieldLabels', {'id': 'no label', 'label': 'no label',});
lyr_Sieggrabenunbegradigt_4.on('precompose', function (evt) {
    evt.context.globalCompositeOperation = 'normal';
});
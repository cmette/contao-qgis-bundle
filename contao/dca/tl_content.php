<?php

use Cmette\ContaoQgisBundle\Utils\DcaUtils;

$GLOBALS['TL_DCA']['tl_content']['palettes']['qgis_map'] =
    '{type_legend},type,headline,title;' .
    '{map_legend},qgis_map,addFeatureList;' .
    '{text_legend},text;' .
    '{template_legend:hide},customTpl;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},cssID;' .
    '{invisible_legend:hide},invisible,start,stop;'
;
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'addFeatureList';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'addActiveList';

$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addFeatureList'] = 'featureSourceLayer,listHeadline,isActiveList,addActiveList';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addActiveList']  = 'zoomExtentOnHover,zoomExtentOnClick';

$GLOBALS['TL_DCA']['tl_content']['fields']['qgis_map'] =
[
    'inputType' => 'select',
    'search'    => true,
    'filter'    => true,
    'sorting' => true,
    'eval'      => [
        'mandatory' => true,
        'submitOnChange' => true,
        'includeBlankOption'=> false,
        'tl_class' => 'w50',
        'multiple' => false,
        'chosen' => true
    ],
    'sql' => [
        'type'      => 'integer',
        'unsigned'  => true,
        'notnull'   => true,
        'default'   => 0,
    ]
];

$GLOBALS['TL_DCA']['tl_content']['fields']['addFeatureList'] = DcaUtils::buildAddField();

$GLOBALS['TL_DCA']['tl_content']['fields']['featureSourceLayer'] =
[
    'inputType' => 'select',
    'search'    => true,
    'filter'    => true,
    'sorting' => true,
    'eval'      => [
        'mandatory' => true,
        'includeBlankOption'=> false,
        'tl_class' => 'w50',
        'multiple' => false,
        'chosen' => true
    ],
    'sql' => [
        'type'      => 'integer',
        'unsigned'  => true,
        'notnull'   => true,
        'default'   => 0,
    ]
];

$GLOBALS['TL_DCA']['tl_content']['fields']['listHeadline'] =
[
    'inputType' => 'inputUnit',
    'search'    => true,
    'options'   => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
    'eval'      => [
        'maxlength' =>200,
        'basicEntities'=>true,
        'tl_class'  =>'w50 clr'
    ],
    'sql'       => [
        'type'      => 'string',
        'length'    => 255,
        'fixed'     => true,
        'default'   => "a:2:{s:5:\"value\";s:0:\"\";s:4:\"unit\";s:2:\"h2\";}",
    ]
];

$GLOBALS['TL_DCA']['tl_content']['fields']['addActiveList']      = DcaUtils::buildAddField(true, true);
$GLOBALS['TL_DCA']['tl_content']['fields']['zoomExtentOnClick']  = DcaUtils::buildAddField(true, false);
$GLOBALS['TL_DCA']['tl_content']['fields']['zoomExtentOnHover']  = DcaUtils::buildAddField(false, false);
<?php

use Cmette\ContaoQgisBundle\Utils\DcaUtils;
use Doctrine\DBAL\Platforms\MySQLPlatform;

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
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'isSortedList';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'addActiveList';

$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addFeatureList'] = 'featureSourceLayer,listHeadline,isSortedList,addActiveList';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['isSortedList']   = 'listProperties';
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
        'mandatory' => false,
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

$GLOBALS['TL_DCA']['tl_content']['fields']['isSortedList']       = DcaUtils::buildAddField(true, true);
$GLOBALS['TL_DCA']['tl_content']['fields']['listProperties']     = [
    'inputType' => 'rowWizard',
    'fields' => [
        'property' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['listProperties_fields']['property'],
            'inputType' => 'select',
            #'options'       => ['useTitle', 'useProperty', '2rwf', '3rw'],
            'reference'     => &$GLOBALS['TL_LANG']['tl_content']['listProperties_fields'],
            'eval'      => [
                'mandatory' => false,
                'includeBlankOption'=> false,
                #'blankOptionLabel'  => 'kein/unbekannt',
                'multiple'  => false,
                'unique'    => true,
                'chosen'    => false,
                'cell_class'=> 'property'
            ],
        ],
        'sortBy' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['listProperties_fields']['sortBy'],
            'inputType' => 'select',
            'options'   => ['none', 'asc', 'desc'],
            'reference' => &$GLOBALS['TL_LANG']['tl_content']['listProperties_sortBy_options'],
            'eval'      => [
                'mandatory' => false,
                'includeBlankOption'=> false,
                'multiple' => false,
                'chosen' => false,
                'cell_class'=> 'sortBy'
            ],
        ],
    ],  # fields
    'eval' => [
        'tl_class' => 'clr',

        'actions' => [
            'copy',
            'delete',
            'enable',
        ],
        'min' => 1,         // minimum rows
        'max' => 100,        // maximum rows
        'sortable' => true, // disable the sorting, defaults to true
    ],
    'sql' => [
        'type' => 'text',
        'length' => MySQLPlatform::LENGTH_LIMIT_BLOB,
        'notnull' => false
    ],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['zoomExtentOnClick']  = DcaUtils::buildAddField(true, false);
$GLOBALS['TL_DCA']['tl_content']['fields']['zoomExtentOnHover']  = DcaUtils::buildAddField(false, false);
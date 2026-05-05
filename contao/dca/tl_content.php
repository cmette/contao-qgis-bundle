<?php

$GLOBALS['TL_DCA']['tl_content']['palettes']['qgis_map'] =
    '{type_legend},type,headline,title;' .
    '{map_legend},map;' .
    '{template_legend:hide},customTpl;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},cssID;' .
    '{invisible_legend:hide},invisible,start,stop;'
;

$GLOBALS['TL_DCA']['tl_content']['fields']['qgis_map'] =
[
    'inputType' => 'test',
    'search'    => true,
    'filter'    => true,
    'sorting' => true,
    'eval'      => [
        'mandatory' => true,
        'includeBlankOption'=> false,
        'tl_class' => '',
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

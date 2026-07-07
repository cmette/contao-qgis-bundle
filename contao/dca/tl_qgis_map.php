<?php

use Cmette\ContaoQgisBundle\Models\QgisLayerModel;
use Cmette\ContaoQgisBundle\Utils\DcaUtils;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;

$strTable = 'tl_qgis_map';

System::loadLanguageFile($strTable);

$GLOBALS['TL_DCA'][$strTable] = [
	'config' =>  [
		'dataContainer'     => DC_Table::class,
        'switchToEdit'      => true,
		'enableVersioning'  => true,
        'sql' => [
            'keys' => [
                'id'        => 'primary',
                'tstamp'    => 'index',
            ],
        ],
	],
	'list' => [
		'sorting' =>  [
            'mode'                  => DataContainer::MODE_SORTED,
			'fields'                => ['title'],
			'headerFields'          => ['title'],
			'panelLayout'           => 'filter,sort,search,limit',
            'defaultSearchField'    => 'title',
            #'renderAsGrid'  => true,
			#'limitHeight'   => 160

            # requires special bundle oneup/contao-backend-sortable-list-views
            'sortableListView' => true,
		],
		'label' =>  [
			'fields' =>  ['title'],
            // If true Contao will generate a table header with column names (e.g. back end member list)
            // If the DCA uses showColumns then the return value of the list.label.label-Callback
            // must be an array of strings. Otherwise just the label as a string.
            'showColumns' => false,
			#'format' => '%s',
		],
		'operations' =>  [
            'edit',
            'activate',
            '!delete',
            'toggle',
        ],
	],

	// Palettes
	'palettes' =>  [
		'__selector__'  =>  ['addOpenLayers','addOpenLayersExt'],
		'default'       =>
            '{type_legend},title;' .
            '{olconfig_legend},addOpenLayers,addOpenLayersExt;' .
            '{mapconfig_legend},showCrsFE,showScopeFE,showCrsBE,showScopeBE,;' .
            '{layers_legend},layers;' .
            '{parameters_legend},map,center,extent,zoom,mapCrs,mapPadding;' .
            '',
	],

	// Subpalettes
	'subpalettes' =>  [
        'addOpenLayers'     => 'olDistVersion,loadOpenLayersJs,loadOpenLayersCss',
        'addOpenLayersExt'  => 'olExtDistVersion,loadOpenLayersExtJs,loadOpenLayersExtCss,useCompass;',
    ],

	// Fields
	'fields' => [
        /**********************************************************************
         * without legend
         **********************************************************************/
        'id'        => ['sql' => "int(10) unsigned NOT NULL auto_increment"],
        'tstamp'    => ['sql' => "int(10) unsigned NOT NULL default 0",],
        'published' => DcaUtils::buildPublishedField(),
        /**********************************************************************
         * type_legend
         **********************************************************************/
        'title' => [
            'inputType'     => 'text',
            'eval'          => [
                'mandatory' => true,
                'unique'    => true,
                'tl_class'  =>'w50'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '',
            ]
        ],
        /**********************************************************************
         * olconfig_legend
         **********************************************************************/
        // switch add open layers
        'addOpenLayers'     => DcaUtils::buildAddField(default:true, tl_class: ''),
        // select open layers versions
        'olDistVersion'   => [
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => ['10.8.0','10.9.0'],
            'eval'          => [
                'mandatory' => false,
                'unique'    => false,
                'tl_class'  =>'w16'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 6,
                'fixed'     => true,
                'default'   => '10.8.0',
            ]
        ],
        // load open layers js
        'loadOpenLayersJs' => DcaUtils::buildAddField(default:false, tl_class: 'w16', submitOnChange: false),
        // load open layers css
        'loadOpenLayersCss' => DcaUtils::buildAddField(default:false, tl_class: 'w16', submitOnChange: false),
        // load ol-ext library
        'addOpenLayersExt'  => DcaUtils::buildAddField(true),
        // select between versions
        'olExtDistVersion'   => [
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => ['4.0.38'],
            #'reference' => &$GLOBALS['TL_LANG'][$strTable]['lineCap_options'],
            'eval'          => [
                'mandatory' => false,
                'unique'    => false,
                'tl_class'  =>'w16'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 6,
                'fixed'     => true,
                'default'   => '4.0.38',
            ]
        ],
        // load open layers extensions js
        'loadOpenLayersExtJs' => DcaUtils::buildAddField(default:false, tl_class: 'w16', submitOnChange: false),
        // load open layers extensions css
        'loadOpenLayersExtCss' => DcaUtils::buildAddField(default:false, tl_class: 'w16', submitOnChange: false),
        // load open layers extensions css
        'useCompass' => DcaUtils::buildAddField(default:false, tl_class: 'w16 clr', submitOnChange: false),
        /**********************************************************************
         * mapconfig_legend
         **********************************************************************/
        'showCrsFE'   => DcaUtils::buildAddField(default:false, tl_class: 'w50', submitOnChange: false),
        'showScopeFE' => DcaUtils::buildAddField(default:false, tl_class: 'w50', submitOnChange: false),
        'showCrsBE'   => DcaUtils::buildAddField(default:false, tl_class: 'w50', submitOnChange: false),
        'showScopeBE' => DcaUtils::buildAddField(default:false, tl_class: 'w50', submitOnChange: false),
        /**********************************************************************
         * layers_legend
         **********************************************************************/
        # list of layers
        'layers' => [
            'inputType' => 'rowWizard',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'fields' => [
                'layer' => [
                    'label'     => &$GLOBALS['TL_LANG'][$strTable]['layer_fields']['layer'],
                    'inputType' => 'select',
                    #'options'   => SourcesLibraryModel::getLibrariesOptions(true, false),
                    'foreignKey'=> 'tl_qgis_layer.title',
                    'relation'      => ['type'=>'belongsTo', 'load'=>'lazy'],
                    #'reference' => &$GLOBALS['TL_LANG'][$strTable]['catalog_fields']['provider_options'],
                    'eval'          => [
                        'mandatory' => true,
                        'unique'    => true,
                        'cell_class'  => 'layer'
                    ],
                ],
                'style' => [
                    'label'     => &$GLOBALS['TL_LANG'][$strTable]['layer_fields']['style'],
                    'inputType' => 'select',
                    'foreignKey' => "tl_qgis_style.name",
                    'relation'  => [
                        'type'  => 'hasOne',
                        'load'  => 'lazy'
                    ],
                    'eval'      => [
                        // wenn addSeries true, dann muss eine Reihe angegeben werden!
                        'mandatory' => false,
                        'includeBlankOption'=> true,
                        #'blankOptionLabel'  => 'kein/unbekannt',
                        'multiple' => false,
                        'chosen' => true,
                        'cell_class'  => 'style'
                    ],
                ],
                'zoom' => DcaUtils::buildRowWizardZoomField($strTable, 'layer'),
            ],
            'eval' => [
                'tl_class' => 'clr',
                'explanation' => &$GLOBALS['TL_LANG'][$strTable]['xpl']['layers'],
                'helpwizard' => true,
                'actions' => [
                    'copy',
                    'delete',
                    'enable',
                ],
                'min'       => 0,   // minimum rows
                'max'       => 20,  // maximum rows
                'sortable'  => true, // disable the sorting, defaults to true
            ],
            'sql' => [
                'type'      => 'text',
                'length'    => MySQLPlatform::LENGTH_LIMIT_BLOB,
                'notnull'   => false
            ],
        ],
        /**********************************************************************
         * parameters_legend
         **********************************************************************/
        # Kartenansicht zur Übernahme von Parametern
        'map'   => [
            'inputType'     => 'olmap',
            'eval'          => [
                'tl_class'  =>'w50'
            ],
        ],
        # Mittelpunkt der Karte, hier string: [x,y]
        'center' => [
            'inputType'     => 'text',
            'eval'          => [
                'mandatory' => true,
                'unique'    => false,
                'tl_class' => 'w25',
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '',
            ]
        ],
        # Extent (BoundingBox) der Karte, hier string: [[x,y],[x,y]]
        'extent' => [
            'inputType'     => 'text',
            'eval'          => [
                'mandatory' => true,
                'unique'    => false,
                'tl_class' => 'w25',
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '[]',
            ]
        ],
        # Mittelpunkt der Karte, hier [x,y]
        'zoom' => [
            'inputType'     => 'text',
            'eval'          => [
                'wizard'    => true,
                'mandatory' => true,
                'rgxp'      => 'digit',
                'unique'    => false,
                'tl_class'  => 'w25',
            ],
            'sql' => [
                'type'      => 'decimal',
                'notnull'   => true,
                'precision' => 17,
                'scale'     => 15,
                'unsigned'  => false,
                'default'   => '12.000000000000000',
                'comment'   => ''
            ],
        ],
        # Koordinatensystem, in das center_ponit transferiert werden soll
        'mapCrs' => [
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => QgisLayerModel::getFeatureProjections(),
            'eval'      => [
                // wenn addSeries true, dann muss eine Reihe angegeben werden!
                'mandatory' => true,
                'tl_class' => 'w25',
                'multiple' => false,
                'chosen' => true,
            ],
            'sql' => [
                'type'      => 'string',
                'length'    => 20,
                'fixed'     => true,
                'default'   => 'EPSG:3857',
            ]
        ],
        # padding
        'mapPadding' => [
            'inputType'     => 'text',
            'eval'          => [
                'mandatory' => true,
                'unique'    => false,
                'tl_class'  => 'w25',
                #'rgxp'      => 'custom',
                #'customRgxp'=> "/^\[(?:-?\d+(?:\.\d+)?), (?:-?\d+(?:\.\d+)?), (?:-?\d+(?:\.\d+)?), (?:-?\d+(?:\.\d+)?)\]$/iu",
                #'errorMsg'  => "Bitte geben Sie hier nur vier Koordinaten  der Form: [x1,x2,x3,x4] ein.",
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '[20,20,20,20]',
            ]
        ],
	],
];
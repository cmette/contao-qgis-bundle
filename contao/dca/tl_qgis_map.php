<?php

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
			'panelLayout'           => 'filter;sort,search,limit',
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
            '{mapconfig_legend},addOpenLayers;addOpenLayersExt;' .
            '{layers_legend},layers;' .
            '',
	],

	// Subpalettes
	'subpalettes' =>  [
        'addOpenLayers'     => 'olDistVersion,loadOpenLayersJs,laodOpenLayersCss',
        'addOpenLayersExt'  => 'loadOpenLayersExtJs,laodOpenLayersExtCss;useCompass;',
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
         * globalconfig_legend
         **********************************************************************/
        // switch add open layers
        'addOpenLayers'     => DcaUtils::buildAddField(true),
        // select between versions
        'olDistVersion'   => [
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => ['10.8.0','10.9.0'],
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
                'default'   => '10.8.0',
            ]
        ],
        // load open layers js
        'loadOpenLayersJs' => [
            'inputType'     => 'checkbox',
            'eval'          => [
                'tl_class'  => 'w16'
            ],
            'sql'   => [
                'type'      => 'boolean',
                'default'   => true
            ],
        ],
        // load open layers css
        'laodOpenLayersCss' => [
            'inputType'     => 'checkbox',
            'eval'          => [
                'tl_class'  => 'w16'
            ],
            'sql'   => [
                'type'      => 'boolean',
                'default'   => true
            ],
        ],
        // load ol-ext library
        'addOpenLayersExt'  => DcaUtils::buildAddField(true),
        // load open layers extensions js
        'loadOpenLayersExtJs' => [
            'inputType'     => 'checkbox',
            'eval'          => [
                'tl_class'  => 'w50'
            ],
            'sql'   => [
                'type'      => 'boolean',
                'default'   => true
            ],
        ],
        // load open layers extensions css
        'loadOpenLayersExtCss' => [
            'inputType'     => 'checkbox',
            'eval'          => [
                'tl_class'  => 'w50'
            ],
            'sql'   => [
                'type'      => 'boolean',
                'default'   => true
            ],
        ],
        // load open layers extensions css
        'useCompass' => [
            'inputType'     => 'checkbox',
            'eval'          => [
                'tl_class'  => 'w50'
            ],
            'sql'   => [
                'type'      => 'boolean',
                'default'   => false
            ],
        ],
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
                        'tl_class'  => 'w25'
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
                        'chosen' => true
                    ],
                ],
            ],
            'eval' => [
                'tl_class' => 'clr',
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
                'type' => 'text',
                'length' => MySQLPlatform::LENGTH_LIMIT_BLOB,
                'notnull' => false
            ],
            /**********************************************************************
             * **_legend
             **********************************************************************/
        ],
	],
];
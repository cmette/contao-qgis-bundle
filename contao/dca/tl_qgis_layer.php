<?php

use Cmette\ContaoQgisBundle\Models\QgisLayerModel;
use Cmette\ContaoQgisBundle\Utils\DcaUtils;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;

$strTable = 'tl_qgis_layer';

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
		'__selector__'  =>  ['type','source_type'],
		'default'   =>
            '{title_legend},title,style;' .
            '{type_legend},type;' .
            '',
        'Image' =>
            '{title_legend},title,style;' .
            '{type_legend},type;' .
            '{source_legend},image_source,attribution,image_source_singleSRC,image_extent;' .
            '',
        'Tile' =>
            '{title_legend},title,style;' .
            '{type_legend},type;' .
            '{source_legend},source,attribution,format;' .
            '',
        'Vector' =>
            '{title_legend},title,style;' .
            '{type_legend},type;' .
            '{source_legend},source,attribution,format;' .
            '{features_legend},source_name,data_projection,feature_projection;source_type;' .
            '',
	],

	// Subpalettes
	'subpalettes' =>  [
        'source_type_FeatureCollection' => 'features',
        'source_type_Feature' => 'feature',
    ],

	// Fields
	'fields' => [
        /**********************************************************************
         * without legend
         **********************************************************************/
        'id'        => ['sql' => "int(10) unsigned NOT NULL auto_increment"],
        'tstamp'    => ['sql' => "int(10) unsigned NOT NULL default 0",],
        'published' => DcaUtils::buildPublishedField(),
        # requires special bundle oneup/contao-backend-sortable-list-views
        #'sorting'=> ['sql' => "int(10) unsigned NOT NULL default 0",],
        /**********************************************************************
         * title_legend
         **********************************************************************/
        // layer.title
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
        # ein Style für die gesamte Ebene - Feature Styles haben Vorrang!
        'style' => [
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
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
                'tl_class' => 'w25',
                'multiple' => false,
                'chosen' => true
            ],
            'sql' => [
                'type'      => 'integer',
                'unsigned'  => true,
                'notnull'   => true,
                'default'   => 0,
            ]
        ],
        /**********************************************************************
         * type_legend
         **********************************************************************/
        # layer.type = nur QGis intern
        'type' => [
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'inputType' => 'select',
            'options'   => QgisLayerModel::getLayerTypes(),
            'reference' => &$GLOBALS['TL_LANG'][$strTable]['type_options'],
            'eval'          => [
                'mandatory' => true,
                'unique'    => false,
                'tl_class'  =>'w25'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 20,
                'fixed'     => true,
                'default'   => 'OSM',
            ]
        ],
        /**********************************************************************
         * source_legend
         **********************************************************************/
        # Datenquelle des Layers, jedes Layer hat nur eine Datenquelle
        # jeder LayerType hat ein spezifisches Set von Datenquellen-Typen

        # ImageSources ---------------------------
        'image_source' => [
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => QgisLayerModel::getSourceTypes('Image'),
            'eval'      => [
                'mandatory' => true,
                'includeBlankOption'=> false,
                'tl_class' => 'w25',
                'multiple' => false,
                'chosen' => true,
                'submitOnChange'=>true,
            ],
            'sql' => [
                'type'      => 'string',
                'length'    => 20,
                'fixed'     => true,
                'default'   => '',
            ]
        ],
        # ImageSourceUrl
        'image_source_singleSRC' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'filesOnly'  => true,
                'fieldType'  => 'radio',
                'extensions' => '%contao.image.valid_extensions%',
            ],
            'sql' => [
                'type' => 'binary',
                'length' => 16,
                'fixed' => true,
                'notnull' => false,
            ],
        ],
        # Extent (BoundingBox) der Karte, hier string: [[x,y],[x,y]]
        'image_extent' => [
            'inputType'     => 'text',
            'eval'          => [        // ToDo: regexp extent?
                'mandatory' => true,
                'unique'    => false,
                'tl_class' => 'w50',
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '[]',
            ]
        ],

        #
        'source' => [
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => QgisLayerModel::getSourceTypes('Tile'),
            'eval'      => [
                'mandatory' => true,
                'includeBlankOption'=> false,
                'tl_class' => 'w25',
                'multiple' => false,
                'chosen' => true,
                'submitOnChange'=>true,
            ],
            'sql' => [
                'type'      => 'string',
                'length'    => 20,
                'fixed'     => true,
                'default'   => '',
            ]
        ],
        /**********************************************************************
         * config_legend
         **********************************************************************/
        // Transparenz 0 ... 1
        'opacity' => [
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'inputType'     => 'text',
            'eval'          => [
                'mandatory' => true,
                'unique'    => false,
                'tl_class'  =>'w16'
            ],
            'sql'       => [
                'type'      => 'decimal',
                'notnull'   => true,
                'precision' => 16,
                'scale'     => 6,
                'unsigned'  => false,
                'default'   => '1.000000',
                'comment'   => ''
            ]
        ],

        # jede Datenquelle hat eine attribution
        'attribution' => [
            'inputType' => 'text',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'eval'      => [
                'mandatory' => false,
                'tl_class' => 'w25',
            ],
            'sql' => [
                'type'      => 'string',
                'length'    => 50,
                'fixed'     => true,
                'default'   => '',
            ]
        ],
        /**********************************************************************
         * format_legend
         **********************************************************************/
        # Format der Quelle
        'format' => [
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => QgisLayerModel::getFormatTypes(),
            #'foreignKey' => 'tl_qgis_source.title',
            #'relation'  => [
            #    'type'  => 'hasOne',
            #    'load'  => 'lazy'
            #],
            'eval'      => [
                // wenn addSeries true, dann muss eine Reihe angegeben werden!
                'mandatory' => true,
                'includeBlankOption'=> false,
                #'blankOptionLabel'  => 'kein/unbekannt',
                'tl_class' => 'w25',
                'multiple' => false,
                'chosen' => true,
                'submitOnChange' => true,
            ],
            'sql' => [
                'type'      => 'string',
                'length'    => 20,
                'fixed'     => true,
                'default'   => 'OSM',
            ]
        ],
        # dataprojection für readFeatures()
        'data_projection' => [
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => QgisLayerModel::getDataProjections(),
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
                'default'   => 'EPSG:4326',
            ]
        ],
        # dataprojection für readFeatures()
        'feature_projection' => [
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
                'default'   => 'EPSG:4326',
            ]
        ],

        /**********************************************************************
         * JSON_legend
         **********************************************************************/
        #
        'source_type' => [
            'search'    => true,
            'inputType' => 'select',
            'options'   => ['Feature','FeatureCollection'],
            'eval'      => [
                'includeBlankOption'=> false,
                'tl_class' => 'w25',
                'multiple' => false,
            ],
            'sql' => [
                'type'      => 'string',
                'length'    => 20,
                'fixed'     => true,
                'default'   => 'OSM',
            ]
        ],
        'source_name' => [
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'w25'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '',
            ]
        ],

        # speichert alle Features dieses Layers
        'features' => [
            'inputType' => 'rowWizard',
            'fields' => [
                'feature' => [
                    'label'     => &$GLOBALS['TL_LANG'][$strTable]['features_fields']['feature'],
                    'inputType' => 'select',
                    'foreignKey' => 'tl_qgis_feature.name',
                    'relation'  => [
                        'type'  => 'hasOne',
                        'load'  => 'lazy'
                    ],
                    'eval'      => [
                        // wenn addSeries true, dann muss eine Reihe angegeben werden!
                        'mandatory' => true,
                        'includeBlankOption'=> false,
                        #'blankOptionLabel'  => 'kein/unbekannt',
                        'multiple'  => false,
                        'unique'    => true,
                        'chosen'    => true,
                        'cell_class'=> 'feature'
                    ],
                ],
                'style' => [
                    'label'     => &$GLOBALS['TL_LANG'][$strTable]['features_fields']['style'],
                    'inputType' => 'select',
                    'foreignKey' => "tl_qgis_style.name",
                    'relation'  => [
                        'type'  => 'hasOne',
                        'load'  => 'lazy'
                    ],
                    'eval'      => [
                        // wenn addSeries true, dann muss eine Reihe angegeben werden!
                        'mandatory' => false,
                        'includeBlankOption'=> false,
                        'multiple' => false,
                        'chosen' => true,
                        'cell_class'=> 'style'
                    ],
                ],
                'zoom' => DcaUtils::buildRowWizardZoomField($strTable, 'features'),
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
        ],
    ],
];
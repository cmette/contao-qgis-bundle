<?php

/*
 * Feature ist ...
 *
 * ein Vektorobjekt für geografische Merkmale mit einer Geometrie und anderen Attributeigenschaften,
 * ähnlich den Funktionen in Vektordateiformaten wie GeoJSON.
 *
 * Features können in der Kartenansicht mit setStyle individuell gestaltet werden; ansonsten
 * verwenden sie den Stil ihrer Vektorebene.
 * Beachten Sie, dass Attributeigenschaften Eigenschaften des BaseObject Feature sind.
 * Dadurch sind sie observable und erhalten Getter/Setter-Accessors.
 *
 * Typischerweise hat ein Feature eine einzige Geometrieeigenschaft.
 * Sie können die Geometrie unter Verwendung der setGeometry-Methode festlegen und mit getGeometry abfragen.
 *
 * Es ist möglich, mehr als eine Geometrie auf einem Feature zu speichern, indem ein anderes Attribut
 * als geomtery verwendet wird. Standardmäßig wird die für das Rendering verwendete Geometrieaber
 * im Attribute geometry erwartet.
 *
 * {
        "type": "Feature",
        "properties": {"id": "10", "label": "Georeferenziertes Gebiet"},
        "geometry": {
            "type": "MultiPolygon",
            "coordinates": [[[[13.864511036229285, 51.426322263016623], [13.855416574434182, 51.425075854596571], [13.853380013792695, 51.430753539201142], [13.862480151423076, 51.432000898553966], [13.864511036229285, 51.426322263016623]]]]
        }
    }
 */

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;

$strTable = 'tl_qgis_feature';

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
			'fields'                => ['name'],
			'headerFields'          => ['name'],
			'panelLayout'           => 'filter;sort,search,limit',
            'defaultSearchField'    => 'name',
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
        'global_operations' => [
            'importJson' => [
                'label' => &$GLOBALS['TL_LANG'][$strTable]['importJson'],
                'href'  => 'key=importJson',
                'class' => 'header_my_op',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
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
		'__selector__'  =>  [],
		'default'       =>
            '{name_legend},name,import;' .
            '{properties_legend},properties;' .
            '{style_legend},style;' .
            '{label_legend},useAsLabel;' .
            '{geometry_legend},geometry_type,geometry_coordinates;' .
            '',
	],

	// Subpalettes
	'subpalettes' =>  [],

	// Fields
	'fields' => [
        /**********************************************************************
         * without legend
         **********************************************************************/
        'id'        => ['sql' => "int(10) unsigned NOT NULL auto_increment"],
        'tstamp'    => ['sql' => "int(10) unsigned NOT NULL default 0",],
        'published' => ['toggle' => true,'inputType' => 'checkbox','sql' => ['type' => 'boolean', 'default' => false],],
        # requires special bundle oneup/contao-backend-sortable-list-views
        #'sorting'=> ['sql' => "int(10) unsigned NOT NULL default 0",],
        /**********************************************************************
         * name_legend
         **********************************************************************/
        # Name ders Features - nicht vorgeschrieben, ich verwende es aber intern
        'name' => [
            'inputType' => 'text',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'eval'          => [
                'mandatory' => true,
                'unique'    => false,
                'tl_class'  =>'w50'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '',
            ]
        ],
        'import' => [
            'label' => 'label',
            'eval'          => [
                'tl_class'  =>'w50'
            ],
        ],
        /**********************************************************************
         * property_legend
         **********************************************************************/
        # list of layers
        'properties' => [
            'inputType' => 'text',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'eval'          => [
                'mandatory' => true,
                'unique'    => true,
                'tl_class'  =>'w50'
            ],
            'sql' => [
                'type' => 'text',
                'length' => MySQLPlatform::LENGTH_LIMIT_BLOB,
                'notnull' => false
            ],
        ],
        /**********************************************************************
         * style_legend
         **********************************************************************/
        # ein Style für dieses Feature - Feature Styles haben Vorrang vor Layer-Styles!
        'style' => [
            'search'    => true,
            'filter'    => true,
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
                'includeBlankOption'=> false,
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
         * label_legend
         **********************************************************************/
        'useAsLabel' => [
            'inputType'     => 'radioTable',
            'options'       => ['useTitle', 'useProperty', '2rwf', '3rw'],
            'reference'     => &$GLOBALS['TL_LANG'][$strTable]['useAsLabel'],
            'eval'          => [
                'helpwizard'    => true,
                'cols'          =>4,
                'submitOnChange'=> false
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => 'useTitle',
            ]
        ],
        /**********************************************************************
         * geometry_legend
         **********************************************************************/
        # Geometrie Daten
        'geometry_type' => [
            'label'     => &$GLOBALS['TL_LANG'][$strTable]['geometry_fields']['type'],
            'search'    => true,
            'filter'    => true,
            'sorting'   => true,
            'inputType' => 'select',
            'options'   => ['Point','MultiPoint','LineString','MultiLineString','Polygon','MultiPolygon','GeometryCollection'],
            'eval'          => [
                'mandatory' => true,
                'tl_class'  => 'w25'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '',
            ]
        ],
        'geometry_coordinates' => [
            'label'     => &$GLOBALS['TL_LANG'][$strTable]['geometry_fields']['coordinates'],
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => [
                'mandatory' => true,
                'unique'    => false,
            ],
            'sql'       => [
                'type' => 'text',
                'length' => MySQLPlatform::LENGTH_LIMIT_BLOB,
                'notnull' => false
            ]
        ],
        /**********************************************************************
         * **_legend
         **********************************************************************/
	],
];
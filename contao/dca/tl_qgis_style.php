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

use Cmette\ContaoQgisBundle\Utils\DcaUtils;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;

$strTable = 'tl_qgis_style';

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
		'operations' =>  [
            'edit',
            '!copy',
            'activate',
            '!delete',
            'toggle',
        ],
	],

	// Palettes
	'palettes' =>  [
		'__selector__'  =>  [],
		'default'       =>
            '{name_legend},name;' .
            '{stroke_legend},stroke_color,stroke_alpha,width;lineCap,lineJoin,miterLimit;lineDash,lineDashOffset;' .
            '{fill_legend},fill_color,fill_alpha;' .
            '{image_legend},image_type;' .
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
        'published' => DcaUtils::buildPublishedField(),
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
        /**********************************************************************
         * stroke_legend
         **********************************************************************/
        # color: string | Color — Strichfarbe (z. B. '#ff0000' oder 'rgba(255,0,0,0.8)')
        'stroke_color'     => [
            'inputType' => 'text',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'eval'          => [
                'maxlength'     => 6,
                'colorpicker'   => true,
                'isHexColor'    => true,
                'decodeEntities'=> true,
                'tl_class'      => 'w16 wizard'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 50,
                'fixed'     => true,
                'default'   => '',
            ],
        ],
        # stroke alpha 0...1
        'stroke_alpha'    => [
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 6,
                'rgxp'      => 'decimal',
                'decodeEntities' => true,
                'tl_class'  => 'w16',
                'minval'    => 0,
                'maxval'    => 1,
            ],
            'sql' => [
                'type'      => 'decimal',
                'notnull'   => true,
                'precision' => 4,
                'scale'     => 3,
                'unsigned'  => false,
                'default'   => '1.000',
                'comment'   => ''
            ],
        ],
        # width: number — Strichstärke in Pixel z.B. 'rgba(255,127,0,1.0)',
        'width'     => [    # hier eine Funktion m2px(1.26)}),
            'inputType' => 'text',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'eval'          => [
                'mandatory' => false,
                'unique'    => false,
                'tl_class'  =>'w16'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 50,
                'fixed'     => true,
                'default'   => '',
            ]
        ],
        # lineCap: 'butt' | 'round' | 'square' — Linienendstil
        'lineCap'   => [    # 'butt',
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => ['butt','round','square'],
            'reference' => &$GLOBALS['TL_LANG'][$strTable]['lineCap_options'],
            'eval'          => [
                'mandatory' => false,
                'unique'    => false,
                'tl_class'  =>'w16'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 6,
                'fixed'     => true,
                'default'   => 'butt',
            ]
        ],
        # lineJoin: 'miter' | 'round' | 'bevel' — Eckverbindungstyp
        'lineJoin'  => [    # 'miter',
            'inputType' => 'select',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'options'   => ['miter','round','bevel' ],
            'reference' => &$GLOBALS['TL_LANG'][$strTable]['lineJoin_options'],
            'eval'          => [
                'mandatory' => false,
                'unique'    => false,
                'tl_class'  =>'w16'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 5,
                'fixed'     => true,
                'default'   => 'miter',
            ]
        ],
        # number — Miter-Limit (nur relevant bei lineJoin = 'miter')
        'miterLimit'    => [
            'inputType' => 'text',
            'eval'      => [
                'rgxp'      => 'natural',
                'tl_class'  => 'w16'
            ],
            'sql' => [
                'type'    => 'smallint',
                'length'  => 5,
                'unsigned'=> true,
                'notnull' => true,
                'default' => 0,
            ],
        ],
        # lineDash: number[] — Muster für gestrichelte Linien (z. B. ein Array [10, 6])
        'lineDash'  => [    # null,
            'inputType' => 'text',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'eval'          => [
                'mandatory' => true,
                'unique'    => false,
                'tl_class'  =>'w33',
                'rgxp'      => 'custom',
                'customRgxp'=> '/^\[\d*\]$/',
                'errorMsg'  => &$GLOBALS['TL_LANG'][$strTable]['lineDash_rgxp_error'],
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 255,
                'fixed'     => true,
                'default'   => '[]',
            ],
        ],
        # lineDashOffset: number — Versatz für das Strichmuster
        'lineDashOffset'    => [
            'inputType' => 'text',
            'eval'      => [
                'rgxp'      => 'natural',
                'tl_class'  => 'w16'
            ],
            'sql' => [
                'type'    => 'smallint',
                'length'  => 5,
                'unsigned'=> true,
                'notnull' => true,
                'default' => 0,
            ],
        ],
        /**********************************************************************
         * fill_legend
         **********************************************************************/
        # fill unterstützt nur color
        'fill_color'     => [
            'inputType' => 'text',
            'search'    => true,
            'filter'    => false,
            'sorting'   => true,
            'eval'          => [
                'maxlength'     => 6,
                'colorpicker'   => true,
                'isHexColor'    => true,
                'decodeEntities'=> true,
                'tl_class'      => 'w16 wizard'
            ],
            'sql'       => [
                'type'      => 'string',
                'length'    => 6,
                'fixed'     => true,
                'default'   => '',
            ],
        ],
        # fill alpha 0...1
        'fill_alpha'    => [
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 6,
                'rgxp'      => 'decimal',
                'decodeEntities' => true,
                'tl_class'  => 'w16',
                'minval'    => 0,
                'maxval'    => 1,
            ],
            'sql' => [
                'type'      => 'decimal',
                'notnull'   => true,
                'precision' => 4,
                'scale'     => 3,
                'unsigned'  => false,
                'default'   => '1.000',
                'comment'   => ''
            ],
        ],
	],
];
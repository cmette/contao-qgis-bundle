<?php

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;

$strTableName = 'tl_qgis_tag';

System::loadLanguageFile($strTableName);

$GLOBALS['TL_DCA'][$strTableName] = [
	// Config
	'config' =>  [
		'dataContainer'     => DC_Table::class,
		'enableVersioning'  => false,
		'sql' =>  [
			'keys' =>  [
				'id' => 'primary',
			]
		]
	],

	// List
	'list' => [
		'sorting' =>  [
			'mode'          => DataContainer::MODE_SORTED,
            'flag'          => 1,
			'fields'        =>  ['name'],
			'headerFields'  =>  ['id', 'name'],
			'panelLayout'   => 'filter;sort,search,limit',
		],
		'label' =>  [
			'fields' =>  ['id', 'name'],
			'format' => '%s <span style="color:#999;padding-left:3px">%s</span>',
		],
		'global_operations' => [
			'all' => [
				'href'      => 'act=select',
				'class'     => 'header_edit_all',
				'attributes'=> 'onclick="Backend.getScrollOffset()" accesskey="e"'
			]
		],
		'operations' =>  [
			'edit',
			'!delete',
		]
	],

	// Palettes
	'palettes' =>  [
		'__selector__'  =>  [],
		'default'       =>
            '{title_legend},id,tstamp,name;'
	],

	// Subpalettes
	'subpalettes' =>  [],

	// Fields
	'fields' => [
        'id'        => [
            'sql'       => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'    => [
            'sql'       => "int(10) unsigned NOT NULL default 0",
        ],
        'name'      => [
            'search'    => true,
            'inputType' => 'text',
            'eval'      =>  ['mandatory' => true, 'maxlength' => 100, 'tl_class' => 'w25', 'unique' => true],
            'sql'       => "varchar(100) NOT NULL default ''"
        ],
	]
];
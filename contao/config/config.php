<?php

use Cmette\ContaoQgisBundle\Models\QgisFeatureModel;
use Cmette\ContaoQgisBundle\Models\QgisStyleModel;
use Cmette\ContaoQgisBundle\Models\QgisLayerModel;
use Cmette\ContaoQgisBundle\Models\QgisMapModel;

use Cmette\ContaoQgisBundle\Widget\Backend\OlMapWidget;
use Contao\ArrayUtil;
use Contao\System;

use Symfony\Component\HttpFoundation\Request;

$currentRequest     = System::getContainer()->get('request_stack')->getCurrentRequest();
$scopeMatcher       = System::getContainer()->get('contao.routing.scope_matcher');
$isBackendRequest   = $scopeMatcher->isBackendRequest($currentRequest ?? Request::create(''));
$isFrontendRequest  = $scopeMatcher->isFrontendRequest($currentRequest ?? Request::create(''));

$assetsDir          = "bundles/contaoqgis";

$moduleQgis = [
    'qgis' => [
        'qgis_maps' => [
            'tables'        => ['tl_qgis_map'],
            'stylesheet'    => ["$assetsDir/scss/qgis.css"],
            //'javascript'    => ["$assetsDir/js/resumable/resumable.js", "$assetsDir/js/SupervisorResumableWidget.js.twig"],

            // permission checks are always executed
            //'disablePermissionChecks' => false
            // module is always shown in the navigation.
        ],
        'qgis_layers' => [
            'tables'        => ['tl_qgis_layer'],
            'stylesheet'    => ["$assetsDir/scss/qgis.css"],
        ],
        'qgis_features' => [
            'tables'        => ['tl_qgis_feature'],
            'stylesheet'    => ["$assetsDir/scss/qgis.css"],
        ],
        'qgis_styles' => [
            'tables'        => ['tl_qgis_style'],
            'stylesheet'    => ["$assetsDir/scss/qgis.css"],
        ],
        'qgis_tags' => [
            'tables'        => ['tl_qgis_tag'],
            'stylesheet'    => ["$assetsDir/scss/qgis.css"],
        ],
    ],
];

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'],0, $moduleQgis);

// Front end modules
#$GLOBALS['FE_MOD']['qgis'] = array
#(
#	'pedigree_module'   => SupervisorFrontendModuleController::class
#);

// Add permissions
$GLOBALS['TL_PERMISSIONS'][] = 'ped_conf';
$GLOBALS['TL_PERMISSIONS'][] = 'ped_tree';

// register backend widgets
$GLOBALS['BE_FFL']['olmap']   = OlMapWidget::class;

// register model classes
$GLOBALS['TL_MODELS']['tl_qgis_map']     = QgisMapModel::class;
$GLOBALS['TL_MODELS']['tl_qgis_layer']   = QgisLayerModel::class;
$GLOBALS['TL_MODELS']['tl_qgis_feature'] = QgisFeatureModel::class;
$GLOBALS['TL_MODELS']['tl_qgis_style']   = QgisStyleModel::class;

// Style sheet
$GLOBALS['TL_CSS'][] = "$assetsDir/scss/qgis.css";
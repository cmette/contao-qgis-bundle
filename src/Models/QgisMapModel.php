<?php

declare(strict_types=1);

/*
 * This file is part of the Contao QGis Bundle.
 *
 * (c) Christian Mette
 *
 * @license LGPL-3.0-or-later
 */

namespace Cmette\ContaoQgisBundle\Models;

use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;

/**
 * Reads and writes source entities. This refers to abstract sources such as
 * literary sources, maps, manuscripts, photos, etc.
 *
 * @property int $id
 * @property int $tstamp
 *
 * @method static QgisMapModel|null                                findById($id, array $opt=array())
 * @method static Collection|array<QgisMapModel>|QgisMapModel|null findByPid($val, array $opt = [])
 */
class QgisMapModel extends Model
{
    use ModelHelperTrait;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_qgis_map';

    public function getLayers():Collection|null
    {
        $arrLayers  = StringUtil::deserialize($this->layers, true);
        $collLayers = [];

        if (!empty($arrLayers)) {
            foreach ($arrLayers as $itemLayer) {
                if($objLayer = QgisLayerModel::findById($itemLayer['layer'])) {
                    if($this->isFrontendRequest() && $itemLayer['enable'] === '1' && $objLayer->published) {
                        $collLayers[] = $objLayer;
                    } elseif ($this->isBackendRequest()) {
                        $collLayers[] = $objLayer;
                    } else {
                        // no answer
                    }
                } else {
                    // layer deleted or not defined
                }
            }
        } else {
            // no layers defined for this map
        }

        return new Collection($collLayers, self::$strTable);
    }

    public function getAllStyles():Collection|null
    {
        $arrStyles = [];

        // aggregate all map layers
        if($layers = $this->getLayers()) {
            /* @var QgisLayerModel $layer*/
            foreach ($layers as $layer) {
                // 1. get the layer style
                if($layer->style > 0 && ($style = QgisStyleModel::findById($layer->style))) $arrStyles[$style->id] = $style;
                // 2. get all layer features
                if($layerFeatures = $layer->getAllFeaturesAsCollection())
                    // aggregate all feature styles
                    foreach ($layerFeatures as $layerFeature) {
                        // get the feature style
                        if($style = QgisStyleModel::findById($layerFeature->style)) {
                            $arrStyles[$style->id] = $style;
                        };
                    };
            }
        };

        ksort($arrStyles);

        $styleCollection = new Collection($arrStyles, 'tl_qgis_style');

        return $styleCollection;
    }
}

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

use Contao\ContentModel;
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

    public function getCenter()
    {
        return ($this->tstamp && !empty($this->center)) ? (string)$this->center : '[0.0, 0.0]'; // ToDo: configurable center
    }

    public function getZoom()
    {
        return ($this->tstamp && !empty($this->zoom)) ? $this->zoom : '13.0'; // ToDo: configurable zoom
    }

    /**
     * @param $layerId
     * @return QgisLayerModel|null
     */
    public function getLayer($layerId):QgisLayerModel|null
    {
        /* @var QgisLayerModel $objLayer */
        $objLayer = QgisLayerModel::findById($layerId);

        if($objLayer) {
            return ($this->isFrontendRequest() && !$objLayer->published) ? null : $objLayer;
        }

        return $objLayer;
    }

    /**
     * liefert alle Layer, die in einer Map verwendet werden
     *
     * @return Collection|null
     */
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

    /**
     * liefert alle styles, die in einer Map verwendet werden
     *
     * @return Collection|null
     */
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

    public function getExtent(): array
    {
        $arrLayers = StringUtil::deserialize($this->layers, true);

        if(count($arrLayers) > 0) {
            foreach ($arrLayers as $layer) {
                /* @var QgisLayerModel $objLayer */
                $objLayer = QgisLayerModel::findById($layer['layer']);  // ToDo: wenn Layer gelöscht?

                if (!array_key_exists('zoom', $layer)) {
                    return ['mode' => 'params'];
                }

                switch ($layer['zoom']) {
                    case 'params':
                        $zoom = ['mode' => 'params'];
                        break;
                    case 'layer':
                        $zoom = ['mode' => 'layer', 'source' => $objLayer->type, 'var' => "layer_{$layer['layer']}",];
                        break;
                    case 'feature':
                        /* @var QgisFeatureModel $collFeatures */
                        $collFeatures = $objLayer->getAllFeaturesAsCollection(true, ['combine']);
                        if ($collFeatures->count() > 0) {
                            $mode = 'feature';
                            $extent = json_encode(QgisFeatureModel::getExtentFromFeatures($collFeatures));
                            $zoom = ['mode' => 'feature', 'extent' => $extent];
                        } else {
                            $zoom = ['mode' => 'layer', 'source' => $objLayer->type, 'var' => "layer_{$layer['layer']}",];
                        }
                        break;
                    default:
                        $zoom = ['mode' => 'params'];
                }
            };
        } else {
            $zoom = ['mode' => 'params'];
        }

        return $zoom;
    }

    public function getPropertiesFromData($contentId): string|null
    {
        $result = [];

        $objContent = ContentModel::findById($contentId);
        $result = json_encode(StringUtil::deserialize($objContent->listProperties, true), JSON_UNESCAPED_UNICODE);

dump($result);
        return $result;
    }
}

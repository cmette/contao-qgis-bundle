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
 * @method static QgisMapModel|null findById($id, array $opt=array())
 * @method static Collection|array<QgisMapModel>|QgisMapModel|null findByPid($val, array $opt = [])
 */
class QgisLayerModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_qgis_layer';

    /*
        A layer is a visual representation of data from a source. OpenLayers has four basic types of layers:

        ol/layer/Tile - Renders sources that provide tiled images in grids that are organized by zoom levels for specific resolutions.
        ol/layer/Image - Renders sources that provide map images at arbitrary extents and resolutions.
        ol/layer/Vector - Renders vector data client-side.
        ol/layer/VectorTile - Renders data that is provided as vector tiles.
    */
    private const LAYER_TYPES = [
        'Tile',
        'Image',
        'Vector',
        'VectorTile'
    ];

    private const SOURCE_TYPES = [
        'GeoTIFF',
        'Google',
        'IIIF',
        'Image',
        'OSM',
        'Raster',
        'Source',
        'Tile',
        'Vector',
        'WMTS',
        'XYZ',
    ];

    private const FORMAT_TYPES = [
        'GML',
        'GeoJSON'
    ];

    private const DATA_PROJECTIONS = [
        'EPSG:3857',
        'EPSG:4326',
    ];

    private const FEATURE_PROJECTIONS = [
        'EPSG:3857',
        'EPSG:4326',
    ];


    public static function getLayerTypes(): array
    {
        return self::LAYER_TYPES;
    }

    public static function getSourceTypes(): array
    {
        return self::SOURCE_TYPES;
    }

    public static function getFormatTypes(): array
    {
        return self::FORMAT_TYPES;
    }

    public static function getDataProjections(): array
    {
        return self::DATA_PROJECTIONS;
    }

    public static function getFeatureProjections(): array
    {
        return self::FEATURE_PROJECTIONS;
    }

    public function getAllFeaturesAsJson(string|null $name = null):string
    {
        /**
         * type MultiPolygon:
         *
         * MultiPolygon ist ein Array von Polygonen.
         * Ein Polygon ist ein Array von LinearRings.
         * Ein LinearRing ist ein Array von Koordinatenpunkten [x, y] (oder [x, y, z]).
         * Jeder LinearRing muss geschlossen sein: erster Punkt = letzter Punkt.
         * Achtung! auch die dritte Dimension muss geschlossen sein!
         */

        $arrFeatures = (StringUtil::deserialize($this->features, true));
        $strFeatures = '';

        foreach ($arrFeatures as $feature)
        {
            if ($feature['enable'] === '1')
            {
                if($objFeature = QgisFeatureModel::findById($feature['feature']))
                {
                    if($objFeature->published)
                    {
                        $properties = html_entity_decode($objFeature->properties);
                        $_name = empty($name) ? $objFeature->name : $name;

                        $objStyle = $objFeature->getRelated('style');
                        $_styleId = ($objStyle) ? $objStyle->id : 0;

                        $strFeatures .= "{
            \"type\": \"Feature\",
            \"name\": \"$_name\",
            \"styleId\": \"$_styleId\",
            \"properties\": {$properties},
            \"geometry\": {
                \"type\": \"{$objFeature->geometry_type}\",
                \"coordinates\": {$objFeature->geometry_coordinates}
            }
    },";
                    } else {
                        // Feature Datensatz ist unpublished
                    }
                } else {
                    // Feature not found ToDo: ?
                }
            } else {
                // Feature ist 'disabled' auf dieser Ebene ToDo: ?
            }
        }

        $literalJSONObject = <<< EOJ
{
    "type": "$this->source_type",
    "name": "$this->source_name",
    "features": [$strFeatures]
}
EOJ;

        return $literalJSONObject;
    }

    /**
     * @return array
     */
    public function getAllFeaturesAsCollection(bool $enable = true, array $arrZoomFilter = ['parent','extent','combine']): Collection
    {
        $arrFeatures = [];
        // filtere  nach enabled und zoom
        $arrRowFeatures = array_values(
            array_filter(
                array_map(function ($feature) use ($enable, $arrZoomFilter) {
                    if($feature['enable'] === ($enable?'1':'0') && in_array($feature['zoom'], $arrZoomFilter)) { return $feature; }
                },
                    StringUtil::deserialize($this->features, true)
                )
            )
        );

        if(count($arrRowFeatures) > 0)
            foreach ($arrRowFeatures as $feature)
                if($objFeature = QgisFeatureModel::findById($feature['feature'])) $arrFeatures[] = $objFeature;

        $collection = new Collection($arrFeatures, 'tl_qgis_feature');

        return $collection;
    }

    /**
     * @return int
     */
    public function countUsage(): int
    {
        $id = $this->id;
        $len = \strlen((string) $id);

        $entities = QgisMapModel::findBy(["layers LIKE '%\"layer\";s:$len:\"$id\"%'"], []);

        return null !== $entities ? $entities->count() : 0;
    }
}

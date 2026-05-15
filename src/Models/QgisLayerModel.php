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

use Cmette\ContaoSourcesBundle\Models\SourcesEntityModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Symplify\EasyCodingStandard\Console\Output\JsonOutputFormatter;

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

    public function getAllFeatures(string $name):string
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

        foreach ($arrFeatures as $feature) {
            if ($feature['enable'] === '1') {
                if($objFeature = QgisFeatureModel::findById($feature['feature'])) {
                    $properties = html_entity_decode($objFeature->properties);

                    $strFeatures .= "{
            \"type\": \"Feature\",
            \"name\": \"$name\",
            \"properties\": {$properties},
            \"geometry\": {
                \"type\": \"{$objFeature->geometry_type}\",
                \"coordinates\": {$objFeature->geometry_coordinates}
            }
    },";
                } else {
                    // Feature not found
                }
            }
        }

        $literalJSONObject = <<< EOJ
{
    "type": "$this->source_type",
    "name": "$this->source_name",
    "features": [$strFeatures]
}
EOJ;
dump($literalJSONObject);
        return $literalJSONObject;
    }

    public function getFeatures(): array
    {
        $arrFeatures = (StringUtil::deserialize($this->features, true));

        return $arrFeatures;
    }

    public function countUsage(): int
    {
        $id = $this->id;
        $len = \strlen((string) $id);

        $entities = QgisMapModel::findBy(["layers LIKE '%\"layer\";s:$len:\"$id\"%'"], []);

        return null !== $entities ? $entities->count() : 0;
    }
}

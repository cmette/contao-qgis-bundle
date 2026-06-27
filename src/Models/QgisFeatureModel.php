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

/**
 * Reads and writes ol/Features.
 *
 * @property int $id
 * @property int $tstamp
 *
 * @method static QgisFeatureModel|null                                findById($id, array $opt=array())
 * @method static Collection|array<QgisFeatureModel>|QgisFeatureModel|null findByPid($val, array $opt = [])
 */
class QgisFeatureModel extends Model
{
    use ModelHelperTrait;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_qgis_feature';

    public function countUsage(): int
    {
        $id = $this->id;
        $len = \strlen((string) $id);

        $entities = QgisLayerModel::findBy(["features LIKE '%\"feature\";s:$len:\"$id\"%'"], []);

        return null !== $entities ? $entities->count() : 0;
    }






    public static function createEmptyExtent() {
        return [INF, INF, -INF, -INF]; // [minX, minY, maxX, maxY]
    }

    public static function extendExtent(array &$extent, array $env) {
        $extent[0] = min($extent[0], $env[0]);
        $extent[1] = min($extent[1], $env[1]);
        $extent[2] = max($extent[2], $env[2]);
        $extent[3] = max($extent[3], $env[3]);
    }

    public static function geomExtent(array $coords) {
dump($coords);
        // coords = [[x,y], [x,y], ...] for line/polygon; point = [[x,y]]
        $minX = INF; $minY = INF; $maxX = -INF; $maxY = -INF;
        foreach ($coords as $c) {
            $x = $c[0]; $y = $c[1];
            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x);
            $maxY = max($maxY, $y);
        }
        return [$minX, $minY, $maxX, $maxY];
    }

    public static function geomExtentMultiPolygon(array $multiPolygon): array {
        $minX = INF; $minY = INF; $maxX = -INF; $maxY = -INF;
        foreach ($multiPolygon as $polygon) {
            foreach ($polygon as $ring) {
                foreach ($ring as $pos) {
                    $x = $pos[0]; $y = $pos[1];
                    if ($x < $minX) $minX = $x;
                    if ($y < $minY) $minY = $y;
                    if ($x > $maxX) $maxX = $x;
                    if ($y > $maxY) $maxY = $y;
                }
            }
        }
        if ($minX === INF) {
            return [0,0,0,0]; // oder null/[] nach Wunsch
        }
        return [$minX, $minY, $maxX, $maxY];
    }


    /* Beispiel: mehrere Geometrien zusammenführen
    $features = [
    [[0,0],[1,1]],         // Line/Polygon ring simplified
    [[2,2]],               // Point
    [[-1,0],[0,2]]
    ];
    $combined = createEmptyExtent();
    foreach ($features as $coords) {
    $env = geomExtent($coords);
    extendExtent($combined, $env);
    }
    print_r($combined);
    */
    public static function getExtentFromFeatures(Collection $features)
    {
        $combined = self::createEmptyExtent();

        foreach ($features as $feature) {
dump($feature->name);
dump(json_encode($feature->geometry_coordinates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $env = self::geomExtentMultiPolygon(json_decode($feature->geometry_coordinates, true));
#dump($env);
            self::extendExtent($combined, $env);
        }
dump($combined);
        return $combined;
    }
}

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
 * Reads and writes ol/Features.
 *
 * @property int $id
 * @property int $tstamp
 *
 * @method static QgisStyleModel|null                                findById($id, array $opt=array())
 * @method static Collection|array<QgisStyleModel>|QgisStyleModel|null findByPid($val, array $opt = [])
 */
class QgisStyleModel extends Model
{
    use ModelHelperTrait;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_qgis_style';

    /**
     * erwartet einen Feldnamen wie z.B. 'stroke' und
     * wandelt dann die Feldinhalte von
     * stroke_color (HEX ohne #) und stroke_alpha (0..1) in ein rgba-String um
     *
     * @param string $field
     * @return string
     */
    public function getRgba(string $field): string
    {
        $colorField = "{$field}_color";
        $alphaField = "{$field}_alpha";
        $color = $this->$colorField;
        $alpha = $this->$alphaField;

        [$r,$g,$b] = sscanf("#$color", "#%02x%02x%02x");

        return "rgba($r,$g,$b,$alpha)";
    }




    public function countUsage(): int
    {
        $id = $this->id;
        $len = \strlen((string) $id);

        $inMaps = QgisMapModel::countBy(["layers LIKE '%\"style\";s:$len:\"$id\"%'"], []);
        $inLayers = QgisLayerModel::countBy(["features LIKE '%\"style\";s:$len:\"$id\"%'"], []);
        $inStyles = QgisFeatureModel::countBy('style', $this  ->id);

#dump("in Maps: {$inMaps} in Layers: {$inLayers} in Styles: {$inStyles}");
        return $inMaps + $inLayers + $inStyles;
    }
}

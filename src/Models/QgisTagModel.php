<?php

declare(strict_types=1);

/*
 * Contao Pedigree Bundle for Contao Open Source CMS
 *
 * Copyright (c) 2023 C. Mette
 *
 * @package    contao-pedigree-bundle
 * @link       https://github.com/cmette/contao-pedigree-bundle
 * @license    LGPL-3.0-or-later
 * @author     Christian Mette
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmette\ContaoQgisBundle\Models;

use Contao\Model;
use Contao\Model\Collection;

/**
 * Reads and writes tags.
 */
class QgisTagModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_qgis_tag';

    /**
     * @return int
     */
    public function countUsage(): int
    {
        $id = $this->id;
        $len = \strlen((string) $id);

        #$inMaps    = QgisMapModel::countBy(["layers LIKE '%\"style\";s:$len:\"$id\"%'"], []);
        #$inLayers  = QgisLayerModel::countBy(["features LIKE '%\"style\";s:$len:\"$id\"%'"], []);
        $inFeatures = QgisFeatureModel::countBy(["tags LIKE '%;s:$len:\"$id\"%'"], []);
        #$inStyles  = QgisFeatureModel::countBy('style', $this  ->id);

#dump("in Maps: {$inMaps} in Layers: {$inLayers} in Styles: {$inStyles}");
        return $inFeatures;
    }
}

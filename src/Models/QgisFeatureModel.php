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
}

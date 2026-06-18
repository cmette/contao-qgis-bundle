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

namespace Cmette\ContaoQgisBundle\EventListener\DataContainer;

use Cmette\ContaoQgisBundle\Models\QgisStyleModel;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Message;

class QgisFeatureListener
{
    use QgisListenerHelperTrait;
    private const STR_TABLE = 'tl_qgis_feature';

    public string $requestToken = '';

    public function __construct(
        private readonly ContaoCsrfTokenManager $tokenManager,
        #private readonly AuthorizationCheckerInterface $authorizationChecker,
    )
    {
        $this->requestToken = htmlspecialchars($this->tokenManager->getDefaultTokenValue());
    }

    #[AsCallback(table: self::STR_TABLE, target: 'list.label.group')]
    public function ListLabelGroupCallback(string $group, string|null $mode, string $field, array $row, DataContainer $dc): string
    {
        return $row['name'][0];
    }

    /**
     * @param array  $row   record data
     * @param string $label current label
     *
     * @return array
     */
    #[AsCallback(table: self::STR_TABLE, target: 'list.label.label')]
    public function ListLabelLabelCallback(array $row, string $label, DataContainer $dc, array $labels): array|string
    {
        $title  = $row['name'];
        $type   = $row['geometry_type'];

        $style  = QgisStyleModel::getStyleBox($row['style']);

        return $this->buildLabelWithCounter($row, "{$title} [{$type}]", $style);
    }

    /**
     * controls the delete button depending on the use of the author
     */
    #[AsCallback(table: self::STR_TABLE, target: 'list.operations.delete.button')]
    public function listDeleteButton(DataContainerOperation $operation): void
    {
        $this->handleDeleteButton($operation);
    }


    #[AsCallback(table: self::STR_TABLE, target: 'fields.geometry_coordinates.save')]
    public function geometryCoordinatesSave(mixed $value, DataContainer $dc): mixed
    {
        $withoutSpaces = str_replace(' ', '', $value);
        $countR = mb_substr_count($withoutSpaces, "[", 'UTF-8');
        $countL = mb_substr_count($withoutSpaces, "]", 'UTF-8');

        if ($countR > $countL) {
            #Message::addError("Es fehlt eine ] Linksklammer");
            throw new \Exception("Es fehlt eine ] Linksklammer");
        } elseif ($countR < $countL) {
            #Message::addError("Es fehlt eine [ Rechtslammer");
            throw new \Exception("Es fehlt eine [ Rechtsklammer");
        }


        return $withoutSpaces;
    }
}

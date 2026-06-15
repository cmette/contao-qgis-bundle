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

use Contao\Controller;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Image;
use Contao\Message;
use Contao\Model;
use Contao\StringUtil;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class QgisLayerListener
{
    use QgisListenerHelperTrait;
    private const STR_TABLE = 'tl_qgis_layer';

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
        return $row['title'][0];
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
        $title  = $row['title'];
        $type   = $row['type'];

        return $this->buildLabelWithCounter($row, "{$title}{$type}");
    }

    /**
     * controls the delete button depending on the use of the author
     */
    #[AsCallback(table: self::STR_TABLE, target: 'list.operations.delete.button')]
    public function listDeleteButton(DataContainerOperation $operation): void
    {
        $this->handleDeleteButton($operation);
    }


    #[AsCallback(table: self::STR_TABLE, target: 'fields.features.save')]
    public function fieldsFeaturesSave(mixed $value, DataContainer $dc): mixed
    {
        // hole alle featureIDs
        $featureIds = array_filter(array_map(function ($feature) { return $feature['feature']; }, StringUtil::deserialize($value, true)));
        // zähle Vorkommen aller Werte
        $counts = array_count_values($featureIds);
        // filtere mehr als 1 Vorkommen
        $duplicates = array_keys(array_filter($counts, function($c) { return $c > 1; }));
        //
        if(count($duplicates) > 0) Message::addError("Ein oder mehrere Features werden mehrfach angezeigt. Bitte prüfen Sie die Liste der Features.");

        return $value;
    }
}

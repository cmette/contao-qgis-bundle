<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Sources Bundle.
 *
 * (c) Christian Mette
 *
 * @license LGPL-3.0-or-later
 */

namespace Cmette\ContaoQgisBundle\Utils;

use Contao\DataContainer;

class DcaUtils
{
    public static function buildPublishedField(): array
    {
        return [
            'flag' => DataContainer::SORT_INITIAL_LETTER_DESC,
            'inputType' => 'checkbox',
            'toggle'    => true,
            'eval' => [
                'doNotCopy' => true,
            ],
            'sql' => [
                'type' => 'boolean',
                'default' => false,
            ],
        ];
    }

    /**
     * @param bool $search
     * @param bool $filter
     * @param bool $sorting
     * @param bool $submitOnChange
     * @param string $tl_class
     * @param bool $default
     * @return array
     */
    public static function buildAddField(
        bool $search = false,
        bool $filter = false,
        bool $sorting = false,
        bool $submitOnChange = true,
        string $tl_class = 'w16',
        bool $default = false): array
    {
        return [
            'inputType' => 'checkbox',
            'search'    => $search,
            'filter'    => $filter,
            'sorting'   => $sorting,
            'eval' => [
                'submitOnChange' => $submitOnChange,
                'tl_class'  => $tl_class,
            ],
            'sql' => [
                'type' => 'boolean',
                'default' => $default,
            ],
        ];
    }

    public static function buildRowWizardZoomField(string $strTable, string $rowWizardField): array
    {
        $options = match($rowWizardField) {
            'layer'     => ['params','layer','feature'],
            'features'   => ['parent','combine'],
        };

        return [
            'label'     => &$GLOBALS['TL_LANG'][$strTable]["{$rowWizardField}_fields"]['zoom'],
            'inputType' => 'select',
            'options'   => $options,
            'reference' => &$GLOBALS['TL_LANG'][$strTable]["{$rowWizardField}_fields"]['zoom_options'],
            'eval'          => [
                'mandatory' => false,
                'tl_class'  => 'w25',
                'cell_class'=> 'zoom'
            ],
        ];
    }
}

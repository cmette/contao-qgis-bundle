<?php

namespace Cmette\ContaoQgisBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\Model;

trait QgisListenerHelperTrait
{
    /**
     * @param array $row
     * @param string $label
     * @return array|string
     */
    public function buildLabelWithCounter(array $row, string $label): array|string
    {

        $model = Model::getClassFromTable(self::STR_TABLE)::findById($row['id']);

        return ($model && (int)(($count = $model->countUsage()) > 0)) ?
            "<span class='used'>$label ($count)</span>" :
            "<span class='unused'>$label</span>";
    }

    /**
     * @param DataContainerOperation $operation
     * @return void
     */
    public function handleDeleteButton(DataContainerOperation $operation): void
    {
        $class  = Model::getClassFromTable(self::STR_TABLE);
        $record = $operation->getRecord();
        $model  = $class::findById($record['id']);

        $usage = $model->countUsage();
        // Show the icon only but no link if the user cannot edit
        if ($usage > 0) {
            $operation['label'] = $GLOBALS['TL_LANG'][self::STR_TABLE]['deletion_disabled'];
            $operation['title'] = $GLOBALS['TL_LANG'][self::STR_TABLE]['deletion_disabled'];
            $operation['icon'] = 'delete--disabled.svg';
            return;
        }
    }
}
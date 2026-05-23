<?php

namespace Cmette\ContaoQgisBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\Model;

trait QgisListenerHelperTrait
{
    /**
     * @param array $row
     * @param string $label
     * @param string $style
     * @return array|string
     */
    public function buildLabelWithCounter(array $row, string $label, string $style = ''): array|string
    {

        $model = Model::getClassFromTable(self::STR_TABLE)::findById($row['id']);

        return ($model && (int)(($count = $model->countUsage()) > 0)) ?
            "<span class='used'>$label ({$count} mal verwendet)</span>$style" :
            "<span class='unused'>$label (nicht verwendet)</span>$style";
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
        if ($usage > 0 || $model->id === 0) {
            $operation['label'] = $GLOBALS['TL_LANG'][self::STR_TABLE]['deletion_disabled'];
            $operation['title'] = $GLOBALS['TL_LANG'][self::STR_TABLE]['deletion_disabled'];
            $operation['icon'] = 'delete--disabled.svg';
            return;
        }
    }
}
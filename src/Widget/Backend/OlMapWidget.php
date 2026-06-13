<?php

namespace Cmette\ContaoQgisBundle\Widget\Backend;

use Cmette\ContaoQgisBundle\Models\QgisMapModel;
use Contao\System;
use Contao\Widget;

class OlMapWidget extends Widget
{
    protected $blnSubmitInput = false;
    protected $blnForAttribute = true;
    protected $strTemplate = 'be_widget_map';

    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);

        $GLOBALS['TL_JAVASCRIPT'][] = "files/libs/openlayers/dist/10.8.0/ol.js";
        $GLOBALS['TL_CSS'][] = "files/libs/openlayers/dist/10.8.0/ol.css";

        $this->record = QgisMapModel::findById($this->objDca->id) ?: new QgisMapModel();
    }

    public function generate(): string
    {
        return '';
    }
}
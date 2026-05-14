<?php

declare(strict_types=1);

/*
 * This file is part of the Contao QGis Bundle.
 *
 * (c) Christian Mette
 *
 * @license LGPL-3.0-or-later
 */

namespace Cmette\ContaoQgisBundle\Controller\ContentElement;

use Cmette\ContaoQgisBundle\Models\QgisMapModel;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'qgis_map', category: 'open_layers')]
class QgisMapController extends AbstractContentElementController
{
    // this code comes from:
    // vendor/contao/core-bundle/src/Controller/ContentElement/TextController.php

    public function __construct(private readonly Studio $studio)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $map    = QgisMapModel::findOneBy(['id = ?',"published = '1'"],[$model->qgis_map]);
        // $settings   = SourcesSettingModel::findOneBy("published = '1'", [1]);
        $settings = [];

        if ($map) {
            //  map found
            /*
            $figure = !$source->addImage ? null : $this->studio
                ->createFigureBuilder()
                ->fromUuid($source->singleSRC ?: '')
                ->setSize($source->size)
                ->setOverwriteMetadata($source->getOverwriteMetaFromSource())
                ->enableLightbox($source->fullsize)
                ->setLinkAttribute('title', 'neuer Titel')
                ->buildIfResourceExists();
            */
            /*
                <link rel="stylesheet" href="files/libs/openlayers/dist/10.8.0/ol.css">
                <script src="files/libs/olext/dist/4.0.38/ol-ext.js"></script>
                <link rel="stylesheet" href="files/libs/olext/dist/4.0.38/ol-ext.css">
                <script src="files/libs/autolinker/dist/4.1.5/Autolinker.js"></script>
            */
            $GLOBALS['TL_JAVASCRIPT'][] = 'files/libs/openlayers/dist/10.8.0/ol.js';
            $GLOBALS['TL_JAVASCRIPT'][] = 'files/libs/olext/dist/4.0.38/ol-ext.js';
            $GLOBALS['TL_JAVASCRIPT'][] = 'files/libs/autolinker/dist/4.1.5/Autolinker.js';

            $figure = null;
            $template->set('layout', $map->floating);
        } else {
            // source not available
            $figure = null;
            $template->set('layout', 'above');
        }

        $template->set('settings', $settings);
        $template->set('map', $map);
        $template->set('image', $figure);

        // handle Backend Request
        if ($this->isBackendScope($request)) {
            return $template->getResponse();
        }

        return $map ? $template->getResponse() : new Response();
    }
}

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
use Contao\StringUtil;
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
            if($map->addOpenLayers) {
                if ($map->loadOpenLayersJs)     $GLOBALS['TL_JAVASCRIPT'][] = "files/libs/openlayers/dist/{$map->olDistVersion}/ol.js";
                if ($map->loadOpenLayersCss)    $GLOBALS['TL_CSS'][] = "files/libs/openlayers/dist/{$map->olDistVersion}/ol.css";
            }
            if($map->addOpenLayersExt) {
                if ($map->loadOpenLayersExtJs)  $GLOBALS['TL_JAVASCRIPT'][] = "files/libs/olext/dist/{$map->olExtDistVersion}/ol-ext.js";
                if ($map->loadOpenLayersExtCss) $GLOBALS['TL_CSS'][] = "files/libs/olext/dist/{$map->olExtDistVersion}/ol-ext.css";

                if ($map->useCompass) $GLOBALS['TL_CSS'][] = "files/libs/olext/custom/compass.css|static";
                // <link rel="stylesheet" href="files/content-arnsdorf/hoyk/ol-ext_popup.css">
                // <link rel="stylesheet" href="files/content-arnsdorf/hoyk/ol-ext_layerswitcher.css">
            }

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
        $arrListHeadline = StringUtil::deserialize($model->listHeadline, true);
        $template->set('listHeadline', !empty($arrListHeadline['value']) ? "<{$arrListHeadline['unit']}>{$arrListHeadline['value']}</{$arrListHeadline['unit']}>" : '');

        // handle Backend Request
        //if ($this->isBackendScope($request)) return $template->getResponse();

        $template->set('scope', $this->isBackendScope($request));

        return $map ? $template->getResponse() : new Response();
    }
}

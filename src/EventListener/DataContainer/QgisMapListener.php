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
use Contao\Image;
use phpDocumentor\Reflection\Types\Self_;

class QgisMapListener
{
    use QgisListenerHelperTrait;
    private const STR_TABLE = 'tl_qgis_map';

    public string $requestToken = '';

    public function __construct(
        private readonly ContaoCsrfTokenManager $tokenManager,
        #private readonly AuthorizationCheckerInterface $authorizationChecker,
    )
    {
        $this->requestToken = htmlspecialchars($this->tokenManager->getDefaultTokenValue());
    }

    ##[AsCallback(table: self::STR_TABLE, target: 'list.label.group')]
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
    ##[AsCallback(table: self::STR_TABLE, target: 'list.label.label')]
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
    ##[AsCallback(table: self::STR_TABLE, target: 'list.operations.delete.button')]
    public function listDeleteButton(DataContainerOperation $operation): void
    {
        $this->handleDeleteButton($operation);
    }




    ##[AsCallback(table: self::STR_TABLE, target: 'fields.extent.input_field')]
    public function fieldsExtentInputField(DataContainer $dc, string $label): string
    {
        $html = 'hier';

        $name = $id = "extent";

        $fieldConfig = $GLOBALS['TL_DCA'][self::STR_TABLE]['fields'][$name];

        $config = [
            'id'        => $id,
            'name'      => $name,
            'value'     => $value ?? ($fieldConfig['default'] ?? ''),
            #'label'     => $fieldConfig['label'] ?? [],
            'mandatory' => $fieldConfig['eval']['mandatory'] ?? false,
            'eval'      => $fieldConfig['eval'] ?? [],
            'options'   => $fieldConfig['options'] ?? ($fieldConfig['foreignKey'] ?? []),
        ];
        $h3     = "<h3><label for='ctrl_$name'><span class='invisible'>Pflichtfeld </span>{$fieldConfig['label'][0]}<span class='mandatory'>*</span></label></h3>;";
        $label  = "<p class='tl_help tl_tip' data-contao--tooltips-target='tooltip'>{$fieldConfig['label'][1]}</p>";
        $image  = Image::getHtml('/bundles/contaoqgis/img/brackets.svg', $fieldConfig['label'][2], " id='cmd_$name' style='cursor:pointer;background-color:#b0b0b0;' width='20px' height='20px' title=''");
        $code   =<<<Code
<script type='application/javascript'>
document.addEventListener('DOMContentLoaded', function () {
    cmd_$name = document.getElementById('cmd_$name');
    cmd_$name.addEventListener('click', function (e) {
        e.preventDefault();
        const extent = map_2.getView().calculateExtent(map_2.getSize());;
        map_2.getView().fit(extent, {
        size: map_2.getSize(),
        padding: [40, 40, 40, 40], // top,right,bottom,left
        duration: 600,
        nearest: true
      });
    });
});
</script>
Code;

        $widget = new \Contao\TextField($config);

        $html = $widget->generate();
/*
        function addButton(field) {
            field.parentNode.classList.add('wizard');

            var img = document.createElement('img');

            img.src = "/bundles/contaoqgis/img/brackets.svg";
            img.setAttribute('id', 'map_zoom_to_extent');
            img.setAttribute('width', '20');
            img.setAttribute('height', '20');
            img.setAttribute('alt', 'Zoom to Extent');
            img.setAttribute('title', 'Zoom to Extent');

            img.addEventListener('click', function (e) {
                alert('Zoom to Extent');
                e.preventDefault();
            });

            field.parentNode.appendChild(img);
        }
        document.addEventListener('DOMContentLoaded', function () {
            addButton(document.querySelector('#ctrl_extent'));
        });
*/


        return "<div class='w50 widget wizard'>{$h3}{$html}{$image}{$label}{$code}</div>";
    }
}

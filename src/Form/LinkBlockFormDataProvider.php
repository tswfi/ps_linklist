<?php
/**
 * 2007-2018 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\LinkList\Form;

use PrestaShop\LinkList\Model\LinkBlock;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

class LinkBlockFormDataProvider implements FormDataProviderInterface
{
    /** @var int|null */
    private $idLinkBlock;

    /**
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getData()
    {
        if (null === $this->idLinkBlock) {
            return [];
        }

        $linkBlock = new LinkBlock($this->idLinkBlock);

        $arrayLinkBlock = (array) $linkBlock;

        return ['link_block' => [
            'id_link_block' => $arrayLinkBlock['id_link_block'],
            'block_name' => $arrayLinkBlock['name'],
            'id_hook' => $arrayLinkBlock['id_hook'],
            'cms' => $arrayLinkBlock['content']['cms'],
            'product' => $arrayLinkBlock['content']['product'],
            'static' => $arrayLinkBlock['content']['static'],
        ]];
    }

    /**
     * @param array $data
     * @return array
     */
    public function setData(array $data)
    {
        return [];
    }

    /**
     * @return int
     */
    public function getIdLinkBlock()
    {
        return $this->idLinkBlock;
    }

    /**
     * @param int $idLinkBlock
     * @return LinkBlockFormDataProvider
     */
    public function setIdLinkBlock($idLinkBlock)
    {
        $this->idLinkBlock = $idLinkBlock;

        return $this;
    }
}

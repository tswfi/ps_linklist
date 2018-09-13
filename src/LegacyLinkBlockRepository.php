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

namespace PrestaShop\Module\LinkList;

use PrestaShop\Module\LinkList\Model\LinkBlock;
use Symfony\Component\Translation\TranslatorInterface as Translator;
use Language;
use Context;
use Tools;
use Shop;
use Meta;
use Hook;
use DB;

/**
 * Class LegacyLinkBlockRepository.
 */
class LegacyLinkBlockRepository
{
    private $db;
    private $shop;
    private $db_prefix;
    private $translator;

    /**
     * @param DB $db
     * @param Shop $shop
     * @param Translator $translator
     */
    public function __construct(Db $db, Shop $shop, Translator $translator)
    {
        $this->db = $db;
        $this->shop = $shop;
        $this->db_prefix = $db->getPrefix();
        $this->translator = $translator;
    }

    /**
     * @param int $id_hook
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getByIdHook($id_hook)
    {
        $id_hook = (int) $id_hook;

        $sql = "SELECT cb.`id_link_block`
                    FROM {$this->db_prefix}link_block cb
                    WHERE `id_hook` = $id_hook
                    ORDER by cb.`position`
                ";
        $ids = $this->db->executeS($sql);

        $cmsBlock = array();
        foreach ($ids as $id) {
            $cmsBlock[] = new LinkBlock((int) $id['id_link_block']);
        }

        return $cmsBlock;
    }
}

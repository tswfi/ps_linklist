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

namespace PrestaShop\LinkList\Form\ChoiceProvider;

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;

class CMSPageChoiceProvider implements FormChoiceProviderInterface
{
    /** @var CMSCategoryChoiceProvider */
    private $cmsCategoryChoiceProvider;
    /** @var Connection */
    private $connection;
    /** @var string */
    private $dbPrefix;
    /** @var integer */
    private $idLang;
    /** @var integer */
    private $idShop;

    /**
     * CMSPageChoiceProvider constructor.
     * @param CMSCategoryChoiceProvider $cmsCategoryChoiceProvider
     * @param Connection                $connection
     * @param string                    $dbPrefix
     * @param int                       $idLang
     * @param int                       $idShop
     */
    public function __construct(
        CMSCategoryChoiceProvider $cmsCategoryChoiceProvider,
        Connection $connection,
        $dbPrefix,
        $idLang,
        $idShop
    ) {
        $this->cmsCategoryChoiceProvider = $cmsCategoryChoiceProvider;
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
        $this->idLang = $idLang;
        $this->idShop = $idShop;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        $choices = [];

        $categories = $this->cmsCategoryChoiceProvider->getChoices();
        foreach ($categories as $categoryName => $categoryId) {
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->select('c.id_cms, cl.meta_title')
                ->from($this->dbPrefix.'cms', 'c')
                ->innerJoin('c', $this->dbPrefix.'cms_lang', 'cl', 'c.id_cms = cl.id_cms')
                ->innerJoin('c', $this->dbPrefix.'cms_shop', 'cs', 'c.id_cms = cs.id_cms')
                ->andWhere('c.active = 1')
                ->andWhere('cl.id_lang = :idLang')
                ->andWhere('cs.id_shop = :idShop')
                ->andWhere('c.id_cms_category = :idCmsCategory')
                ->setParameter('idCmsCategory', $categoryId)
                ->setParameter('idLang', $this->idLang)
                ->setParameter('idShop', $this->idShop)
                ->orderBy('c.position')
            ;
            $pages = $qb->execute()->fetchAll();
            if (count($pages) > 0) {
                foreach ($pages as $page) {
                    $choices[$categoryName][$page['id_cms']. ' ' . $page['meta_title']] = $page['id_cms'];
                }
            }
        }

        return $choices;
    }
}

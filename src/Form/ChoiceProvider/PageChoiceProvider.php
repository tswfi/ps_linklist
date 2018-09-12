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

namespace PrestaShop\Module\LinkList\Form\ChoiceProvider;

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShop\PrestaShop\Core\Foundation\Database\EntityNotFoundException;

/**
 * Class PageChoiceProvider
 * @package PrestaShop\Module\LinkList\Form\ChoiceProvider
 */
final class PageChoiceProvider extends AbstractDatabaseChoiceProvider
{
    /**
     * @var array
     */
    private $pageNames;

    /**
     * PageChoiceProvider constructor.
     * @param Connection $connection
     * @param $dbPrefix
     * @param $idLang
     * @param $idShop
     * @param array $pageNames
     */
    public function __construct(
        Connection $connection,
        $dbPrefix,
        $idLang,
        $idShop,
        array $pageNames
    ) {
        parent::__construct($connection, $dbPrefix, $idLang, $idShop);
        $this->pageNames = $pageNames;
    }

    /**
     * @return array
     * @throws EntityNotFoundException
     */
    public function getChoices()
    {
        $choices = [];
        foreach ($this->pageNames as $pageName) {
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->select('m.id_meta, ml.title')
                ->from($this->dbPrefix.'meta', 'm')
                ->leftJoin('m', $this->dbPrefix.'meta_lang', 'ml', 'm.id_meta = ml.id_meta')
                ->andWhere($qb->expr()->orX(
                    'm.page = :page',
                        'm.page = :pageSlug'
                ))
                ->andWhere('ml.id_lang = :idLang')
                ->andWhere('ml.id_shop = :idShop')
                ->setParameter('idLang', $this->idLang)
                ->setParameter('idShop', $this->idShop)
                ->setParameter('page', $pageName)
                ->setParameter('pageSlug', str_replace('-', '', strtolower($pageName)))
            ;
            $meta = $qb->execute()->fetchAll();
            if (!empty($meta)) {
                $choices[$meta[0]['title']] = $pageName;
            }
        }

        return $choices;
    }
}

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

namespace PrestaShop\Module\LinkList\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PrestaShop\PrestaShop\Adapter\Entity\PrestaShopDatabaseException;

/**
 * Class LinkBlockRepository
 * @package PrestaShop\Module\LinkList\Repository
 */
class LinkBlockRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dbPrefix;

    /**
     * @var array
     */
    private $languages;

    /**
     * LinkBlockRepository constructor.
     * @param Connection $connection
     * @param string     $dbPrefix
     * @param array      $languages
     */
    public function __construct(Connection $connection, $dbPrefix, array $languages)
    {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
        $this->languages = $languages;
    }

    /**
     * Returns the list of hook with associated Link blocks
     * @return array
     */
    public function getHooksWithLinks()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('h.id_hook, h.name, h.title')
            ->from($this->dbPrefix . 'link_block', 'lb')
            ->leftJoin('lb', $this->dbPrefix . 'hook', 'h', 'lb.id_hook = h.id_hook')
            ->groupBy('h.id_hook')
            ->orderBy('h.name')
        ;

        return $qb->execute()->fetchAll();
    }

    public function createLinkBlock(array $blockName, $idHook, array $cms, array $static, array $product, array $custom)
    {
        $content = json_encode([
            'cms' => empty($cms) ? [false] : $cms,
            'static' => empty($static) ? [false] : $static,
            'product' => empty($product) ? [false] : $product,
        ]);

        $qb = $this->connection->createQueryBuilder();
        $qb->select('MAX(lb.position)')
            ->from($this->dbPrefix.'link_block', 'lb')
            ->andWhere('lb.id_hook = :idHook')
            ->setParameter('idHook', $idHook)
        ;
        $maxPosition = $qb->execute()->fetchColumn(0);
        dump($maxPosition);

        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert($this->dbPrefix.'link_block')
            ->values([
                'id_hook' => ':idHook',
                'position' => ':position',
                'content' => ':content',
            ])
            ->setParameters([
                'idHook' => $idHook,
                'position' => $maxPosition + 1,
                'content' => $content,
            ]);
        ;
        $statement = $qb->execute();
        if ($statement instanceof Statement && !empty($statement->errorInfo())) {
            throw new PrestaShopDatabaseException('Insertion error: '.json_encode($statement->errorInfo()));
        }
        $linkBlockId = $this->connection->lastInsertId();

        foreach ($this->languages as $language) {
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->insert($this->dbPrefix.'link_block_lang')
                ->values([
                    'id_link_block' => ':linkBlockId',
                    'id_lang' => ':idLang',
                    'name' => ':name',
                    'custom_content' => ':customContent',
                ])
                ->setParameters([
                    'linkBlockId' => $linkBlockId,
                    'idLang' => $language['id_lang'],
                    'name' => $blockName[$language['id_lang']],
                    'customContent' => json_encode(
                        empty($custom) ? [false] : $custom
                    ),
                ]);
            ;

            $statement = $qb->execute();
            if ($statement instanceof Statement && !empty($statement->errorInfo())) {
                throw new PrestaShopDatabaseException('Insertion error: '.json_encode($statement->errorInfo()));
            }
        }


        return $linkBlockId;
    }
}

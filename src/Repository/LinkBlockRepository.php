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
use Doctrine\DBAL\Query\QueryBuilder;
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

    /**
     * @param array $blockName
     * @param int   $idHook
     * @param array $cms
     * @param array $static
     * @param array $product
     * @param array $custom
     * @return string
     * @throws PrestaShopDatabaseException
     */
    public function create(array $blockName, $idHook, array $cms, array $static, array $product, array $custom)
    {
        $maxPosition = $this->getHookMaxPosition($idHook);

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
                'content' => json_encode([
                    'cms' => empty($cms) ? [false] : $cms,
                    'static' => empty($static) ? [false] : $static,
                    'product' => empty($product) ? [false] : $product,
                ]),
            ]);
        ;
        $this->executeQueryBuilder($qb, 'Link block error: ');
        $linkBlockId = $this->connection->lastInsertId();

        $this->updateLanguages($linkBlockId, $blockName, $custom);

        $this->clearModuleCache();

        return $linkBlockId;
    }

    /**
     * @param int   $linkBlockId
     * @param array $blockName
     * @param int   $idHook
     * @param array $cms
     * @param array $static
     * @param array $product
     * @param array $custom
     * @return string
     * @throws PrestaShopDatabaseException
     */
    public function update($linkBlockId, array $blockName, $idHook, array $cms, array $static, array $product, array $custom)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update($this->dbPrefix.'link_block', 'lb')
            ->andWhere('lb.id_link_block = :linkBlockId')
            ->set('id_hook', ':idHook')
            ->set('content', ':content')
            ->setParameters([
                'linkBlockId' => $linkBlockId,
                'idHook' => $idHook,
                'content' => json_encode([
                    'cms' => empty($cms) ? [false] : $cms,
                    'static' => empty($static) ? [false] : $static,
                    'product' => empty($product) ? [false] : $product,
                ]),
            ])
        ;
        $this->executeQueryBuilder($qb, 'Link block error: ');

        $this->updateLanguages($linkBlockId, $blockName, $custom);

        $this->clearModuleCache();

        return $linkBlockId;
    }

    /**
     * @param int $idLinkBlock
     * @throws PrestaShopDatabaseException
     */
    public function delete($idLinkBlock)
    {
        $tableNames = [
            'link_block_shop',
            'link_block_lang',
            'link_block',
        ];

        foreach ($tableNames as $tableName) {
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->delete($this->dbPrefix.$tableName)
                ->andWhere('id_link_block = :idLinkBlock')
                ->setParameter('idLinkBlock', $idLinkBlock)
            ;
            $this->executeQueryBuilder($qb, 'Delete error: ');
        }
        $this->clearModuleCache();
    }

    /**
     * @param int   $linkBlockId
     * @param array $blockName
     * @param array $custom
     * @throws PrestaShopDatabaseException
     */
    private function updateLanguages($linkBlockId, array $blockName, array $custom)
    {
        foreach ($this->languages as $language) {
            $qb = $this->connection->createQueryBuilder();
            $qb
                ->select('lbl.id_link_block')
                ->from($this->dbPrefix.'link_block_lang', 'lbl')
                ->andWhere('lbl.id_link_block = :linkBlockId')
                ->andWhere('lbl.id_lang = :langId')
                ->setParameter('linkBlockId', $linkBlockId)
                ->setParameter('langId', $language['id_lang'])
            ;
            $foundRows = $qb->execute()->rowCount();

            $qb = $this->connection->createQueryBuilder();
            if (!$foundRows) {
                $qb
                    ->insert($this->dbPrefix.'link_block_lang')
                    ->values([
                        'id_link_block' => ':linkBlockId',
                        'id_lang' => ':langId',
                        'name' => ':name',
                        'custom_content' => ':customContent',
                    ])
                ;
            } else {
                $qb
                    ->update($this->dbPrefix.'link_block_lang', 'lbl')
                    ->set('name', ':name')
                    ->set('custom_content', ':customContent')
                    ->andWhere('lbl.id_link_block = :linkBlockId')
                    ->andWhere('lbl.id_lang = :langId')
                ;
            }

            $qb
                ->setParameters([
                    'linkBlockId' => $linkBlockId,
                    'langId' => $language['id_lang'],
                    'name' => $blockName[$language['id_lang']],
                    'customContent' => empty($custom) ? null : json_encode($custom),
                ]);
            ;
            $this->executeQueryBuilder($qb, 'Link block language error: ');
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $errorPrefix
     * @return Statement|int
     * @throws PrestaShopDatabaseException
     */
    private function executeQueryBuilder(QueryBuilder $qb, $errorPrefix = 'SQL error: ')
    {
        $statement = $qb->execute();
        if ($statement instanceof Statement && !empty($statement->errorInfo())) {
            throw new PrestaShopDatabaseException($errorPrefix.json_encode($statement->errorInfo()));
        }

        return $statement;
    }

    /**
     * @param int $idHook
     * @return bool|string
     */
    private function getHookMaxPosition($idHook)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('MAX(lb.position)')
            ->from($this->dbPrefix.'link_block', 'lb')
            ->andWhere('lb.id_hook = :idHook')
            ->setParameter('idHook', $idHook)
        ;

        return $qb->execute()->fetchColumn(0);
    }

    /**
     * Clears the module cache
     */
    private function clearModuleCache()
    {
        $module = \Module::getInstanceByName('ps_linklist');
        $module->_clearCache($module->templateFile);
    }
}

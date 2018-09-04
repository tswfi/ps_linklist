<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\LinkList\Form\ChoiceProvider;

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;

/**
 * Class AbstractDatabaseChoiceProvider
 * @package PrestaShop\Module\LinkList\Form\ChoiceProvider
 */
abstract class AbstractDatabaseChoiceProvider implements FormChoiceProviderInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $dbPrefix;

    /**
     * @var int
     */
    protected $idLang;

    /**
     * @var int
     */
    protected $idShop;

    /**
     * AbstractDatabaseChoiceProvider constructor.
     * @param Connection $connection
     * @param string     $dbPrefix
     * @param int|null   $idLang
     * @param int|null   $idShop
     */
    public function __construct(Connection $connection, $dbPrefix, $idLang = null, $idShop = null)
    {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
        $this->idLang = $idLang;
        $this->idShop = $idShop;
    }

    /**
     * @inheritDoc
     */
    abstract public function getChoices();
}

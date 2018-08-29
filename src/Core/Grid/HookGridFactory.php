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

namespace PrestaShop\LinkList\Core\Grid;

use PrestaShop\LinkList\Core\Grid\Definition\Factory\LinkBlockDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterFormFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class HookGridFactory
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var HookDispatcherInterface */
    private $hookDispatcher;
    /** @var GridDataFactoryInterface */
    private $dataFactory;
    /** @var FilterFormFactoryInterface */
    private $filterFormFactory;

    /**
     * HookGridFactory constructor.
     * @param TranslatorInterface        $translator
     * @param HookDispatcherInterface    $hookDispatcher
     * @param GridDataFactoryInterface   $dataFactory
     * @param FilterFormFactoryInterface $filterFormFactory
     */
    public function __construct(
        TranslatorInterface $translator,
        GridDataFactoryInterface $dataFactory,
        HookDispatcherInterface $hookDispatcher,
        FilterFormFactoryInterface $filterFormFactory
    ) {
        $this->translator = $translator;
        $this->hookDispatcher = $hookDispatcher;
        $this->dataFactory = $dataFactory;
        $this->filterFormFactory = $filterFormFactory;
    }

    /**
     * Each definition depends on the hook, therefore each factory also
     * depends on the hook
     *
     * @param array $hook
     * @return GridFactory
     */
    public function buildGridFactoryByHook(array $hook)
    {
        $definitionFactory = new LinkBlockDefinitionFactory($hook);
        $definitionFactory->setTranslator($this->translator);
        $definitionFactory->setHookDispatcher($this->hookDispatcher);

        return new GridFactory(
            $definitionFactory,
            $this->dataFactory,
            $this->filterFormFactory
        );
    }
}

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

namespace PrestaShop\LinkList\Controller\Admin\Improve\Design;

use PrestaShop\LinkList\Core\Grid\HookGridFactory;
use PrestaShop\LinkList\Core\Search\Filters\LinkBlockFilters;
use PrestaShop\LinkList\Repository\LinkBlockRepository;
use PrestaShop\PrestaShop\Core\Grid\Presenter\GridPresenter;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LinkWidgetController extends FrameworkBundleAdminController
{
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @Template("@Modules/ps_linklist/views/templates/admin/link_block/list.html.twig")
     *
     * @return array
     */
    public function listAction(Request $request)
    {
        //Get hook list, then loop through hooks setting it in in the filter
        /** @var LinkBlockRepository $repository */
        $repository = $this->get('link_block_repository');
        $hooks = $repository->getHooksWithLinks();

        /** @var HookGridFactory $hookGridFactory */
        $hookGridFactory = $this->get('prestashop.core.grid.factory.hook');
        /** @var GridPresenter $gridPresenter */
        $gridPresenter = $this->get('prestashop.core.grid.presenter.grid_presenter');

        $grids = [];
        foreach ($hooks as $hook) {
            $filters = $this->buildFiltersByRequestAndHook($request, $hook);

            $gridLinkBlockFactory = $hookGridFactory->buildGridFactoryByHook($hook);
            $grid = $gridLinkBlockFactory->getGrid($filters);
            $grids[] = $gridPresenter->present($grid);
        }

        return [
            'grids' => $grids,
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ];
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @Template("@Modules/ps_linklist/views/templates/admin/link_block/edit.html.twig")
     *
     * @param int $linkBlockId
     *
     * @return array
     */
    public function editAction($linkBlockId)
    {

    }

    /**
     * @param int $linkBlockId
     *
     * @return RedirectResponse
     */
    public function deleteAction($linkBlockId)
    {
        return $this->redirectToRoute('admin_link_widget_list');
    }

    /**
     * @param Request $request
     * @param array   $hook
     * @return LinkBlockFilters
     */
    private function buildFiltersByRequestAndHook(Request $request, array $hook)
    {
        $filtersParams = array_merge(LinkBlockFilters::getDefaults(), $request->query->all());
        $filtersParams['filters']['id_lang'] = $this->getContext()->language->id;
        $filtersParams['filters']['id_hook'] = $hook['id_hook'];

        return new LinkBlockFilters($filtersParams);
    }
}

<?php
/**
 * 2007-2018 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\LinkList\Controller\Admin\Improve\Design;

use PrestaShop\Module\LinkList\Core\Grid\LinkBlockGridFactory;
use PrestaShop\Module\LinkList\Core\Search\Filters\LinkBlockFilters;
use PrestaShop\Module\LinkList\Repository\LinkBlockRepository;
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
        $repository = $this->get('prestashop.module.link_block.repository');
        $hooks = $repository->getHooksWithLinks();

        $filtersParams = $this->buildFiltersParamsByRequest($request);

        /** @var LinkBlockGridFactory $linkBlockGridFactory */
        $linkBlockGridFactory = $this->get('prestashop.module.link_block.grid.factory');
        $grids = $linkBlockGridFactory->getGrids($hooks, $filtersParams);

        /** @var GridPresenter $gridPresenter */
        $gridPresenter = $this->get('prestashop.core.grid.presenter.grid_presenter');
        $presentedGrids = [];
        foreach ($grids as $grid) {
            $presentedGrids[] = $gridPresenter->present($grid);
        }

        return [
            'grids' => $presentedGrids,
            'enableSidebar' => true,
            'layoutHeaderToolbarBtn' => $this->getToolbarButtons(),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ];
    }

    /**
     * @AdminSecurity("is_granted('create', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @Template("@Modules/ps_linklist/views/templates/admin/link_block/form.html.twig")
     *
     * @return array
     * @throws \Exception
     */
    public function createAction()
    {
        $this->get('prestashop.module.link_block.form_provider')->setIdLinkBlock(null);
        $form = $this->get('prestashop.module.link_block.form_handler')->getForm();

        return [
            'linkBlockForm' => $form->createView(),
        ];
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))", message="Access denied.")
     * @Template("@Modules/ps_linklist/views/templates/admin/link_block/form.html.twig")
     *
     * @param int $linkBlockId
     *
     * @return array
     * @throws \Exception
     */
    public function editAction($linkBlockId)
    {
        $this->get('prestashop.module.link_block.form_provider')->setIdLinkBlock($linkBlockId);
        $form = $this->get('prestashop.module.link_block.form_handler')->getForm();

        return [
            'linkBlockForm' => $form->createView(),
        ];
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function createProcessAction(Request $request)
    {
        return $this->redirectToRoute('admin_link_block_edit', ['linkBlockId' => $request->get('id_link_block')]);
    }

    /**
     * @AdminSecurity("is_granted('update', request.get('_legacy_controller'))", message="Access denied.")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function editProcessAction(Request $request)
    {
        return $this->redirectToRoute('admin_link_block_edit', ['linkBlockId' => $request->get('id_link_block')]);
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
     * @return array
     */
    private function buildFiltersParamsByRequest(Request $request)
    {
        $filtersParams = array_merge(LinkBlockFilters::getDefaults(), $request->query->all());
        $filtersParams['filters']['id_lang'] = $this->getContext()->language->id;

        return $filtersParams;
    }

    /**
     * Gets the header toolbar buttons.
     *
     * @return array
     */
    private function getToolbarButtons()
    {
        $toolbarButtons = [];
        $toolbarButtons['add'] = [
            'href' => $this->generateUrl('admin_link_block_create'),
            'desc' => $this->trans('New block', 'Modules.Linklist.Admin'),
            'icon' => 'add_circle_outline',
        ];

        return $toolbarButtons;
    }
}

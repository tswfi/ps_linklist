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

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\LinkList\LegacyLinkBlockRepository;
use PrestaShop\LinkList\Presenter\LinkBlockPresenter;

class Ps_Linklist extends Module implements WidgetInterface
{
    protected $_html;
    protected $_display;
    /**
     * @var LinkBlockPresenter
     */
    private $linkBlockPresenter;
    /**
     * @var LegacyLinkBlockRepository
     */
    private $linkBlockRepository;

    public $templateFile;

    public function __construct()
    {
        $this->name = 'ps_linklist';
        $this->author = 'PrestaShop';
        $this->version = '2.1.5';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Link List', array(), 'Modules.Linklist.Admin');
        $this->description = $this->trans('Adds a block with several links.', array(), 'Modules.Linklist.Admin');
        $this->secure_key = Tools::encrypt($this->name);

        $this->ps_versions_compliancy = array('min' => '1.7.5.0', 'max' => _PS_VERSION_);
        $this->templateFile = 'module:ps_linklist/views/templates/hook/linkblock.tpl';

        $this->linkBlockPresenter = new LinkBlockPresenter(new Link(), $this->context->language);
    }

    public function install()
    {
        return parent::install()
            && $this->installTab()
            && $this->linkBlockRepository = $this->get('link_block_repository')
            && $this->linkBlockRepository->createTables()
            && $this->linkBlockRepository->installFixtures($this->context->getTranslator())
            && $this->registerHook('displayFooter')
            && $this->registerHook('actionUpdateLangAfter');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTab()
            && $this->linkBlockRepository = $this->get('link_block_repository')
            && $this->linkBlockRepository->dropTables();
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = "AdminLinkWidget";
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = "Link Widget";
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentThemes');
        $tab->module = $this->name;
        return $tab->add();
    }

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminLinkWidget');
        $tab = new Tab($id_tab);
        return $tab->delete();
    }

    public function hookActionUpdateLangAfter($params)
    {
        if (!empty($params['lang']) && $params['lang'] instanceOf Language) {
            include_once _PS_MODULE_DIR_ . $this->name . '/lang/LinkBlockLang.php';

            Language::updateMultilangFromClass(_DB_PREFIX_ . 'link_block_lang', 'LinkBlockLang', $params['lang']);
        }
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminLinkWidget')
        );
    }

    public function renderWidget($hookName, array $configuration)
    {
        $key = 'ps_linklist|' . $hookName;

        if (!$this->isCached($this->templateFile, $this->getCacheId($key))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId($key));
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $id_hook = Hook::getIdByName($hookName);

        $linkBlocks = $this->linkBlockRepository->getByIdHook($id_hook);

        $blocks = array();
        foreach ($linkBlocks as $block) {
            $blocks[] = $this->linkBlockPresenter->present($block);
        }

        return array(
            'linkBlocks' => $blocks
        );
    }
}

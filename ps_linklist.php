<?php
/**
 * 2007-2020 PrestaShop and Contributors
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\Module\LinkList\LegacyLinkBlockRepository;
use PrestaShop\Module\LinkList\Presenter\LinkBlockPresenter;
use PrestaShop\Module\LinkList\Model\LinkBlockLang;
use PrestaShop\Module\LinkList\Repository\LinkBlockRepository;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\Shop\Context;

/**
 * Class Ps_Linklist.
 */
class Ps_Linklist extends Module implements WidgetInterface
{
    const MODULE_NAME = 'ps_linklist';

    protected $_html;
    protected $_display;
    /**
     * @var LinkBlockPresenter
     */
    private $linkBlockPresenter;
    /**
     * @var LegacyLinkBlockRepository
     */
    private $legacyBlockRepository;
    /**
     * @var LinkBlockRepository
     */
    private $repository;

    public $templateFile;

    /**
     * @var string
     */
    public $templateFileColumn;

    public function __construct()
    {
        $this->name = 'ps_linklist';
        $this->author = 'PrestaShop';
        $this->version = '4.0.0';
        $this->need_instance = 0;
        $this->tab = 'front_office_features';

        $tabNames = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tabNames[$lang['locale']] = $this->trans('Link List', array(), 'Modules.Linklist.Admin', $lang['locale']);
        }
        $this->tabs = [
            [
                'route_name' => 'admin_link_block_list',
                'class_name' => 'AdminLinkWidget',
                'visible' => true,
                'name' => $tabNames,
                'parent_class_name' => 'AdminParentThemes',
            ],
        ];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Link List', array(), 'Modules.Linklist.Admin');
        $this->description = $this->trans('Give more visibility to your content/static pages (CMS, external pages, or else), where you want and when you want, to make your visitors feel like shopping on your store.', array(), 'Modules.Linklist.Admin');
        $this->secure_key = Tools::encrypt($this->name);

        $this->ps_versions_compliancy = array('min' => '1.7.7.0', 'max' => _PS_VERSION_);
        $this->templateFile = 'module:ps_linklist/views/templates/hook/linkblock.tpl';
        $this->templateFileColumn = 'module:ps_linklist/views/templates/hook/linkblock-block.tpl';

        $this->linkBlockPresenter = new LinkBlockPresenter(new Link(), $this->context->language);
        $this->legacyBlockRepository = new LegacyLinkBlockRepository(Db::getInstance(), $this->context->shop, $this->context->getTranslator());
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $installed = $this->installFixtures();

        if ($installed
            && $this->registerHook('displayFooter')
            && $this->registerHook('actionUpdateLangAfter')) {
            return true;
        }

        $this->uninstall();

        return false;
    }

    /**
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function installFixtures()
    {
        $installed = true;
        $result = $this->getRepository()->createTables();
        if (false === $result || (is_array($result) && !empty($result))) {
            if (is_array($result)) {
                $this->addModuleErrors($result);
            }
            $installed = false;
        }

        $result = $this->getRepository()->installFixtures();
        if (false === $result || (is_array($result) && !empty($result))) {
            if (is_array($result)) {
                $this->addModuleErrors($result);
            }
            $installed = false;
        }

        return $installed;
    }

    public function uninstall()
    {
        $uninstalled = true;
        $result = $this->getRepository()->dropTables();
        if (false === $result || (is_array($result) && !empty($result))) {
            if (is_array($result)) {
                $this->addModuleErrors($result);
            }
            $uninstalled = false;
        }

        return $uninstalled && parent::uninstall();
    }

    public function hookActionUpdateLangAfter($params)
    {
        if (!empty($params['lang']) && $params['lang'] instanceof Language) {
            Language::updateMultilangFromClass(_DB_PREFIX_ . 'link_block_lang', LinkBlockLang::class, $params['lang']);
        }
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
        parent::_clearCache($this->templateFileColumn);
    }

    public function getContent()
    {
        // We need to explicitely get Symfony container, because $this->get will use the admin legacy container
        $sfContainer = SymfonyContainer::getInstance();
        $router = $sfContainer->get('router');
        Tools::redirectAdmin(
            $router->generate('admin_link_block_list')
        );
    }

    public function renderWidget($hookName, array $configuration)
    {
        $key = 'ps_linklist|' . $hookName;

        if ($hookName === 'displayLeftColumn' || $hookName === 'displayRightColumn') {
            $template = $this->templateFileColumn;
        } else {
            $template = $this->templateFile;
        }

        if (!$this->isCached($template, $this->getCacheId($key))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($template, $this->getCacheId($key));
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $id_hook = Hook::getIdByName($hookName);

        $linkBlocks = $this->legacyBlockRepository->getByIdHook($id_hook);

        $blocks = array();
        foreach ($linkBlocks as $block) {
            $blocks[] = $this->linkBlockPresenter->present($block);
        }

        return array(
            'linkBlocks' => $blocks,
            'hookName' => $hookName,
        );
    }

    /**
     * @param array $errors
     */
    private function addModuleErrors(array $errors)
    {
        foreach ($errors as $error) {
            $this->_errors[] = $this->trans($error['key'], $error['parameters'], $error['domain']);
        }
    }

    /**
     * @return LinkBlockRepository|LegacyLinkBlockRepository|null
     */
    private function getRepository()
    {
        if (null === $this->repository) {
            try {
                $this->repository = $this->get('prestashop.module.link_block.repository');
            } catch (Throwable $e) {
                try {
                    $container = SymfonyContainer::getInstance();
                    if (null !== $container) {
                        //Module is not installed so its services are not loaded
                        /** @var LegacyContext $context */
                        $legacyContext = $container->get('prestashop.adapter.legacy.context');
                        /** @var Context $shopContext */
                        $shopContext = $container->get('prestashop.adapter.shop.context');
                        $this->repository = new LinkBlockRepository(
                            $container->get('doctrine.dbal.default_connection'),
                            $container->getParameter('database_prefix'),
                            $legacyContext->getLanguages(true, $shopContext->getContextShopID()),
                            $container->get('translator')
                        );
                    }
                } catch (Throwable $e) {
                }
            }
        }

        // Container is not available so we use legacy repository as fallback
        if (!$this->repository) {
            $this->repository = $this->legacyBlockRepository;
        }

        return $this->repository;
    }
}

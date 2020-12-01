<?php

namespace Drupal\cms_breadcrumbs\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;

use Drupal\epic_import\Classes\Menu;

class BreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * CMS Breadcrumbs config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Drupal site config object. 
   * 
   * @var \Drupal\Core\Config\Config
   */
  protected $siteConfig;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The router request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $context;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Module handler object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The menu active trail interface.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $menuActiveTrail;

  /**
   * The menu link manager interface.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Constructor for BreadcrumbBuilder.
   * 
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, RequestContext $context, RequestStack $request_stack, LanguageManagerInterface $language_manager, TitleResolverInterface $title_resolver, ModuleHandlerInterface $module_handler, MenuActiveTrailInterface $menu_active_trail, MenuLinkManagerInterface $menu_link_manager) {
    
    $this->config = $config_factory->get('cms_breadcrumbs.settings');
    $this->siteConfig = $config_factory->get('system.site');
    $this->context = $context;
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack;
    $this->titleResolver = $title_resolver;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->menuActiveTrail = $menu_active_trail;
    $this->menuLinkManager = $menu_link_manager;
  }
    
    
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Used to specify which context to apply build()
    if ($node = $route_match->getParameter('node')) {
      // Apply to all node entities
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
      
    $breadcrumb = new Breadcrumb();
    $links = [];

    // Get current language
    $curr_lang = $this->languageManager->getCurrentLanguage()->getId();
    
    // Set configured header breadcrumbs
    if ($breadcrumb_settings = $this->config->get($curr_lang)) {
      $half_length = count($breadcrumb_settings) / 2;
      for ($i = 0; $i < $half_length; $i++ ) {
        $title = 'title_' . $i;
        $url = 'url_' . $i;
        if (!empty($breadcrumb_settings[$title]) && !empty($breadcrumb_settings[$url])) {
          $links[] = \Drupal\Core\Link::fromTextAndUrl($breadcrumb_settings[$title], Url::fromUri($breadcrumb_settings[$url]));
        }
      }
    }

    // Get site name from system config
    $site_name = $this->siteConfig->get('name');

    // If this page is not the home page, add home link
    if (!(\Drupal::service('path.matcher')->isFrontPage())) {
      $links[] = \Drupal\Core\Link::fromTextAndUrl($site_name, Url::fromRoute('<front>', [], ['absolute' => TRUE]));
    }

    /* Attempt 2 - menu breadcrumbs*/
    // https://drupal.stackexchange.com/questions/252503/can-menutree-load-a-menus-items-including-children
    // https://stackoverflow.com/questions/36768732/displaying-sub-menu-tree-in-drupal-8
    // https://drupal.stackexchange.com/questions/202953/how-to-get-all-parent-menu-items-titles-of-the-current-node 

    // load menu trail
    $menu_name = 'sidebar';
    $trail_ids = $this->menuActiveTrail->getActiveTrailIds('main');

    // load node
    $nid = $route_match->getRawParameter('node');
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    $uuid = $node->uuid();

    // load menu link content object
    $menu_content = $this->entityTypeManager->getStorage('menu_link_content')->loadByProperties(['menu_name' => $menu_name]);
    
    // load menu link for current node
    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $menu_links = $menu_link_manager->loadLinksByRoute('entity.node.canonical', array('node' => $nid));
    $menu_link = reset($menu_links);

    $path = trim($this->context->getPathInfo(), '/');
    $path = urldecode($path);
    $path_elements = explode('/', $path);
    //$path_elements = array_reverse($path_elements);
    
    // debug
    if ($fp = fopen("/tmp/LINK", "w")) {
      fwrite($fp, print_r($menu_link, TRUE));
      fclose($fp);
    } 
    
    /*$currentPluginId = $menu_link->getPluginId();

    foreach (array_reverse($trail_ids) as $key => $value) {
      if ($value && $value !== $currentPluginId) {
        $links[] = 
          new Link(
            $menu_link_manager->createInstance($value)->getTitle(),
            $menu_link_manager->createInstance($value)->getUrlObject()
          )
        ;
        $breadcrumb->addCacheableDependency($menu_link_manager);
     }
    }*/
     
    $breadcrumb->addCacheableDependency($this->config);
    $breadcrumb->addCacheableDependency($this->siteConfig);
    $breadcrumb->addCacheContexts(['route', 'url.path', 'languages']);

    return $breadcrumb->setLinks($links);
  }
}

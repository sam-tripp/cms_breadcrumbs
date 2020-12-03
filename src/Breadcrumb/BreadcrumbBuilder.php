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

    // load menu trail
    $menu_name = 'sidebar';
    $trail_ids = $this->menuActiveTrail->getActiveTrailIds($menu_name);
    $curr_trail_id = array_shift($trail_ids);

    // load node
    $nid = $route_match->getRawParameter('node');
    $node = $this->entityTypeManager->getStorage('node')->load($nid);

    // load menu link content object
    $menu_content = $this->entityTypeManager->getStorage('menu_link_content')->loadByProperties(['menu_name' => $menu_name]);


    // generate breadcrumbs from active trail ids
    if (!empty($trail_ids)) {
      foreach (array_reverse($trail_ids) as $key => $value) {
        if ($value && $value !== $curr_trail_id) {
          $links[] = 
            new Link(
              $this->menuLinkManager->createInstance($value)->getTitle(),
              $this->menuLinkManager->createInstance($value)->getUrlObject()
            )
          ;
          $breadcrumb->addCacheableDependency($this->menuLinkManager);
        }
      }
    }

     
    $breadcrumb->addCacheableDependency($this->config);
    $breadcrumb->addCacheableDependency($this->siteConfig);
    $breadcrumb->addCacheContexts(['route', 'url.path', 'languages']);

    return $breadcrumb->setLinks($links);
  }
}

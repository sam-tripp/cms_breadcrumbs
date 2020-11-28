<?php

namespace Drupal\cms_breadcrumbs\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Language\LanguageManagerInterface;

class BreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * CMS Breadcrumbs config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The router request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $context;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructor for BreadcrumbBuilder.
   * 
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestContext $context, LanguageManagerInterface $language_manager, TitleResolverInterface $title_resolver) {
    
    $this->config = $config_factory->get('cms_breadcrumbs.settings');
    $this->context = $context;
    $this->languageManager = $language_manager;
    $this->titleResolver = $title_resolver;
    
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
 
    // debug
    if ($fp = fopen("/tmp/test_en", "w")) {
      fwrite($fp, print_r($this->config->get('en'), TRUE));
      fclose($fp);
    } 
    if ($fp = fopen("/tmp/test_fr", "w")) {
      fwrite($fp, print_r($this->config->get('fr'), TRUE));
      fclose($fp);
    } 
    

    // If this page is not the home page, add home link
    if (!(\Drupal::service('path.matcher')->isFrontPage())) {
      $links[] = \Drupal\Core\Link::fromTextAndUrl('Clean Growth Hub', Url::fromRoute('<front>', [], ['absolute' => TRUE]));
    }


    /* Resolve breadcrumbs from path
    $path = trim($this->context->getPathInfo(), '/');
    $path = urldecode($path);
    $path_elements = explode('/', $path);*/

    $breadcrumb->addCacheContexts(['route', 'url.path', 'languages']);

    return $breadcrumb->setLinks($links);
  }
}

<?php

namespace Drupal\cms_breadcrumbs\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Language\LanguageManagerInterface;

class BreadcrumbBuilder implements BreadcrumbBuilderInterface {
   
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
   * The current path object.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructor for BreadcrumbBuilder.
   * 
   * @param \Drupal\Core\Routing\RequestContext $context
   * The router request context.
   *//*
  public function __construct(RequestContext $context, LanguageManagerInterface $language_manager, CurrentPathStack $current_path) {
    $this->context = $context;
    $this->languageManager = $language_manager;
    $this->currentPath = $current_path;
  }*/
    
    
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
    //$curr_lang = $this->languageManager->getCurrentLanguage()->getId();
    
    // Hard-coded header breadcrumbs for Clean Growth Hub
    // TODO add translation
    $links[] = \Drupal\Core\Link::fromTextAndUrl('Canada.ca', Url::fromUri('https://www.canada.ca/en.html'));
    $links[] = \Drupal\Core\Link::fromTextAndUrl('Science and innovation', Url::fromUri('https://www.canada.ca/en/services/science.html'));
    $links[] = \Drupal\Core\Link::fromTextAndUrl('R&D and innovation', Url::fromUri('https://www.canada.ca/en/services/science/innovation.html'));
    $links[] = \Drupal\Core\Link::fromTextAndUrl('Clean technology', Url::fromUri('https://www.canada.ca/en/services/science/innovation/clean-technology.html'));
    $links[] = \Drupal\Core\Link::fromTextAndUrl('Clean Growth Hub', Url::fromRoute('<front>', [], ['absolute' => TRUE]));


    /* Resolve breadcrumbs from path
    $path = trim($this->context->getPathInfo(), '/');
    $path = urldecode($path);
    $path_elements = explode('/', $path);*/

    $breadcrumb->addCacheContexts(['route', 'url.path', 'languages']);

    return $breadcrumb->setLinks($links);
  }
}

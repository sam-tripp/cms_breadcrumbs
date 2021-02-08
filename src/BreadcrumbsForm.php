<?php

namespace Drupal\cms_breadcrumbs;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\Validator\Constraints\Length;

class BreadcrumbsForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cms_breadcrumbs_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [BreadcrumbConstants::MODULE_SETTINGS];
  }

  // TODO remove field hardcoding (AJAX plugin)
  // TODO form layout side-by-side columns for FR and EN
  // Look into serialize() https://www.php.net/manual/en/function.serialize.php
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(BreadcrumbConstants::MODULE_SETTINGS);
    //$en_config = $config->get('en');
    //$fr_config = $config->get('fr');

    $form = [];

    // Setting to include home page breadcrumb segment - default ON
    $include_home_segment = $config->get(BreadcrumbConstants::INCLUDE_HOME_SEGMENT);
    if (!isset($include_home_segment)) {
      $include_home_segment = TRUE;
    }
    $form[BreadcrumbConstants::INCLUDE_HOME_SEGMENT] = [
      '#type'           => 'checkbox',
      '#title'          => 'Include the front page as a segment in the breadcrumb',
      '#description'    => 'Uncheck to remove the front page of this Drupal site from the breadcrumb trail.',
      '#default_value'  => $include_home_segment,
    ];

    // Settings to append active menu crumbs - default ON
    $append_active_menu = $config->get(BreadcrumbConstants::APPEND_ACTIVE_MENU_BREADCRUMBS);
    if (!isset($append_active_menu)) {
      $append_active_menu = TRUE;
    }
    $form[BreadcrumbConstants::APPEND_ACTIVE_MENU_BREADCRUMBS] = [
      '#type'          => 'checkbox',
      '#title'         => 'Append active menu trail to breadcrumb',
      '#description'   => 'Uncheck to remove active menu links from breadcrumb trail.',
      '#default_value' => $append_active_menu,
    ];

    // English settings
    $general_settings_en = [
      '#type'   => 'details',
      '#title'  => 'General settings',
      '#open'   => TRUE,
    ];

    // French settings
    $general_settings_fr = [
      '#type'   => 'details',
      '#title'  => 'Paramètres générales',
      '#open'   => TRUE,
    ];

    // TODO - Add AJAX to generate these fields when provided number of required header crumbs

    // First header breadcrumb
    $general_settings_en['en_title_0'] = [
      '#type'           => 'textfield',
      '#title'          => 'Title',
      '#description'    => 'Set the title of the \'Global Home\' breadcrumb.',
      '#default_value'  => $config->get('en')['title_0'] ?? 'Canada.ca',
    ];

    $general_settings_en['en_url_0'] = [
      '#type'           => 'textfield',
      '#title'          => 'URL',
      '#description'    => 'Set the URL of the \'Global Home\' breadcrumb.',
      '#default_value'  => $config->get('en')['url_0'] ?? 'https://www.canada.ca/en.html',
    ];

    $general_settings_fr['fr_title_0'] = [
      '#type'           => 'textfield',
      '#title'          => 'Title',
      '#description'    => 'Set the title of the \'Global Home\' breadcrumb.',
      '#default_value'  => $config->get('fr')['title_0'] ?? 'Canada.ca',
    ];

    $general_settings_fr['fr_url_0'] = [
      '#type'           => 'textfield',
      '#title'          => 'URL',
      '#description'    => 'Set the URL of the \'Global Home\' breadcrumb.',
      '#default_value'  => $config->get('fr')['url_0'] ?? 'https://www.canada.ca/fr.html',
    ];

    // Second header breadcrumb
    $general_settings_en['en_title_1'] = [
      '#type'           => 'textfield',
      '#title'          => 'Title',
      '#description'    => 'Set the title of the second leading breadcrumb.',
      '#default_value'  => $config->get('en')['title_1'] ?? '',
    ];

    $general_settings_en['en_url_1'] = [
      '#type'           => 'textfield',
      '#title'          => 'URL',
      '#description'    => 'Set the URL of the second leading breadcrumb.',
      '#default_value'  => $config->get('en')['url_1'] ?? '',
    ];

    $general_settings_fr['fr_title_1'] = [
      '#type'           => 'textfield',
      '#title'          => 'Title',
      '#description'    => 'Set the title of the second leading breadcrumb.',
      '#default_value'  => $config->get('fr')['title_1'] ?? '',
    ];

    $general_settings_fr['fr_url_1'] = [
      '#type'           => 'textfield',
      '#title'          => 'URL',
      '#description'    => 'Set the URL of the second leading breadcrumb.',
      '#default_value'  => $config->get('fr')['url_1'] ?? '',
    ];

    // Third header breadcrumb
    $general_settings_en['en_title_2'] = [
      '#type'           => 'textfield',
      '#title'          => 'Title',
      '#description'    => 'Set the title of the third leading breadcrumb.',
      '#default_value'  => $config->get('en')['title_2'] ?? '',
    ];

    $general_settings_en['en_url_2'] = [
      '#type'           => 'textfield',
      '#title'          => 'URL',
      '#description'    => 'Set the URL of the third leading breadcrumb.',
      '#default_value'  => $config->get('en')['url_2'] ?? '',
    ];

    $general_settings_fr['fr_title_2'] = [
      '#type'           => 'textfield',
      '#title'          => 'Title',
      '#description'    => 'Set the title of the third leading breadcrumb.',
      '#default_value'  => $config->get('fr')['title_2'] ?? '',
    ];

    $general_settings_fr['fr_url_2'] = [
      '#type'           => 'textfield',
      '#title'          => 'URL',
      '#description'    => 'Set the URL of the third leading breadcrumb.',
      '#default_value'  => $config->get('fr')['url_2'] ?? '',
    ];

    // Fourth header breadcrumb
    $general_settings_en['en_title_3'] = [
      '#type'           => 'textfield',
      '#title'          => 'Title',
      '#description'    => 'Set the title of the fourth leading breadcrumb.',
      '#default_value'  => $config->get('en')['title_3'] ?? '',
    ];

    $general_settings_en['en_url_3'] = [
      '#type'           => 'textfield',
      '#title'          => 'URL',
      '#description'    => 'Set the URL of the fourth leading breadcrumb.',
      '#default_value'  => $config->get('en')['url_3'] ?? '',
    ];

    $general_settings_fr['fr_title_3'] = [
      '#type'           => 'textfield',
      '#title'          => 'Title',
      '#description'    => 'Set the title of the fourth leading breadcrumb.',
      '#default_value'  => $config->get('fr')['title_3'] ?? '',
    ];

    $general_settings_fr['fr_url_3'] = [
      '#type'           => 'textfield',
      '#title'          => 'URL',
      '#description'    => 'Set the URL of the fourth leading breadcrumb.',
      '#default_value'  => $config->get('fr')['url_3'] ?? '',
    ];

    $form[BreadcrumbConstants::MODULE_NAME][] = $general_settings_en;
    $form[BreadcrumbConstants::MODULE_NAME][] = $general_settings_fr;

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $settings = $this->configFactory->getEditable(BreadcrumbConstants::MODULE_SETTINGS);

    $values = $form_state->cleanValues()->getValues();

    $en_keys = [];
    $fr_keys = [];

    foreach($values as $field_key => $field_value) {
      if (preg_match('/^en/', $field_key)) {
        $field_key = preg_replace('/^en\_/', '', $field_key);
        $en_keys[$field_key] = $field_value;
      } else if (preg_match('/^fr/', $field_key)) {
        $field_key = preg_replace('/^fr\_/', '', $field_key);
        $fr_keys[$field_key] = $field_value;
      } else {
        $settings->set($field_key, $field_value);
      }
    }
    $settings->set('en', $en_keys);
    $settings->set('fr', $fr_keys);
    $settings->save();

    parent::submitForm($form, $form_state);
  }
}


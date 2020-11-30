<?php

namespace Drupal\cms_breadcrumbs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\Validator\Constraints\Length;
use Drupal\Core\Breadcrumb\Breadcrumb;

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
    return ['cms_breadcrumbs.settings'];
  }

  // TODO remove field hardcoding (AJAX plugin)
  // TODO form layout side-by-side columns for FR and EN
  // Look into serialize() https://www.php.net/manual/en/function.serialize.php
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cms_breadcrumbs.settings');
    //$en_config = $config->get('en');
    //$fr_config = $config->get('fr');

    $form = [];

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

    // Second header breadcrumb
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

    
    $form['cms_breadcrumbs'][] = $general_settings_en;
    $form['cms_breadcrumbs'][] = $general_settings_fr;

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $settings = $this->configFactory->getEditable('cms_breadcrumbs.settings');

    $values = $form_state->cleanValues()->getValues();

    if ($fp = fopen("/tmp/settings", "w")) {
          fwrite($fp, print_r($values, TRUE));
          fclose($fp);
    } 
    $en_keys = [];
    $fr_keys = [];
    foreach($values as $field_key => $field_value) {
      if (preg_match('/^en/', $field_key)) {
        $field_key = preg_replace('/^en\_/', '', $field_key);
        $en_keys[$field_key] = $field_value;
      } else if (preg_match('/^fr/', $field_key)) {
        $field_key = preg_replace('/^fr\_/', '', $field_key);
        $fr_keys[$field_key] = $field_value;
      }
    }
    $settings->set('en', $en_keys);
    $settings->set('fr', $fr_keys);
    $settings->save();

    parent::submitForm($form, $form_state);
  }
}


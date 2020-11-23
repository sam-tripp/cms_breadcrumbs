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

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cms_breadcrumbs.settings');

    // General settings
    $general_settings = [
      '#type'   => 'details',
      '#title'  => 'General settings',
      '#open'   => TRUE,
    ];

    $general_settings['home'] = [
      '#type'           => 'textfield',
      '#title'          => 'Home',
      '#description'    => 'Set the title of the \'Home\' link.',
      '#default_value'  => 'Canada.ca',
    ];

    $general_settings['home_url'] = [
      '#type'           => 'textfield',
      '#title'          => 'Home',
      '#description'    => 'Set the URL of the \'Home\' link.',
      '#default_value'  => 'https://www.canada.ca/en.html',
    ];

    $form = [];
    $form['cms_breadcrumbs'][] = $general_settings;

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $settings = $this->configFactory->getEditable('cms_breadcrumbs.settings');

    $values = $form_state->cleanValues()->getValues();
    foreach($values as $field_key => $field_value) {
      $settings->set($field_key, $field_value);
    }
    $settings->save();

    parent::submitForm($form, $form_state);
  }
}


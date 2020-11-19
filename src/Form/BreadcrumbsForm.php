<?php

namespace Drupal\cms_breadcrumbs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\Validator\Constraints\Length;
use Drupal\Core\Breadcrumb\Breadcrumb;

class BreadcrumbsForm extends FormBase {
  
  public $breadcrumb_fields = 4;
  
  public function getFormId() {
    return 'ised_breadcrumbs';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    for ($i = 1; $i < $this->breadcrumb_fields + 1; $i++) {

        // Set text field
        $num = strval($i);
        $form["text_$i"] = [
            '#type' => 'textfield',
            '#title' => 'Breadcrumb Text',
            '#description' => '',
            '#default_value' => '',
        ];

        // Set route field
        $form["url_$i"] = [
            '#type' => 'textfield',
            '#title' => 'Breadcrumb URL',
            '#description' => '',
            '#default_value' => '',
        ];

    }
 
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Set Breadcrumbs',
      '#tableselect' => False,
      '#tabledrag' => False,
    ];

    $form_state->disableRedirect(true);

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    
  }
}


<?php

namespace Drupal\napcs_model_law\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Form to download NAPCS Model Law data templates.
 */
class ModelLawDownloadForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'napcs_model_law_download_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    foreach ($this->getDownloadManager()->getDefinitions() as $plugin) {
      $options[$plugin['id']] = $plugin['name'];
    }
    $form['plugin'] = [
      '#type' => 'select',
      '#title' => 'Select data to download',
      '#options' => $options,
      '#required' => TRUE,
    ];
    $form['download'] = [
      '#type' => 'submit',
      '#value' => t('Download'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // No validation.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $plugin = $form_state->getValue('plugin');
    $download = $this->getDownloadManager()->createInstance($plugin);
    try {
      $file = $download->download();
      $filename = "$plugin-" . date('Y-m-d-His') . '.csv';
      $response = new BinaryFileResponse($file);
      $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
      $form_state->setResponse($response);
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
    }
  }

  /**
   * Return the download manager.
   */
  protected function getDownloadManager() {
    return \Drupal::service('plugin.manager.model_law_download');
  }

}

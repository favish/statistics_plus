<?php

/**
 * @file
 * Contains \Drupal\statistics_plus\Form\SettingsForm.
 */
namespace Drupal\statistics_plus\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

class StatisticsPlusSettingsForm extends ConfigFormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   * The unique string identifying the form.
   */
  public function getFormId() {
    return 'statistics_plus_settings_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   * An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * The current state of the form.
   *
   * @return array
   * The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('statistics_plus.settings');
    $form['enable_fuzz'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Fuzz'),
      '#description' => $this->t('Check to enable fuzz on entity view counts.'),
      '#default_value' => $config->get('enable_fuzz')
    ];
    $form['icon_placement'] = [
      '#type' => 'select',
      '#options' => ['before' => 'Before', 'after' => 'After'],
      '#title' => $this->t('Icon Placement'),
      '#description' => $this->t('Set whether to position the icon relative to the view count text.'),
      '#default_value' => $config->get('icon_placement')
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   * An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('statistics_plus.settings');
    $config
      ->set('enable_fuzz', $form_state->getValue('enable_fuzz'))
      ->set('icon_placement', $form_state->getValue('icon_placement'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['statistics_plus.settings'];
  }
}
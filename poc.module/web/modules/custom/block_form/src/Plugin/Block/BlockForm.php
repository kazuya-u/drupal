<?php

namespace Drupal\block_form\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Annotation.
 *
 * @Block(
 *   id = "custom_block_ume",
 *   admin_label = @Translation("umeki"),
 * )
 */
class BlockForm extends BlockBase {

  /**
   * This is build().
   */
  public function build() {
    $config = $this->getConfiguration();
    $name = isset($config['name']) ? $config['name'] : 'word';
    // kint($config);
    // exit;
    return [
      '#type' => 'fieldset',
      '#title' => $config['label'],
      'umeki' => [
        '#title' => 'umeki',
        '#type' => 'markup',
        '#markup' => $name,
      ],
    ];
  }

  /**
   * This is blockForm().
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#description' => $this->t('好きな文字を入力してください'),
      // '#defalut_value' => $config['name'] ?: '',
      '#defalut_value' => isset($config['name']) ? $config['name'] : '',
    ];
    return $form;
  }

  /**
   * This is blockSubmit().
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['name'] = $form_state->getValue('name');
    $this->configuration['umeki'] = 'umeume';
  }

}

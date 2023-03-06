<?php

namespace Drupal\edit_with_modal\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'article' block.
 *
 * @Block(
 *   id = "favorite_links",
 *   admin_label = @Translation("favorite links"),
 *   category = @Translation("Custom example")
 * )
 */
class AddNodeToBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $form = \Drupal::formBuilder()->getForm('Drupal\edit_with_modal\Form\AddNodeToForm');

    return $form;
  }

}

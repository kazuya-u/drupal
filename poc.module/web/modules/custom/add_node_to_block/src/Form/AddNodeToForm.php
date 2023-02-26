<?php

namespace Drupal\add_node_to_block\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * This is .
 */
class AddNodeToForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_node_to_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['hoge'] = [
      '#type' => 'fieldset',
      '#title' => '記事投稿ブロック',
      '#markup' => 'ブロックから記事の作成を行います。',
      'title' => [
        '#type' => 'textfield',
        '#title' => 'title',
      ],
      'text' => [
        '#type' => 'textfield',
        '#title' => 'body',
      ],
      'submit' => [
        '#name' => '送信',
        '#value' => '送信',
        '#type' => 'submit',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $hoge = $form_state->getValues();
    $title = $hoge['title'];
    $body = $hoge['text'];
    $node = Node::create([
      'type' => 'article',
      'title' => $title,
      'body' => $body,
    ]);
    $node->save();
  }

}

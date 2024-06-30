<?php

namespace Drupal\views_pager_token\Plugin\views\pager;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsPager;
use Drupal\views\Plugin\views\pager\PagerPluginBase;

/**
 * Plugin for views without pagers. Available to enter token.
 *
 * @ingroup views_pager_plugins
 */
#[ViewsPager(
  id: "some_token",
  title: new TranslatableMarkup("@title @token", [
    '@title' => "Display a specified number of items",
    '@token' => "(Available Token.)",
  ]),
  help: new TranslatableMarkup("Display a limited number items that this view might find."),
  display_types: ["basic"],
)]
final class SomeToken extends PagerPluginBase {

  /**
   * {@inheritDoc}
   */
  public function summaryTitle() {
    return '設定';
  }

  /**
   * {@inheritDoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['limit'] = ['default' => 10];
    $options['offset'] = ['default' => 0];

    return $options;
  }

  /**
   * {@inheritDoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $pager_text = $this->displayHandler->getPagerText();
    $form['limit'] = [
      '#type' => 'textfield',
      '#title' => $pager_text['items per page title'],
      '#description' => <<<HTML
      {$pager_text['items per page description']}<br>
      Token entry is available for use.
      HTML,
      '#default_value' => $this->options['limit'] ?? '0',
    ];

    $form['help_token'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => 'all',
      '#global_types' => TRUE,
      '#click_insert' => TRUE,
      '#show_restricted' => FALSE,
      '#recursion_limit' => 3,
      '#text' => $this->t('Browse available tokens'),
    ];

    $form['offset'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Offset (number of items to skip)'),
      '#description' => $this->t('For example, set this to 3 and the first 3 items will not be displayed.'),
      '#default_value' => $this->options['offset'],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getItemsPerPage() {
    return $this->replaceToken($this->options['limit'] ?? 0);
  }

  /**
   * Replace Token.
   */
  private function replaceToken($argument): int {
    $token_service = \Drupal::token();
    $value = $token_service->replace($argument);
    if (is_numeric($value)) {
      return $value;
    }
    return (int) $value ?: 0;
  }

  /**
   * {@inheritDoc}
   */
  public function usePager() {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function useCountQuery() {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $this->view->query->setLimit($this->replaceToken($this->options['limit'] ?? 0) ?: 0);
    $this->view->query->setOffset($this->options['offset']);
  }

  /**
   * {@inheritdoc}
   */
  public function postExecute(&$result): void {
    $this->total_items = count($result);
  }

}

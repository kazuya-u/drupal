<?php

declare(strict_types=1);

namespace Drupal\japanese_era_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeFormatterBase;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\japanese_era_formatter\Enum\Era;

/**
 * Plugin implementation of the 'Japanese Era' formatter for 'datetime' fields.
 *
 * @FieldFormatter(
 *   id = "datetime_japanese_era",
 *   label = @Translation("Japanese Era"),
 *   field_types = {"datetime"},
 * )
 */
final class JapaneseEraDateFormatter extends DateTimeFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'date_format' => DateTimeItemInterface::DATETIME_STORAGE_FORMAT,
      'format' => '@datetime',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['date_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date/time format'),
      '#description' => $this->t('See <a href="https://www.php.net/manual/datetime.format.php#refsect1-datetime.format-parameters" target="_blank">the documentation for PHP date formats</a>.'),
      '#default_value' => $this->getSetting('date_format'),
    ];
    $form['format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Format'),
      '#description' => $this->t('"@datetime", "@era" and "@since_year" can be replaced with appropriate phrases.<br>e.g. 「@datetime （@era）」'),
      '#default_value' => $this->getSetting('format'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatDate($date) {
    $settings_date_format = $this->getSetting('date_format');
    $settings_format = $this->getSetting('format');
    $settings_timezone = $this->getSetting('timezone_override') ?: $date->getTimezone()->getName();
    $formatted_date = $this->dateFormatter->format($date->getTimestamp(), 'custom', $settings_date_format, $settings_timezone != '' ? $settings_timezone : NULL);

    $era = Era::fromDateTime($date);
    $settings_format = str_replace('@datetime', $formatted_date, $settings_format);
    $settings_format = str_replace('@era', $era->getJapaneseName(), $settings_format);
    $settings_format = str_replace('@since_year', (string) $era->yearSinceStartEra($date), $settings_format);

    return $settings_format;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if (!empty($item->date)) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date = $item->date;

        $elements[$delta] = $this->buildDate($date);
      }
    }

    return $elements;
  }

}

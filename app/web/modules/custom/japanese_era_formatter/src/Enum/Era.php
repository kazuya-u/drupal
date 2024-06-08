<?php

namespace Drupal\japanese_era_formatter\Enum;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * 元号.
 */
enum Era: string {
  case Meiji = 'M';
  case Taisho = 'T';
  case Showa = 'S';
  case Heisei = 'H';
  case Reiwa = 'R';

  /**
   * 元号の開始日を取得.
   */
  public function getStartDate(): string {
    return match($this) {
      self::Meiji => '1873-01-01',
      self::Taisho => '1912-07-30',
      self::Showa => '1926-12-25',
      self::Heisei => '1989-01-08',
      self::Reiwa => '2019-05-01',
    };
  }

  /**
   * 和暦名を取得.
   */
  public function getJapaneseName(): string {
    return match($this) {
      self::Meiji => '明治',
      self::Taisho => '大正',
      self::Showa => '昭和',
      self::Heisei => '平成',
      self::Reiwa => '令和',
    };
  }

  /**
   * Datetimeオブジェクトから元号を取得.
   */
  public static function fromDateTime(DrupalDateTime $date): ?self {
    foreach (array_reverse(self::cases()) as $case) {
      if ($date >= $case->getStartDate()) {
        return $case;
      }
    }
    // 日付がどの元号にも属していない場合.
    return NULL;
  }

  /**
   * 元号が開始して何年かを取得.
   */
  public function yearSinceStartEra($datetime): int {
    $startdate = new DrupalDateTime($this->getStartDate());
    return $startdate->diff($datetime)->y + 1;
  }

}

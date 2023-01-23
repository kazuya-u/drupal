<?php

namespace Drupal\jmeter\Form;

use Drupal;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Psr\Container\ContainerInterface;
class JmeterForm extends FormBase {

  /**
   *
   * @var Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Constructs.
   *
   * @param Drupal\Core\File\FileSystem $fileSystemInterface
   */
  public function __construct(FileSystemInterface $fileSystemInterface) {
    $this->fileSystem = $fileSystemInterface;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system')
    );
  }

  const JMETER_SERVER = [
    'jmeter01',
    'jmeter02',
  ];

  /**
   * {@inheritDoc}
   */
  public function getFormId()
  {
    return 'jmeter_toggle';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $file_dir = explode('docroot', __DIR__);
    $takayama_file = $file_dir['0'] . 'files-private/takayama/start.txt';
    $file_dir = $file_dir['0'] . 'files-private/jmeter/';

    foreach(self::JMETER_SERVER as $server) {
      $user_name = file_exists($file_dir . $server . '.txt') ? file_get_contents($file_dir . $server . '.txt') : '';
      $form[$server . 'status'] = [
        '#type' => 'textfield',
        '#value' => file_exists($file_dir . $server . '.txt') ? $user_name . 'が' . $server . '起動中。' : $server . '停止中。',
        '#disabled' => true,
      ];
      $form[$server . 'start'] = [
        '#type' => 'submit',
        '#value' => $server . ' Start',
        '#disabled' => file_exists($takayama_file) ? true :  file_exists($file_dir . $server . '.txt'),
      ];
      $form[$server . 'stop'] = [
        '#type' => 'submit',
        '#value' => $server . ' Stop',
        '#disabled' => !file_exists($file_dir . $server . '.txt'),
      ];
    }
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $file_dir = explode('docroot', __DIR__);
    $file_dir = $file_dir['0'] . 'files-private/jmeter/';
    $user_name = \Drupal::currentUser()->getDisplayName();
    $form_input = $form_state->getUserInput();
    $form_input_op = $form_input['op'];
    if (preg_match('/^(?<server>[^\s]+) (?<mode>[^\s]+)$/', $form_input_op, $matches) && in_array($matches['server'], self::JMETER_SERVER, TRUE)) {
      $file_name = $file_dir . $matches['server'] . '.txt';
      if ($matches['mode'] === 'Start') {
        // Create file.
        touch($file_name);
        $this->messenger()->addStatus($this->t($matches['server'] . 'が起動しました。 - 使用者：' . $user_name));
        file_put_contents($file_name, $user_name);
      }
      else {
        // Delete file.
        \Drupal::service('file_system')->unlink($file_name);
        $this->messenger()->addStatus($this->t($matches['server'] . 'が停止しました。'));
      }
    }
  }
}

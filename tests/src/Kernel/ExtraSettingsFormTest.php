<?php

declare (strict_types = 1);

namespace Drupal\Tests\tawk_to\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\tawk_to\Form\TawkToExtraSettingsForm;

/**
 * Test tawk_to extra settings via API.
 *
 * @group tawk_to
 */
class ExtraSettingsFormTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'tawk_to',
    'system',
    'path',
    'path_alias',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system']);
    $this->installConfig(['tawk_to']);
    $this->installConfig(['path']);
    $this->installEntitySchema('path_alias');
  }

  /**
   * Tests the form is showing configuration updates.
   */
  public function testFormShowConfigurationUpdates(): void {
    $this->config('tawk_to.settings')
      ->set('visibility', [
        'request_path' => [
          'id' => 'request_path',
          'pages' => '<front>',
          'negate' => FALSE,
          'context_mapping' => [],
        ],
      ])
      ->set('show_user_name', TRUE)
      ->set('user_name', 'test')
      ->set('show_user_email', TRUE)
      ->set('user_email', 'test@example.com')
      ->save();
    $form = \Drupal::formBuilder()->getForm(TawkToExtraSettingsForm::class);

    $this->assertEquals($form['user']['user']['show_user_name']['#default_value'], TRUE);
    $this->assertEquals($form['user']['user']['user_name']['#default_value'], 'test');
    $this->assertEquals($form['user']['user']['show_user_email']['#default_value'], TRUE);
    $this->assertEquals($form['user']['user']['user_email']['#default_value'], 'test@example.com');
    $this->assertEquals($form['visibility']['request_path']['pages']['#default_value'], '<front>');
  }

}

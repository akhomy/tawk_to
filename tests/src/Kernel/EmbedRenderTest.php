<?php

declare (strict_types = 1);

namespace Drupal\Tests\tawk_to\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Test tawk_to render via API.
 *
 * @group tawk_to
 */
class EmbedRenderTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'common_test',
    'tawk_to',
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
    $this->config('tawk_to.settings')
      ->set('show_user_name', TRUE)
      ->set('user_name', 'test')
      ->set('show_user_email', TRUE)
      ->set('user_email', 'test@example.com')
      ->set('tawk_to_widget_page_id', 'TEST_WIDGET_PAGE_ID')
      ->set('tawk_to_widget_id', 'TEST_WIDGET_ID')
      ->save();
  }

  /**
   * Tests the form is showing configuration updates.
   */
  public function testEmbedRenderPageBottom(): void {
    $html_renderer = \Drupal::getContainer()->get('main_content_renderer.html');

    $page = [];
    // Build page top and bottom in order to trigger tawk_to hook_page_bottom().
    $html_renderer->buildPageTopAndBottom($page);

    // Checks if the tawk.to widget is rendered as expected from configuration.
    $widget_render = $page['page_bottom']['tawk_to_widget']['#items'];
    $this->assertEquals('TEST_WIDGET_PAGE_ID', $widget_render['page_id']);
    $this->assertEquals('TEST_WIDGET_ID', $widget_render['widget_id']);
    $this->assertEquals('test', $widget_render['user_name']);
    $this->assertEquals('test@example.com', $widget_render['user_email']);

  }

}

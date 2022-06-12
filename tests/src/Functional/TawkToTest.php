<?php

declare (strict_types = 1);

namespace Drupal\Tests\tawk_to\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests tawk_to functionality.
 *
 * @group tawk_to
 */
class TawkToTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'tawk_to',
    'system',
    'path',
    'path_alias',
    'test_page_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $web_user = $this->drupalCreateUser([
      'access content',
      'administer tawk_to settings',
    ]);
    $this->drupalLogin($web_user);

    // Configures tawk_to.
    $this->config('tawk_to.settings')
      ->set('tawk_to_widget_page_id', 'TEST_WIDGET_PAGE_ID')
      ->set('tawk_to_widget_id', 'TEST_WIDGET_ID')
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
    // Use the test page as the front page.
    $this->config('system.site')->set('page.front', '/test-page')->save();
  }

  /**
   * Tests the main settings page.
   */
  public function testTawkToSettingsPage(): void {
    $this->drupalGet('admin/config/services/tawk_to/widget');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', 'iframe#tawkIframe');
  }

  /**
   * Tests the extra settings page.
   */
  public function testTawkToExtraSettingsPage(): void {
    $this->drupalGet('admin/config/services/tawk_to/exta_settings');
    $edit = [];
    $edit['user']['user']['show_user_name'] = FALSE;
    $edit['user']['user']['show_user_email'] = FALSE;
    $this->submitForm($edit, 'Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests the widget is rendered as configured on the front page.
   */
  public function testTawkToWidgetRenrederOnTheFrontPage(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('TEST_WIDGET_PAGE_ID');
  }

}

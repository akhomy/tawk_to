<?php

namespace Drupal\tawk_to\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\tawk_to\Cache\TawkToCacheManager;
use Drupal\Core\Utility\Token;

/**
 * Defines the rendering tawk.to service.
 */
class TawkToEmbedRender {

  /**
   * The tawk.to embed URL.
   */
  const EMBED_URL = 'https://embed.tawk.to';

  /**
   * The widget page id.
   *
   * @var string
   */
  protected $widgetPageId;

  /**
   * The widget id.
   *
   * @var string
   */
  protected $widgetId;

  /**
   * The user name.
   *
   * @var string
   */
  protected $userName;

  /**
   * The user email.
   *
   * @var string
   */
  protected $userEmail;

  /**
   * The cache manager.
   *
   * @var \Drupal\tawk_to\Cache\TawkToCacheManager
   */
  protected $cacheManager;

  /**
   * The condition plugin defination.
   *
   * @var \Drupal\tawk_to\Service\TawkToConditionPluginsHandler
   */
  protected $conditionPluginsHandler;

  /**
   * Constructs the TawkToEmbedRender.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param Drupal\tawk_to\Service\TawkToConditionPluginsHandler $conditionPluginsHandler
   *   The tawk.to access controller handler.
   * @param Drupal\tawk_to\Cache\TawkToCacheManager $cacheManager
   *   The cache manager.
   */
  public function __construct(ConfigFactoryInterface $configFactory, Token $token, TawkToConditionPluginsHandler $conditionPluginsHandler, TawkToCacheManager $cacheManager) {
    $this->conditionPluginsHandler = $conditionPluginsHandler;
    $this->cacheManager = $cacheManager;
    $this->widgetPageId = $configFactory->get('tawk_to.settings')->get('tawk_to_widget_page_id');
    $this->widgetId = $configFactory->get('tawk_to.settings')->get('tawk_to_widget_id');
    if ($configFactory->get('tawk_to.settings')->get('show_user_name')) {
      $userName = $configFactory->get('tawk_to.settings')->get('user_name');
      $this->userName = $token->replace($userName, [], ['clear' => TRUE]);
    }
    if ($configFactory->get('tawk_to.settings')->get('show_user_email')) {
      $userEmail = $configFactory->get('tawk_to.settings')->get('user_email');
      $this->userEmail = $token->replace($userEmail, [], ['clear' => TRUE]);
    }
  }

  /**
   * Checks acess to the current requests and return renderable array or NULL.
   *
   * @return array|null
   *   The render renderable array or NULL.
   */
  public function render() {
    if ($this->widgetPageId === '' || $this->widgetId === '') {
      return NULL;
    }
    if ($this->conditionPluginsHandler->checkAccess()) {
      $userName = $userEmail = '';
      if (TRUE) {
        $userName = \Drupal::token()->replace($userName, [], ['clear' => TRUE]);
      }
      return [
        '#theme' => 'tawk_to',
        '#items' => [
          'page_id' => $this->widgetPageId,
          'widget_id' => $this->widgetId,
          'embed_url' => self::EMBED_URL,
          'user_name' => $this->userName,
          'user_email' => $this->userEmail,
        ],
        '#cache' => [
          'contexts' => $this->cacheManager->getCacheContexts(),
          'tags' => $this->cacheManager->getCacheTags(),
        ],
      ];
    }
    return NULL;
  }

}

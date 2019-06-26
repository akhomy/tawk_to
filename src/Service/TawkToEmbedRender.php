<?php

namespace Drupal\tawk_to\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\tawk_to\Cache\TawkToCacheManager;

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
   * @param Drupal\tawk_to\Service\TawkToConditionPluginsHandler $conditionPluginsHandler
   *   The tawk.to access controller handler.
   * @param Drupal\tawk_to\Cache\TawkToCacheManager $cacheManager
   *   The cache manager.
   */
  public function __construct(ConfigFactoryInterface $configFactory, TawkToConditionPluginsHandler $conditionPluginsHandler, TawkToCacheManager $cacheManager) {
    $this->widgetPageId = $configFactory->get('tawk_to.settings')->get('tawk_to_widget_page_id');
    $this->widgetId = $configFactory->get('tawk_to.settings')->get('tawk_to_widget_id');
    $this->conditionPluginsHandler = $conditionPluginsHandler;
    $this->cacheManager = $cacheManager;
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
      return [
        '#theme' => 'tawk_to',
        '#items' => [
          'page_id' => $this->widgetPageId,
          'widget_id' => $this->widgetId,
          'embed_url' => self::EMBED_URL,
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

<?php

namespace Drupal\tawk_to\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines the rendering tawk.to service.
 */
class TawkToEmbedRender {

  /**
   * The tawk.to embed URL.
   */
  const TAWK_TO_EMBED_URL = 'https://embed.tawk.to';

  /**
   * The widget page id.
   *
   * @var array
   */
  protected $tawkToWidgetPageId;

  /**
   * The widget id.
   *
   * @var array
   */
  protected $tawkToWidgetId;

  /**
   * The condition plugin defination.
   *
   * @var array
   */
  protected $tawkToAccessControlHandler;

  /**
   * Constructs the TawkToEmbedRender.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param Drupal\tawk_to\Service\TawkToAccessControlHandler $tawkToAccessControlHandler
   *   The tawk.to access controller handler.
   */
  public function __construct(ConfigFactoryInterface $configFactory, TawkToAccessControlHandler $tawkToAccessControlHandler) {
    $this->tawkToWidgetPageId = $configFactory->get('tawk_to.settings')->get('tawk_to_widget_page_id');
    $this->tawkToWidgetId = $configFactory->get('tawk_to.settings')->get('tawk_to_widget_id');
    $this->tawkToAccessControlHandler = $tawkToAccessControlHandler;
  }

  /**
   * Checks acess to the current requests and return renderable array or NULL.
   *
   * @return array|null
   *   The render renderable array or NULL.
   */
  public function render() {
    if ($this->tawkToWidgetPageId === '' || $this->tawkToWidgetId === '') {
      return NULL;
    }
    if ($this->tawkToAccessControlHandler->checkAccess()) {
      $items = [];
      $items['page_id'] = $this->tawkToWidgetPageId;
      $items['embed_url'] = self::TAWK_TO_EMBED_URL;
      $items['widget_id'] = $this->tawkToWidgetId;
      return ['#theme' => 'tawk_to', '#items' => $items];
    }
    return NULL;
  }

}

<?php

namespace Drupal\tawk_to\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Controller routine that manages tawk.to widget settings.
 */
class TawkToWidgetController extends ControllerBase {

  /**
   * The tawk.to plugins URL.
   */
  const TAWK_TO_PLUGINS_URL = 'https://plugins.tawk.to';

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  public $request;

  /**
   * The settings config.
   *
   * @var \Drupal\Core\Config\Config
   */
  public $config;

  /**
   * Constructs a TawkToWidgetController object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory service.
   */
  public function __construct(RequestStack $request, ConfigFactoryInterface $config) {
    $this->request = $request;
    $this->config = $config->getEditable('tawk_to.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('request_stack'),
        $container->get('config.factory')
    );
  }

  /**
   * Constructs url for configuration iframe.
   *
   * @return string
   *   Base iframe URL.
   */
  private function tawkToGetIframeUrl() {
    $pageId = $this->config->get('tawk_to_widget_page_id');
    $widgetId = $this->config->get('tawk_to_widget_id');
    return self::TAWK_TO_PLUGINS_URL . '/generic/widgets?currentWidgetId=' . $widgetId . '&currentPageId=' . $pageId;
  }

  /**
   * Constructs a widgets content.
   *
   * @return array
   *   Renderable array.
   */
  public function widgetsContent() {
    $items = [];
    $items['baseUrl'] = self::TAWK_TO_PLUGINS_URL;
    $items['iframeUrl'] = $this->tawkToGetIframeUrl();
    return ['#theme' => 'tawk_to_iframe', '#items' => $items];
  }

  /**
   * Callback for settting widget with ajax in tawk.to iframe.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON object for JS code.
   */
  public function setWidget() {
    $pageId = $this->request->getCurrentRequest()->get('pageId');
    $widgetId = $this->request->getCurrentRequest()->get('widgetId');

    if (!$pageId || !$widgetId) {
      return new JsonResponse(['success' => FALSE]);
    }

    if (preg_match('/^[0-9A-Fa-f]{24}$/', $pageId) !== 1 || preg_match('/^[a-z0-9]{1,50}$/i', $widgetId) !== 1) {
      return new JsonResponse(['success' => FALSE]);
    }
    $this->config->set('tawk_to_widget_page_id', $pageId)->save();
    $this->config->set('tawk_to_widget_id', $widgetId)->save();

    return new JsonResponse(['success' => TRUE]);
  }

  /**
   * Callback for removing widget with ajax in tawk.to iframe.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON object for JS code.
   */
  public function removeWidget() {
    $this->config->clear('tawk_to_widget_page_id')->save();
    $this->config->clear('tawk_to_widget_id')->save();
    return new JsonResponse(['success' => TRUE]);
  }

}

<?php

declare(strict_types = 1);

namespace Drupal\tawk_to\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * @var \Drupal\Core\Config\StorableConfigBase
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
  public function __construct(RequestStack $request, ConfigFactoryInterface $config, LanguageManagerInterface $languageManager) {
    $this->request = $request;
    $this->languageManager = $languageManager;
    $currentLanguage = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $this->config = $config->getEditable('tawk_to.settings');
    // Allows saving of the widget settings form multiple languages.
    if ($this->languageManager instanceof ConfigurableLanguageManagerInterface) {
      /** @var \Drupal\language\ConfigurableLanguageManagerInterface $languageManager */
      $languageManager = $this->languageManager;
      $configOverride = $languageManager
        ->getLanguageConfigOverride($currentLanguage, 'tawk_to.settings');
      if (!$configOverride->isNew()) {
        $this->config = $configOverride;
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * Constructs url for configuration iframe.
   *
   * @return string
   *   Base iframe URL.
   */
  private function tawkToGetIframeUrl(): string {
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
  public function widgetsContent(): array {
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
  public function setWidget(): JsonResponse {
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
  public function removeWidget(): JsonResponse {
    $this->config->clear('tawk_to_widget_page_id')->save();
    $this->config->clear('tawk_to_widget_id')->save();
    return new JsonResponse(['success' => TRUE]);
  }

}

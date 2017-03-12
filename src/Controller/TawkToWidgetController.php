<?php

namespace Drupal\tawk_to\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller routines for page example routes.
 */
class TawkToWidgetController extends ControllerBase {

  /**
   * Request stack.
   *
   * @var RequestStack
   */
  public $request;

  /**
   * Constructs a TawkToWidgetController object.
   */
  public function __construct(RequestStack $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
        // Load the service required to construct this class.
        $container->get('request_stack')
    );
  }

  /**
   * Base url for tawk.to application which serves iframe.
   */
  private function tawkToGetBaseUrl() {
    return 'https://plugins.tawk.to';
  }

  /**
   * Retrieves widget details from database.
   */
  public static function tawkToGetWidget() {
    // Rewrite based on config.
    $config = \Drupal::config('tawk_to.settings');
    return array(
      'page_id' => $config->get('tawk_to_widget_page_id'),
      'widget_id' => $config->get('tawk_to_widget_widget_id'),
    );
  }

  /**
   * Constructs url for configuration iframe.
   */
  private function tawkToGetIframeUrl() {

    $widget = TawkToWidgetController::tawkToGetWidget();

    if (!$widget) {
      $widget = array(
        'page_id' => '',
        'widget_id' => '',
      );
    }

    return $this->tawkToGetBaseUrl() . '/generic/widgets?currentWidgetId=' . $widget['widget_id'] . '&currentPageId=' . $widget['page_id'];
  }

  /**
   * Creates markup for settings page.
   */
  public function tawkToRenderWidgetIframe($baseUrl, $iframeUrl) {
    return '<script type="text/javascript" src="' . $baseUrl . '/public/js/jquery-1.11.0.min.js"></script>

  <iframe
      id="tawkIframe"
      src=""
      style="min-height: 400px; width : 100%; border: none">
  </iframe>

  <script type="text/javascript">
      var currentHost = window.location.protocol + "//" + window.location.host;
      var url = "' . $iframeUrl . '&parentDomain=" + currentHost;

      jQuery("#tawkIframe").attr("src", url);

      var iframe = jQuery("#tawk_widget_customization")[0];

      window.addEventListener("message", function(e) {
          if(e.origin === "' . $baseUrl . '") {

              if(e.data.action === "setWidget") {
                  setWidget(e);
              }

              if(e.data.action === "removeWidget") {
                  removeWidget(e);
              }
          }
      });

      function setWidget(e) {
          jQuery.post("/admin/config/tawk/setwidget", {
              pageId : e.data.pageId,
              widgetId : e.data.widgetId
          }, function(r) {
              if(r.success) {
                  e.source.postMessage({action: "setDone"}, "' . $baseUrl . '");
              } else {
                  e.source.postMessage({action: "setFail"}, "' . $baseUrl . '");
              }
          });
      }

      function removeWidget(e) {
          jQuery.post("/admin/config/tawk/removewidget", function(r) {
              if(r.success) {
                  e.source.postMessage({action: "removeDone"}, "' . $baseUrl . '");
              } else {
                  e.source.postMessage({action: "removeFail"}, "' . $baseUrl . '");
              }
          });
      }
  </script>';
  }

  /**
   * Constructs a page with descriptive content.
   *
   * Our router maps this method to the path 'admin/config/tawk/widget'.
   */
  public function content() {
    $baseUrl = $this->tawkToGetBaseUrl();
    $iframeUrl = $this->tawkToGetIframeUrl();
    return array(
      '#markup' => Markup::create($this->tawkToRenderWidgetIframe($baseUrl, $iframeUrl)),
    );
  }

  /**
   * Callback for set widget via ajax in TawkTo iframe.
   *
   * @see tawkToRenderWidgetIframe()
   * Our router maps this method to the path 'admin/config/tawk/setwidget'.
   */
  public function setWidget($pageId = NULL, $widgetId = NULL) {
    $pageId = $this->request->getCurrentRequest()->get('pageId');
    $widgetId = $this->request->getCurrentRequest()->get('widgetId');

    if (!$pageId || !$widgetId) {
      return new JsonResponse(array('success' => FALSE));
    }

    if (preg_match('/^[0-9A-Fa-f]{24}$/', $pageId) !== 1 || preg_match('/^[a-z0-9]{1,50}$/i', $widgetId) !== 1) {
      return new JsonResponse(array('success' => FALSE));
    }
    $config = \Drupal::configFactory()->getEditable('tawk_to.settings');
    $config->set('tawk_to_widget_page_id', $pageId)->save();
    $config->set('tawk_to_widget_widget_id', $widgetId)->save();

    return new JsonResponse(array('success' => TRUE));
  }

  /**
   * Callback for remove widget via ajax in TawkTo iframe.
   *
   * @see tawkToRenderWidgetIframe()
   * Our router maps this method to the path 'admin/config/tawk/removewidget'.
   */
  public function removeWidget() {
    $config = \Drupal::configFactory()->getEditable('tawk_to.settings');
    $config->clear('tawk_to_widget_page_id')->save();
    $config->clear('tawk_to_widget_widget_id')->save();
    return new JsonResponse(array('success' => TRUE));
  }

}

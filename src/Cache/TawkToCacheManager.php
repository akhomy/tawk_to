<?php

namespace Drupal\tawk_to\Cache;

use Drupal\tawk_to\Service\TawkToConditionPluginsHandler;

/**
 * Defines the cache manager tawk.to service.
 */
class TawkToCacheManager {

  /**
   * The condition plugin defination.
   *
   * @var \Drupal\tawk_to\Service\TawkToConditionPluginsHandler
   */
  protected $conditionsPluginsHandler;

  /**
   * Constructs the TawkToCacheManager.
   *
   * @param Drupal\tawk_to\Service\TawkToConditionPluginsHandler $conditionsPluginsHandler
   *   The tawk.to access controller handler.
   */
  public function __construct(TawkToConditionPluginsHandler $conditionsPluginsHandler) {
    $this->conditionsPluginsHandler = $conditionsPluginsHandler;
  }

  /**
   * Gets cache tags based on the module settings and context plugins tags.
   *
   * @return array
   *   The cache tags.
   */
  public function getCacheTags() {
    $tags = ['config:tawk_to.settings'];
    $conditions = $this->conditionsPluginsHandler->getConditions();
    foreach ($conditions as $condition) {
      if ($condition instanceof CacheableDependencyInterface) {
        $tags = array_merge($tags, $condition->getCacheTags());
      }
    }
    return $tags;
  }

  /**
   * Gets cache tags based on the module settings and context plugins tags.
   *
   * @return array
   *   The cache tags.
   */
  public function getCacheContexts() {
    $contexts = ['session'];
    $conditions = $this->conditionsPluginsHandler->getConditions();
    foreach ($conditions as $condition) {
      if ($condition instanceof CacheableDependencyInterface) {
        $contexts = array_merge($contexts, $condition->getCacheContexts());
      }
    }
    return $contexts;
  }

}

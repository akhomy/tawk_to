<?php

namespace Drupal\tawk_to\Service;

use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines the access control handler for the block entity type.
 */
class TawkToAccessControlHandler {

  use ConditionAccessResolverTrait;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $manager;

  /**
   * The condition plugin defination.
   *
   * @var array
   */
  protected $tawkToVisibility;

  /**
   * Constructs the tawk.to access control handler instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $manager
   *   The ConditionManager for building the conditions based on the defination.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ExecutableManagerInterface $manager) {
    $this->tawkToVisibility = $config_factory->get('tawk_to.settings')->get('visibility');
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess() {
    $conditions = [];
    if (!empty($this->tawkToVisibility)) {
      foreach ($this->tawkToVisibility as $condition_id => $defination) {
        $conditions[$condition_id] = $this->manager->createInstance($condition_id, $defination);
      }
      return $this->resolveConditions($conditions, 'and');
    }
    return TRUE;
  }

}

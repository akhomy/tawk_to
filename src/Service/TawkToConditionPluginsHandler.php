<?php

declare(strict_types = 1);

namespace Drupal\tawk_to\Service;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;

/**
 * Defines the condition plugins handler.
 */
class TawkToConditionPluginsHandler {

  use ConditionAccessResolverTrait;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $contextHandler
   *   The ContextHandler for applying contexts to conditions properly.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $contextRepository
   *   The lazy context repository service.
   * @param \Drupal\Core\Condition\ConditionManager $manager
   *   The ConditionManager for building the visibility UI.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ContextHandlerInterface $contextHandler, ContextRepositoryInterface $contextRepository, ConditionManager $manager) {
    $this->tawkToVisibility = $configFactory->get('tawk_to.settings')->get('visibility');
    $this->contextHandler = $contextHandler;
    $this->contextRepository = $contextRepository;
    $this->manager = $manager;
  }

  /**
   * Checks conditions access to the widget.
   *
   * @return bool
   *   TRUE if access to widget allowed, FALSE otherwise.
   */
  public function checkAccess(): bool {
    $conditions = [];
    if (!empty($this->tawkToVisibility)) {
      $conditions = $this->getConditions();
      return $this->resolveConditions($conditions, 'and');
    }
    return TRUE;
  }

  /**
   * Gets condition plugins based on the module configuration.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]
   *   A set of conditions.
   */
  public function getConditions(): array {
    $conditions = [];
    foreach ($this->tawkToVisibility as $conditionId => $configuration) {
      $condition = $this->manager->createInstance($conditionId, $configuration);
      if ($condition instanceof ContextAwarePluginInterface) {
        try {
          $contextMapping = $condition->getContextMapping();
          if ($contextMapping) {
            $contexts = $this->contextRepository->getRuntimeContexts(array_values($contextMapping));
            $this->contextHandler->applyContextMapping($condition, $contexts);
          }
        }
        catch (ContextException $e) {
          // @todo Think about a better way to handle this.
        }
      }
      $conditions[$conditionId] = $condition;
    }
    return $conditions;
  }

}

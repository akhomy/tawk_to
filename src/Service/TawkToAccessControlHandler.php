<?php

namespace Drupal\tawk_to\Service;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines the access control handler for the block entity type.
 */
class TawkToAccessControlHandler {

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
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $manager
   *   The ConditionManager for building the visibility UI.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ContextHandlerInterface $contextHandler, ContextRepositoryInterface $contextRepository, ExecutableManagerInterface $manager) {
    $this->tawkToVisibility = $configFactory->get('tawk_to.settings')->get('visibility');
    $this->contextHandler = $contextHandler;
    $this->contextRepository = $contextRepository;
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess() {
    $conditions = [];
    if (!empty($this->tawkToVisibility)) {
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
            // @todo: Think the best way to handle this.
          }
        }
        $conditions[$conditionId] = $condition;
      }
      return $this->resolveConditions($conditions, 'and');
    }
    return TRUE;
  }

}

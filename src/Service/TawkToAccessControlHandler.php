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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The ContextHandler for applying contexts to conditions properly.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $manager
   *   The ConditionManager for building the visibility UI.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository, ExecutableManagerInterface $manager) {
    $this->tawkToVisibility = $config_factory->get('tawk_to.settings')->get('visibility');
    $this->contextHandler = $context_handler;
    $this->contextRepository = $context_repository;
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess() {
    $conditions = [];
    $missing_context = FALSE;
    if (!empty($this->tawkToVisibility)) {
      foreach ($this->tawkToVisibility as $condition_id => $configuration) {
        $condition = $this->manager->createInstance($condition_id, $configuration);
        if ($condition instanceof ContextAwarePluginInterface) {
          try {
            $context_mapping = $condition->getContextMapping();
            if ($context_mapping) {
              $contexts = $this->contextRepository->getRuntimeContexts(array_values($context_mapping));
              $this->contextHandler->applyContextMapping($condition, $contexts);
            }
          }
          catch (ContextException $e) {
            $missing_context = TRUE;
          }
        }
        $conditions[$condition_id] = $condition;
      }
      if ($missing_context) {
        return FALSE;
      }
      return $this->resolveConditions($conditions, 'and');
    }
    return TRUE;
  }

}

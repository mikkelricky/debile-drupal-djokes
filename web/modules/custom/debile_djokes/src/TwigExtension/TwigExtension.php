<?php

namespace Drupal\debile_djokes\TwigExtension;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension.
 */
class TwigExtension extends AbstractExtension {
  /**
   * The account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $account;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Constructor.
   */
  public function __construct(AccountInterface $account, ModuleHandlerInterface $moduleHandler) {
    $this->account = $account;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('is_granted', [$this, 'isGranted']),
    ];
  }

  /**
   * Is granted.
   *
   * Heavily inspired by
   * https://symfony.com/doc/current/reference/twig_reference.html#is-granted.
   *
   * Examples:
   *
   *   Check for role:           is_granted('editor')
   *   Check for permission:     is_granted('administer nodes')
   *   Check for access on node: is_granted('update', node)
   */
  public function isGranted(string $attribute = NULL, $object = NULL): bool {
    if (NULL !== $attribute) {
      // If no object is passed we Check for permission or role.
      if (NULL === $object) {
        if ($this->account->hasPermission($attribute)
          || in_array($attribute, $this->account->getRoles(), TRUE)) {
          return TRUE;
        }
      }

      // Check access on object.
      if (($object instanceof ContentEntityBase) && $object->access($attribute, $this->account)) {
        return TRUE;
      }

      // Let others decide.
      $votes = $this->moduleHandler->invokeAll(
        'os2loop_settings_is_granted',
        [
          $attribute,
          $object,
        ]
      );
      // If one computer says "Yes" we say "Yes".
      if (!empty(array_filter($votes))) {
        return TRUE;
      }
    }

    return FALSE;
  }

}

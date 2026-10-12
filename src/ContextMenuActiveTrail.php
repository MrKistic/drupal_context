<?php

namespace Drupal\context;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Extend the MenuActiveTrail class.
 */
class ContextMenuActiveTrail extends MenuActiveTrail {

  /**
   * The Context module context manager.
   *
   * @var \Drupal\context\ContextManager
   */
  protected $contextManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager, RouteMatchInterface $route_match, CacheBackendInterface $cache, LockBackendInterface $lock, PathMatcherInterface $pathMatcher, ContextManager $context_manager) {
    // Note: $pathMatcher provided in core since Drupal 11.2.0 - https://www.drupal.org/node/3523495.
    parent::__construct($menu_link_manager, $route_match, $cache, $lock, $pathMatcher);
    $this->contextManager = $context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    $found = parent::getActiveLink($menu_name);

    // Get active reaction of Menu type.
    foreach ($this->contextManager->getActiveReactions('menu') as $reaction) {
      $menu_items = $reaction->execute();
      foreach ($menu_items as $menu_link_content) {
        $menu = strtok($menu_link_content, ':');
        if ($menu == $menu_name) {
          $plugin_id = substr($menu_link_content, strlen($menu) + 1);
          return $this->menuLinkManager->createInstance($plugin_id);
        }
      }
    }
    return $found;
  }

}

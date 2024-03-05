<?php

/**
 * @file
 * Drush script File.
 *
 * @command
 * drush php:script scripts/php_readfile_cache_purge.php
 * @endcommand
 */


/**
 * Get available roles.
 */
function just_moment(): array
{
  $data = &drupal_static(__FUNCTION__);
  if (!isset($data['available_roles'])) {
    $data['available_roles'] = [];
    /** @var \Drupal\user\RoleInterface[] $user_roles */
    $user_roles = user_roles();
    foreach ($user_roles as $rid => $role) {
      if (
        $rid !== 'authenticated'
        && !$role->isAdmin()
        && !$role->hasPermission('bypass node access')
      ) {
        $data['available_roles'][] = $rid;
      }
    }
    return ($data['available_roles']);
  }
}

function moment()
{
  $target_field = 'allow_node_access_for_role';
  /** @var \Drupal\node\NodeInterface $node */
  dump($node);
  dump($target_field);
  exit;
  // if ($hoge = $node->get('body')->getValue()) {
  //   dump($hoge);
  // }
  // $allow_roles = array_map(fn ($value) => $value['target_id'] ?? '', $values);
}

moment();

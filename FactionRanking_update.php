<?php
// 0.2.0
function switch_table_name($old_table, $new_table) {

  global $db;

  $execute = true;

  try {
    $verif = $db->query('SHOW COLUMNS FROM '.$old_table.';');
  } catch(Exception $e) {
    $execute = false;
  }
  if(isset($verif) || empty($verif)) {
    $execute = false;
  }

  if($execute) {

    @$db->query('RENAME TABLE `'.$old_table.'` TO `'.$new_table.'`;');

  }

}
function add_column($table, $name, $sql) {

  global $db;

  $verif = $db->query('SHOW COLUMNS FROM '.$table.';');
  $execute = true;
  foreach ($verif as $k => $v) {
    if($v['COLUMNS']['Field'] == $name) {
      $execute = false;
      break;
    }
  }
  if($execute) {
    @$query = $db->query('ALTER TABLE `'.$table.'` ADD `'.$name.'` '.$sql.';');
  }
}
function remove_column($table, $name) {

  global $db;

  $verif = $db->query('SHOW COLUMNS FROM '.$table.';');
  $execute = false;
  foreach ($verif as $k => $v) {
    if($v['COLUMNS']['Field'] == $name) {
      $execute = true;
      break;
    }
  }
  if($execute) {
    @$query = $db->query('ALTER TABLE `'.$table.'` DROP COLUMN `'.$name.'`;');
  }
}
$users = array();
function author_to_userid($table, $column = 'author') {

  global $db;
  global $users;
  $verif = $db->query('SHOW COLUMNS FROM '.$table.';');
  $execute = false;
  foreach ($verif as $k => $v) {
    if($v['COLUMNS']['Field'] == $column) {
      $execute = true;
      break;
    }
  }
  if($execute) {

    $data = $db->query('SELECT * FROM '.$table);
    foreach ($data as $key => $value) {

      $table_author_id = $value[$table]['id'];
      $author_name = $value[$table][$column];

      if(isset($users[$author_name])) {
        $author_id = $users[$author_name];
      } else {
        // on le cherche
        $search_author = $db->query('SELECT id FROM users WHERE pseudo=\''.$author_name.'\'');
        if(!empty($search_author)) {
          $author_id = $users[$author_name] = $search_author[0]['users']['id'];
        } else {
          $author_id = $users[$author_name] = 0;
        }
      }

      // On leur met l'id
      $db->query('UPDATE '.$table.' SET user_id='.$author_id.' WHERE id='.$table_author_id);

      unset($table_author_id);
      unset($author_name);
      unset($author_id);
      unset($search_author);

    }
    unset($data);

    remove_column($table, $column);

  }
}

 // factionranking__rf_configurations
   switch_table_name('ranking_faction_configurations', 'factionranking__rf_configurations');

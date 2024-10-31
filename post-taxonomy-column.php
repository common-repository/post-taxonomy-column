<?php
/*
Plugin Name: Post Taxonomy Column
Plugin URI: http://www.bang-on.net/
Description: If you include a taxonomy's id in the columns for any post type, this will fill in the values.
Version: 1.1
Author: Marcus Downing
Author URI: http://www.bang-on.net
License: Private
*/

/*  Copyright 2011  Marcus Downing  (email : marcus@bang-on.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//  Init
add_action('manage_posts_custom_column', 'taxonomy_column_cell');
add_action('manage_pages_custom_column', 'taxonomy_column_cell');

add_action('init', 'taxonomy_column_init');
function taxonomy_column_init () {
  //  add columns
  $post_types = get_post_types(array('public' => true));
  foreach ($post_types as $post_type) {
    //add_filter("manage_edit-{$post_type}_columns", 'taxonomy_column_insert');
    $inserter = new taxonomy_column_inserter($post_type);
    add_filter("manage_edit-{$post_type}_columns", array($inserter, 'insert'));
  }
}

//  Insert columns
class taxonomy_column_inserter {
  var $post_type;
  function taxonomy_column_inserter($post_type) {
    $this->post_type = $post_type;
  }

  function insert ($columns) {
    $out = array();
    foreach ($columns as $key => $value) {
      $out[$key] = $value;
      if ($key == "author") {
        // insert columns
        $taxonomies = get_taxonomies(array('_builtin' => false), 'objects');
        //$taxonomies = get_object_taxonomies($this->post_type, 'objects');
        foreach ($taxonomies as $taxonomy) {
          $ok = (boolean) get_option("post_tax_{$this->post_type}__{$taxonomy->name}", true);
          if ($ok)
            $out[$taxonomy->name] = $taxonomy->labels->name;
        }
        //$out["keyword"] = "Keywords";
      }
    }
    return $out;
  }
}

//  Write cells
function taxonomy_column_cell ($column) {
  global $post;
  $tax = get_taxonomy($column);
  if (!empty($tax)) {
    $out = get_the_term_list($post->ID, $column, '', ', ', '');
    if (!is_wp_error($out))
      echo $out;
  }
}



//  Settings
add_action('admin_menu', 'taxonomy_column_settings_init');
function taxonomy_column_settings_init() {
  add_options_page('Taxonomy Columns', 'Taxonomy Columns', 'administrator', basename(__FILE__), 'taxonomy_column_settings');
}

function taxonomy_column_settings() {
  //  basic data
  $taxonomies = get_taxonomies(array('_builtin' => false), 'objects');
  $taxonomy_names = get_taxonomies(array('_builtin' => false), 'names');
  $post_types = get_post_types(array('public' => true), 'objects');

  //  save settings
  $save = isset($_POST['save']) && (boolean) $_POST['save'];
  if ($save) {
    foreach ($post_types as $post_type) {
      foreach ($taxonomies as $taxonomy) {
        $code = $post_type->name."__".$taxonomy->name;
        $ok = isset($_POST[$code]) && (boolean) $_POST[$code];
        //echo "<p>Saving $code: {$_POST[$code]} = $ok</p>";
        update_option("post_tax_$code", $ok ? 1 : 0);
      }
    }
    echo "<div id='message' class='updated'><p>Settings saved.</p></div>";
  }

  //  load settings
  $post_type_tax = array();
  foreach ($post_types as $post_type) {
    $post_type_tax[$post_type->name] = array();
    foreach ($taxonomies as $taxonomy) {
      $code = $post_type->name."__".$taxonomy->name;
      //echo "<p>Loading $code: ".get_option("post_tax_$code");
      $post_type_tax[$post_type->name][$taxonomy->name] = (boolean) get_option("post_tax_$code", true);
    }
  }

  //echo "<pre>"; print_r($taxonomies); echo "</pre>";
  //echo "<pre>"; print_r($post_types); echo "</pre>";
  //echo "<pre>"; print_r($post_type_tax); echo "</pre>";

  //  write settings
  ?><div class="wrap">
    <style>
      tr.ab { background: #f2f7fc; }
      td.ab { background: #fafafa; }
      tr.ab td.ab { background: #e8f0f8; }
    </style>
    <?php screen_icon("themes"); ?> <h2>Taxonomy Columns</h2>
    <a href="http://www.bang-on.net">
      <img src="<?php echo plugins_url('bang.png', __FILE__); ?>" style="float: right; margin-right: 10px;" /></a>

    <p>Add the following columns to the post listings:</p>
    <form method="post" action="options-general.php?page=post-taxonomy-column.php">
      <table class="form-table">
        <thead><tr><th></th>
          <th colspan='<?php echo count($taxonomies); ?>' style='text-align: center; font-weight: bold;'>Taxonomies</th></tr>
        <tr><th style='font-weight: bold;'>Post Types</th>
          <?php
            foreach ($taxonomies as $taxonomy) {
              echo "<th>{$taxonomy->labels->name}</th>";
            }
          ?>
        </tr></thead>
        <?php
          $ab = true;
          foreach ($post_types as $post_type) {
            $active_taxonomies = get_object_taxonomies($post_type->name, 'names');
            //$active_taxonomies = $post_type->taxonomies;
            //foreach ($taxonomies as $taxonomy) {
            //  if (!in_array($taxonomy->name, $active_taxonomies))
            //    if (in_array($post_type->name, $taxonomy->object_type) || $post_type->name == $taxonomy->object_type)
            //      $active_taxonomies[] = $taxonomy->name;
            //}

            //$active_taxonomies = array_intersect($post_type->taxonomies, $taxonomy_names);
            if (empty($active_taxonomies))
              continue;
            $ab = !$ab;

            echo "<tr class='ab$ab'><td><a href='edit.php?post_type={$post_type->name}'>{$post_type->labels->name}</a></td>";
            $ab2 = true;
            foreach ($taxonomies as $taxonomy) {
              $ab2 = !$ab2;
              $code = $post_type->name."__".$taxonomy->name;
              echo "<td class='ab$ab2'>";
              if (in_array($taxonomy->name, $active_taxonomies)) {
                echo "<input type='checkbox' name='$code' id='$code'";
                if ($post_type_tax[$post_type->name][$taxonomy->name])
                  echo " checked";
                echo " />";
              }
              echo "</td>";
            }
          }
        ?>
      </table>
      <input type='hidden' name='save' value='on'/>
      <p><input type='submit' value='Save Settings' class='button-primary'/></p>
    </form>
  <?php
}


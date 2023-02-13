<?php
/*
Plugin Name: Notion to WordPress
Description: Import content from Notion to WordPress as a post.
Version: 1.0
Author: Panhavad Duk
*/

function notion_to_wordpress_import() {
  $api_key = get_option('notion_api_key');
  $database_id = get_option('notion_database_id');
  if (!empty($api_key) && !empty($database_id)) {
    $headers = array(
      'Authorization' => 'Bearer ' . $api_key,
      'Notion-Version' => '2022-06-28',
      'Content-Type' => 'application/json',
    );
    $query_params = array(
      'filter' => array(
        'property' => 'object',
        'value' => 'page',
      ),
    );
    $query_params = json_encode($query_params);
    $url = 'https://api.notion.com/v1/databases/' . $database_id . '/query';
    $response = wp_remote_post($url, array(
      'headers' => $headers,
//       'body' => $query_params,
    ));
    if (is_wp_error($response)) {
      $error_message = $response->get_error_message();
      echo 'Failed to get data from Notion: ' . $error_message;
    } else {
      $data = json_decode(wp_remote_retrieve_body($response), true);
      if (!empty($data['results'])) {
		echo wp_remote_retrieve_body($response);
        echo '<ul>';
        foreach ($data['results'] as $result) {
          echo '<li>' . $result['title'][0]['text']['content'] . '</li>';
        }
        echo '</ul>';
      } else {
        echo 'No results found in the Notion database.';
      }
    }
  } else {
    echo 'Please enter a valid Notion API key and database ID in the settings.';
  }
}


function notion_to_wordpress_settings_page() {
  ?>
  <div class="wrap">
    <h1>Notion to WordPress Settings</h1>
    <form method="post" action="options.php">
      <?php
        settings_fields('notion_to_wordpress_settings');
        do_settings_sections('notion_to_wordpress_settings');
      ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Notion API Key</th>
          <td><input type="text" name="notion_api_key" value="<?php echo esc_attr(get_option('notion_api_key')); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Notion Database ID</th>
          <td><input type="text" name="notion_database_id" value="<?php echo esc_attr(get_option('notion_database_id')); ?>" /></td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
    <hr>
    <h2>Import Result:</h2>
    <?php notion_to_wordpress_import(); ?>
  </div>
  <?php
}


add_action('admin_menu', 'notion_to_wordpress_add_menu');
function notion_to_wordpress_add_menu() {
  add_menu_page('Notion to WordPress', 'Notion to WordPress', 'manage_options', 'notion-to-wordpress', 'notion_to_wordpress_settings_page');
}

add_action('admin_init', 'notion_to_wordpress_settings');
function notion_to_wordpress_settings() {
  register_setting('notion_to_wordpress_settings', 'notion_api_key');
  register_setting('notion_to_wordpress_settings', 'notion_database_id');
}

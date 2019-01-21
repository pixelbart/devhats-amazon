<?php
namespace DevhatsAmazon;
new Admin;

class Admin
{
  public $github = '4ae4d5ef5a1b31960256d5ec955face763d12757';

  public function __construct()
  {
    // register menu
    add_action('admin_menu', array( $this, 'register_menu' ) );
  }

  public function update_checker()
  {
    require plugin_dir_path( DH_AMAZON_PLUGIN ) . 'vendor/plugin-update-checker/plugin-update-checker.php';
    $update_checker = UpdateChecker::buildUpdateChecker(
      'https://github.com/pixelbart/devhats-amazon/',
      DH_AMAZON_PLUGIN,
      'devhats-amazon'
    );

    $update_checker->setAuthentication($this->github);
  }

  public function register_menu()
  {
    add_submenu_page(
      'options-general.php',
      __( 'Devhats Amazon', DH_AMAZON_TEXTDOMAIN ),
      __( 'Devhats Amazon', DH_AMAZON_TEXTDOMAIN ),
      'manage_options',
      'devhats-amazon',
      array( $this, 'settings_callback' )
    );

    // register settings
    add_action( 'admin_init', array( $this, 'register_settings' ) );
  }

  public function fields()
  {
    return array(
      'devhats-amazon_public' => _x('Öffentlicher Schlüssel', 'settings field', DH_AMAZON_TEXTDOMAIN),
      'devhats-amazon_private' => _x('Privater Schlüssel', 'settings field', DH_AMAZON_TEXTDOMAIN),
      'devhats-amazon_local' => _x('Standort', 'settings field', DH_AMAZON_TEXTDOMAIN),
      'devhats-amazon_version' => _x('Version', 'settings field', DH_AMAZON_TEXTDOMAIN),
      'devhats-amazon_tag' => _x('Tag', 'settings field', DH_AMAZON_TEXTDOMAIN),
    );
  }

  public function register_settings()
  {
    $group  = 'devhats-amazon-settings';
    $fields = $this->fields();

    foreach( $fields as $id => $label ) {
      register_setting( $group, $id );
    }
  }

  public function settings_callback()
  {
    $fields = $this->fields();
    $file = 'templates/admin.php';
    $desc = __('Das Plugin funktioniert auch ohne Optionen. Die Werbeinnahmen gehen dann allerdings an Pixelbuben.', DH_AMAZON_TEXTDOMAIN);

    include_once( plugin_dir_path( DH_AMAZON_PLUGIN ) . $file );
  }
}

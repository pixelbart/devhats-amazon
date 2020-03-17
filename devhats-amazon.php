<?php
/*
Plugin Name: Simple Product Advertising API Plugin
Description: Mit diesem Plugin lassen sich mit Hilfe der Amazon ASIN Produkte auf der Seite einbinden.
<<<<<<< HEAD
Version: 1.0.1
Author: Kevin Pliester
Author URI: https://pixelbuben.de
=======
Version: 1.0.0
Author: Pixelbart
Author URI: https://pixelbart.de
>>>>>>> 31e962f8e62192dd981e7e5553ebd80de44e9da2
License: GPLv2 or later
Text Domain: devhats-amazon
*/

// prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;

// set directory
define('DH_AMAZON_PLUGIN', __FILE__);
define('DH_AMAZON_TEXTDOMAIN', 'devhats-amazon');

foreach ( glob( plugin_dir_path( DH_AMAZON_PLUGIN ) . "includes/*.php" ) as $file ) {
	include_once $file;
}

foreach ( glob( plugin_dir_path( DH_AMAZON_PLUGIN ) . "classes/*.php" ) as $file ) {
	include_once $file;
}

<?php
/*
Plugin Name: Algolia Search & Filter by North UX Design
Plugin URI:  
Description: Adds Algolia search and filter functionality to WordPress
Version:     0.1
Author:      North UX Design
Author URI:  https://northuxdesign.com/
*/
namespace AlgoliaSearchFilter;

defined( 'ABSPATH' ) || die();


define( 'ALGOLIASF_CORE_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'ALGOLIASF_CORE_URL', plugin_dir_url( __FILE__ ) );

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

require plugin_dir_path( __FILE__ ) . 'includes/class-algolia-post-hooks.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-algolia-indexing.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-algolia-menus.php';
require plugin_dir_path( __FILE__ ) . 'algolia-blocks/algolia-blocks.php';
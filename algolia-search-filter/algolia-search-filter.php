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


function algolia_search_filter_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'algolia-template-block',
        plugins_url('blocks/algolia-template-block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/algolia-template-block.js')
    );
}
//add_action('enqueue_block_editor_assets', __NAMESPACE__ . '\algolia_search_filter_enqueue_block_editor_assets');


function algolia_search_filter_enqueue_frontend_assets() {
    wp_enqueue_script(
        'algolia-template-init',
        plugins_url('blocks/algolia-template-init.js', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/algolia-template-init.js'),
        true
    );
}
//add_action('wp_enqueue_scripts', __NAMESPACE__ . '\algolia_search_filter_enqueue_frontend_assets');
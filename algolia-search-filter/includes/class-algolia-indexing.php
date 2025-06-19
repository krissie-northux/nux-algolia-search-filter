<?php

namespace AlgoliaSearchFilter;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use Algolia\AlgoliaSearch\Api\SearchClient;

class Algolia_Indexing {

    private static $index;

    private static $app_id;
    private static $api_write_key;

    private static $client;

    public function __construct( $post_type ) {
        $prefix = get_option( 'index_prefix' );
        self::$index = $prefix . '_' . $post_type;
        self::$app_id = get_option( 'algolia_app_id' );
        self::$api_write_key = get_option( 'algolia_api_write_key' );
        self::$client = SearchClient::create(self::$app_id, self::$api_write_key);
    }

    public function index_post( $post_id, $post, $update ) {
        //error_log('Indexing post: ' . $post_id);
        $post_data = $this->prepare_post_data( $post );
        //error_log(print_r($post_data, true));
        try {
            $result = self::$client->saveObject( self::$index, $post_data );
            //error_log(print_r($result, true));
        } catch ( \Exception $e ) {
           //error_log( 'Algolia Indexing Error: ' . $e->getMessage() );
        }
   
    }

    public function delete_post( $post_id ) {
        //error_log('Deleting post: ' . $post_id);
        try {
            $result = self::$client->deleteObject( self::$index, $post_id );
            //error_log(print_r($result, true));
        } catch ( \Exception $e ) {
           //error_log( 'Algolia Deletion Error: ' . $e->getMessage() );
        }
    }

    public function prepare_post_data( \WP_Post $post ) {
        $data = array(
            'objectID' => $post->ID,
            'featured_image' => get_the_post_thumbnail_url( $post->ID, 'large' ),
            'post_title' => $post->post_title,
            /*'post_content' => $post->post_content,*/
            'post_excerpt' => $post->post_excerpt,
            'post_date' => $post->post_date,
            'post_modified' => $post->post_modified,
            'post_author' => $post->post_author,
            'post_status' => $post->post_status,
            'post_type' => $post->post_type,
            'post_name' => $post->post_name,
            'post_parent' => $post->post_parent,
            'guid' => $post->guid,
            'menu_order' => $post->menu_order,
            'permalink' => get_permalink( $post->ID ),
        );

        $data = apply_filters( 'algoliasf_post_data', $data, $post );
        $data = apply_filters( 'algoliasf_post_data_' . $post->post_type, $data, $post );

        return $data;
    }

    public function update_facets( $post_type, $taxonomies ) {
        $facets = [];
        foreach ($taxonomies as $taxonomy) {
            $option_name = 'algolia_subpage_setting__' . $post_type . '__' . $taxonomy->name;
            $include = get_option($option_name);
            if ( $include ) {
                $facets[] = 'searchable('.$taxonomy->name.')';
            }
           
        }
        self::$client->setSettings( self::$index, [ 'attributesForFaceting' => $facets ] );
    }

    public function reset_index() {
        try {
            $result = self::$client->clearObjects( self::$index );
            //error_log(print_r($result, true));
        } catch ( \Exception $e ) {
           //error_log( 'Algolia Clear Index Error: ' . $e->getMessage() );
        }
    }

   
}
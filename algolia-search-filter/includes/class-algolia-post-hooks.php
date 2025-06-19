<?php

namespace AlgoliaSearchFilter;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use Algolia\AlgoliaSearch\Api\SearchClient;

class Algolia_Post_Hooks {
    public function __construct() {
        add_filter( 'algoliasf_post_data', array( $this, 'filter_algolia_data' ), 10, 2 );
        add_action( 'init', array( $this, 'apply_post_type_filters' ), 11 );
        add_action( 'save_post', array( $this, 'index_post' ), 10, 3 );
    }

    public function index_post( $post_id, $post, $update ) {
        //error_log('Indexing post: ' . $post_id);
        //error_log(print_r($post, true));
        //error_log(print_r($update, true));
        if ( $post->post_status !== 'publish' ) {
            //error_log('Deleting post '. $post_id .' status: ' . $post->post_status);
            //remove from index
            $indexing = new Algolia_Indexing( $post->post_type );
            $indexing->delete_post( $post_id );
            return;
        }
        if ( ! in_array( $post->post_type, get_option('algolia_filter_search_post_types', array()) ) ) {
            //error_log('Not indexing post type: ' . $post->post_type);
            return;
        }

        $indexing = new Algolia_Indexing( $post->post_type );
        $indexing->index_post( $post_id, $post, $update );
    }

    public function delete_post( $post_id ) {
        //error_log('Deleting post: ' . $post_id);
        if ( ! in_array( get_post_type( $post_id ), get_option('algolia_filter_search_post_types', array()) ) ) {
            return;
        }

        $indexing = new Algolia_Indexing( get_post_type( $post_id ) );
        $indexing->delete_post( $post_id );
    }

    public function filter_algolia_data( $data, $post ) {
        //$data['post_content'] = strip_tags( $data['post_content'] );
        return $data;
    }

    public static function apply_post_type_filters() {
        //$post_types = get_post_types();
        $post_types = get_option('algolia_filter_search_post_types', array());

        foreach ( $post_types as $post_type ) {
            $filter_name = 'algoliasf_post_data_' . $post_type;
            add_filter($filter_name, function($data, $post) use ($post_type) {
                //check taxonomies for this post type and the taxonomy option to see if we should include it
                $taxonomies = get_object_taxonomies( $post_type );
                $taxonomies = array_filter( $taxonomies, function( $taxonomy ) use ( $post_type ) {
                    return get_option( 'algolia_subpage_setting__' . $post_type . '__' . $taxonomy );
                } );

                foreach ( $taxonomies as $taxonomy ) {
                    $terms = get_the_terms( $post->ID, $taxonomy );
                    if ( $terms ) {
                        $data[ $taxonomy ] = array_map( function( $term ) {
                            return $term->name;
                        }, $terms );
                    }
                    if ( $terms && $taxonomy === 'video_category' ) {
                        /*$data['sort_id'] = array_map( function( $term ) {
                            return $term->term_id;
                        }, $terms );*/
                        foreach($terms as $term){
                            $data['sort_id'] = $term->term_id;
                        }
                    }
                }
                
                return $data;
            }, 10, 2 );
        }
    }

}
new Algolia_Post_Hooks();
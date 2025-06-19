<?php

namespace AlgoliaSearchFilter;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class Algolia_Menus {
    public function __construct() {

        // Main Menu
        add_action( 'admin_menu', [ $this, 'algolia_menu' ] );

        // Register settings
        add_action( 'admin_init', [ $this, 'algolia_filter_search_register_settings' ] );
        
        add_action( 'admin_menu', [ $this, 'add_sub_menus' ] );

        //add_action( 'update_option', array( $this, 'update_algolia_facet_setting' ), 10, 3 );

        add_action('wp_admin_ajax_start_processing', array( $this, 'start_processing_ajax'));
        add_action('wp_ajax_start_processing', array( $this, 'start_processing_ajax'));

        add_action('wp_admin_ajax_clear_processing', array( $this, 'clear_processing_ajax'));
        add_action('wp_ajax_clear_processing', array( $this, 'clear_processing_ajax'));

        add_action('async_processor_cron', 'AlgoliaSearchFilter\Algolia_Menus::async_processor_cron', 10, 3);
        add_action('async_processor_clear_batch', 'AlgoliaSearchFilter\Algolia_Menus::async_processor_clear_batch', 10, 3);

        add_action('admin_enqueue_scripts', array( $this, 'async_processor_scripts'));

        add_action('wp_ajax_check_status', array( $this, 'check_status_ajax') );
        add_action('wp_admin_ajax_check_status', array( $this, 'check_status_ajax') );

        add_action('wp_ajax_check_latest_status', array( $this, 'check_latest_status_ajax' ) );
        add_action('wp_admin_ajax_check_latest_status', array( $this, 'check_latest_status_ajax' ) );
    }

    public function update_algolia_facet_setting( $option_name, $old_value, $new_value ) {
        if ( strpos( $option_name, 'algolia_subpage_setting' ) !== false ) {
            $post_type = explode('__', $option_name)[1];
            $taxonomy = explode('__', $option_name)[2];
            //error_log(print_r($taxonomy, true));
            $algolia = new Algolia_Indexing($post_type);
            if ( ! empty($new_value) ) {
                $algolia->set_facet($taxonomy);
            } else {
                $algolia->remove_facet($taxonomy);
            }
            
            //error_log('Option name: ' . $option_name);
            //error_log('Old value: ' . $old_value);
            //error_log('New value: ' . $new_value);
        }

    }

    public function algolia_menu() {
        add_menu_page(
            'Algolia Filter & Search', 
            'Algolia Filter & Search', 
            'manage_options', 
            'algolia-filter-search', 
            [ $this, 'algolia_filter_search_page' ],
            'dashicons-filter'
         );
    }

    // Page callback
    public function algolia_filter_search_page() {
        ?>
        <div class="wrap">
            <h1>Algolia Filter & Search</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('algolia-filter-search');
                do_settings_sections('algolia-filter-search');
                ?>
                <table class="form-table">
                <tr>
                        <th scope="row">Algolia App ID</th>
                        <td><p>Enter the app ID provided by Algolia.</p></br><input type="text" name="algolia_app_id" value="<?php echo get_option('algolia_app_id'); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Algolia API Key</th>
                        <td><p>Enter the api key provided by Algolia.</p></br><input type="text" name="algolia_api_key" value="<?php echo get_option('algolia_api_key'); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Algolia Write API Key</th>
                        <td><p>Enter the write api key provided by Algolia in order to allow this site to push records to the algolia index.</p></br><input type="text" name="algolia_api_write_key" value="<?php echo get_option('algolia_api_write_key'); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Index Prefix</th>
                        <td><p>In order to ensure that each site environment has a unique index, please enter an index prefix for this environment.</p></br><input type="text" name="index_prefix" value="<?php echo get_option('index_prefix'); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Post Types</th>
                        <td>
                            <p>Select which post types you'd like to index in Algolia. Each post type will have it's own index. Adjusting settings for each post type can be found in the Search and Filter Options section of each enabled post type.</p>
                            </br>
                            <?php
                            $post_types = get_post_types(array('public' => true));
                            foreach ($post_types as $post_type) {
                                $checked = in_array($post_type, get_option('algolia_filter_search_post_types', array()));
                                ?>
                                <label>
                                    <input type="checkbox" name="algolia_filter_search_post_types[]" value="<?php echo $post_type; ?>" <?php checked($checked); ?>>
                                    <?php echo $post_type; ?>
                                </label>
                                <br>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function algolia_filter_search_register_settings() {
        //settings for the main page
        register_setting('algolia-filter-search', 'index_prefix');
        register_setting('algolia-filter-search', 'algolia_app_id');
        register_setting('algolia-filter-search', 'algolia_api_key');
        register_setting('algolia-filter-search', 'algolia_api_write_key');
        register_setting('algolia-filter-search', 'algolia_filter_search_post_types');

        //settings for the sub pages
        $post_types = get_post_types([], 'objects');
        foreach ($post_types as $post_type) {
            //if the post_type has been selected to be indexed
            if ( in_array($post_type->name, get_option('algolia_filter_search_post_types', array())) ) {
                $taxonomies = get_object_taxonomies($post_type->name, 'objects');
                foreach ($taxonomies as $taxonomy) {
                    register_setting('algolia_subpage_settings', 'algolia_subpage_setting__' . $post_type->name . '__' . $taxonomy->name);
                }
            }
        }
    }

    public function add_sub_menus() {
        $post_types = get_post_types(array('public' => true));
        foreach ($post_types as $post_type) {
            if ( in_array($post_type, get_option('algolia_filter_search_post_types', array())) ) {
                add_submenu_page(
                    'edit.php?post_type=' . $post_type,
                    'Search and Filter Options',
                    'Search and Filter Options',
                    'manage_options',
                    'search-options',
                    [ $this, 'post_type_submenu_page' ]
                );
            }
        }
    }

    public function post_type_submenu_page() {
        $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'none';
        $taxonomies = get_object_taxonomies($post_type, 'objects');

        //check if we just submitted the form
        if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] ) {
            $algolia = new Algolia_Indexing( $post_type );
            $algolia->update_facets( $post_type, $taxonomies );
        }
        //$custom_fields = $this->get_common_custom_fields($post_type);
        //$acf_field_groups = $this->get_acf_field_groups($post_type);
        //error_log('ACF Field Groups: ' . print_r($acf_field_groups, true));
        //error_log('Custom fields: ' . print_r($custom_fields, true));
        ?>
        <div class="wrap">
            <h1><?php
            //echo the post type name
            echo get_post_type_object($_GET['post_type'])->labels->name;
            ?> Search and Filter Settings</h1>
            <script>
                var postType = "<?php echo isset($_GET['post_type']) ? $_GET['post_type'] : 'none'; ?>";
            </script>
            <p>When you update a <?php echo get_post_type_object($_GET['post_type'])->labels->singular_name; ?> it will automatically update in Algolia, but you can also re-sync all <?php echo get_post_type_object($_GET['post_type'])->labels->name; ?> using the button below.  </p>
            <label><input type="checkbox" id="reset-index" name="reindex-all" value="1">&nbsp;Reset Index  </label><br/><small>This will completely clear the index and then push fresh current data to it. It may temporarily affect functionality of the video search feature, and should only be used if experiencing issues that can't be resolved in other ways.</small><br/><br/>
            <button id="start-processing">Start Processing</button>
            <button id="clear-processing">Clear Stuck Process</button>
            <style>
                #total-progress {
                    width: 100%;
                    height: 30px;
                    background-color: #ffffff;
                    text-align: center;
                    line-height: 30px;
                    color: white;
                }
                #progress-bar {
                    width: 0;
                    height: 30px;
                    background-color: #4caf50;
                    text-align: center;
                    line-height: 30px;
                    color: white;
                }
                .status-wrapper {
                    margin-top: 20px;
                    padding: 20px;
                }
            </style>
            <div class="status-wrapper">
                <div id="total-progress"><div id="progress-bar"></div></div>
                <div id="status-message"></div>
            </div>
            <form method="post" action="options.php">
                <?php
                settings_fields('algolia_subpage_settings');
                do_settings_sections('algolia_subpage_settings'); ?>
                <p>Select which taxonomies you would like to be included for searching and filtering <?php echo get_post_type_object($_GET['post_type'])->labels->name; ?> in Algolia blocks on the site.</p>
                <?php
                foreach ($taxonomies as $taxonomy) {
                    $option_name = 'algolia_subpage_setting__' . $post_type . '__' . $taxonomy->name;
                    $checked = get_option($option_name) ? 'checked' : '';
                    ?>
                    <div>
                        <label>
                            <input type="checkbox" name="<?php echo $option_name; ?>" value="1" <?php echo $checked; ?>>
                            <?php echo $taxonomy->labels->name; ?>
                        </label>
                    </div>
                    <?php
                }
                submit_button();
                ?>
            </form>
        </div>
 
        <?php
    }

    private function get_common_custom_fields($post_type) {
        global $wpdb;
    
        // Get all post IDs for the given post type
        $post_ids = $wpdb->get_col($wpdb->prepare("
            SELECT ID
            FROM $wpdb->posts
            WHERE post_type = %s
        ", $post_type));
    
        if (empty($post_ids)) {
            return [];
        }
    
        // Get all meta keys for the given post type
        $meta_keys = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT meta_key
            FROM $wpdb->postmeta
            WHERE post_id IN (
                SELECT ID
                FROM $wpdb->posts
                WHERE post_type = %s
            )
        ", $post_type));
    
        // Find common meta keys
        $common_meta_keys = [];
        foreach ($meta_keys as $meta_key) {
           //error_log('Checking meta key ' . $meta_key);
            $count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*)
                FROM $wpdb->postmeta
                WHERE meta_key = %s
                AND post_id IN (" . implode(',', array_map('intval', $post_ids)) . ")
            ", $meta_key));
           //error_log('Count: ' . $count);
            if ($count > (count($post_ids) / 6)) {
                $common_meta_keys[] = $meta_key;
            }
        }
    
        return $common_meta_keys;
    }
    //may not use
    private function get_acf_field_groups($post_type) {
        if (!function_exists('acf_get_field_groups')) {
            return [];
        }
    
        $field_groups = acf_get_field_groups(array('post_type' => $post_type));
        return $field_groups;
    }
    //may not use
    private function get_custom_fields($post_type) {
        global $wpdb;
        $query = $wpdb->prepare("
            SELECT DISTINCT meta_key
            FROM $wpdb->postmeta
            WHERE post_id IN (
                SELECT ID
                FROM $wpdb->posts
                WHERE post_type = %s
            )
        ", $post_type);
    
        $results = $wpdb->get_results($query, ARRAY_A);
        $custom_fields = [];
        foreach ($results as $row) {
            $custom_fields[$row['meta_key']] = $row['meta_key'];
        }
        return $custom_fields;
    }

    public function check_latest_status_ajax() {
        $post_type = $_POST['post_type'];
        //error_log('Checking latest status for async_processor_latest_batch_' . $post_type);
        $latestBatchId = get_option('async_processor_latest_batch_' . $post_type);
        //error_log('Latest batch ID: ' . $latestBatchId);
        if ($latestBatchId) {
            $status = get_option('async_processor_batch_' . $latestBatchId);
            //error_log('Status: ' . print_r($status, true));
            $status['batch_id'] = $latestBatchId;
            wp_send_json_success($status);
        } else {
            wp_send_json_success(array('status' => 'idle'));
        }
    }

    public function start_processing_ajax() {
        // Check if the user has permission to start processing
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'You do not have permission to perform this action.' );
        }
        $reset_index = $_POST['reset_index'];
        //error_log('Reset index: ' . $reset_index);
        // Get post type
        $post_type = $_POST['post_type'];
    
        // Create a new batch
        $batch_id = wp_generate_uuid4();
       //error_log('Starting processing for async_processor_latest_batch_' . $post_type);
        update_option('async_processor_latest_batch_' . $post_type, $batch_id);
        // Store batch status
        update_option('async_processor_batch_' . $batch_id, array(
            'status' => 'processing',
            'progress' => 0,
        ));
  
        // Start processing in background
       $event = wp_schedule_single_event(time(), 'async_processor_cron', array($batch_id, $post_type, $reset_index), true);
        //error_log('Event: ' . print_r($event, true));
        spawn_cron();
        // Return batch ID
        wp_send_json_success(array('batch_id' => $batch_id));
    }

    public function clear_processing_ajax() {
        // Get post type
        $post_type = $_POST['post_type'];
    
        // Get latest batch ID
        $latestBatchId = get_option('async_processor_latest_batch_' . $post_type);
        //error_log('Clearing processing for async_processor_latest_batch_' . $post_type);
        // Clear batch
        delete_option('async_processor_batch_' . $latestBatchId);
        delete_option('async_processor_latest_batch_' . $post_type);
        // Return success
        wp_send_json_success();
    }

    public static function async_processor_cron($batch_id, $post_type, $reset = false) {
        // Set cron to clear batch option after 1 hour
        wp_schedule_single_event(time() + 3600, 'async_processor_clear_batch', array($batch_id, $post_type));
       //error_log('Processing batch ' . $batch_id);

       if ($reset) {
            //error_log('Resetting index');
            $algolia = new Algolia_Indexing($post_type);
            $algolia->reset_index();
        }
        // Get posts
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
        ));
        $total_posts = count($posts);
        // Process posts
        foreach ($posts as $post) {
            
            // Process post
            $result = self::process_post($post);
    
            // Update progress
            $progress = get_option('async_processor_batch_' . $batch_id)['progress'];
            //error_log('Progress: ' . $progress);
            //error_log('Total posts: ' . $total_posts);
            update_option('async_processor_batch_' . $batch_id, array(
                'status' => 'processing',
                'progress' => $progress + 1,
                'total_posts' => $total_posts,
            ));
        }
        // make a readable date time from right now
        $date = date('m-d-Y H:i:s');
        // Update status
        update_option('async_processor_batch_' . $batch_id, array(
            'status' => 'complete',
            'progress' => count($posts),
            'total_posts' => count($posts),
            'timestamp' => $date,
        ));

        
        
    }

    public static function async_processor_clear_batch($batch_id, $post_type) {
       //error_log('Clearing batch ' . $batch_id);
        
        delete_option('async_processor_batch_' . $batch_id);
    }

    public static function process_post($post) {
       // //error_log('Processing post ' . $post->ID);
        // Process post
        $algolia = new Algolia_Indexing($post->post_type);
        $algolia->index_post($post->ID, $post, true);
        return true;
    }

    public function async_processor_scripts() {
        //only load on the algolia filter search page and post search options pages
        if ( strpos($GLOBALS['current_screen']->base , 'search-options') !== false  || strpos($GLOBALS['current_screen']->base , 'algolia-filter-search') !== false) {
            wp_enqueue_script('async-processor', ALGOLIASF_CORE_URL . 'assets/async-processor.js', array('jquery'), '1.1', true);
            wp_localize_script('async-processor', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
            ));
        }
        
        
    }

    public function check_status_ajax() {
        $batch_id = $_POST['batch_id'];
       // //error_log('Checking status for batch ' . $batch_id);
        $status = get_option('async_processor_batch_' . $batch_id);
        $status['batch_id'] = $batch_id;
        wp_send_json_success($status);
    }

}

new Algolia_Menus();
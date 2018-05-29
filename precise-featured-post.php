<?php
/*************************************************************************
Plugin Name: Precise Featured Post
Description: Featured Post Plugin For Wordpress.
Version: 2.0
Author: Rodrigo Tomonari Muino
Author URI: https://github.com/rodrigotomonari
Text Domain: precise_featured_post
*************************************************************************/

class PreciseFeaturedPost
{
    private static $instance = null;
    private $plugin_path;
    private $plugin_url;
    private $version = '2.0';
    private $plugin_name = 'Precise Featured Post';
    private $plugin_slug = 'precise_featured_post';
    private $permission = 'publish_posts';

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
     */
    private function __construct()
    {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url  = plugin_dir_url(__FILE__);

        // Hook to register admin JS and CSS
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

        // Hook to create meta box in post edit page
        add_action('add_meta_boxes', array($this, 'set_meta_boxes'));

        // Add hook to add columns to post listing table
        add_action('load-edit.php', array($this, 'set_columns_to_post_listing'));

        // Hook to save feature data in post submit
        add_action('save_post', array($this, 'save_post'));

        // Create Ajax action
        add_action('wp_ajax_' . $this->plugin_slug . '_action', array($this, 'feature_post'));
    }

    /**
     * Get feature post options
     *
     * @return array
     */
    public function get_feature_post_options()
    {
        $options = array(
            'post' => array(
                array(
                    'name'  => 'featured',
                    'label' => __('Featured?', $this->plugin_name),
                )
            )
        );

        if (has_filter($this->plugin_slug . '_options')) {
            $options = apply_filters($this->plugin_slug . '_options', $options);
        }

        return $options;
    }

    /**
     * Callback to add admin JS and CSS
     *
     * @param string $hook Hook name
     */
    public function admin_enqueue_scripts($hook)
    {
        if ($hook == 'edit.php') {
            wp_enqueue_script($this->plugin_slug . '_script', $this->plugin_url . 'js/script.js', array('jquery'));
            wp_enqueue_style($this->plugin_slug . '_style', $this->plugin_url . 'css/style.css');
        }
    }

    /**
     * Set featured post meta box in post/custom_post edit page
     */
    public function set_meta_boxes()
    {
        // Add one meta box for each post type in $options
        foreach ($this->get_feature_post_options() as $type => $section) {
            add_meta_box($this->plugin_slug . '_meta-box', __('Featured Post', $this->plugin_slug), array(
                $this,
                'meta_box_content'
            ), $type, 'side', 'high');
        }
    }

    /**
     * Meta box content
     */
    public function meta_box_content()
    {
        include __DIR__ . '/view/meta-box-content.php';
    }

    /**
     * Hooks to add columns to post listing table
     */
    public function set_columns_to_post_listing()
    {
        // Add one column for each entry in $options
        foreach ($this->get_feature_post_options() as $type => $section) {
            add_filter('manage_' . $type . '_posts_columns', array($this, 'set_custom_columns'));

            add_action('manage_' . $type . '_posts_custom_column', array(
                $this,
                'set_custom_columns_content'
            ), 10, 2);
        }
    }

    /**
     * Callback to set custom columns in post/custom_type table list
     *
     * @param array $columns Array of column names
     *
     * @return array New array of column names with custom columns
     */
    public function set_custom_columns($columns)
    {
        $post_type      = 'post';
        $custom_columns = array();
        if (isset($_GET['post_type'])) {
            $post_type = $_GET['post_type'];
        }

        foreach ($this->get_feature_post_options()[$post_type] as $section) {
            $custom_columns[$this->format_column($section['name'])] = $section['label'];
        }

        return array_merge($columns, $custom_columns);
    }

    /**
     * Callback to set custom columns content in post/custom_type table list
     *
     * @param string $column The name of the column to display
     * @param int $post_id The ID of the current post
     *
     */
    public function set_custom_columns_content($column, $post_id)
    {
        $name  = str_replace($this->plugin_slug . '_column' . '_', '', $column);
        $key   = $this->format_meta_key($name);
        $value = intval(get_post_meta($post_id, $key, true));
        if (strpos($column, $this->plugin_slug . '_column' . '_') !== false) {
            ?>
            <div class="precise_featured_post_star <?= ($value > 0) ? "precise_featured_post_checked" : "" ?>">
                <input class="field_name" type="hidden" value="<?= $key ?>"/>
                <input class="post_id" type="hidden" value="<?php echo $post_id ?>"/>
            </div>
            <?php
        }
    }

    /**
     * Callback to featured post data while saving post in edit page
     */
    public function save_post()
    {
        global $post;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if ( ! isset($_POST[$this->plugin_slug . '_meta_box_nonce']) || ! wp_verify_nonce($_POST[$this->plugin_slug . '_meta_box_nonce'],
                $this->plugin_slug . '_meta_box_save')) {
            return;
        }

        if ( ! current_user_can($this->permission)) {
            return;
        }

        foreach ($this->get_feature_post_options()[$post->post_type] as $section) {
            $key = $this->format_field_name($section['name']);
            if (isset($_POST[$key]) && $_POST[$key] == 1) {
                update_post_meta($post->ID, $this->format_meta_key($section['name']), time());
            } else {
                delete_post_meta($post->ID, $this->format_meta_key($section['name']));
            }
        }
    }

    /**
     * Callback for the ajax request called when clicking in the post listing star
     */
    public function feature_post()
    {
        if ( ! current_user_can($this->permission)) {
            echo json_encode(array('status' => 'error', 'msg' => __('Permission Deny', $this->plugin_slug)));

            return true;
        }

        $id = intval($_POST['post_id']);
        if (isset($_POST['field_name'])) {
            $value = intval(get_post_meta($id, $_POST['field_name'], true));
            if ($value > 0) {
                delete_post_meta($id, $_POST['field_name']);
                echo json_encode(array('status' => 'ok', 'id' => $id, 'action' => 'uncheck'));
            } else {
                update_post_meta($id, $_POST['field_name'], time());
                echo json_encode(array('status' => 'ok', 'id' => $id, 'action' => 'check'));
            }
        } else {
            echo json_encode(array('status' => 'error'));
        }
        exit;
    }

    /**
     * Function to format the post meta key
     *
     * @param string $name
     *
     * @return string Formatted key
     */
    public function format_meta_key($name)
    {
        return '_' . $this->plugin_slug . '_' . $name;
    }

    /**
     * Function to format meta box name
     *
     * @param string $name
     *
     * @return string Formatted meta box name
     */
    public function format_field_name($name)
    {
        return 'meta_box_' . $name;
    }

    /**
     * Function to format column name
     *
     * @param string $name
     *
     * @return string Formatted column name
     */
    public function format_column($name)
    {
        return $this->plugin_slug . '_column' . '_' . $name;
    }
}

PreciseFeaturedPost::get_instance();

/**
 * Helper function to get post meta key name
 *
 * @param string $key
 *
 * @return string Formatted post meta key name
 */
function precise_featured_post_key($key)
{
    return PreciseFeaturedPost::get_instance()->format_meta_key($key);
}

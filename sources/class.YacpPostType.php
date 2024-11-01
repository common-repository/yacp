<?php

/**
 * Created by PhpStorm.
 * User: vincent
 * Date: 11/26/18
 * Time: 2:45 PM
 */

namespace YACP;

/**
 * The Core of this custom post type was generated thanks to the CPT UI plugin. Then He was
 * customized to add all needed features.
 * Class YacpPostType
 * @package YACP
 */
class YacpPostType
{

    /**
     * This is where all needed vars are instantiated.
     * Some needed actions are listed here too
     * YacpPostType constructor.
     */
    public function __construct()
    {
        $this->custom_post_slug = 'yacp_post';

        $this->labels = array(
            "name" => __("YACP Countdowns", "yacp_textdomain"),
            "singular_name" => __("YACP Countdown", "yacp_textdomain"),
        );

        $this->args = array(
            "label" => __("YACP Countdowns", "yacp_textdomain"),
            "labels" => $this->labels,
            "description" => "",
            "public" => false,
            "publicly_queryable" => false,
            "show_ui" => true,
            "delete_with_user" => false,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => true,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "exclude_from_search" => true,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => false,
            "query_var" => false,
            "supports" => array("title", "posts-formats"),
            "menu_icon" => "dashicons-clock",
        );

        // See var theme_classes in YACP.php, they must be sync
        $this->available_themes = array(
            'default' => __('Default Theme', 'yacp_textdomain'),
            'losange' => __('Losange Theme', 'yacp_textdomain'),
            'inline' => __('Inline Theme', 'yacp_textdomain'),
            'simple-white' => __('Simple White Theme', 'yacp_textdomain'),
            'simple-black' => __('Simple Black Theme', 'yacp_textdomain'),
            'custom' => __('My Custom CSS', 'yacp_textdomain'),
        );

        $this->custom_fields = array(
            'theme' => array(
                'name' => __('Theme', 'yacp_textdomain'),
                'key' => '_yacp_theme'
            ),
            'date' => array(
                'name' => __('Date', 'yacp_textdomain'),
                'key' => '_yacp_date'
            ),
            'utc' => array(
                'name' => __('UTC Date', 'yacp_textdomain'),
                'key' => '_yacp_utc'
            ),
            'zero_pad' => array(
                'name' => __('Zero pad', 'yacp_textdomain'),
                'key' => '_yacp_zero_pad'
            ),
            'count_up' => array(
                'name' => __('Count up', 'yacp_textdomain'),
                'key' => '_yacp_count_up'
            ),
            'days' => array(
                'name' => __('Wording (day)', 'yacp_textdomain'),
                'key' => '_yacp_days'
            ),
            'hours' => array(
                'name' => __('Wording (hour)', 'yacp_textdomain'),
                'key' => '_yacp_hours'
            ),
            'minutes' => array(
                'name' => __('Wording (minute)', 'yacp_textdomain'),
                'key' => '_yacp_minutes'
            ),
            'seconds' => array(
                'name' => __('Wording (second)', 'yacp_textdomain'),
                'key' => '_yacp_seconds'
            ),
            'plural_letter' => array(
                'name' => __('Plural Letter', 'yacp_textdomain'),
                'key' => '_yacp_plural_letter'
            ),
        );

        add_action('init', array($this, 'registerMyCptsYacpPost'));
        add_action('add_meta_boxes', array($this, 'customMetaBoxes'));
        add_action('save_post', array($this, 'yacpSaveMetaBoxData'));
        add_filter('manage_yacp_post_posts_columns', array($this, 'setCustomEditYacpPostColumns'));
        add_action('manage_yacp_post_posts_custom_column', array($this, 'customYacpPostColumn'), 10, 2);
    }

    /**
     * Set custom columns for YACP posts
     * @param $columns
     * @return mixed
     */
    public function setCustomEditYacpPostColumns($columns)
    {
        $columns['yacp_date'] = __('End Date', 'yacp_textdomain');
        $columns['yacp_theme'] = __('Theme', 'yacp_textdomain');

        return $columns;
    }

    /**
     * Fill the custom columns with countdowns data
     * @param $column
     * @param $post_id
     */
    public function customYacpPostColumn($column, $post_id)
    {
        switch ($column) {
            case 'yacp_date':
                $terms = get_post_meta($post_id, '_yacp_date', true);
                echo '<p class="mod-date">' . date('m/d/Y @ g:i a', strtotime($terms)) . '</p>';
                break;
            case 'yacp_theme':
                echo get_post_meta($post_id, '_yacp_theme', true);
                break;
        }
    }

    /**
     * Check if we are in EDIT page or NEW POST page
     * @param string $new_edit
     * @return bool
     */
    protected function isEditPage($new_edit = 'edit')
    {
        global $pagenow;

        if ($new_edit == 'edit') {
            return in_array($pagenow, array('post.php',));
        } elseif ($new_edit == "new") { //check for new post page
            return in_array($pagenow, array('post-new.php'));
        } else { //check for either new or edit
            return in_array($pagenow, array('post.php', 'post-new.php'));
        }
    }

    /**
     * Create the context for admin meta boxes view
     * @param $post
     * @return array
     */
    protected function getTemplateContext($post)
    {
        /*
         * Use get_post_meta() to retrieve an existing value
         * from the database and use the value for the form.
         */
        return array(
            'ID' => $post->ID,
            'theme' => get_post_meta($post->ID, $this->custom_fields['theme']['key'], true),
            'date' => get_post_meta($post->ID, $this->custom_fields['date']['key'], true),
            'utc' => get_post_meta($post->ID, $this->custom_fields['utc']['key'], true),
            'zero_pad' => get_post_meta($post->ID, $this->custom_fields['zero_pad']['key'], true),
            'count_up' => get_post_meta($post->ID, $this->custom_fields['count_up']['key'], true),
            'days' => get_post_meta($post->ID, $this->custom_fields['days']['key'], true),
            'hours' => get_post_meta($post->ID, $this->custom_fields['hours']['key'], true),
            'minutes' => get_post_meta($post->ID, $this->custom_fields['minutes']['key'], true),
            'seconds' => get_post_meta($post->ID, $this->custom_fields['seconds']['key'], true),
            'plural_letter' => get_post_meta($post->ID, $this->custom_fields['plural_letter']['key'], true),
        );
    }

    /**
     * Generate the heart <3 of the shortcode
     * @param $center
     * @param $key
     * @param $value
     * @return string
     */
    protected function populateShortcode($center, $key, $value)
    {
        return ' ' . $center . $key . '="' . $value . '"';
    }

    /**
     * Create the preview shortcode to allow user to copy/paste it
     * @param $post
     * @return string
     */
    protected function getShortcodePreview($post)
    {
        $sc_start = '[yacp';
        $sc_center = '';
        $sc_end = ']';

        if ($this->isEditPage()) {
            $sc_center = $this->populateShortcode($sc_center, 'id', $post->ID);
            return $sc_start . $sc_center . $sc_end;
        }

        return __(
            'The Shortcode preview will be displayed here after post is saved',
            'yacp_textdomain'
        );
    }


    /**
     * Add the YACP meta box
     */
    public function customMetaBoxes()
    {
        add_meta_box(
            'yacp_shortcode_preview_box',
            __('Shortcode', 'yacp_textdomain'),
            array($this, 'yacpAddShortcodePreview'),
            $this->custom_post_slug,
            'normal',
            'low'
        );

        add_meta_box(
            'yacp_countdown',
            __('YACP Countdown Settings', 'yacp_textdomain'),
            array($this, 'yacpAddThemeFields'),
            $this->custom_post_slug,
            'normal',
            'high'
        );

        add_meta_box(
            'yacp_donation_box',
            __('Make a donation <3', 'yacp_textdomain'),
            array($this, 'yacpDonation'),
            $this->custom_post_slug,
            'side',
            'core'
        );

        add_meta_box(
            'yacp_last_box',
            __('test', 'yacp_textdomain'),
            array($this, 'yacpLastBox'),
            $this->custom_post_slug,
            'advanced',
            'low'
        );
    }

    public function yacpLastBox($post)
    {
        include 'admin/last_box.php';
    }

    public function yacpDonation($post)
    {
        include 'admin/donation_box.php';
    }

    public function yacpAddShortcodePreview($post)
    {
        // var used in the included template
        $shortcode = $this->getShortcodePreview($post);
        include 'admin/tpl.yacp_shortcode_preview.php';
    }

    /**
     * Callback of the add_meta_box above
     * This is where the form template is built
     * @param $post
     */
    public function yacpAddThemeFields($post)
    {

        // Add a nonce field so we can check for it later.
        wp_nonce_field('yacpSaveMetaBoxData', 'yacp_meta_box_nonce');

        // var used in the included template
        $ctx = $this->getTemplateContext($post);
        include 'admin/tpl.yacp_custom_field.php';
    }

    protected function sanitizeData($data)
    {
        return strip_tags(
            sanitize_text_field(
                $data
            )
        );
    }

    /**
     * Save the custom meta tags for YACP post type
     * @param $post_id
     */
    public function yacpSaveMetaBoxData($post_id)
    {
        if (!isset($_POST['yacp_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['yacp_meta_box_nonce'], 'yacpSaveMetaBoxData')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && $this->custom_post_slug == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }

        if (!isset($_POST['yacp_theme']) || !isset($_POST['yacp_date'])) {
            return;
        }

        $theme = $this->sanitizeData($_POST['yacp_theme']);
        $date = $this->sanitizeData($_POST['yacp_date']);
        $days = $this->sanitizeData($_POST['yacp_days']);
        $hours = $this->sanitizeData($_POST['yacp_hours']);
        $minutes = $this->sanitizeData($_POST['yacp_minutes']);
        $seconds = $this->sanitizeData($_POST['yacp_seconds']);
        $plural_letter = $this->sanitizeData($_POST['yacp_plural_letter']);

        $utc = !empty($this->sanitizeData($_POST['yacp_utc']))
            && ($this->sanitizeData($_POST['yacp_utc']) === 'on');
        $zero_pad = !empty($this->sanitizeData($_POST['yacp_zero_pad']))
            && ($this->sanitizeData($_POST['yacp_zero_pad']) === 'on');
        $count_up = !empty($this->sanitizeData($_POST['yacp_count_up']))
            && ($this->sanitizeData($_POST['yacp_count_up']) === 'on');

        update_post_meta($post_id, $this->custom_fields['theme']['key'], $theme);
        update_post_meta($post_id, $this->custom_fields['date']['key'], $date);
        update_post_meta($post_id, $this->custom_fields['utc']['key'], $utc);
        update_post_meta($post_id, $this->custom_fields['zero_pad']['key'], $zero_pad);
        update_post_meta($post_id, $this->custom_fields['count_up']['key'], $count_up);
        update_post_meta($post_id, $this->custom_fields['days']['key'], $days);
        update_post_meta($post_id, $this->custom_fields['hours']['key'], $hours);
        update_post_meta($post_id, $this->custom_fields['minutes']['key'], $minutes);
        update_post_meta($post_id, $this->custom_fields['seconds']['key'], $seconds);
        update_post_meta($post_id, $this->custom_fields['plural_letter']['key'], $plural_letter);
    }

    /**
     * Simply Register the YACP Custom Post Type
     */
    public function registerMyCptsYacpPost()
    {
        /**
         * Post Type: YACP Countdowns.
         */
        register_post_type($this->custom_post_slug, $this->args);
    }
}

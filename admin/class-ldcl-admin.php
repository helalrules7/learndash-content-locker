<?php
require_once ABSPATH . 'wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';
require_once ABSPATH . 'wp-includes/pluggable.php';


class LDCL_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_post_ldcl_save_course_serial', array($this, 'handle_course_serial_form_submission'));
        add_action('wp_ajax_ldcl_get_course_or_lesson_title', array($this, 'ldcl_get_course_or_lesson_title'));
        add_action('wp_ajax_ldcl_get_serial_data', array($this, 'ldcl_get_serial_data'));
        add_action('wp_ajax_ldcl_update_serial_data', array($this, 'ldcl_update_serial_data'));
        add_action('wp_ajax_ldcl_delete_serial', array($this, 'ldcl_delete_serial_callback'));
    }
    // In your PHP plugin file or functions.php

    function ldcl_delete_serial_callback() {
        global $wpdb;
    
        // Check nonce for security
        check_ajax_referer( 'ldcl_delete_serial_nonce', 'nonce' );
    
        $serial_id = isset( $_POST['serial_id'] ) ? intval( $_POST['serial_id'] ) : 0;
    
        // Delete the serial from the database
        $table_name = $wpdb->prefix . 'ldcl_serials';
        $wpdb->delete( $table_name, array( 'id' => $serial_id ) );
    
        // Return a response (optional)
        wp_send_json_success( 'Serial deleted successfully' );
        wp_die();
    }

function ldcl_update_serial_data() {
    if (!isset($_POST['serial_id'], $_POST['serial_title'], $_POST['validity_date'])) {
        wp_send_json_error(array('message' => 'Missing parameters'));
    }

    $serial_id = sanitize_text_field($_POST['serial_id']);
    $serial_title = sanitize_text_field($_POST['serial_title']);
    $validity_date = sanitize_text_field($_POST['validity_date']);

    // Update the serial data in your database or perform necessary operations.
    // Example: Update using $wpdb
    global $wpdb;
    $table_name = $wpdb->prefix . 'ldcl_serials';
    $result = $wpdb->update(
        $table_name,
        array(
            'serial_title' => $serial_title,
            'validity_date' => $validity_date
        ),
        array('id' => $serial_id),
        array('%s', '%s'),
        array('%d')
    );

    if ($result !== false) {
        wp_send_json_success(array('message' => 'Serial updated successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to update serial'));
    }
}

    public function add_admin_pages()
    {
        add_menu_page('LDCL Serials', 'LDCL Serials', 'manage_options', 'ldcl-serials', array($this, 'serials_table_page'), 'dashicons-lock');
        add_submenu_page('ldcl-serials', 'Create Course Serial', 'Create Course Serial', 'manage_options', 'ldcl-create-course-serial', array($this, 'create_course_serial_page'));
       
    } 

    public function enqueue_admin_scripts()
    {
        wp_enqueue_style('ldcl-admin-css', LDCL_PLUGIN_URL . 'assets/css/ldcl-admin.css');
        wp_enqueue_script('ldcl-admin-js', LDCL_PLUGIN_URL . 'assets/js/ldcl-admin.js', array('jquery'), null, true);
    }

    public function serials_table_page()
    {
        include LDCL_PLUGIN_DIR . 'admin/views/view-serials-table.php';
    }

    public function create_course_serial_page()
    {
        $courses = $this->get_courses();
        include LDCL_PLUGIN_DIR . 'admin/views/view-create-serial-course.php';
    }


    public function handle_course_serial_form_submission()
    {
        if (isset($_POST['ldcl_course_serial_nonce']) && wp_verify_nonce($_POST['ldcl_course_serial_nonce'], 'ldcl_save_course_serial')) {
            global $wpdb;

            $serial_title = sanitize_text_field($_POST['serial_title']);
            $generated_serial = substr(sanitize_text_field($_POST['generated_serial']), 0, 20); // Limit to 20 characters
            $course_id = intval($_POST['course_select']);
            $validity_date = sanitize_text_field($_POST['validity_date']);

            $table_name = $wpdb->prefix . 'ldcl_serials';

            $insert_result = $wpdb->insert(
                $table_name,
                array(
                    'serial_title' => $serial_title,
                    'serial' => $generated_serial,
                    'course_or_lesson_id' => $course_id,
                    'validity_date' => $validity_date,
                    'used' => 0,
                    'created_at' => current_time('mysql')
                )
            );

            if (!$insert_result) {
                error_log('Error inserting course serial: ' . $wpdb->last_error);
            }

            // Redirect to avoid form resubmission
            wp_redirect(admin_url('admin.php?page=ldcl-create-course-serial&success=1'));
            exit;
        }
    }


    public function get_courses()
    {
        $args = array(
            'post_type' => 'sfwd-courses',
            'posts_per_page' => -1, // Retrieve all courses
        );

        $query = new WP_Query($args);

        $course_list = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $course_list[get_the_ID()] = get_the_title();
            }
            wp_reset_postdata();
        } else {
            error_log('No LearnDash courses found.');
        }

        return $course_list;
    }
    public function get_course_or_lesson_details($id)
    {
        $post = get_post($id);
        return $post;
    }

    function ldcl_get_serial_data() {
        if (!isset($_POST['serial_id'])) { // Change $_GET to $_POST
            wp_send_json_error(array('message' => 'Serial ID is required'));
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'ldcl_serials';
        $serial_id = intval($_POST['serial_id']); // Change $_GET to $_POST
    
        $serial = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $serial_id));
    
        if ($serial) {
            wp_send_json_success($serial);
        } else {
            wp_send_json_error(array('message' => 'Serial not found'));
        }
    }

function ldcl_get_course_or_lesson_title() {
    if (!isset($_POST['id'])) {
        wp_send_json_error(array('message' => 'ID is required'));
    }

    $id = $_POST['id'];
    $post = get_post($id);

    if ($post) {
        wp_send_json_success(array('title' => $post->post_title));
    } else {
        wp_send_json_error(array('message' => 'Course or lesson not found'));
    }
}

    public function get_serial_by_id($serial_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ldcl_serials';

        $serial = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $serial_id)
        );

        return $serial;
    }
}
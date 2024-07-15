<?php
require_once ABSPATH . 'wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';
require_once ABSPATH . 'wp-includes/pluggable.php';
require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';


class LDCL_Admin
{
/**
 * Initializes the LDCL_Admin class and sets up necessary actions and hooks.
 */
public function __construct()
{
    // Add admin menu page
    add_action('admin_menu', array($this, 'add_admin_pages'));

    // Enqueue admin scripts and styles
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

    // Handle course serial form submission
    add_action('admin_post_ldcl_save_course_serial', array($this, 'handle_course_serial_form_submission'));

    // Get course or lesson title via AJAX
    add_action('wp_ajax_ldcl_get_course_or_lesson_title', array($this, 'ldcl_get_course_or_lesson_title'));

    // Get serial data via AJAX
    add_action('wp_ajax_ldcl_get_serial_data', array($this, 'ldcl_get_serial_data'));

    // Update serial data via AJAX
    add_action('wp_ajax_ldcl_update_serial_data', array($this, 'ldcl_update_serial_data'));

    // Delete serial via AJAX
    add_action('wp_ajax_ldcl_delete_serial', array($this, 'ldcl_delete_serial_callback'));

    // Export serials to Excel via AJAX
    add_action('wp_ajax_export_serials_to_excel', array($this, 'ldcl_export_serials_to_excel'));
}

/**
 * Adds admin menu pages for LDCL Serials plugin.
 *
 * This function registers two admin menu pages:
 * 1. 'LDCL Serials' page: Displays a table of all serials.
 * 2. 'Create Course Serial' page: Allows users to create new course serials.
 *
 * @access public
 * @return void
 */
public function add_admin_pages()
{
    add_menu_page(
        'LDCL Serials', // Page title
        'LDCL Serials', // Menu title
        'manage_options', // Capability required
        'ldcl-serials', // Menu slug
        array($this, 'serials_table_page'), // Function to call when rendering the page
        'dashicons-lock' // Icon URL
    );

    add_submenu_page(
        'ldcl-serials', // Parent menu slug
        'Create Course Serial', // Page title
        'Create Course Serial', // Menu title
        'manage_options', // Capability required
        'ldcl-create-course-serial', // Menu slug
        array($this, 'create_course_serial_page') // Function to call when rendering the page
    );
}

    public function enqueue_admin_scripts()
    {
        wp_enqueue_style('ldcl-admin-css', LDCL_PLUGIN_URL . 'assets/css/ldcl-admin.css');
        wp_enqueue_script('ldcl-admin-js', LDCL_PLUGIN_URL . 'assets/js/ldcl-admin.js', array('jquery'), null, true);
        // Localize the script with new data
    wp_localize_script('ldcl-admin-js', 'ldcl_admin', array(
        'nonce' => wp_create_nonce('export_serials_nonce')
    ));
    }
  /**
 * Exports serials data to an Excel file.
 *
 * This function verifies the nonce, retrieves all serials from the database,
 * creates a new Spreadsheet object, populates the data, applies styles,
 * and saves the file as an Excel file.
 *
 * @return void
 */
public function ldcl_export_serials_to_excel() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'export_serials_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ldcl_serials';
    $serials = $wpdb->get_results("SELECT * FROM $table_name");

    if (empty($serials)) {
        wp_send_json_error('No serials found');
        return;
    }

    // Create new Spreadsheet object
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers for the Excel file
    $headers = ['Serial', 'Title', 'Course', 'Validity', 'Used', 'Used By', 'Created At'];
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . '1', $header);
        $column++;
    }

    // Populate the data
    $row = 2;
    foreach ($serials as $serial) {
        $sheet->setCellValue('A' . $row, $serial->serial);
        $sheet->setCellValue('B' . $row, $serial->serial_title);
        $sheet->setCellValue('C' . $row, get_the_title($serial->course_or_lesson_id));
        $sheet->setCellValue('D' . $row, $serial->validity_date);
        $sheet->setCellValue('E' . $row, $serial->used ? 'Yes' : 'No');
        $sheet->setCellValue('F' . $row, $serial->used_by_user_id ? get_userdata($serial->used_by_user_id)->user_login : 'N/A');
        $sheet->setCellValue('G' . $row, date('Y-m-d', strtotime($serial->created_at)));
        $row++;
    }

    // Set column width to auto size
    foreach (range('A', 'G') as $col) { // Adjusted to 'G' which is the last column
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Add borders to the cells
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
        ],
    ];
    $sheet->getStyle('A1:G' . ($row - 1))->applyFromArray($styleArray);

    // Style header row
    $headerStyleArray = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'D3D3D3', // Light gray
            ],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
        ],
    ];
    $sheet->getStyle('A1:G1')->applyFromArray($headerStyleArray);

    // Save the file
    $filename = 'serials_list.xlsx';
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $file_path = plugin_dir_path(__FILE__) . $filename;
    $writer->save($file_path);

    // Send the file as a response
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    readfile($file_path);

    // Clean up
    unlink($file_path);
    exit;
}
    

/**
 * Deletes a serial from the database.
 *
 * This function is an AJAX callback function that handles the deletion of a serial from the database.
 * It checks the nonce for security, retrieves the serial ID from the POST data, deletes the serial from the database,
 * and returns a success response.
 *
 * @return void
 */
function ldcl_delete_serial_callback() {
    global $wpdb;

    // Check nonce for security
    check_ajax_referer( 'ldcl_delete_serial_nonce', 'nonce' );

    // Retrieve the serial ID from the POST data
    $serial_id = isset( $_POST['serial_id'] ) ? intval( $_POST['serial_id'] ) : 0;

    // Delete the serial from the database
    $table_name = $wpdb->prefix . 'ldcl_serials';
    $wpdb->delete( $table_name, array( 'id' => $serial_id ) );

    // Return a success response
    wp_send_json_success( 'Serial deleted successfully' );
    wp_die();
}

/**
 * Updates a serial's data in the database.
 *
 * This function handles the AJAX request to update a serial's data in the database.
 * It checks for the required parameters in the POST data, sanitizes the input,
 * and updates the serial data using the WordPress database API.
 *
 * @since 1.0.0
 *
 * @return void
 */
function ldcl_update_serial_data() {
    // Check if required parameters are set
    if (!isset($_POST['serial_id'], $_POST['serial_title'], $_POST['validity_date'])) {
        wp_send_json_error(array('message' => 'Missing parameters'));
    }

    // Sanitize input parameters
    $serial_id = sanitize_text_field($_POST['serial_id']);
    $serial_title = sanitize_text_field($_POST['serial_title']);
    $validity_date = sanitize_text_field($_POST['validity_date']);

    // Update the serial data in the database
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

    // Check the result and send appropriate response
    if ($result !== false) {
        wp_send_json_success(array('message' => 'Serial updated successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to update serial'));
    }
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


    /**
 * Handles the submission of the course serial form in the admin area.
 *
 * This function is responsible for validating the form submission, sanitizing the input,
 * and inserting the new course serial into the database. It also handles any errors that may occur during the process.
 *
 * @since 1.0.0
 *
 * @return void
 */
public function handle_course_serial_form_submission()
{
    // Check if the required nonce and form data are set
    if (isset($_POST['ldcl_course_serial_nonce']) && wp_verify_nonce($_POST['ldcl_course_serial_nonce'], 'ldcl_save_course_serial')) {
        global $wpdb;

        // Sanitize and validate input parameters
        $serial_title = sanitize_text_field($_POST['serial_title']);
        $generated_serial = substr(sanitize_text_field($_POST['generated_serial']), 0, 20); // Limit to 20 characters
        $course_id = intval($_POST['course_select']);
        $validity_date = sanitize_text_field($_POST['validity_date']);

        // Prepare the table name for the database query
        $table_name = $wpdb->prefix . 'ldcl_serials';

        // Insert the new course serial into the database
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

        // Check the result of the insert query
        if (!$insert_result) {
            // Log any errors that occurred during the insert query
            error_log('Error inserting course serial: ' . $wpdb->last_error);
        }

        // Redirect to the create course serial page with a success message
        // to avoid form resubmission
        wp_redirect(admin_url('admin.php?page=ldcl-create-course-serial&success=1'));
        exit;
    }
}


/**
 * Retrieves a list of LearnDash courses.
 *
 * This function queries the WordPress database for all LearnDash courses
 * and returns them as an associative array with the course ID as the key
 * and the course title as the value.
 *
 * @return array An associative array of LearnDash courses.
 */
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
/**
 * Retrieves the details of a course or lesson based on the provided ID.
 *
 * @param int $id The ID of the course or lesson.
 *
 * @return WP_Post|null The details of the course or lesson if found, or null if not found.
 *
 * @since 1.0.0
 */
public function get_course_or_lesson_details($id)
{
    $post = get_post($id);
    return $post;
}
/**
 * Retrieves a serial's data from the database based on the provided serial ID.
 *
 * This function is an AJAX callback function that handles the retrieval of a serial's data from the database.
 * It checks the POST data for the 'serial_id' parameter, retrieves the serial data from the database,
 * and returns the serial data as a JSON response.
 *
 * @since 1.0.0
 *
 * @return void
 */
function ldcl_get_serial_data() {
    // Check if the 'serial_id' parameter is set in the POST data
    if (!isset($_POST['serial_id'])) {
        // If not set, send an error response and exit
        wp_send_json_error(array('message' => 'Serial ID is required'));
    }

    // Access the WordPress database using the global $wpdb object
    global $wpdb;

    // Prepare the table name for the database query
    $table_name = $wpdb->prefix . 'ldcl_serials';

    // Retrieve the 'serial_id' parameter from the POST data and sanitize it
    $serial_id = intval($_POST['serial_id']);

    // Prepare a SQL query to retrieve the serial data from the database
    $serial = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $serial_id));

    // Check if the serial data was found in the database
    if ($serial) {
        // If found, send a success response with the serial data
        wp_send_json_success($serial);
    } else {
        // If not found, send an error response
        wp_send_json_error(array('message' => 'Serial not found'));
    }
}

/**
 * Retrieves the title of a course or lesson based on the provided ID.
 *
 * This function is an AJAX callback function that handles the retrieval of a course or lesson's title from the database.
 * It checks the POST data for the 'id' parameter, retrieves the post data using the provided ID,
 * and returns the post title as a JSON response.
 *
 * @since 1.0.0
 *
 * @return void
 */
function ldcl_get_course_or_lesson_title() {
    // Check if the 'id' parameter is set in the POST data
    if (!isset($_POST['id'])) {
        // If not set, send an error response and exit
        wp_send_json_error(array('message' => 'ID is required'));
    }

    // Retrieve the 'id' parameter from the POST data and sanitize it
    $id = $_POST['id'];

    // Retrieve the post data using the provided ID
    $post = get_post($id);

    // Check if the post data was found
    if ($post) {
        // If found, send a success response with the post title
        wp_send_json_success(array('title' => $post->post_title));
    } else {
        // If not found, send an error response
        wp_send_json_error(array('message' => 'Course or lesson not found'));
    }
}

/**
 * Retrieves a serial's data from the database based on the provided serial ID.
 *
 * @param int $serial_id The ID of the serial to retrieve.
 *
 * @return object|null The serial data object if found, or null if not found.
 *
 * @since 1.0.0
 */
public function get_serial_by_id($serial_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'ldcl_serials';

    $serial = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $serial_id)
    );

    return $serial;
}}
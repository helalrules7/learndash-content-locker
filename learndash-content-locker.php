<?php
/*
Plugin Name: LearnDash Content Locker
Description: Lock LearnDash courses with a serial number.
Version: 1.0
Author: <a href="https://ahmedhelal.dev">Ahmed Helal</a> | <a href="https://buymeacoffee.com/ahmedhelal">Buy Me Coffee!</a>
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'LDCL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LDCL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// Include Composer autoload file

// Include necessary files.
require_once LDCL_PLUGIN_DIR . 'includes/class-ldcl-serial.php';
require_once LDCL_PLUGIN_DIR . 'admin/class-ldcl-admin.php';
require_once LDCL_PLUGIN_DIR . 'public/class-ldcl-public.php';


// Initialize the plugin.
function ldcl_init() {
    $ldcl_admin = new LDCL_Admin();
    $ldcl_public = new LDCL_Public();

}

register_activation_hook( __FILE__, 'ldcl_create_serials_table' );

/**
 * Loads the plugin's textdomain for localization.
 *
 * This function loads the plugin's textdomain, allowing for translations of strings used in the plugin.
 *
 * @return void
 */
function ldcl_load_textdomain() {
    load_plugin_textdomain('learndash-content-locker', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'ldcl_load_textdomain');



/**
 * Creates the ldcl_serials table in the WordPress database.
 * This table is used to store serial numbers for courses and lessons.
 *
 * @global wpdb $wpdb WordPress database object.
 *
 * @return void
 */
function ldcl_create_serials_table() {
    global $wpdb;

    // Define the table name with the WordPress prefix
    $table_name = $wpdb->prefix . 'ldcl_serials';

    // Get the character collation for the current database
    $charset_collate = $wpdb->get_charset_collate();

    // SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        serial_title varchar(255) NOT NULL,
        serial varchar(16) NOT NULL,
        course_or_lesson_id mediumint(9) NOT NULL,
        validity_date date NOT NULL,
        used tinyint(1) DEFAULT 0 NOT NULL,
        used_by_user_id mediumint(9) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Include the upgrade.php file to use dbDelta function
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // Execute the SQL query using dbDelta function
    dbDelta( $sql );

    // Create the serial input page
    ldcl_create_serial_page();
}

function ldcl_enqueue_scripts() {
    wp_enqueue_script( 'jquery' );   
    // Enqueue Font Awesome CSS from CDN
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4', 'all' );
    // Enqueue Bootstrap CSS and JS from CDN
    wp_enqueue_style( 'bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', array(), '4.5.2', 'all' );

    wp_enqueue_script( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), '4.5.2', true );

    wp_enqueue_style('tablesorter-css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.3/css/theme.default.min.css');
    wp_enqueue_script('tablesorter-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.3/js/jquery.tablesorter.min.js', array('jquery'), null, true);
    wp_enqueue_script('bwip-js', 'https://cdnjs.cloudflare.com/ajax/libs/bwip-js/3.1.0/bwip-js-min.js', array(), '3.1.0', true);
    wp_enqueue_script('ldcl-scripts', LDCL_PLUGIN_URL . 'public/js/ldcl-scripts.js', array('jquery'), '1.0', true);

}
add_action( 'admin_enqueue_scripts', 'ldcl_enqueue_scripts' );
add_action( 'plugins_loaded', 'ldcl_init' );

function ldcl_enqueue_custom_styles() {
    wp_enqueue_style('ldcl-styles', LDCL_PLUGIN_URL . 'public/css/ldcl-styles.css');
    if (is_rtl()) {
        wp_enqueue_style('ldcl-rtl-styles', LDCL_PLUGIN_URL . 'public/css/ldcl-rtl.css', array('ldcl-styles'));
        wp_enqueue_style('ldcl-admin-rtl', LDCL_PLUGIN_URL . 'public/css/admin-rtl.css', array('ldcl-styles', 'ldcl-rtl-styles'));
    }
}
add_action('admin_enqueue_scripts', 'ldcl_enqueue_custom_styles');


function ldcl_force_admin_rtl($classes) {
    if (is_rtl() && isset($_GET['page']) && strpos($_GET['page'], 'ldcl-') === 0) {
        $classes .= ' force-rtl';
    }
    return $classes;
}
add_filter('admin_body_class', 'ldcl_force_admin_rtl');

/**
 * Adds inline styles to the admin area for RTL (Right-to-Left) languages.
 * This function checks if the current WordPress installation is using an RTL language.
 * If it is, it outputs inline CSS styles to align the admin menu and other elements properly.
 *
 * @return void
 */
function ldcl_add_inline_styles() {
    if (is_rtl()) {
        echo '<style>
            /* Add any additional RTL styles here */
                /* ldcl-rtl.css */
            .wp-admin #adminmenu {
                text-align: right;

            }
            html{
                text-align: right !important;
            }
            
        </style>';
    }
}
add_action('admin_head', 'ldcl_add_inline_styles');

function ldcl_add_customizer_rtl_styles() {
    if (is_rtl()) {
        echo '<style id="ldcl-customizer-rtl-styles">
            /* Force RTL styles for customizer */
            .wp-customizer .wp-full-overlay-sidebar,
            .wp-customizer .wp-full-overlay-sidebar-content,
            .wp-customizer .customize-panel-back,
            .wp-customizer .customize-section-back,
            .wp-customizer .customize-control,
            .wp-customizer .customize-control-title,
            .wp-customizer .customize-control input,
            .wp-customizer .customize-control textarea,
            .wp-customizer .customize-control select {
                direction: rtl !important;
                text-align: right !important;
            }
            
            .wp-customizer .customize-panel-back,
            .wp-customizer .customize-section-back {
                float: right !important;
            }
            
            .wp-customizer .customize-controls-close {
                left: 0 !important;
                right: auto !important;
            }

            body.rtl #adminmenu .wp-submenu {
                left: auto;
                right: 160px;
            }

/* Add more specific rules as needed */
            
            /* Add any other specific styles here */

            body, body.rtl, html{
           text-align: right !important;
    }
        </style>';
    }
}
add_action('customize_controls_print_styles', 'ldcl_add_customizer_rtl_styles');

function ldcl_enqueue_late_customizer_rtl_styles() {
    if (is_rtl()) {
        wp_add_inline_style('customize-controls', '
            /* Repeat the same styles as above */
            .wp-customizer .wp-full-overlay-sidebar,
            .wp-customizer .wp-full-overlay-sidebar-content,
            .wp-customizer .customize-panel-back,
            .wp-customizer .customize-section-back,
            .wp-customizer .customize-control,
            .wp-customizer .customize-control-title,
            .wp-customizer .customize-control input,
            .wp-customizer .customize-control textarea,
            .wp-customizer .customize-control select {
                direction: rtl !important;
                text-align: right !important;
            }
            
            .wp-customizer .customize-panel-back,
            .wp-customizer .customize-section-back {
                float: right !important;
            }
            
            .wp-customizer .customize-controls-close {
                left: 0 !important;
                right: auto !important;
            }
                        body, body.rtl, html{
           text-align: right !important;
    }
        ');
    }
}
add_action('customize_controls_enqueue_scripts', 'ldcl_enqueue_late_customizer_rtl_styles', 20);

// Create a page for serial input
/**
 * Creates a page for serial input in WordPress.
 * If the page does not exist, it creates a new page with the specified title, slug, and content.
 *
 * @return void
 */
function ldcl_create_serial_page() {
    $page_title = __('Enter Serial Code', 'learndash-content-locker');
    $post_name = 'enter-serial-code';
    $page_content = '[ldcl_serial_form]';

    // Set up the query arguments for the WP_Query
    $query_args = array(
        'post_type' => 'page',
        'title'     => $page_title,
        'name'       => $post_name,
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids'
    );

    // Perform the query to check if the page already exists
    $query = new WP_Query($query_args);

    // If no posts were found, create a new page
    if (!$query->have_posts()) {
        $page_data = array(
            'post_type'    => 'page',
            'post_title'   => $page_title,
            'post_name'     => $post_name,
            'post_content' => $page_content,
            'post_status'  => 'publish',
            'post_author'  => 1,
        );

        // Insert the new page into the database
        wp_insert_post($page_data);
    }
}

// Shortcode for serial input form
/**
 * Outputs the serial input form shortcode.
 *
 * This function generates a form for users to enter a serial code for accessing a course.
 * The form includes three input fields for the serial code parts and a submit button.
 * It also includes JavaScript to automatically move focus to the next input field when the current one reaches its maximum length.
 *
 * @return string The HTML markup for the serial input form.
 */
function ldcl_serial_form_shortcode() {
    ob_start();
    ?>
<form method="post" id="ldcl-serial-form" dir="rtl" style="direction:rtl" dir="rtl">
    <?php if (isset($_GET['ldcl_message'])): ?>
    <div class="ldcl-message <?php echo esc_attr($_GET['ldcl_message_type']); ?>">
        <?php echo esc_html($_GET['ldcl_message']); ?>
    </div>
    <?php endif; ?>
    <h5><?php _e('Enter the 8 characters serial number', 'learndash-content-locker'); ?></h5>
    <div dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="direction:<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
        <input type="text" placeholder="xxxx" name="ldcl_serial_part1" id="ldcl_serial_part1" maxlength="4" required>
        <span>-</span>
        <input type="text" placeholder="xxxx" name="ldcl_serial_part2" id="ldcl_serial_part2" maxlength="4" required>
    </div>
    <button type="submit"><?php _e('Confirm Serial Number', 'learndash-content-locker'); ?></button>
    <?php wp_nonce_field('ldcl_save_settings', 'ldcl_settings_nonce'); ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('#ldcl-serial-form input[type="text"]');

        inputs.forEach((input, index) => {
            input.addEventListener('input', (event) => {
                if (input.value.length === 4 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
        });
    });
    </script>
</form>
<?php
    return ob_get_clean();
}
add_shortcode('ldcl_serial_form', 'ldcl_serial_form_shortcode');

// Handle serial form submission and grant access to the course
/**
 * Handles the submission of the serial form and grants access to the course.
 *
 * This function checks if the serial form data is submitted and validates the serial code.
 * If the serial code is valid and not expired, it updates the serial usage in the database,
 * grants access to the course, and redirects the user to the course page.
 * If the serial code is invalid or expired, it redirects back to the form with an error message.
 *
 * @return void
 */
function ldcl_handle_serial_form_submission() {
    if (isset($_POST['ldcl_serial_part1']) && isset($_POST['ldcl_serial_part2'])) {
        global $wpdb;
        $serial_code = sanitize_text_field($_POST['ldcl_serial_part1']) . '-' .
                       sanitize_text_field($_POST['ldcl_serial_part2']);
        $table_name = $wpdb->prefix . 'ldcl_serials';

        $serial = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE serial = %s AND used = 0", $serial_code)
        );

        if ($serial) {
            // Check validity
            $current_date = date('Y-m-d');
            if ($serial->validity_date >= $current_date) {
                // Update serial usage
                $user_id = get_current_user_id();
                $wpdb->update(
                    $table_name,
                    array(
                        'used' => 1,
                        'used_by_user_id' => $user_id,
                        'used_date' => current_time('mysql')
                    ),
                    array('id' => $serial->id),
                    array('%d', '%d', '%s'),
                    array('%d')
                );

                // Grant access to the course
                ld_update_course_access($user_id, $serial->course_or_lesson_id);

                // Redirect to course page
                wp_redirect(get_permalink($serial->course_or_lesson_id));
                exit;
            } else {
                $message = _e('Serial number is outdated', 'learndash-content-locker');
                $message_type = 'error';
            }
        } else {
            $message = _e('Invalid or already used serial code', 'learndash-content-locker');
            $message_type = 'error';
        }

        // Redirect back to the form with the message
        $redirect_url = add_query_arg(array(
            'ldcl_message' => urlencode($message),
            'ldcl_message_type' => urlencode($message_type),
        ), wp_get_referer());

        wp_redirect($redirect_url);
        exit;
    }
}

add_action('template_redirect', 'ldcl_handle_serial_form_submission');

/**
 * Checks and updates the database table for the LearnDash Content Locker plugin.
 * This function ensures that the 'used_date' column exists in the 'ldcl_serials' table.
 * If the column does not exist, it adds the column with a default value of NULL.
 *
 * @global wpdb $wpdb WordPress database object.
 *
 * @return void
 */
function ldcl_update_db_check() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ldcl_serials';

    // Check if the 'used_date' column exists in the table
    $row = $wpdb->get_row("SHOW COLUMNS FROM $table_name LIKE 'used_date'");

    // If the column does not exist, add it with a default value of NULL
    if (!$row) {
        $wpdb->query("ALTER TABLE $table_name ADD used_date datetime DEFAULT NULL");
    }
}
add_action('plugins_loaded', 'ldcl_update_db_check');

?>
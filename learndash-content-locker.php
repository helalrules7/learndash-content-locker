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

function ldcl_create_serials_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ldcl_serials';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        serial_title varchar(255) NOT NULL,
        serial varchar(20) NOT NULL,
        course_or_lesson_id mediumint(9) NOT NULL,
        validity_date date NOT NULL,
        used tinyint(1) DEFAULT 0 NOT NULL,
        used_by_user_id mediumint(9) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Create serial input page
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
}
add_action('wp_enqueue_scripts', 'ldcl_enqueue_custom_styles');


// Create a page for serial input
function ldcl_create_serial_page() {
    $page_title = 'أدخل كود الكورس';
    $post_name = 'enter-serial-code';
    $page_content = '[ldcl_serial_form]';

    $query = new WP_Query(array(
        'post_type' => 'page',
        'title'     => $page_title,
        'name'       => $post_name,
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids'
    ));

    if (!$query->have_posts()) {
        $page = array(
            'post_type'    => 'page',
            'post_title'   => $page_title,
            'post_name'     => $post_name,
            'post_content' => $page_content,
            'post_status'  => 'publish',
            'post_author'  => 1,
        );
        wp_insert_post($page);
    }
}

// Shortcode for serial input form
function ldcl_serial_form_shortcode() {
    ob_start();
    ?>
<form method="post" id="ldcl-serial-form">
    <?php if (isset($_GET['ldcl_message'])): ?>
    <div class="ldcl-message <?php echo esc_attr($_GET['ldcl_message_type']); ?>">
        <?php echo esc_html($_GET['ldcl_message']); ?>
    </div>
    <?php endif; ?>
    <h5>أدخل الكود المكون من 12 رقم/حرف</h5>
    <div>
        <input type="text" placeholder="xxxx" name="ldcl_serial_part1" id="ldcl_serial_part1" maxlength="4" required>
        <span>-</span>
        <input type="text" placeholder="xxxx" name="ldcl_serial_part2" id="ldcl_serial_part2" maxlength="4" required>
        <span>-</span>
        <input type="text" placeholder="xxxx" name="ldcl_serial_part3" id="ldcl_serial_part3" maxlength="4" required>
    </div>
    <button type="submit">تأكيد الكود</button>
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
function ldcl_handle_serial_form_submission() {
    if (isset($_POST['ldcl_serial_part1']) && isset($_POST['ldcl_serial_part2']) && isset($_POST['ldcl_serial_part3'])) {
        global $wpdb;
        $serial_code = sanitize_text_field($_POST['ldcl_serial_part1']) . '-' .
                       sanitize_text_field($_POST['ldcl_serial_part2']) . '-' .
                       sanitize_text_field($_POST['ldcl_serial_part3']);
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
                $message = 'تاريخ إنتهاء الكود قد مر بالفعل ، تواصل مع السكرتارية لشراء كود جديد';
                $message_type = 'error';
            }
        } else {
            $message = 'الكود المدخل غير صحيح او مستخدم من قبل ، تواصل مع السكرتارية لشراء كود جديد';
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

function ldcl_update_db_check() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ldcl_serials';

    $row = $wpdb->get_row("SHOW COLUMNS FROM $table_name LIKE 'used_date'");
    if (!$row) {
        $wpdb->query("ALTER TABLE $table_name ADD used_date datetime DEFAULT NULL");
    }
}
add_action('plugins_loaded', 'ldcl_update_db_check');

?>
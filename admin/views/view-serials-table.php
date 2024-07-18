<?php
// Include the modal
require_once LDCL_PLUGIN_DIR . 'admin/views/view-edit-modal.php';
require_once LDCL_PLUGIN_DIR . 'admin/views/view-delete-modal.php';
require_once LDCL_PLUGIN_DIR . 'admin/views/view-barcode-modal.php';
require_once LDCL_PLUGIN_DIR . 'includes/class-ldcl-serial.php';
require_once LDCL_PLUGIN_DIR . 'admin/class-ldcl-admin.php';
require_once LDCL_PLUGIN_DIR . 'public/class-ldcl-public.php';

add_action('wp_ajax_export_serials_to_excel', 'ldcl_export_serials_to_excel');
add_action('wp_ajax_nopriv_export_serials_to_excel', 'ldcl_export_serials_to_excel');

// Include WordPress functions
require_once ABSPATH . 'wp-load.php';
?>

<div class="wrap ldcl-admin-page">

    <h2><?php _e('Generated Serials', 'learndash-content-locker'); ?></h2>
    <hr>
    <a href="<?php echo admin_url('admin.php?page=ldcl-create-course-serial'); ?>"><button class="ldcl_btn"><i
                class="fa fa-graduation-cap"></i>
            <?php _e('Create New Serial', 'learndash-content-locker'); ?></button></a>
    &nbsp;
    <a href="#"><button class="ldcl_btn" id="exportSerialsBtn"><i class="fa fa-file-excel"></i>
            <?php _e(' Export to excel', 'learndash-content-locker'); ?></button></a>
    <!-- Add this search input -->

    <hr>
    <div class="container">
        <input type="text" id="serialSearchInput"
            placeholder="<?php _e('Search serials...', 'learndash-content-locker'); ?>"
            style="float: right; margin-bottom: 10px; padding: 5px; width: 50%; border: 1px solid #ddd; border-radius: 4px;">

        <table id="serialsTable" class="widefat fixed rtlready" cellspacing="0">
            <thead>
                <tr>
                    <th id="serial"><?php _e('Serial', 'learndash-content-locker'); ?></th>
                    <th id="title"><?php _e('Title', 'learndash-content-locker'); ?></th>
                    <th id="course_or_lesson"><?php _e('Course', 'learndash-content-locker'); ?></th>
                    <th id="validity"><?php _e('Validity', 'learndash-content-locker'); ?></th>
                    <th id="used"><?php _e('Is Used?', 'learndash-content-locker'); ?></th>
                    <th id="usedby"><?php _e('Username', 'learndash-content-locker'); ?></th>
                    <th id="created_at"><?php _e('Created', 'learndash-content-locker'); ?></th>
                    <th>#</th>
                </tr>
            </thead>
            <tbody class="rtlready">
                <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'ldcl_serials';
    $serials = $wpdb->get_results("SELECT * FROM $table_name");

    if (!empty($serials)) {
        foreach ($serials as $serial) {
            $course_or_lesson_title = get_the_title($serial->course_or_lesson_id);
            $used_by_user_id = $serial->used_by_user_id;
            $used_by_user_link = 'N/A';
            
            if ($used_by_user_id) {
                $used_by_user = get_userdata($used_by_user_id);
                if ($used_by_user) {
                    $used_by_user_link = '<a href="' . esc_url(get_edit_user_link($used_by_user_id)) . '">' . esc_html($used_by_user->user_login) . '</a>';
                }
            }
                        // Convert the created_at to a date format
                        $created_at_date = date('Y-m-d', strtotime($serial->created_at));

        ?>
                <tr>
                    <td id="serial-<?php echo esc_attr($serial->id); ?>">
                        <?php echo esc_html($serial->serial); ?></td>
                    <td><?php echo esc_html($serial->serial_title); ?></td>
                    <td><?php echo esc_html($course_or_lesson_title); ?></td>
                    <td><?php echo esc_html($serial->validity_date); ?></td>
                    <td><?php echo esc_html($serial->used ? 'Yes' : 'No'); ?></td>
                    <td><?php echo $used_by_user_link; ?></td>
                    <td><?php echo esc_html($created_at_date); ?></td>
                    <td>
                        <button type="button" class="custombtn edit-serial-btn"
                            title="<?php echo esc_attr__('Edit', 'learndash-content-locker'); ?>"
                            data-serial-id="<?php echo esc_attr($serial->id); ?>">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button type="button" class="custombtn delete-serial-btn"
                            title="<?php echo esc_attr__('Delete', 'learndash-content-locker'); ?>"
                            data-serial-id="<?php echo esc_attr($serial->id); ?>">
                            <i class="fa fa-trash"></i>
                        </button>
                        <button type="button" class="custombtn copy-serial-btn"
                            title="<?php echo esc_attr__('Copy', 'learndash-content-locker'); ?>"
                            data-serial-id="<?php echo esc_attr($serial->id); ?>">
                            <i class="fa fa-copy"></i>
                        </button>
                        <button type="button" class="custombtn barcode-serial-btn"
                            title="<?php echo esc_attr__('Barcode', 'learndash-content-locker'); ?>"
                            data-serial-id="<?php echo esc_attr($serial->id); ?>">
                            <i class="fa fa-barcode"></i>
                        </button>
                    </td>
                </tr>
                <?php
        }
    } else {
        ?>
                <tr>
                    <td colspan="8"><?php _e('No serials found', 'learndash-content-locker'); ?></td>
                </tr>
                <?php
    }
    ?>
            </tbody>
        </table>
    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('serialSearchInput');
    const table = document.getElementById('serialsTable');
    const rows = table.getElementsByTagName('tr');

    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this,
                args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    const performSearch = debounce(function() {
        const searchTerm = searchInput.value.toLowerCase();

        for (let i = 1; i < rows.length; i++) {
            let rowMatch = false;
            const cells = rows[i].getElementsByTagName('td');

            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().indexOf(searchTerm) > -1) {
                    rowMatch = true;
                    break;
                }
            }

            rows[i].style.display = rowMatch ? '' : 'none';
        }
    }, 300); // 300ms debounce

    searchInput.addEventListener('input', performSearch);
});
</script>
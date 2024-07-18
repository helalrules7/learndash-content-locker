<?php
require_once LDCL_PLUGIN_DIR . 'includes/class-ldcl-serial.php';
require_once LDCL_PLUGIN_DIR . 'admin/class-ldcl-admin.php';
require_once LDCL_PLUGIN_DIR . 'public/class-ldcl-public.php';
?>
<div class="wrap ldcl-admin-page">
    <div>
        <h2 style="margin: 0 0 0 5px;"><?php _e('Create a new serial code', 'learndash-content-locker'); ?></h2>
    </div>
    <?php if ( isset( $_GET['success'] ) ) : ?>
    <div class="notice notice-success">
        <p><?php _e('New serial code created successfully!', 'learndash-content-locker'); ?></p>
    </div>
    <?php endif; ?>
    <hr>

    <form method="post" class="courses_serial" action="<?php echo admin_url( 'admin-post.php' ); ?>">

        <?php wp_nonce_field( 'ldcl_save_course_serial', 'ldcl_course_serial_nonce' ); ?>
        <input type="hidden" name="action" value="ldcl_save_course_serial">

        <label for="serial_title"><?php _e('Serial Code Title', 'learndash-content-locker'); ?></label>
        <input type="text" id="serial_title" name="serial_title" required
            placeholder="<?php echo esc_attr__('Use username or phone for easy search', 'learndash-content-locker'); ?>">
        <label for="generated_serial"><?php _e('Serial Code', 'learndash-content-locker'); ?></label>
        <input type="text" id="generated_serial" name="generated_serial" readonly>
        <div class="button-container">
            <div class="dropdown-content">
                <button type="button" id="generate_serial"><i
                        class="fa fa-unlock">&nbsp;&nbsp;</i><?php _e('Generate Code', 'learndash-content-locker'); ?></button>
                <button type="button" id="copy_serial"><i
                        class="fa fa-copy">&nbsp;&nbsp;</i><?php _e('Clipboard copy', 'learndash-content-locker'); ?></button>
            </div>
        </div>

        <label for="course_select"><?php _e('Related Course:', 'learndash-content-locker'); ?></label>
        <select id="course_select" name="course_select" required>
            <option value=""><?php _e('Select Course', 'learndash-content-locker'); ?></option>
            <?php if ( ! empty( $courses ) ) : ?>
            <?php foreach ( $courses as $course_id => $course_title ) : ?>
            <option value="<?php echo esc_attr( $course_id ); ?>"><?php echo esc_html( $course_title ); ?></option>
            <?php endforeach; ?>
            <?php else : ?>
            <option value=""><?php _e('No Courses found', 'learndash-content-locker'); ?></option>
            <?php endif; ?>
        </select>
        <label for="validity_date"><?php _e('Validity Date', 'learndash-content-locker'); ?></label>
        <input type="date" id="validity_date" name="validity_date" required>
        <span id="days_left"></span>
        <hr>

        <div class="button-container">
            <div class="dropdown-content">
                <button type="submit"><i class="fa fa-save">&nbsp;&nbsp;</i>
                    <?php _e('Save Code', 'learndash-content-locker'); ?></button>
            </div>
        </div>

    </form>
</div>
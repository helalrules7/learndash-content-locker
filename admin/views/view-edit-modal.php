<!-- Edit Serial Modal -->
<?php
require_once LDCL_PLUGIN_DIR . 'includes/class-ldcl-serial.php';
require_once LDCL_PLUGIN_DIR . 'admin/class-ldcl-admin.php';
require_once LDCL_PLUGIN_DIR . 'public/class-ldcl-public.php';
?>
<!-- Edit Serial Modal -->
<div class="modal fade" id="editSerialModal" tabindex="-1" role="dialog" aria-labelledby="editSerialModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSerialModalLabel">
                    <?php _e('Edit Serial', 'learndash-content-locker'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="updateSerialForm">
                    <input type="hidden" id="serial_id" name="serial_id">
                    <div class="form-group">
                        <label for="serial_title"><?php _e('Serial Title', 'learndash-content-locker'); ?></label>
                        <input type="text" class="form-control" id="serial_title" name="serial_title" required
                            placeholder="<?php echo esc_attr__('Use username or phone for easy search', 'learndash-content-locker'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="serial_code"><?php _e('Serial', 'learndash-content-locker'); ?></label>
                        <input readonly type="text" class="form-control" id="serial_code" name="serial_code" required>
                    </div>
                    <div class="form-group">
                        <label for="validity_date"><?php _e('Validity Date', 'learndash-content-locker'); ?></label>
                        <input type="date" class="form-control" id="validity_date" name="validity_date" required>
                    </div>
                    <div class="form-group">
                        <label hidden
                            for="course_or_lesson_id"><?php _e('Related Course', 'learndash-content-locker'); ?></label>
                        <input readonly type="hidden" class="form-control" id="course_or_lesson_id"
                            name="course_or_lesson_id" required>
                    </div>
                    <div class="form-group">
                        <label
                            for="course_or_lesson_title"><?php _e('Related Course', 'learndash-content-locker'); ?></label>
                        <input readonly type="text" class="form-control" id="course_or_lesson_title"
                            name="course_or_lesson_title" required>
                    </div>
                    <button type="submit"
                        class="btn btn-primary"><?php _e('Update', 'learndash-content-locker'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
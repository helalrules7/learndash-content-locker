<!-- Edit Serial Modal -->
<?php
require_once LDCL_PLUGIN_DIR . 'includes/class-ldcl-serial.php';
require_once LDCL_PLUGIN_DIR . 'admin/class-ldcl-admin.php';
require_once LDCL_PLUGIN_DIR . 'public/class-ldcl-public.php';
?>
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

<script>
// JavaScript to handle modal interactions
(function($) {
    $(document).ready(function() {
        // Disable the update button initially
        $('#updateSerialForm button[type="submit"]').prop('disabled', true);

        // Watch for changes in input fields
        $('#serial_title').on('input', function() {
            // Enable the update button if the serial title changes
            $('#updateSerialForm button[type="submit"]').prop('disabled', false);
        });

        // Watch for changes in the validity date field
        $('#validity_date').on('change', function() {
            var validityDate = $(this).val();

            // Check if validity date is in the future
            var today = new Date();
            var selectedDate = new Date(validityDate);
            if (selectedDate < today) {
                // Show error message
                alert('Validity date must be today or later.');
                $('#updateSerialForm button[type="submit"]').prop('disabled',
                    true); // Disable update button
            } else {
                // Enable the update button if the date is valid
                $('#updateSerialForm button[type="submit"]').prop('disabled', false);
            }
        });

        // Handle form submission for updating serial data.
        $('#updateSerialForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission.

            // Gather updated data from form fields.
            var serialId = $('#serial_id').val();
            var serialTitle = $('#serial_title').val();
            var validityDate = $('#validity_date').val();

            // Make an AJAX request to update the serial data.
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'ldcl_update_serial_data',
                    serial_id: serialId,
                    serial_title: serialTitle,
                    validity_date: validityDate
                },
                success: function(response) {
                    // Handle the success response if needed.
                    console.log('Serial updated successfully:', response);

                    // Optionally, you can close the modal or update UI based on success.
                    $('#editSerialModal').modal('hide');
                    location.reload(); // Reload the page
                },
                error: function(xhr, status, error) {
                    // Handle the error.
                    console.error('AJAX Error:', status, error);
                }
            });
        });
    });
})(jQuery);
</script>
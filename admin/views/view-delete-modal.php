<!-- Delete Serial Modal -->
<?php
require_once LDCL_PLUGIN_DIR . 'includes/class-ldcl-serial.php';
require_once LDCL_PLUGIN_DIR . 'admin/class-ldcl-admin.php';
require_once LDCL_PLUGIN_DIR . 'public/class-ldcl-public.php';
?>

<div class="modal fade" id="deleteSerialModal" tabindex="-1" role="dialog" aria-labelledby="deleteSerialModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSerialModalLabel">
                    <?php _e('Delete Serial', 'learndash-content-locker'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="deleteSerialForm">
                    <input type="hidden" id="serial_id" name="serial_id">
                    <div class="form-group">
                        <label
                            for="deleteConfirmation"><?php _e('Are you sure you want to delete serial ', 'learndash-content-locker'); ?>
                            "<span style="color:dodgerblue; font-weight: bold;"
                                id="serial_title_title"></span>"<?php _e('? <br> This will also delete the user access to the serial', 'learndash-content-locker'); ?></label>
                    </div>
                    <hr>
                    <button type="submit" id="confirmDeleteBtn"
                        class="btn btn-danger"><?php _e('Confirm', 'learndash-content-locker'); ?></button>
                    <button type="button" class="btn btn-default"
                        data-dismiss="modal"><?php _e('Cancel', 'learndash-content-locker'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function($) {
    $(document).ready(function() {
        // Handle delete button click event.
        $('#confirmDeleteBtn').on('click', function() {
            var serialId = $('#serial_id').val();
            var nonce = '<?php echo wp_create_nonce( 'ldcl_delete_serial_nonce' ); ?>';

            // Make an AJAX request to delete the serial.
            $.ajax({
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                type: 'POST',
                data: {
                    action: 'ldcl_delete_serial',
                    serial_id: serialId,
                    nonce: nonce
                },
                success: function(response) {
                    console.log('Serial deleted successfully:', response);

                    // Optionally, update UI or inform user.
                    location.reload();
                    // Hide the modal after deletion.
                    $('#deleteSerialModal').modal('hide');

                    // Optionally, update the UI to reflect deletion without reloading the page.
                    // Remove the deleted row from the table dynamically.
                    $('#serial_row_' + serialId).remove();

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
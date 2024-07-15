<?php
// Include the modal
require_once LDCL_PLUGIN_DIR . 'admin/views/view-edit-modal.php';
require_once LDCL_PLUGIN_DIR . 'admin/views/view-delete-modal.php';
require_once LDCL_PLUGIN_DIR . 'admin/views/view-barcode-modal.php';

// Include WordPress functions
require_once ABSPATH . 'wp-load.php';
?>

<h2>Generated Serials</h2>
<hr>
<a href="<?php echo admin_url('admin.php?page=ldcl-create-course-serial'); ?>"><button class="ldcl_btn"><i
            class="fa fa-graduation-cap"></i> Create Course Serial</button></a>
<hr>
<table id="serialsTable" class="widefat fixed" cellspacing="0">
    <thead>
        <tr>
            <th id="serial">Serial</th>
            <th id="title">Title</th>
            <th id="course_or_lesson">Course</th>
            <th id="validity">Validity</th>
            <th id="used">Used</th>
            <th id="usedby">Used By</th>
            <th id="created_at">Created At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
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
            <td id="serial-<?php echo esc_attr($serial->id); ?>"><?php echo esc_html($serial->serial); ?></td>
            <td><?php echo esc_html($serial->serial_title); ?></td>
            <td><?php echo esc_html($course_or_lesson_title); ?></td>
            <td><?php echo esc_html($serial->validity_date); ?></td>
            <td><?php echo esc_html($serial->used ? 'Yes' : 'No'); ?></td>
            <td><?php echo $used_by_user_link; ?></td>
            <td><?php echo esc_html($created_at_date); ?></td>
            <td>
                <button type="button" class="custombtn edit-serial-btn" title="Edit Serial"
                    data-serial-id="<?php echo esc_attr($serial->id); ?>">
                    <i class="fa fa-edit"></i>
                </button>
                <button type="button" class="custombtn delete-serial-btn" title="Delete Serial"
                    data-serial-id="<?php echo esc_attr($serial->id); ?>">
                    <i class="fa fa-trash"></i>
                </button>
                <button type="button" class="custombtn copy-serial-btn" title="Copy to clipboard"
                    data-serial-id="<?php echo esc_attr($serial->id); ?>">
                    <i class="fa fa-copy"></i>
                </button>
                <button type="button" class="custombtn barcode-serial-btn" title="Generate Barcode"
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
            <td colspan="8">No serials found.</td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>


<script>
(function($) {
    $(document).ready(function() {
        const barcodeCanvas = document.getElementById('barcodeCanvas');

        $("#serialsTable").tablesorter({
            headers: {
                7: {
                    sorter: false
                }
            }
        });
        // Handle edit button click event.
        $('.barcode-serial-btn').on('click', function() {
            var serialId = $(this).data('serial-id');

            // Make an AJAX request to fetch the serial data based on the serial ID.
            $.ajax({
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                type: 'POST',
                data: {
                    action: 'ldcl_get_serial_data',
                    serial_id: serialId
                },
                success: function(response) {
                    var serialData = response.data;
                    $('#serialTextP').text(serialData.serial);
                    var serialText = serialData.serial;
                    bwipjs.toCanvas(barcodeCanvas, {
                        bcid: 'code128', // Barcode type
                        text: serialText, // Serial text
                        scale: 4, // Scale factor
                        includetext: true, // Include the text in the barcode
                    }, function(err, cvs) {
                        if (err) {
                            console.error('BWIP-JS Error:', err);
                        } else {
                            console.log('Barcode generated successfully');
                            // Access the canvas context
                            var ctx = cvs.getContext('2d');

                            // Set text properties
                            ctx.font = '30px Arial';
                            ctx.fillStyle = '#000';
                            ctx.textAlign = 'center';

                            // Calculate text position
                            var textX = cvs.width / 2;
                            var textY = cvs.height - 10; // Adjust as needed

                            // Display the serial text in the canvas
                            ctx.fillText(serialText, textX, textY);
                        }
                    });
                    $('#barcodeModal').modal('show');
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


<script>
(function($) {
    $(document).ready(function() {
        // Handle Serial button click event.
        $('.delete-serial-btn').on('click', function() {
            var serialId = $(this).data('serial-id');
            console.log(serialId);

            // Make an AJAX request to fetch the serial data based on the serial ID.
            $.ajax({
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                type: 'POST',
                data: {
                    action: 'ldcl_get_serial_data',
                    serial_id: serialId
                },
                success: function(response) {
                    var serialData = response.data;
                    console.log(serialData);

                    // Populate the modal form fields with the serial data.
                    $('#serial_id').val(serialData.id);
                    $('#serial_title_title').text(serialData
                        .serial_title); // Update text content

                    // Show the delete modal
                    $('#deleteSerialModal').modal('show');
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyButtons = document.querySelectorAll('.copy-serial-btn');

    // Handle copy serial button click
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const serialId = this.getAttribute('data-serial-id');
            const serialTd = document.getElementById('serial-' + serialId);

            if (serialTd) {
                const serialText = serialTd.textContent.trim();

                const tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = serialText;
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);

                // Create tooltip
                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip');
                tooltip.textContent = 'Serial copied to clipboard!';
                document.body.appendChild(tooltip);

                // Remove the tooltip after 3 seconds
                setTimeout(function() {
                    tooltip.remove();
                }, 3000);
            } else {
                console.error('Serial Id Not Found:', 'serial-' + serialId);
            }
        });
    });

    // Handle barcode button click

});

(function($) {
    // Handle form submission for updating serial data.
    $('#updateSerialForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission.

        // Gather updated data from form fields.
        var serialId = $('#serial_id').val();
        var serialTitle = $('#serial_title').val();
        var validityDate = $('#validity_date').val();

        // Make an AJAX request to update the serial data.
        $.ajax({
            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
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

                // Reload the page to reflect the updated data.
                location.reload();
            },
            error: function(xhr, status, error) {
                // Handle the error.
                console.error('AJAX Error:', status, error);
            }
        });
    });
});
(jQuery);

(function($) {
    $(document).ready(function() {
        // Handle edit button click event.
        $('.edit-serial-btn').on('click', function() {
            var serialId = $(this).data('serial-id');
            // Make an AJAX request to fetch the serial data based on the serial ID.
            $.ajax({
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                type: 'POST',
                data: {
                    action: 'ldcl_get_serial_data',
                    serial_id: serialId
                },
                success: function(response) {
                    var serialData = response.data;

                    // Populate the modal form fields with the serial data.
                    $('#serial_id').val(serialData.id);
                    $('#serial_title').val(serialData.serial_title);
                    $('#serial_code').val(serialData.serial);
                    $('#validity_date').val(serialData.validity_date);
                    $('#course_or_lesson_id').val(serialData.course_or_lesson_id);

                    // Fetch course or lesson details based on serialData.course_or_lesson_id
                    $.ajax({
                        url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                        type: 'POST',
                        data: {
                            action: 'ldcl_get_course_or_lesson_title',
                            id: serialData.course_or_lesson_id
                        },
                        success: function(titleResponse) {
                            // Populate the course/lesson title in the form
                            $('#course_or_lesson_title').val(
                                titleResponse
                                .data.title);

                            // Show the modal only after the title is populated.
                            $('#editSerialModal').modal('show');
                        },
                        error: function(xhr, status, error) {
                            // Handle the error.
                            console.error('AJAX Error:', status, error);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    // Handle the error.
                    console.error('AJAX Error:', status, error);
                }
            });
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
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
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

                    // Reload the page to reflect the updated data.
                    location.reload();
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
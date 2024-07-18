(function ($) {
    $(document).ready(function () {
        $(document).on('click', '.edit-serial-btn', function () {
            var serialId = $(this).data('serial-id');
            $.ajax({
                url: ldclAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ldcl_get_serial_data',
                    serial_id: serialId
                },
                success: function (response) {
                    if (response.success) {
                        var serialData = response.data;
                        $('#serial_id').val(serialData.id);
                        $('#serial_title').val(serialData.serial_title);
                        $('#serial_code').val(serialData.serial);
                        $('#validity_date').val(serialData.validity_date);
                        $('#course_or_lesson_id').val(serialData.course_or_lesson_id);

                        $.ajax({
                            url: ldclAjax.ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'ldcl_get_course_or_lesson_title',
                                id: serialData.course_or_lesson_id
                            },
                            success: function (titleResponse) {
                                if (titleResponse.success) {
                                    $('#course_or_lesson_title').val(titleResponse.data.title);
                                    $('#editSerialModal').modal('show');
                                } else {
                                    console.error('Error fetching course/lesson title:', titleResponse.data.message);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('AJAX Error:', status, error);
                            }
                        });
                    } else {
                        console.error('Error fetching serial data:', response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        });

        $('#updateSerialForm').on('submit', function (e) {
            e.preventDefault();
            var serialId = $('#serial_id').val();
            var serialTitle = $('#serial_title').val();
            var validityDate = $('#validity_date').val();

            $.ajax({
                url: ldclAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ldcl_update_serial_data',
                    serial_id: serialId,
                    serial_title: serialTitle,
                    validity_date: validityDate,
                    nonce: ldclAjax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        console.log('Serial updated successfully:', response.data);
                        $('#editSerialModal').modal('hide');
                        location.reload();
                    } else {
                        console.error('Error updating serial:', response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        });
    });
})(jQuery);
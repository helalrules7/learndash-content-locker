(function ($) {
    $(document).ready(function () {
        $.ajax({
            url: ldclAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_delete_nonce',
                _: new Date().getTime() // Add a timestamp to prevent caching
            },
            success: function (response) {
                if (response.success) {
                    ldclAjax.nonce = response.data.nonce;
                }
            }
        });

        $(document).on('click', '.delete-serial-btn', function () {
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
                        $('#serial_title_title').text(serialData.serial_title);
                        $('#deleteSerialModal').modal('show');
                    } else {
                        console.error('Error fetching serial data:', response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        });


        $('#confirmDeleteBtn').on('click', function (e) {
            e.preventDefault();
            var serialId = $('#serial_id').val();
            $.ajax({
                url: ldclAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ldcl_delete_serial',
                    serial_id: serialId,
                    nonce: ldclAjax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $('#deleteSerialModal').modal('hide');
                        location.reload();
                    } else {
                        console.error('Error deleting serial:', response.data.message);
                        alert('Error deleting serial: ' + response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Error deleting serial. Please try again.');
                }
            });
        });
    });
})(jQuery);
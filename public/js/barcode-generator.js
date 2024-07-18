(function ($) {
    $(document).ready(function () {
        const barcodeCanvas = document.getElementById('barcodeCanvas');

        $("#serialsTable").tablesorter({
            headers: {
                7: {
                    sorter: false
                }
            }
        });

        $('.barcode-serial-btn').on('click', function () {
            var serialId = $(this).data('serial-id');

            $.ajax({
                url: ldclAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ldcl_get_serial_data',
                    serial_id: serialId
                },
                success: function (response) {
                    var serialData = response.data;
                    $('#serialTextP').text(serialData.serial);
                    var serialText = serialData.serial;
                    bwipjs.toCanvas(barcodeCanvas, {
                        bcid: 'code128',
                        text: serialText,
                        scale: 4,
                        includetext: true,
                    }, function (err, cvs) {
                        if (err) {
                            console.error('BWIP-JS Error:', err);
                        } else {
                            console.log('Barcode generated successfully');
                            var ctx = cvs.getContext('2d');
                            ctx.font = '30px Arial';
                            ctx.fillStyle = '#000';
                            ctx.textAlign = 'center';
                            var textX = cvs.width / 2;
                            var textY = cvs.height - 10;
                            ctx.fillText(serialText, textX, textY);
                        }
                    });
                    $('#barcodeModal').modal('show');
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        });
    });
})(jQuery);
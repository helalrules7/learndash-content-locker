jQuery(document).ready(function ($) {
    $('#exportSerialsBtn').on('click', function (e) {
        e.preventDefault();
        var nonce = ldcl_admin.nonce;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'export_serials_to_excel',
                nonce: nonce
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (response) {
                var blob = new Blob([response], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'serials_list.xlsx';
                link.click();
            },
            error: function (xhr, status, error) {
                console.error('Error exporting serials:', error);
            }
        });
    });
});
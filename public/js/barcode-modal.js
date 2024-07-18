// public/js/barcode-modal.js
(function ($) {
    $(document).ready(function () {
        function setupDownloadButton() {
            const downloadBarcodeBtn = document.getElementById('downloadBarcodeBtn');
            if (downloadBarcodeBtn) {
                downloadBarcodeBtn.addEventListener('click', function () {
                    const serialText = $('#serialTextP').text();
                    console.log('Serial Text:', serialText);

                    const canvas = document.getElementById('barcodeCanvas');
                    if (canvas) {
                        const url = canvas.toDataURL('image/png');
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = serialText + '.png';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    } else {
                        console.error('Barcode canvas not found');
                    }
                });
            } else {
                console.error('Download button not found');
            }
        }

        $('#barcodeModal').on('shown.bs.modal', function () {
            setupDownloadButton();
        });
    });
})(jQuery);
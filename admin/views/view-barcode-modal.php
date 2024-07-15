<div id="barcodeModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Serial Barcode</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modal_body">
                <canvas id="barcodeCanvas"></canvas>
            </div>
            <p id="serialTextP" hidden></p>
            <div class="modal-buttons">
                <hr>

                <button id="downloadBarcodeBtn" class="custombtn">Download barcode</button>
                <button id="copySerialTextBtn" class="custombtn">Copy to clipboard</button>
            </div>
        </div>
    </div>

</div>

<script>
(function($) {
    $(document).ready(function() {
        // Correctly retrieve serial text
        const serialText = $('#serialTextP').text();

        // Download barcode image button
        const downloadBarcodeBtn = document.getElementById('downloadBarcodeBtn');
        if (downloadBarcodeBtn) {
            downloadBarcodeBtn.addEventListener('click', function() {
                console.log(serialText);
                const canvas = document.getElementById('barcodeCanvas');
                const url = canvas.toDataURL('image/png');
                const a = document.createElement('a');
                a.href = url;
                a.download = serialText + '.png'; // Fixed filename for download
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        }

        // Copy serial text button
        const copySerialTextBtn = document.getElementById('copySerialTextBtn');
        if (copySerialTextBtn) {
            copySerialTextBtn.addEventListener('click', function() {
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
            });
        }
    });
})(jQuery);
</script>
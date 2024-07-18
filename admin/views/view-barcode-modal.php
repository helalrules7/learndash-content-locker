<?php
require_once LDCL_PLUGIN_DIR . 'includes/class-ldcl-serial.php';
require_once LDCL_PLUGIN_DIR . 'admin/class-ldcl-admin.php';
require_once LDCL_PLUGIN_DIR . 'public/class-ldcl-public.php';
?>
<div id="barcodeModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?php _e('Serial Barcode', 'learndash-content-locker'); ?></h5>
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
                <button id="downloadBarcodeBtn" class="custombtn">
                    <?php _e('Download barcode', 'learndash-content-locker'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php

class LDCL_Public {
    public function __construct() {
        add_shortcode( 'ldcl_lock_page', array( $this, 'lock_page_shortcode' ) );
    }

    public function lock_page_shortcode() {
        ob_start();
        ?>
<form id="ldcl-lock-form">
    <input type="text" name="ldcl_serial" placeholder="xxxx-xxxx-xxxx" required>
    <button type="submit">Unlock</button>
</form>
<?php
        return ob_get_clean();
    }
}
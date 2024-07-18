<?php

class LDCL_Public {
    /**
 * Initializes the LDCL_Public class and adds the shortcode for locking pages.
 *
 * @since 1.0.0
 */
public function __construct() {
    add_shortcode( 'ldcl_lock_page', array( $this, 'lock_page_shortcode' ) );
}

    /**
 * Outputs the lock page shortcode form.
 *
 * This function is used as a shortcode to display a form for users to enter a serial number to unlock a locked page.
 * The form is rendered using PHP's output buffering to capture the HTML content and return it as a string.
 *
 * @since 1.0.0
 *
 * @return string The HTML content of the lock page shortcode form.
 */
public function lock_page_shortcode() {
    ob_start();
    ?>
<form id="ldcl-lock-form">
    <input type="text" name="ldcl_serial" placeholder="xxxx-xxxx" required>
    <button type="submit">تأكيد الكود</button>
</form>
<?php
    return ob_get_clean();
}
}
<h2>Edit Serial</h2>
<form method="post" class="edit_ldcl"
    action="<?php echo esc_url(admin_url('admin-post.php?action=ldcl_edit_serial&id=' . $serial_id)); ?>">
    <?php wp_nonce_field('ldcl_edit_serial', 'ldcl_edit_serial_nonce'); ?>
    <input type="hidden" name="action" value="ldcl_edit_serial">

    <label for="serial_title">Serial Title:</label>
    <input type="text" id="serial_title" name="serial_title"
        value="<?php echo isset($serial) ? esc_attr($serial->serial_title) : ''; ?>" required>

    <label for="validity_date">Validity Date:</label>
    <input type="date" id="validity_date" name="validity_date"
        value="<?php echo isset($serial) ? esc_attr($serial->validity_date) : ''; ?>" required>

    <label for="course_or_lesson">Associated Course or Lesson:</label>
    <input type="text" id="course_or_lesson" name="course_or_lesson"
        value="<?php echo isset($course_or_lesson_type) ? esc_attr($course_or_lesson_type->post_title) : ''; ?>"
        readonly>

    <button type="submit" name="ldcl_edit_serial_submit">Update Serial</button>
</form>
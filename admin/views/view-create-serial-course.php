    <div style="display: flex; align-items: center;">
        <a href="admin.php?page=ldcl-serials" style="text-decoration: none; color: dodgerblue; margin: 0;">
            <h2 style="color:dodgerblue">LDCLSerials</h2>
        </a>
        &nbsp;»
        <h2 style="margin: 0 0 0 5px;">Create Course Serial</h2>
    </div>
    <hr>
    <?php if ( isset( $_GET['success'] ) ) : ?>
    <div class="notice notice-success">
        <p>Course Serial saved successfully!</p>
    </div>
    <?php endif; ?>
    <form method="post" class="courses_serial" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <?php wp_nonce_field( 'ldcl_save_course_serial', 'ldcl_course_serial_nonce' ); ?>
        <input type="hidden" name="action" value="ldcl_save_course_serial">

        <label for="serial_title">Serial Title:</label>
        <input type="text" id="serial_title" name="serial_title" required>
        <label for="generated_serial">Generated Serial:</label>
        <input type="text" id="generated_serial" name="generated_serial" readonly>
        <div class="button-container">
            <div class="dropdown-content">
                <button type="button" id="generate_serial"><i class="fa fa-unlock">&nbsp;&nbsp;</i>Generate
                    Serial</button>
                <button type="button" id="copy_serial"><i class="fa fa-copy">&nbsp;&nbsp;</i>Copy Serial</button>
            </div>
        </div>

        <label for="course_select">Select Course:</label>
        <select id="course_select" name="course_select" required>
            <option value="">Select a course</option>
            <?php if ( ! empty( $courses ) ) : ?>
            <?php foreach ( $courses as $course_id => $course_title ) : ?>
            <option value="<?php echo esc_attr( $course_id ); ?>"><?php echo esc_html( $course_title ); ?></option>
            <?php endforeach; ?>
            <?php else : ?>
            <option value="">No courses available</option>
            <?php endif; ?>
        </select>
        <label for="validity_date">Validity Date:</label>
        <input type="date" id="validity_date" name="validity_date" required>
        <span id="days_left"></span>
        <hr>

        <div class="button-container">
            <div class="dropdown-content">
                <button type="submit"><i class="fa fa-save">&nbsp;&nbsp;</i> Save</button>
            </div>
        </div>

    </form>
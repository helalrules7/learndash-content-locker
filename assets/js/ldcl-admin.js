jQuery(document).ready(function ($) {

    $('a[href="admin.php?page=ldcl-edit-serial"]').addClass('custom-editmenu-item-class');

    $('#generate_serial').click(function () {
        var serial = '';
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for (var i = 0; i < 12; i++) {
            serial += chars.charAt(Math.floor(Math.random() * chars.length));
            if ((i + 1) % 4 === 0 && i < 11) {
                serial += '-';
            }
        }
        $('#generated_serial').val(serial);
    });

    $('#validity_date').change(function () {
        var selectedDate = new Date($(this).val());
        var today = new Date();
        var timeDiff = selectedDate.getTime() - today.getTime();
        var daysLeft = Math.ceil(timeDiff / (1000 * 3600 * 24));
        $('#days_left').text(daysLeft + ' days left');
    });
});
document.getElementById('generate_serial').addEventListener('click', function () {
    // Generate a random serial and update the input field
    var generatedSerial = Math.random().toString(36).substr(2, 9).toUpperCase();
    document.getElementById('generated_serial').value = generatedSerial;
});

document.getElementById('copy_serial').addEventListener('click', function () {
    // Copy the generated serial to the clipboard
    var generatedSerial = document.getElementById('generated_serial').value;
    if (generatedSerial === '') {
        // Display the tooltip
        var danger_tooltip = document.createElement('div');
        danger_tooltip.classList.add('danger_tooltip');
        danger_tooltip.textContent = 'Click on Generate Serial First !';
        document.body.appendChild(danger_tooltip);

        // Remove the tooltip after 3 seconds
        setTimeout(function () {
            danger_tooltip.remove();
        }, 3000);

        return; // Exit the function if no serial is generated
    }

    var copyText = document.getElementById('generated_serial');
    copyText.select();
    document.execCommand("copy");
    // Display the tooltip
    var tooltip = document.createElement('div');
    tooltip.classList.add('tooltip');
    tooltip.textContent = 'Serial copied to clipboard!';
    document.body.appendChild(tooltip);

    // Remove the tooltip after 3 seconds
    setTimeout(function () {
        tooltip.remove();
    }, 3000);

});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.courses_serial');
    const generatedSerialInput = document.getElementById('generated_serial');
    const courseSelect = document.getElementById('course_select');
    const validityDateInput = document.getElementById('validity_date');
    const daysLeftSpan = document.getElementById('days_left');


    form.addEventListener('submit', function (event) {
        if (courseSelect.value === '' || courseSelect.value === 'No courses available' || generatedSerialInput.value === '') {
            event.preventDefault();
            alert('Please select a course and generate a serial before submitting.');
        }
    });

    form.addEventListener('submit', function (event) {
        const validityDate = new Date(validityDateInput.value);
        const currentDate = new Date();
        const timeDiff = validityDate - currentDate;
        const daysLeft = Math.floor(timeDiff / (1000 * 3600 * 24));

        if (daysLeft <= 0) {
            event.preventDefault();
            alert('Validity date must be in the future.');
        }
    });

});




document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('#ldcl-serial-form input[type="text"]');

    inputs.forEach((input, index) => {
        input.addEventListener('input', (event) => {
            if (input.value.length === 4 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
    });
});

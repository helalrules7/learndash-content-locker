document.addEventListener('DOMContentLoaded', function () {
    const copyButtons = document.querySelectorAll('.copy-serial-btn');

    copyButtons.forEach(button => {
        button.addEventListener('click', function () {
            const serialId = this.getAttribute('data-serial-id');
            const serialTd = document.getElementById('serial-' + serialId);

            if (serialTd) {
                const serialText = serialTd.textContent.trim();

                const tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = serialText;
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);

                var tooltip = document.createElement('div');
                tooltip.classList.add('tooltip');
                tooltip.textContent = ldclTranslations.serialCopied;
                document.body.appendChild(tooltip);

                setTimeout(function () {
                    tooltip.remove();
                }, 3000);
            } else {
                console.error('Serial Id Not Found:', 'serial-' + serialId);
            }
        });
    });
});
// reverse-copy.js
(function () {
    function copyToClipboard(text) {
        const tempInput = document.createElement('input');
        document.body.appendChild(tempInput);
        tempInput.value = text;
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showTooltip((window.ldclTranslations && window.ldclTranslations.serialReverseCopied) || 'Reversed Serial copied to clipboard!');
    }

    function showTooltip(message) {
        const tooltip = document.createElement('div');
        tooltip.classList.add('tooltip');
        tooltip.textContent = message;
        document.body.appendChild(tooltip);

        setTimeout(function () {
            tooltip.remove();
        }, 3000);
    }

    function getSerialText(serialId) {
        const serialElement = document.getElementById('serial-' + serialId);
        return serialElement ? serialElement.textContent.trim() : null;
    }

    function reverseCopy(event) {
        const button = event.currentTarget;
        const serialId = button.getAttribute('data-serial-id');
        const serialText = getSerialText(serialId);

        if (serialText) {
            const reversedText = serialText.split('-').reverse().join('-');
            copyToClipboard(reversedText);
        } else {
            console.error('Serial text not found for ID:', serialId);
        }
    }

    function initReverseCopy() {
        const reverseCopyButtons = document.querySelectorAll('.reverse-copy-serial-btn');
        reverseCopyButtons.forEach(button => {
            button.addEventListener('click', reverseCopy);
        });
    }

    // Initialize when the DOM is fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReverseCopy);
    } else {
        initReverseCopy();
    }
})();
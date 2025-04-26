document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const submitButton = document.getElementById('updateButton');
    const initialValues = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        phoneNumber: document.getElementById('phoneNumber').value,
        typeOfContact: document.getElementById('typeOfContact').value
    };

    // Disable submit button initially
    submitButton.disabled = true;

    // Function to check if any field has changed
    function checkForChanges() {
        const currentValues = {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            phoneNumber: document.getElementById('phoneNumber').value,
            typeOfContact: document.getElementById('typeOfContact').value
        };

        const hasChanges = Object.keys(initialValues).some(key =>
            initialValues[key] !== currentValues[key]
        );

        submitButton.disabled = !hasChanges;
    }

    // Add input event listeners to all form fields
    form.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', checkForChanges);
        field.addEventListener('change', checkForChanges);
    });

    // Form validation
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
}); 
document.addEventListener('DOMContentLoaded', function () {
    // Form elements
    const form = document.getElementById('addContactForm');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const phoneInput = document.getElementById('phone');
    const contactTypeInput = document.getElementById('contactType');

    // Validation patterns
    const namePattern = /^[A-Z][a-zA-Z]*$/;
    const phonePattern = /^\+?[0-9]{10,15}$/;

    // Function to validate name
    function validateName(name) {
        return namePattern.test(name);
    }

    // Function to validate phone number
    function validatePhone(phone) {
        return phonePattern.test(phone);
    }

    // Function to show error message
    function showError(input, message) {
        const formGroup = input.closest('.mb-3');
        const errorElement = formGroup.querySelector('.invalid-feedback');
        input.classList.add('is-invalid');
        errorElement.textContent = message;
    }

    // Function to clear error message
    function clearError(input) {
        const formGroup = input.closest('.mb-3');
        const errorElement = formGroup.querySelector('.invalid-feedback');
        input.classList.remove('is-invalid');
        errorElement.textContent = '';
    }

    // Event listeners for real-time validation
    firstNameInput.addEventListener('input', function () {
        if (!validateName(this.value)) {
            showError(this, 'First name must start with a capital letter and contain only letters');
        } else {
            clearError(this);
        }
    });

    lastNameInput.addEventListener('input', function () {
        if (!validateName(this.value)) {
            showError(this, 'Last name must start with a capital letter and contain only letters');
        } else {
            clearError(this);
        }
    });

    phoneInput.addEventListener('input', function () {
        if (!validatePhone(this.value)) {
            showError(this, 'Please enter a valid phone number (10-15 digits)');
        } else {
            clearError(this);
        }
    });

    // Form submission handler
    form.addEventListener('submit', function (event) {
        let isValid = true;

        // Validate first name
        if (!validateName(firstNameInput.value)) {
            showError(firstNameInput, 'First name must start with a capital letter and contain only letters');
            isValid = false;
        }

        // Validate last name
        if (!validateName(lastNameInput.value)) {
            showError(lastNameInput, 'Last name must start with a capital letter and contain only letters');
            isValid = false;
        }

        // Validate phone number
        if (!validatePhone(phoneInput.value)) {
            showError(phoneInput, 'Please enter a valid phone number (10-15 digits)');
            isValid = false;
        }

        // Validate contact type
        if (!contactTypeInput.value) {
            showError(contactTypeInput, 'Please select a contact type');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

// Auto-dismiss alerts after 5 seconds
setTimeout(function () {
    document.querySelectorAll('.alert').forEach(function (alert) {
        new bootstrap.Alert(alert).close();
    });
}, 5000); 
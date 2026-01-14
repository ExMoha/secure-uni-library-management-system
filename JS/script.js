document.addEventListener('DOMContentLoaded', function() {

    // Collecting all forms to apply the same validation logic
    const forms = document.querySelectorAll('form');

    forms.forEach(function(form) {

        // Applying the validation for each form after the user submits it
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('input[required], textarea[required]');

            // Dealing with empty required fields
            requiredFields.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.border = "2px solid red"; // Visual hint for invalid fields
                } else {
                    input.style.border = "1px solid #ccc"; // Resetting the style if valid
                }
            });

            if (!isValid) {
                event.preventDefault(); // Prevent form sending the form to the backend if it's invalid
                alert('Please fill in all required fields.');
            }
        });
    });

    // Specific validation for Registration Form
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirmPassword"]');
    const nameInput = document.querySelector('input[name="fullname"]');
    const registerForm = document.querySelector('form[action="register.php"]');

    if (registerForm && password && confirmPassword) {
        registerForm.addEventListener('submit', function(event) {
            
            // Checking if full name contains only letters and spaces
            const nameRegex = /^[a-zA-Z\s]+$/;

            if (nameInput && !nameRegex.test(nameInput.value.trim())) {
                event.preventDefault();
                alert('Full Name can only contain letters and spaces.');
                nameInput.style.border = "2px solid red"; // Visual hint for invalid name
                return; // no need to check passwords if name is invalid
            }

            // Checking password length
            if (password.value.length < 8) {
                event.preventDefault();
                alert('Password must be at least 8 characters long.');
                password.style.border = "2px solid red"; // Visual hint for invalid password
                return;
            }

            // Checking password strength (must contain uppercase, lowercase, number, and special character)
            const hasUpperCase = /[A-Z]/.test(password.value);
            const hasLowerCase = /[a-z]/.test(password.value);
            const hasNumber = /[0-9]/.test(password.value);
            const hasSpecialChar = /[!@#$%^&*()]/.test(password.value);
            
            if (!hasUpperCase || !hasLowerCase || !hasNumber || !hasSpecialChar) {
                event.preventDefault();
                alert('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.');
                password.style.border = "2px solid red"; // Visual hint for invalid password
                return;
            }

            // Checking if passwords match
            if (password.value !== confirmPassword.value) {
                event.preventDefault();
                alert('Passwords do not match.');
                confirmPassword.style.border = "2px solid red"; // Visual hint for mismatch
                return;
            }
        });
    }

    // Specific validation for Book Suggestion Form
    const suggestForm = document.querySelector('#suggest-form');
    const titleInput = document.querySelector('#title');
    const authorInput = document.querySelector('#author');
    const descriptionInput = document.querySelector('#description');

    if (suggestForm && titleInput && authorInput) {
        suggestForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate title format (alphanumeric, spaces, hyphens, apostrophes, and common punctuation)
            const titleRegex = /^[a-zA-Z0-9\s\-'.,:;!?()]+$/u;
            if (!titleRegex.test(titleInput.value.trim())) {
                event.preventDefault();
                alert('Book title contains invalid characters. Only letters, numbers, spaces, and common punctuation are allowed.');
                titleInput.style.border = "2px solid red";
                isValid = false;
                return;
            } else {
                titleInput.style.border = "1px solid #ccc";
            }

            // Validate author format (letters, spaces, hyphens, apostrophes)
            const authorRegex = /^[a-zA-Z\s\-'.]+$/u;
            if (!authorRegex.test(authorInput.value.trim())) {
                event.preventDefault();
                alert('Author name contains invalid characters. Only letters, spaces, hyphens, and apostrophes are allowed.');
                authorInput.style.border = "2px solid red";
                isValid = false;
                return;
            } else {
                authorInput.style.border = "1px solid #ccc";
            }
        });
    }
});
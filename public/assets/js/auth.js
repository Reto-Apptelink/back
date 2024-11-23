document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('authForm');
    const formTitle = document.getElementById('formTitle');
    const submitButton = document.getElementById('submitButton');
    const switchFormLink = document.getElementById('switchForm');
    const forgotPasswordLink = document.getElementById('forgotPassword');
    const confirmPasswordField = document.getElementById('confirmPasswordField');
    const passwordRequirements = document.getElementById('passwordRequirements');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');

    let isLoginForm = true;

    switchFormLink.addEventListener('click', function (e) {
        e.preventDefault();
        isLoginForm = !isLoginForm;
        updateFormUI();
    });

    passwordInput.addEventListener('focus', function () {
        if (!isLoginForm) {
            passwordRequirements.classList.remove('d-none');
        }
    });

    passwordInput.addEventListener('blur', function () {
        passwordRequirements.classList.add('d-none');
    });

    function updateFormUI() {
        if (isLoginForm) {
            formTitle.textContent = 'Login';
            submitButton.textContent = 'Login';
            switchFormLink.textContent = "Don't have an account? Register";
            confirmPasswordField.classList.add('d-none');
            forgotPasswordLink.classList.remove('d-none');
        } else {
            formTitle.textContent = 'Register';
            submitButton.textContent = 'Register';
            switchFormLink.textContent = 'Already have an account? Login';
            confirmPasswordField.classList.remove('d-none');
            forgotPasswordLink.classList.add('d-none');
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (validateForm()) {
            // Here you would typically send the form data to your server
            console.log('Form is valid. Submitting...');
        }
    });

    function validateForm() {
        const email = document.getElementById('email').value;
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (!isValidEmail(email)) {
            alert('Please enter a valid email address.');
            return false;
        }

        if (!isLoginForm) {
            if (!isValidPassword(password)) {
                alert('Password does not meet the requirements.');
                return false;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return false;
            }
        }

        return true;
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPassword(password) {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
        return passwordRegex.test(password);
    }
});
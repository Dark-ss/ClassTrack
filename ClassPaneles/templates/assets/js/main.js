document.addEventListener('DOMContentLoaded', function () {
    
    const passwordInputs = document.querySelectorAll('input[type="password"]');

    passwordInputs.forEach(input => {
        const eyeIcon = input.nextElementSibling;
        let passwordVisible = false;

        eyeIcon.addEventListener('click', () => {
            if (passwordVisible) {
                input.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                input.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
            passwordVisible = !passwordVisible;
        });
    });

    var passwordField = document.getElementById("password-field");
    var confirmPasswordField = document.getElementById("confirm-password");
    var passwordHint = document.getElementById("password-hint");
    var confirmpasswordHint = document.getElementById("confirm-password-hint");
    var passwordCheckIcon = document.querySelector(".input_field:nth-child(3) .check-icon");
    var confirmCheckIcon = document.querySelector(".input_field:nth-child(4) .check-icon");
    
    function validatePassword() {
        if (passwordField.value.length < 8) {
            passwordHint.textContent = "Crea una contraseña segura con al menos 8 caracteres";
            passwordCheckIcon.classList.remove('show');
            passwordField.classList.remove('valid');
        } else {
            passwordHint.textContent = "";
            passwordCheckIcon.classList.add('show');
            passwordField.classList.add('valid');
        }
        validateConfirmPassword();
    }
    
    function validateConfirmPassword() {
        if (confirmPasswordField.value === passwordField.value && passwordField.value.length >= 8) {
            confirmpasswordHint.textContent = "";
            confirmCheckIcon.classList.add('show');
            confirmPasswordField.classList.add('valid');
        } else {
            if (confirmPasswordField.value !== "") {
                confirmpasswordHint.textContent = "¡Ups! Las contraseñas no coinciden, por favor intenta de nuevo";
            }
            confirmCheckIcon.classList.remove('show');
            confirmPasswordField.classList.remove('valid');
        }
    }
    
    passwordField.addEventListener("focus", validatePassword);
    passwordField.addEventListener("input", validatePassword);
    passwordField.addEventListener("blur", function() {
        passwordHint.textContent = "";
        validateConfirmPassword();
    });
    
    confirmPasswordField.addEventListener("focus", validateConfirmPassword);
    confirmPasswordField.addEventListener("input", validateConfirmPassword);
    confirmPasswordField.addEventListener("blur", function() {
        if (confirmPasswordField.value === passwordField.value && passwordField.value.length >= 8) {
            confirmpasswordHint.textContent = "";
        }
    });

});


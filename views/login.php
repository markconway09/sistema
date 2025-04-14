<div class="form-container col-10 col-md-6 col-lg-4" id="loginForm">
    <form method="POST" class="form">
        <div class="form-group">
            <label for="user">Usuario</label>
            <input type="text" id="user" name="user" required>
        </div>
        <div class="form-group">
            <label for="pass">Contraseña</label>
            <div style="display: flex; align-items: center;">
                <input type="password" name="pass" id="pass" required>
                <button type="button" class="btn btn-dark" id="togglePassword" style="margin-left: 5px; border-color:#494949"><i class="bi bi-eye-fill"></i></button>
            </div>
        </div>
        <div class="form-group">
            <label for="remember" class="form-check-label">Recuérdame</label>
        </div>
        <div class="form-check form-switch" style="margin-top: -20px; opacity: 0.75;">
            <input type="checkbox" id="remember" name="remember" class="form-check-input">
        </div>
        <button class="form-submit-btn" name="login" type="submit">Entrar</button>
    </form>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('pass');
    const rememberCheckbox = document.getElementById('remember');
    const usernameField = document.getElementById('user');

    // Toggle password visibility
    togglePassword.addEventListener('click', () => {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        togglePassword.innerHTML = type === 'password' ? '<i class="bi bi-eye-fill"></i>' : '<i class="bi bi-eye-slash-fill"></i>';
    });

    // Save login info in cookies
    document.addEventListener('DOMContentLoaded', () => {
        const savedUser = getCookie('username');
        const savedPass = getCookie('password');
        if (savedUser && savedPass) {
            usernameField.value = savedUser;
            passwordField.value = savedPass;
            rememberCheckbox.checked = true;
        }
    });

    rememberCheckbox.addEventListener('change', () => {
        if (rememberCheckbox.checked) {
            setCookie('username', usernameField.value, 7);
            setCookie('password', passwordField.value, 7);
        } else {
            deleteCookie('username');
            deleteCookie('password');
        }
    });

    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
    }

    function getCookie(name) {
        const cookies = document.cookie.split('; ');
        for (let cookie of cookies) {
            const [key, value] = cookie.split('=');
            if (key === name) return value;
        }
        return null;
    }

    function deleteCookie(name) {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/`;
    }
</script>
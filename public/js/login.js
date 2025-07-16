// login.js – steuert Login-/Registrierungslogik inkl. automatischem Mailversand
const email = document.getElementById('email');
const password = document.getElementById('password');
const passwordRepeat = document.getElementById('password-repeat');
const submitBtn = document.getElementById('login-submit-btn');
const passwordLinkInfo = document.getElementById('password-link-info');
const loginErrorInfo = document.getElementById('login-error-info');
const newsletter = document.getElementById('newsletter');


email.addEventListener('input', async function () {
    //reset
    passwordRepeat.setCustomValidity('');
    submitBtn.disabled = false;
    loginErrorInfo.classList.add('hidden');
    loginErrorInfo.classList.remove('inline-block');

    const email = this.value;
    if (!email) return;

    const res = await fetch('/../handlers/check-email.php?email=' + encodeURIComponent(email));
    const data = await res.json();
    const emailToolTip = document.getElementById('email-tooltip');
    const passwordBox = document.getElementById('password-container');
    const repeatBox = document.getElementById('repeat-container');
    const nameContainer = document.getElementById('name-container');

    // Alles zurücksetzen
    passwordBox.classList.add('hidden');
    repeatBox.classList.add('hidden');
    nameContainer.classList.add('hidden');
    submitBtn.disabled = false;
    emailToolTip.textContent = '';
    submitBtn.textContent = 'Absenden';

    document.querySelector('[name="first_name"]').removeAttribute('required');
    document.querySelector('[name="last_name"]').removeAttribute('required');
    document.querySelector('[name="password"]').removeAttribute('required');
    document.querySelector('[name="password_repeat"]').removeAttribute('required');

    // Fall 1: Neue Registrierung
    if (!data.exists) {
        passwordBox.classList.remove('hidden');
        repeatBox.classList.remove('hidden');
        nameContainer.classList.remove('hidden');
        passwordLinkInfo.classList.add('hidden');
        newsletter.classList.remove('hidden');

        document.querySelector('[name="first_name"]').setAttribute('required', 'required');
        document.querySelector('[name="last_name"]').setAttribute('required', 'required');
        document.querySelector('[name="password"]').setAttribute('required', 'required');
        document.querySelector('[name="password_repeat"]').setAttribute('required', 'required');
        document.querySelector('[name="password"]').setAttribute('pattern', '(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).{8,}');
        document.querySelector('[name="password"]').setAttribute('title', 'Mindestens 8 Zeichen, Groß- und Kleinbuchstaben sowie eine Zahl.');

        emailToolTip.textContent = 'Neue Registrierung';
        emailToolTip.classList.add('text-green-600');
        submitBtn.textContent = 'Registrieren';
    }

    // Fall 2: Passwort muss über E-Mail-Link gesetzt werden
    else if (data.exists && data.has_password === false) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Nicht verfügbar';
        newsletter.classList.add('hidden');

        // ✅ Automatisch Mail verschicken + Feedback anzeigen
        await sendPasswordLink(email);
    }

    // Fall 3: Login
    else if (data.exists && data.has_password === true) {
        passwordBox.classList.remove('hidden');
        document.querySelector('[name="password"]').setAttribute('required', 'required');
        document.querySelector('[name="password"]').removeAttribute('title', '');
        document.querySelector('[name="password"]').removeAttribute('pattern', '');
        document.querySelector('[name="password"]').removeAttribute('minlength', '');
        passwordLinkInfo.classList.add('hidden');
        newsletter.classList.add('hidden');
        loginErrorInfo.textContent = '';
        emailToolTip.textContent = 'Bitte Passwort eingeben.';
        emailToolTip.classList.add('text-blue-600');
        submitBtn.textContent = 'Einloggen';
    }
});

async function sendPasswordLink(email) {
    try {
        const res = await fetch('/../handlers/send-password-link.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({email}),
        });

        const result = await res.json();
        passwordLinkInfo.textContent = '';

        passwordLinkInfo.textContent = result.message;

        if (result.status === 'success') {
            passwordLinkInfo.className = 'text-green-600 mt-2';
        } else if (result.status === 'wait') {
            passwordLinkInfo.className = 'text-yellow-600 mt-2';
        } else {
            passwordLinkInfo.className = 'text-red-600 mt-2';
        }

    } catch (err) {
        passwordLinkInfo.id = 'password-link-info';
        passwordLinkInfo.textContent = '⚠️ Fehler beim Senden der E-Mail.';
        passwordLinkInfo.className = 'text-red-600 mt-2';
    }
}

// password-check for login form
function checkPasswords() {
    const pw1 = password.value;
    const pw2 = passwordRepeat.value;
    const pwHint = document.getElementById('check-password-hint');

    if (pw1 && pw2 && pw1 !== pw2) {
        passwordRepeat.setCustomValidity('Passwörter stimmen nicht überein');
        pwHint.innerText = 'Passwörter stimmen nicht überein';
        pwHint.classList.remove('hidden');
        submitBtn.disabled = true;

    } else {
        passwordRepeat.setCustomValidity('');
        submitBtn.disabled = false;
        pwHint.classList.add('hidden');
    }
}

// Events
password.addEventListener('input', checkPasswords);
passwordRepeat.addEventListener('input', checkPasswords);


document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', () => {
        console.log('guck nicht 🙈');
        const targetId = button.dataset.target;
        const input = document.getElementById(targetId);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.textContent = isHidden ? '🙈' : '👁️';
    })
});

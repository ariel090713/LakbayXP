import { initializeApp } from 'https://www.gstatic.com/firebasejs/11.0.0/firebase-app.js';
import { getAuth, signInWithRedirect, getRedirectResult, GoogleAuthProvider } from 'https://www.gstatic.com/firebasejs/11.0.0/firebase-auth.js';

const configEl = document.getElementById('firebase-config');
if (configEl) {
    const app = initializeApp({
        apiKey: configEl.dataset.apiKey,
        authDomain: configEl.dataset.authDomain,
        projectId: configEl.dataset.projectId,
        appId: configEl.dataset.appId,
    });
    const auth = getAuth(app);

    // Check if we're returning from a redirect
    getRedirectResult(auth).then(async (result) => {
        if (!result) return;

        const idToken = await result.user.getIdToken();
        const mode = sessionStorage.getItem('firebase_auth_mode') || 'login';
        sessionStorage.removeItem('firebase_auth_mode');

        const errorEl = document.getElementById('google-error');

        try {
            const res = await fetch(configEl.dataset.routeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ firebase_token: idToken, mode: mode }),
            });

            const data = await res.json();
            if (res.ok && data.redirect) {
                window.location.href = data.redirect;
            } else {
                if (errorEl) { errorEl.textContent = data.message || 'Authentication failed.'; errorEl.classList.remove('hidden'); }
            }
        } catch (e) {
            if (errorEl) { errorEl.textContent = 'Authentication failed.'; errorEl.classList.remove('hidden'); }
        }
    }).catch((error) => {
        // Ignore if no redirect result
        if (error.code !== 'auth/null-user') {
            const errorEl = document.getElementById('google-error');
            if (errorEl) { errorEl.textContent = 'Google sign-in failed.'; errorEl.classList.remove('hidden'); }
        }
    });

    // Attach click handlers
    function startRedirect(mode) {
        sessionStorage.setItem('firebase_auth_mode', mode);
        signInWithRedirect(auth, new GoogleAuthProvider());
    }

    const loginBtn = document.getElementById('google-login-btn');
    const signupBtn = document.getElementById('google-signup-btn');
    if (loginBtn) loginBtn.addEventListener('click', () => startRedirect('login'));
    if (signupBtn) signupBtn.addEventListener('click', () => startRedirect('register'));
}

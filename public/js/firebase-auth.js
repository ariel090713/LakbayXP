import { initializeApp } from 'https://www.gstatic.com/firebasejs/11.0.0/firebase-app.js';
import { getAuth, signInWithPopup, GoogleAuthProvider } from 'https://www.gstatic.com/firebasejs/11.0.0/firebase-auth.js';

let firebaseApp = null;
let firebaseAuth = null;

function getFirebase() {
    if (!firebaseApp) {
        const el = document.getElementById('firebase-config');
        if (!el) return null;
        firebaseApp = initializeApp({
            apiKey: el.dataset.apiKey,
            authDomain: el.dataset.authDomain,
            projectId: el.dataset.projectId,
            appId: el.dataset.appId,
        });
        firebaseAuth = getAuth(firebaseApp);
    }
    return firebaseAuth;
}

async function handleGoogleAuth(mode) {
    const auth = getFirebase();
    if (!auth) return;

    const btnId = mode === 'login' ? 'google-login-btn' : 'google-signup-btn';
    const btn = document.getElementById(btnId);
    const errorEl = document.getElementById('google-error');
    if (!btn) return;

    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.textContent = mode === 'login' ? 'Signing in...' : 'Creating account...';
    if (errorEl) errorEl.classList.add('hidden');

    try {
        const result = await signInWithPopup(auth, new GoogleAuthProvider());
        const idToken = await result.user.getIdToken();
        const routeUrl = document.getElementById('firebase-config').dataset.routeUrl;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const res = await fetch(routeUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ firebase_token: idToken, mode: mode }),
        });

        const data = await res.json();
        if (res.ok && data.redirect) {
            window.location.href = data.redirect;
        } else {
            if (errorEl) { errorEl.textContent = data.message || 'Failed.'; errorEl.classList.remove('hidden'); }
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    } catch (e) {
        if (e.code !== 'auth/popup-closed-by-user') {
            if (errorEl) { errorEl.textContent = 'Google sign-in failed.'; errorEl.classList.remove('hidden'); }
        }
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
}

// Attach immediately (script is loaded after DOM is ready)
function attach() {
    const loginBtn = document.getElementById('google-login-btn');
    const signupBtn = document.getElementById('google-signup-btn');
    if (loginBtn) loginBtn.addEventListener('click', () => handleGoogleAuth('login'));
    if (signupBtn) signupBtn.addEventListener('click', () => handleGoogleAuth('register'));
}

// Try both: immediate and on DOMContentLoaded
attach();
document.addEventListener('DOMContentLoaded', attach);

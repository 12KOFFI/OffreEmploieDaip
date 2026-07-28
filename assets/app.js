import './stimulus_bootstrap.js';

import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.getElementById('togglePassword');
    const inputPassword = document.getElementById('inputPassword');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');

    if (togglePassword && inputPassword) {
        togglePassword.addEventListener('click', () => {
            const isPassword = inputPassword.type === 'password';
            inputPassword.type = isPassword ? 'text' : 'password';
            if (eyeOpen) eyeOpen.classList.toggle('hidden', !isPassword);
            if (eyeClosed) eyeClosed.classList.toggle('hidden', isPassword);
        });
    }

    const toggleRegPassword = document.getElementById('toggleRegPassword');
    const regPassword = document.getElementById('regPassword');
    const regEyeOpen = document.getElementById('regEyeOpen');
    const regEyeClosed = document.getElementById('regEyeClosed');

    if (toggleRegPassword && regPassword) {
        toggleRegPassword.addEventListener('click', () => {
            const isPassword = regPassword.type === 'password';
            regPassword.type = isPassword ? 'text' : 'password';
            if (regEyeOpen) regEyeOpen.classList.toggle('hidden', !isPassword);
            if (regEyeClosed) regEyeClosed.classList.toggle('hidden', isPassword);
        });
    }

    const regPasswordInput = document.getElementById('regPassword');
    if (regPasswordInput) {
        regPasswordInput.addEventListener('input', () => {
            updatePasswordStrength(regPasswordInput.value);
        });
    }
});

function updatePasswordStrength(password) {
    const bars = [
        document.getElementById('str-bar-1'),
        document.getElementById('str-bar-2'),
        document.getElementById('str-bar-3'),
        document.getElementById('str-bar-4'),
    ];
    const text = document.getElementById('str-text');

    if (!bars[0] || !text) return;

    let score = 0;
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;

    const levels = ['', 'weak', 'fair', 'good', 'strong'];
    const labels = ['', 'Faible', 'Moyen', 'Bon', 'Fort'];

    bars.forEach((bar, i) => {
        bar.className = 'h-1.5 flex-1 rounded-full bg-slate-200 transition-colors duration-300';
        if (i < score) {
            bar.classList.add(levels[score]);
        }
    });

    text.textContent = password.length === 0 ? 'Au moins 8 caractères' : labels[score];
}

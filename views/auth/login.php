<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MAE CONSUMER GOODS TRADING — Sign in</title>
<link rel="icon" type="image/png" href="<?= url('/assets/img/logo.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --cream: #F5F1E4;
    --cream-deep: #EFE9D6;
    --card: #FFFEF9;
    --paddy-900: #23321D;
    --paddy-700: #3C5530;
    --paddy-600: #4B6B3C;
    --paddy-500: #6C8B54;
    --husk-gold: #B78A3E;
    --husk-gold-light: #DDBE7E;
    --text-dark: #262A20;
    --text-mute: #6B6A5B;
    --rust: #A9552E;
    --rust-bg: #F7E9DC;
    --rust-border: #E3C1A4;
    --success: #3E7A44;
    --success-bg: #E6F0E7;
    --success-border: #9DCFA5;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    font-family: 'Work Sans', sans-serif;
    background: var(--cream);
    color: var(--text-dark);
    min-height: 100vh;
    display: flex;
  }

  .layout {
    display: flex;
    width: 100%;
    min-height: 100vh;
  }

  /* ---- Left panel ---- */
  .panel-brand {
    flex: 0 0 42%;
    background:
      radial-gradient(circle at 15% 20%, rgba(255,255,255,0.06), transparent 40%),
      linear-gradient(160deg, var(--paddy-900) 0%, var(--paddy-700) 55%, var(--paddy-600) 100%);
    color: var(--cream);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 56px 52px;
    position: relative;
    overflow: hidden;
  }

  .brand-mark {
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: 'Fraunces', serif;
    font-size: 1.5rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    z-index: 2;
  }

  .brand-mark img { flex-shrink: 0; width: 34px; height: 34px; object-fit: contain; }

  .panel-copy {
    z-index: 2;
    max-width: 360px;
  }

  .panel-copy h1 {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 2.3rem;
    line-height: 1.18;
    margin: 0 0 18px;
    color: #FBF8EE;
  }

  .panel-copy p {
    font-size: 0.98rem;
    line-height: 1.6;
    color: #D8DFC9;
    margin: 0;
  }

  .panel-footer {
    z-index: 2;
    font-size: 0.82rem;
    color: #A9BB93;
  }

  .rice-illustration {
    position: absolute;
    right: -40px;
    bottom: -30px;
    width: 340px;
    height: auto;
    opacity: 0.9;
  }

  /* ---- Right panel ---- */
  .panel-form {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    background: var(--cream);
  }

  .form-wrap {
    width: 100%;
    max-width: 380px;
  }

  .form-wrap .mobile-title {
    display: none;
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.6rem;
    color: var(--paddy-700);
    margin-bottom: 4px;
  }

  .form-heading h2 {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.7rem;
    margin: 0 0 6px;
    color: var(--text-dark);
  }

  .form-heading p {
    margin: 0 0 30px;
    font-size: 0.92rem;
    color: var(--text-mute);
  }

  .alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px 15px;
    border-radius: 8px;
    font-size: 0.87rem;
    margin-bottom: 26px;
    line-height: 1.4;
  }
  .alert-error {
    background: var(--rust-bg);
    border: 1px solid var(--rust-border);
    color: var(--rust);
  }
  .alert-success {
    background: var(--success-bg);
    border: 1px solid var(--success-border);
    color: var(--success);
  }
  .alert svg { flex-shrink: 0; margin-top: 1px; }

  .field {
    margin-bottom: 20px;
  }

  .field label {
    display: block;
    font-size: 0.83rem;
    font-weight: 600;
    color: var(--paddy-700);
    margin-bottom: 7px;
  }

  .input-shell {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-shell svg.leading-icon {
    position: absolute;
    left: 13px;
    color: var(--text-mute);
    pointer-events: none;
  }

  .field input {
    width: 100%;
    padding: 12px 14px 12px 40px;
    border: 1.5px solid var(--cream-deep);
    background: var(--card);
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: 'Work Sans', sans-serif;
    color: var(--text-dark);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
  }

  .field input::placeholder { color: #B7B4A2; }

  .field input:focus {
    outline: none;
    border-color: var(--paddy-500);
    box-shadow: 0 0 0 3px rgba(108, 139, 84, 0.18);
  }

  .toggle-visibility {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-mute);
    padding: 4px;
    display: flex;
  }
  .toggle-visibility:hover { color: var(--paddy-700); }

  .field-row-between {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 26px;
    font-size: 0.85rem;
  }

  .remember {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--text-mute);
  }

  .remember input {
    accent-color: var(--paddy-600);
    width: 15px;
    height: 15px;
  }

  a.link {
    color: var(--paddy-700);
    text-decoration: none;
    font-weight: 500;
  }
  a.link:hover { text-decoration: underline; }

  button.sign-in {
    width: 100%;
    padding: 13px;
    background: var(--paddy-700);
    color: #FBF8EE;
    border: none;
    border-radius: 8px;
    font-size: 0.98rem;
    font-family: 'Work Sans', sans-serif;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    transition: background 0.15s ease, transform 0.05s ease;
  }
  button.sign-in:hover { background: var(--paddy-600); }
  button.sign-in:disabled { opacity: 0.7; cursor: not-allowed; }
  button.sign-in:active:not(:disabled) { transform: translateY(1px); }
  button.sign-in:focus-visible {
    outline: 3px solid var(--husk-gold-light);
    outline-offset: 2px;
  }
  @keyframes spin { 100% { transform: rotate(360deg); } }
  .spinner-icon { animation: spin 1s linear infinite; display: none; }

  .divider-note {
    text-align: center;
    font-size: 0.82rem;
    color: var(--text-mute);
    margin-top: 24px;
  }

  @media (max-width: 860px) {
    .panel-brand { display: none; }
    .form-wrap .mobile-title { display: block; }
    .panel-form { padding: 32px 20px; }
  }

  @media (prefers-reduced-motion: reduce) {
    * { transition: none !important; }
  }
</style>
</head>
<body>

<div class="layout">

  <!-- LEFT: brand panel -->
  <div class="panel-brand">
    <div class="brand-mark">
      <img src="<?= url('/assets/img/logo.png') ?>" alt="Logo">
      <?= e(\App\Models\Setting::get('app_name', 'MAE CONSUMER GOODS TRADING')) ?>
    </div>

    <div class="panel-copy">
      <h1>Every sack accounted for, every sale in view.</h1>
      <p>Track stock levels, manage sales, and keep your MAE CONSUMER GOODS TRADING running smoothly from one place.</p>
    </div>

    <div class="panel-footer">MAE CONSUMER GOODS TRADING</div>

    <!-- decorative rice stalk illustration -->
    <svg class="rice-illustration" viewBox="0 0 300 420" fill="none">
      <path d="M150 420 C150 300 140 220 150 60" stroke="#8CA873" stroke-width="4" fill="none" opacity="0.55"/>
      <path d="M150 60 C150 60 100 90 95 130" stroke="#8CA873" stroke-width="3" fill="none" opacity="0.5"/>
      <path d="M150 60 C150 60 200 90 205 130" stroke="#8CA873" stroke-width="3" fill="none" opacity="0.5"/>
      <g opacity="0.9">
        <ellipse cx="150" cy="55" rx="9" ry="14" fill="#DDBE7E" transform="rotate(-10 150 55)"/>
        <ellipse cx="163" cy="70" rx="9" ry="14" fill="#DDBE7E" transform="rotate(15 163 70)"/>
        <ellipse cx="137" cy="70" rx="9" ry="14" fill="#DDBE7E" transform="rotate(-25 137 70)"/>
        <ellipse cx="150" cy="88" rx="9" ry="14" fill="#C9A968" transform="rotate(0 150 88)"/>
        <ellipse cx="170" cy="100" rx="9" ry="14" fill="#DDBE7E" transform="rotate(20 170 100)"/>
        <ellipse cx="130" cy="100" rx="9" ry="14" fill="#DDBE7E" transform="rotate(-30 130 100)"/>
        <ellipse cx="158" cy="115" rx="8" ry="13" fill="#C9A968" transform="rotate(10 158 115)"/>
        <ellipse cx="142" cy="118" rx="8" ry="13" fill="#DDBE7E" transform="rotate(-12 142 118)"/>
      </g>
      <path d="M95 130 C85 150 90 175 100 190" stroke="#7C9A64" stroke-width="3" fill="none" opacity="0.45"/>
      <path d="M205 130 C215 150 210 175 200 190" stroke="#7C9A64" stroke-width="3" fill="none" opacity="0.45"/>
    </svg>
  </div>

  <!-- RIGHT: form panel -->
  <div class="panel-form">
    <div class="form-wrap">

      <div class="mobile-title"><?= e(\App\Models\Setting::get('app_name', 'MAE CONSUMER GOODS TRADING')) ?></div>

      <div class="form-heading">
        <h2>Welcome back</h2>
        <p>Sign in to manage your inventory and sales for MAE CONSUMER GOODS TRADING.</p>
      </div>

      <?php if (isset($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success' ?>">
            <?php if ($flash['type'] === 'error'): ?>
              <!-- Error Triangle Icon -->
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            <?php else: ?>
              <!-- Check Circle Icon -->
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
              </svg>
            <?php endif; ?>
          <span><?= e($flash['message']) ?></span>
        </div>
      <?php endif; ?>

      <form action="<?= url('/login') ?>" method="POST" id="loginForm">
        <?= \App\Middleware\CsrfMiddleware::csrfField() ?>

        <div class="field">
          <label for="username">Username</label>
          <div class="input-shell">
            <svg class="leading-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input id="username" name="username" type="text" placeholder="Enter your username" autocomplete="username" required>
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-shell">
            <svg class="leading-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
            <button type="button" class="toggle-visibility" onclick="togglePassword()" aria-label="Show password">
              <svg id="eye-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="field-row-between">
          <label class="remember">
            <input type="checkbox" name="remember" value="1"> Remember me
          </label>
          <a href="#" class="link">Forgot password?</a>
        </div>

        <button type="submit" class="sign-in" id="submitBtn">
          <span id="btnText">Sign in</span>
          <svg class="spinner-icon" id="btnSpinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
             <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
          </svg>
        </button>
      </form>

      <div class="divider-note">Having trouble signing in? Contact your system administrator.</div>
    </div>
  </div>

</div>

<script>
  function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.innerHTML = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.7 19.7 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a19.87 19.87 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
      input.type = 'password';
      icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
  }

  // Loading state
  document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    const text = document.getElementById('btnText');
    const spinner = document.getElementById('btnSpinner');
    
    btn.disabled = true;
    text.textContent = 'Signing in...';
    spinner.style.display = 'block';
  });
</script>

</body>
</html>

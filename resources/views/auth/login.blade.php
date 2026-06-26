<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login - Sistem Monitoring Kapasitas Mesin Roll Forming">
    <title>Masuk | RF Monitor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <script src="https://unpkg.com/lucide@latest" defer></script>
</head>
<body>
    <div class="login-scene">
        <div class="grid-overlay" aria-hidden="true"></div>
        <div class="float-orb orb-1" aria-hidden="true"></div>
        <div class="float-orb orb-2" aria-hidden="true"></div>
        <div class="float-orb orb-3" aria-hidden="true"></div>

        <div class="login-container">
            <div class="login-left">
                <div class="login-visual">
                    <div class="visual-badge">MONITORING SYSTEM</div>
                    <h2 class="visual-title">Roll <br><span>Forming</span></h2>
                    <p class="visual-desc">Sistem monitoring kapasitas mesin produksi secara real-time untuk Cell 3</p>
                    <div class="visual-stats">
                        <div class="v-stat">
                            <span class="v-stat-val">311</span>
                            <span class="v-stat-label">ea/shift</span>
                        </div>
                        <div class="v-stat-divider"></div>
                        <div class="v-stat">
                            <span class="v-stat-val">67s</span>
                            <span class="v-stat-label">cycle time</span>
                        </div>
                        <div class="v-stat-divider"></div>
                        <div class="v-stat">
                            <span class="v-stat-val">72.5%</span>
                            <span class="v-stat-label">uptime</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="login-right">
                <div class="login-card">
                    <div class="login-header">
                        <div class="login-brand-icon">
                            <svg viewBox="0 0 32 32" fill="none"><rect x="2" y="8" width="28" height="4" rx="1" fill="#14b8a6"/><rect x="6" y="14" width="20" height="3" rx="1" fill="#14b8a6" opacity="0.6"/><rect x="4" y="19" width="24" height="3" rx="1" fill="#14b8a6" opacity="0.4"/><circle cx="16" cy="27" r="3" fill="#14b8a6"/></svg>
                        </div>
                        <h1>Masuk ke RF Monitor</h1>
                        <p>Gunakan email dan password Anda</p>
                    </div>

                    @if($errors->any())
                    <div class="login-alert" role="alert" aria-live="polite">
                        <i data-lucide="alert-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" id="login-form">
                        @csrf
                        <div class="field">
                            <label for="email" class="field-label">EMAIL</label>
                            <div class="field-wrap">
                                <span class="field-icon" aria-hidden="true"><i data-lucide="mail"></i></span>
                                <input type="email" id="email" name="email" class="field-input" value="{{ old('email') }}" required autofocus placeholder="nama@email.com" aria-required="true">
                            </div>
                        </div>

                        <div class="field">
                            <label for="password" class="field-label">PASSWORD</label>
                            <div class="field-wrap">
                                <span class="field-icon" aria-hidden="true"><i data-lucide="lock"></i></span>
                                <input type="password" id="password" name="password" class="field-input" required placeholder="Masukkan password" aria-required="true">
                                <button type="button" class="toggle-pw" id="toggle-pw" aria-label="Toggle password visibility">
                                    <i data-lucide="eye" id="icon-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="field-row">
                            <label class="checkbox-wrap">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                <span>Ingat saya</span>
                            </label>
                        </div>

                        <button type="submit" class="btn-login" id="btn-login">
                            <span>Masuk</span>
                            <i data-lucide="arrow-right"></i>
                        </button>
                    </form>


                </div>
            </div>
        </div>
    </div>

    <script defer>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
        var togglePw = document.getElementById('toggle-pw');
        var pwField = document.getElementById('password');
        if (togglePw && pwField) {
            togglePw.addEventListener('click', function() {
                var isPassword = pwField.type === 'password';
                pwField.type = isPassword ? 'text' : 'password';
            });
        }
    });
    </script>
</body>
</html>

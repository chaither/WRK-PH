<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WRK Services PH HRIS – Login</title>
    <meta name="description" content="Sign in to WRK Services PH Human Resource Information System – streamlining attendance, payroll, and workforce management.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand-dark:    #051534;
            --brand-mid:     #0d2d6b;
            --brand-blue:    #1a56c4;
            --brand-accent:  #3b82f6;
            --brand-light:   #60a5fa;
            --white:         #ffffff;
            --gray-50:       #f8fafc;
            --gray-100:      #f1f5f9;
            --gray-200:      #e2e8f0;
            --gray-400:      #94a3b8;
            --gray-600:      #475569;
            --gray-800:      #1e293b;
            --red-100:       #fee2e2;
            --red-600:       #dc2626;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        /* ─── LAYOUT ───────────────────────────────────────────────── */
        .page-wrapper {
            display: flex;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, var(--brand-dark) 0%, #0b2059 55%, #0e3280 100%);
            position: relative;
            overflow: hidden;
        }

        /* Subtle animated radial glow blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            will-change: transform;
            animation: drift 18s ease-in-out infinite alternate;
        }
        .blob-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(59,130,246,0.18) 0%, transparent 70%);
            top: -120px; left: -100px;
            animation-duration: 20s;
        }
        .blob-2 {
            width: 450px; height: 450px;
            background: radial-gradient(circle, rgba(26,86,196,0.15) 0%, transparent 70%);
            bottom: -80px; left: 30%;
            animation-duration: 16s;
            animation-delay: -5s;
        }
        .blob-3 {
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(96,165,250,0.1) 0%, transparent 70%);
            top: 40%; right: 35%;
            animation-duration: 22s;
            animation-delay: -10s;
        }

        @keyframes drift {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(30px, 20px) scale(1.04); }
            100% { transform: translate(-20px, 40px) scale(0.97); }
        }

        /* ─── LEFT HERO ───────────────────────────────────────────── */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 5rem;
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            color: #93c5fd;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            padding: 0.45rem 1rem;
            border-radius: 999px;
            margin-bottom: 2rem;
            width: fit-content;
        }
        .hero-badge i { font-size: 0.65rem; }

        .hero-title {
            font-size: clamp(2.8rem, 4.5vw, 4.2rem);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: -0.03em;
            color: var(--white);
            margin-bottom: 1.4rem;
        }
        .hero-title .accent {
            color: #60a5fa;
        }

        .hero-desc {
            font-size: 1rem;
            color: rgba(255,255,255,0.65);
            line-height: 1.7;
            max-width: 420px;
            margin-bottom: 3rem;
        }

        /* Feature cards */
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.09);
            backdrop-filter: blur(6px);
            border-radius: 0.875rem;
            padding: 0.9rem 1.2rem;
            transition: background 0.3s ease, transform 0.3s ease;
            cursor: default;
            max-width: 360px;
        }
        .feature-item:hover {
            background: rgba(255,255,255,0.11);
            transform: translateX(5px);
        }

        .feature-icon {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.6rem;
            background: linear-gradient(135deg, var(--brand-blue), var(--brand-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(59,130,246,0.35);
        }
        .feature-icon i {
            color: white;
            font-size: 0.85rem;
        }

        .feature-text h4 {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.15rem;
        }
        .feature-text p {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.5);
            font-weight: 400;
        }

        /* ─── RIGHT LOGIN PANEL ──────────────────────────────────── */
        .login-panel {
            width: 440px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: var(--white);
            border-radius: 1.5rem;
            box-shadow: 0 30px 80px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.05);
            padding: 2.5rem 2.2rem;
            width: 100%;
            animation: slideUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Logo area */
        .card-logo-wrap {
            text-align: center;
            margin-bottom: 1.6rem;
        }
        .card-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin: 0 auto 0.8rem;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(26,86,196,0.2));
        }
        .card-brand-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--brand-blue);
            letter-spacing: 0.01em;
            margin-bottom: 0.2rem;
        }
        .card-brand-sub {
            font-size: 0.78rem;
            color: var(--gray-400);
            font-weight: 400;
        }

        /* Divider */
        .card-divider {
            height: 1px;
            background: var(--gray-200);
            margin: 1.4rem 0;
        }

        /* Form fields */
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.45rem;
        }
        .form-label i {
            color: var(--brand-accent);
            font-size: 0.75rem;
        }

        .input-wrap { position: relative; }
        .input-field {
            width: 100%;
            background: var(--gray-50);
            border: 1.5px solid var(--gray-200);
            border-radius: 0.65rem;
            padding: 0.72rem 1rem;
            font-size: 0.88rem;
            font-family: 'Inter', sans-serif;
            color: var(--gray-800);
            transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
            outline: none;
        }
        .input-field::placeholder { color: var(--gray-400); }
        .input-field:focus {
            border-color: var(--brand-accent);
            background: var(--white);
            box-shadow: 0 0 0 3.5px rgba(59,130,246,0.16);
        }

        /* Password toggle */
        .pw-toggle {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            cursor: pointer;
            font-size: 0.85rem;
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 0;
        }
        .pw-toggle:hover { color: var(--gray-600); }

        /* Remember / Forgot */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.7rem;
            margin-bottom: 1.4rem;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--gray-600);
            cursor: pointer;
        }
        .remember-label input[type="checkbox"] {
            accent-color: var(--brand-accent);
            width: 14px;
            height: 14px;
            cursor: pointer;
        }
        .forgot-link {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--brand-accent);
            text-decoration: none;
            transition: color 0.2s;
        }
        .forgot-link:hover { color: var(--brand-blue); }

        /* Submit button */
        .btn-signin {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            width: 100%;
            padding: 0.85rem 1rem;
            background: linear-gradient(135deg, #1a56c4 0%, #3b82f6 100%);
            color: var(--white);
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(59,130,246,0.38);
            transition: transform 0.25s, box-shadow 0.25s, filter 0.25s;
        }
        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(59,130,246,0.5);
            filter: brightness(1.06);
        }
        .btn-signin:active { transform: translateY(0); }

        /* Error alert */
        .error-alert {
            background: var(--red-100);
            border: 1px solid #fca5a5;
            color: var(--red-600);
            font-size: 0.8rem;
            padding: 0.75rem 1rem;
            border-radius: 0.6rem;
            margin-bottom: 1rem;
        }
        .error-alert ul { list-style: disc; padding-left: 1.2rem; }

        /* Footer */
        .card-footer {
            text-align: center;
            font-size: 0.72rem;
            color: var(--gray-400);
            margin-top: 1.6rem;
        }

        /* ─── RESPONSIVE ─────────────────────────────────────────── */
        @media (max-width: 860px) {
            .page-wrapper { overflow-y: auto; }
            .hero { display: none; }
            .login-panel {
                width: 100%;
                min-height: 100vh;
                align-items: flex-start;
                padding: 3rem 1.5rem;
            }
            html, body { overflow: auto; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    <!-- Ambient blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- ── LEFT HERO ── -->
    <div class="hero">
        <div class="hero-badge">
            <i class="fa-solid fa-shield-halved"></i>
            Human Resource Information System
        </div>

        <h1 class="hero-title">
            Where <span class="accent">Talent</span><br>
            Meets Opportunity
        </h1>

        <p class="hero-desc">
            A powerful, modern HRIS built for WRK Services PH
            — streamlining attendance, payroll, and workforce
            management with precision.
        </p>

        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="feature-text">
                    <h4>Daily Time Record</h4>
                    <p>Real-time attendance tracking</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div class="feature-text">
                    <h4>Payroll Management</h4>
                    <p>Automated computation &amp; payslips</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="feature-text">
                    <h4>Leave &amp; Schedules</h4>
                    <p>Smart leave requests &amp; approvals</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ── RIGHT LOGIN PANEL ── -->
    <div class="login-panel">
        <div class="login-card">

            <!-- Logo / Branding -->
            <div class="card-logo-wrap">
                <img src="{{ asset('logo.png') }}" alt="WRK Services PH Logo" class="card-logo">
                <div class="card-brand-name">WRK SERVICES PH HRIS</div>
                <div class="card-brand-sub">Where Talent Meets Opportunity</div>
            </div>

            <div class="card-divider"></div>

            @if($errors->any())
                <div class="error-alert">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" autocomplete="on">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fa-solid fa-envelope"></i> Email Address
                    </label>
                    <div class="input-wrap">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="input-field"
                            placeholder="you@wrk.com.ph"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fa-solid fa-lock"></i> Password
                    </label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="input-field"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            style="padding-right: 2.6rem;"
                        >
                        <button type="button" class="pw-toggle" onclick="togglePw()" aria-label="Toggle password visibility">
                            <i id="pw-icon" class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember / Forgot -->
                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                </div>

                <!-- Sign In -->
                <button type="submit" class="btn-signin" id="signin-btn">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    SIGN IN
                </button>
            </form>

            <div class="card-footer">
                &copy; {{ date('Y') }} WRK Services PH. All rights reserved.
            </div>
        </div>
    </div>

</div>

<script>
    // Password toggle
    function togglePw() {
        const field = document.getElementById('password');
        const icon  = document.getElementById('pw-icon');
        const isPass = field.type === 'password';
        field.type = isPass ? 'text' : 'password';
        icon.className = isPass ? 'far fa-eye-slash' : 'far fa-eye';
    }

    // Subtle parallax on hero blobs from mouse
    const blobs = document.querySelectorAll('.blob');
    document.addEventListener('mousemove', (e) => {
        const cx = window.innerWidth  / 2;
        const cy = window.innerHeight / 2;
        const dx = (e.clientX - cx) / cx;
        const dy = (e.clientY - cy) / cy;
        blobs.forEach((b, i) => {
            const factor = (i + 1) * 12;
            b.style.transform = `translate(${dx * factor}px, ${dy * factor}px)`;
        });
    });
</script>
</body>
</html>

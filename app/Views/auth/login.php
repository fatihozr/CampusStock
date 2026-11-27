<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Giriş Yap | Stationery Flow E-Ticaret' ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Application CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= time() ?>">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 50%, #dbeafe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            overflow: hidden; /* Prevent scrolling for background items */
            position: relative;
        }

        /* Animated Background Container */
        .bg-animation-wrapper {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 120%;
            height: 120%;
            z-index: 0;
            pointer-events: none;
            transition: transform 0.1s ease-out; /* For parallax */
        }

        /* Floating Items */
        .floating-item {
            position: absolute;
            font-size: 3rem;
            opacity: 0.15;
            filter: blur(1px);
            animation-name: float;
            animation-iteration-count: infinite;
            animation-timing-function: linear;
            will-change: transform, left, top;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(15deg); }
            100% { transform: translateY(0) rotate(360deg); }
        }

        @keyframes moveAcross {
            0% { transform: translateX(-100px) translateY(0) rotate(0deg); }
            100% { transform: translateX(110vw) translateY(-100px) rotate(360deg); }
        }

        /* Glassmorphism Panel */
        .login-wrapper {
            width: 100%;
            max-width: 440px;
            z-index: 10;
            animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            padding: 35px 30px 20px;
            text-align: center;
        }

        .brand-icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            color: #FFFFFF;
            font-size: 2rem;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
            transform: rotate(-5deg);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .glass-card:hover .brand-icon-wrapper {
            transform: rotate(0deg) scale(1.05);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            border-color: #2563eb;
        }

        .input-group-text {
            background: rgba(248, 250, 252, 0.8);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-right: none;
            color: #64748b;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }
        
        .form-control {
            border-left: none;
        }
        
        .form-control:focus + .input-group-text, 
        .input-group-text:has(+ .form-control:focus) {
            border-color: #2563eb;
            color: #2563eb;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-20deg);
            transition: all 0.6s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }
        
        .btn-primary:hover::after {
            left: 150%;
        }

        .toggle-password {
            cursor: pointer;
            border-left: none !important;
            border-right: 1px solid rgba(226, 232, 240, 0.8) !important;
            border-radius: 0 10px 10px 0 !important;
        }
        
        .form-control:focus ~ .toggle-password {
            border-color: #2563eb !important;
        }

        /* Page Transition Out */
        body.transitioning-out {
            opacity: 0;
            transition: opacity 0.5s ease-out;
        }
        
        body.transitioning-out .login-wrapper {
            transform: scale(0.95) translateY(-20px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
    </style>
</head>
<body>

    <!-- Animated Background -->
    <div class="bg-animation-wrapper" id="parallaxBg">
        <!-- JS will populate floating items here -->
    </div>

    <div class="login-wrapper">
        <div class="glass-card">
            <!-- Header -->
            <div class="login-header">
                <div class="brand-icon-wrapper" aria-hidden="true">
                    <i class="bi bi-book-half"></i>
                </div>
                <h1 class="h4 fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">CAMPUSSTOCK KIRTASİYE</h1>
                <p class="mb-0 fs-7 text-muted">Kampüs Kırtasiye Stok & Satış Yönetim Paneli</p>
            </div>

            <!-- Body -->
            <div class="card-body px-4 py-3">

                <!-- Flash Messages -->
                <?php if (!empty($flashError)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 p-3 mb-4 fs-7 border-0 rounded-3 shadow-sm bg-danger text-white" role="alert" style="background: rgba(220, 38, 38, 0.9) !important; backdrop-filter: blur(10px);">
                        <i class="bi bi-exclamation-octagon-fill fs-5 flex-shrink-0"></i>
                        <div><?= e($flashError) ?></div>
                    </div>
                <?php elseif (!empty($flashSuccess)): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 p-3 mb-4 fs-7 border-0 rounded-3 shadow-sm bg-success text-white" role="alert" style="background: rgba(22, 163, 74, 0.9) !important; backdrop-filter: blur(10px);">
                        <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
                        <div><?= e($flashSuccess) ?></div>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="POST" id="loginForm" class="needs-validation" novalidate>
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label for="username_or_email" class="form-label fw-semibold fs-7 text-dark ms-1">Kullanıcı Adı veya E-Posta</label>
                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="username_or_email" name="username_or_email" placeholder="admin" required autofocus autocomplete="username">
                            <div class="invalid-feedback">Lütfen kullanıcı adı veya e-posta giriniz.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold fs-7 text-dark ms-1">Parola</label>
                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" class="form-control border-start-0 border-end-0 px-0" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="input-group-text toggle-password" id="btnTogglePassword" title="Şifreyi Göster/Gizle" aria-label="Şifreyi Göster veya Gizle">
                                <i class="bi bi-eye-slash" id="toggleIcon" aria-hidden="true"></i>
                            </button>
                            <div class="invalid-feedback">Lütfen parolanızı giriniz.</div>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn btn-primary w-100 mb-3 d-flex align-items-center justify-content-center gap-2">
                        <span id="btnText">Giriş Yap</span>
                        <i class="bi bi-arrow-right fs-5" id="btnIcon" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
            
            <div class="text-center pb-4 pt-1">
                <small class="text-muted fs-8 fw-medium">CampusStock Kırtasiye Platformu &copy; <?= date('Y') ?></small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Password Toggle
            const toggleBtn = document.getElementById('btnTogglePassword');
            const pwdInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (toggleBtn && pwdInput && toggleIcon) {
                toggleBtn.addEventListener('click', function () {
                    const isPwd = pwdInput.type === 'password';
                    pwdInput.type = isPwd ? 'text' : 'password';
                    toggleIcon.className = isPwd ? 'bi bi-eye' : 'bi bi-eye-slash';
                });
            }

            // 2. Submit Animation (Smooth Transition)
            const loginForm = document.getElementById('loginForm');
            const btnSubmit = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');

            if (loginForm && btnSubmit) {
                loginForm.addEventListener('submit', function (e) {
                    if (this.checkValidity()) {
                        btnSubmit.disabled = true;
                        btnText.textContent = 'Giriş Yapılıyor...';
                        btnIcon.className = 'spinner-border spinner-border-sm ms-2';
                        
                        // Add exit transition
                        document.body.classList.add('transitioning-out');
                        
                        // Allow form to submit normally after brief animation delay
                        // Since it's synchronous post, we don't preventDefault completely,
                        // but setting the class will instantly start CSS transition while browser loads next page.
                    }
                });
            }

            // 3. Generate Floating Background Items
            const items = ['✏️', '📚', '🖋️', '📎', '📏', '📦', '🛒', '🛍️', '🔖', '📒'];
            const bgWrapper = document.getElementById('parallaxBg');
            const numItems = 20;

            for(let i=0; i<numItems; i++) {
                const el = document.createElement('div');
                el.className = 'floating-item';
                el.textContent = items[Math.floor(Math.random() * items.length)];
                
                // Random position
                const left = Math.random() * 100;
                const top = Math.random() * 100;
                el.style.left = left + '%';
                el.style.top = top + '%';
                
                // Random size to create depth
                const scale = 0.5 + Math.random() * 1.5;
                el.style.transform = `scale(${scale})`;
                
                // Opacity based on size (smaller = more transparent = further back)
                el.style.opacity = (scale * 0.15).toFixed(2);
                
                // Blur based on size (smaller = more blur)
                const blur = scale < 1 ? 2 : 0.5;
                el.style.filter = `blur(${blur}px)`;
                
                // Animation speed (bigger = faster)
                const duration = 15 + (Math.random() * 20) * (3/scale);
                el.style.animationDuration = duration + 's';
                el.style.animationDelay = '-' + (Math.random() * duration) + 's'; // Start at random point in animation
                
                // Different animation types
                if (Math.random() > 0.5) {
                    el.style.animationName = 'moveAcross';
                    el.style.left = '-10%'; // start offscreen left
                }
                
                bgWrapper.appendChild(el);
            }

            // 4. Parallax Mouse Move Effect
            document.addEventListener('mousemove', function(e) {
                const x = (e.clientX / window.innerWidth - 0.5) * 20; // max 10px move
                const y = (e.clientY / window.innerHeight - 0.5) * 20; // max 10px move
                
                // Only move the background wrapper slightly
                requestAnimationFrame(() => {
                    bgWrapper.style.transform = `translate(${x}px, ${y}px)`;
                });
            });
        });
    </script>
</body>
</html>

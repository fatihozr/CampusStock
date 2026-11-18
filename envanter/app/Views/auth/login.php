<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Giriş Yap | Belediye Envanter Takip Sistemi' ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #0F172A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: #FFFFFF;
            border: 0;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            overflow: hidden;
        }

        .login-header-bg {
            background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%);
            padding: 32px 24px;
            text-align: center;
            color: #FFFFFF;
            position: relative;
        }

        .brand-icon-wrapper {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            color: #FFFFFF;
            font-size: 1.75rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            border-color: #2563EB;
        }

        .input-group-text {
            background-color: #F8FAFC;
            border-color: #E2E8F0;
            color: #64748B;
        }

        .btn-primary {
            background-color: #2563EB;
            border-color: #2563EB;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #1D4ED8;
            border-color: #1D4ED8;
            transform: translateY(-1px);
        }

        .toggle-password {
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="card login-card">
            <!-- Header Banner -->
            <div class="login-header-bg">
                <div class="brand-icon-wrapper">
                    <i class="bi bi-building-gear"></i>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fs-8 mb-2">T.C. BELEDİYE BAŞKANLIĞI</span>
                <h5 class="fw-bold mb-1 tracking-wide">BELEDİYE ENVANTER TAKİP SİSTEMİ</h5>
                <p class="mb-0 fs-7 text-white-50">Kurumsal Envanter ve Stok Yönetim Sistemi</p>
            </div>

            <!-- Card Body -->
            <div class="card-body p-4">

                <!-- Flash Error Message -->
                <?php if (!empty($flashError)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 p-3 mb-3 fs-7 border-0 rounded-3 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-octagon-fill fs-5 flex-shrink-0"></i>
                        <div><?= e($flashError) ?></div>
                    </div>
                <?php elseif (!empty($flashSuccess)): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 p-3 mb-3 fs-7 border-0 rounded-3 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
                        <div><?= e($flashSuccess) ?></div>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="POST" id="loginForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="username_or_email" class="form-label fw-semibold fs-7 text-secondary">Kullanıcı Adı veya E-Posta</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username_or_email" name="username_or_email" placeholder="Kullanıcı Adı veya E-Posta" required autofocus autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label fw-semibold fs-7 text-secondary mb-0">Parola</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                            <span class="input-group-text toggle-password" id="btnTogglePassword" title="Şifreyi Göster/Gizle">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                            <label class="form-check-label fs-7 text-muted" for="remember">
                                Beni Hatırla
                            </label>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn btn-primary w-100 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-in-right fs-5" id="btnIcon"></i>
                        <span id="btnText">Sisteme Giriş Yap</span>
                    </button>
                </form>
            </div>

            <!-- Clean Corporate Footer (No Default Credentials) -->
            <div class="card-footer bg-light border-0 text-center py-3">
                <small class="text-muted fs-8">
                    © 2026 Belediye Envanter Takip Sistemi — Bilgi İşlem Müdürlüğü
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('btnTogglePassword');
            const pwdInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            const loginForm = document.getElementById('loginForm');
            const btnSubmit = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');

            if (toggleBtn && pwdInput && toggleIcon) {
                toggleBtn.addEventListener('click', function () {
                    const isPwd = pwdInput.type === 'password';
                    pwdInput.type = isPwd ? 'text' : 'password';
                    toggleIcon.className = isPwd ? 'bi bi-eye' : 'bi bi-eye-slash';
                });
            }

            if (loginForm && btnSubmit) {
                loginForm.addEventListener('submit', function () {
                    btnSubmit.disabled = true;
                    btnText.textContent = 'Giriş yapılıyor...';
                    btnIcon.className = 'spinner-border spinner-border-sm';
                });
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'CampusStock Kırtasiye | Kampüs Stok & Satış Yönetim Sistemi') ?></title>
    <meta name="description" content="<?= e($pageDescription ?? 'CampusStock — Üniversite kampüs kırtasiyeleri için stok takibi, barkodlu hızlı satış (POS), öğrenci siparişleri ve iade yönetim platformu.') ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e(rtrim(base_url(), '/') . ($_SERVER['REQUEST_URI'] ?? '/')) ?>">

    <!-- Open Graph / Sosyal Medya Etiketleri -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:title" content="<?= e($pageTitle ?? 'CampusStock Kırtasiye') ?>">
    <meta property="og:description" content="<?= e($pageDescription ?? 'Üniversitelere özel kırtasiye stok, barkodlu satış ve sipariş yönetim platformu.') ?>">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tom Select CSS for searchable dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom Application CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= filemtime(dirname(__DIR__) . '/public/assets/css/style.css') ?>">

    <script>
        window.BASE_URL = '<?= rtrim(base_url(), '/') ?>';
        (function() {
            const savedTheme = localStorage.getItem('campusstock_theme') || localStorage.getItem('belediye_theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body class="bg-light">
    <a href="#main-content" class="skip-link visually-hidden-focusable">Ana içeriğe atla</a>

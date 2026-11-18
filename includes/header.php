<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Stationery Flow | E-Ticaret ve Depo Sistemi' ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Tom Select CSS for searchable dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom Application CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= filemtime(dirname(__DIR__) . '/public/assets/css/style.css') ?>">
    
    <?php if (($_SESSION['active_module'] ?? 'cleaning') === 'furniture'): ?>
    <style>
        /* Furniture Theme Overrides */
        :root {
            --bs-primary: #8B5A2B;
            --bs-primary-rgb: 139, 90, 43;
            --bs-success: #C19A6B;
            --bs-success-rgb: 193, 154, 107;
        }
        .bg-primary, .bg-primary-gradient, .btn-primary {
            background-color: #8B5A2B !important;
            border-color: #8B5A2B !important;
        }
        .text-primary {
            color: #8B5A2B !important;
        }
        .badge.bg-primary {
            background-color: #8B5A2B !important;
        }
        .nav-pills .nav-link.active {
            background-color: #8B5A2B !important;
        }
        .bg-dark-blue {
            background-color: #4A3B2C !important; /* Dark brown for navbar */
        }
    </style>
    <?php endif; ?>

    <script>
        window.BASE_URL = '<?= rtrim(base_url(), '/') ?>';
        (function() {
            const savedTheme = localStorage.getItem('belediye_theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body class="bg-light">

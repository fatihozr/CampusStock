<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark"><?= e($pageTitle ?? 'Yeni Malzeme') ?></h4>
                </div>
                <a href="<?= base_url('products') ?>" class="btn btn-outline-secondary btn-sm">Geri Dön</a>
            </div>

            <?php if (isset($flashError) && $flashError): ?>
                <div class="alert alert-danger"><?= e($flashError) ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted">Bu sayfa hazırlık aşamasındadır. Malzeme ekleme işlemi sol menüdeki veya stok sayfasındaki hızlı ekle modülünden yapılabilir.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

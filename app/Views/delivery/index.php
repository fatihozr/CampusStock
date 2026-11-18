<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark"><?= e($pageTitle ?? 'Gelen Teslimatlar') ?></h4>
                </div>
            </div>

            <?php if (isset($flashSuccess) && $flashSuccess): ?>
                <div class="alert alert-success"><?= e($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if (isset($flashError) && $flashError): ?>
                <div class="alert alert-danger"><?= e($flashError) ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <?php if (empty($deliveries)): ?>
                        <p class="text-muted text-center py-4">Sistemde kayıtlı teslimat bulunamadı.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Belge No</th>
                                        <th>Teslim Tarihi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($deliveries as $d): ?>
                                    <tr>
                                        <td><?= e($d['document_no'] ?? 'Bilinmiyor') ?></td>
                                        <td><?= e($d['created_at'] ?? '') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

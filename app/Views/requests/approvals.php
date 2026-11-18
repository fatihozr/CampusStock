<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark"><?= e($pageTitle ?? 'Bekleyen Talepler') ?></h4>
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
                    <?php if (empty($requests)): ?>
                        <p class="text-muted text-center py-4">Şu anda bekleyen bir malzeme talebi bulunmamaktadır.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Talep Kodu</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($requests as $r): ?>
                                    <tr>
                                        <td><?= e($r['request_code'] ?? 'Bilinmiyor') ?></td>
                                        <td><span class="badge bg-warning text-dark">Bekliyor</span></td>
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

<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-0">

            <!-- Flash Messages -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 p-3 mb-4 border-0 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill fs-4 text-success flex-shrink-0"></i>
                    <div><?= e($flashSuccess) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 p-3 mb-4 border-0 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-danger flex-shrink-0"></i>
                    <div><?= e($flashError) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>

            <!-- Page Title & Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-trash3 text-danger me-2"></i>Çöp Kutusu / Silinen Kayıtlar</h4>
                    <p class="text-muted mb-0 fs-7">Yanlışlıkla Silinen Kayıtları Geri Yükleme ve Kalıcı Temizleme Merkezi</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="badge bg-danger p-2 fs-7">
                        <i class="bi bi-info-circle me-1"></i> Toplam <?= count($trashItems) ?> Silinmiş Kayıt
                    </span>
                </div>
            </div>

            <!-- Trash Items Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="card-title fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-list-task text-primary"></i> Silinen Kayıtlar Listesi
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($trashItems)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-trash3 text-muted display-4 mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark">Çöp Kutusu Boş</h6>
                            <p class="text-muted fs-7 mb-0">Şu anda çöp kutusunda bekleyen silinmiş herhangi bir kayıt bulunmamaktadır.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive style-scrollbar">
                            <table class="table table-hover align-middle mb-0 border-0 fs-7">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4"># ID</th>
                                        <th>KAYIT TÜRÜ</th>
                                        <th>KOD / BARKOD</th>
                                        <th>KAYIT ADI</th>
                                        <th>SİLİNME TARİHİ</th>
                                        <th>SİLEN KULLANICI</th>
                                        <th>DURUM</th>
                                        <th class="text-end pe-4">İŞLEMLER</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($trashItems as $t): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-secondary">#<?= $t['id'] ?></td>
                                            <td>
                                                <span class="badge bg-secondary rounded-2 px-2 py-1 fs-8">
                                                    <?= e($t['type_label']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark text-white font-monospace">
                                                    <?= e($t['item_code']) ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark"><?= e($t['item_name']) ?></td>
                                            <td class="text-muted">
                                                <i class="bi bi-clock me-1"></i><?= date('d.m.Y H:i', strtotime($t['deleted_at'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-person me-1"></i><?= e($t['deleted_by']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-trash me-1"></i>Çöp Kutusunda
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <!-- Restore Form -->
                                                    <form action="<?= base_url('trash/restore') ?>" method="POST" class="d-inline" onsubmit="return confirm('Bu kaydı aktif listeye geri yüklemek istiyor musunuz?');">
                                                        <input type="hidden" name="trash_id" value="<?= $t['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-success btn-sm rounded-2 d-flex align-items-center gap-1">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Geri Yükle
                                                        </button>
                                                    </form>

                                                    <!-- Purge Modal Trigger (Admin Only) -->
                                                    <?php if (\App\Helpers\Auth::hasPermission('admin')): ?>
                                                        <button type="button" class="btn btn-danger btn-sm rounded-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#purgeModal<?= $t['id'] ?>">
                                                            <i class="bi bi-trash3-fill"></i> Kalıcı Sil
                                                        </button>

                                                        <!-- Purge Modal -->
                                                        <div class="modal fade text-start" id="purgeModal<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content border-0 shadow">
                                                                    <div class="modal-header bg-danger text-white py-3">
                                                                        <h5 class="modal-title fw-bold fs-6"><i class="bi bi-exclamation-triangle-fill me-2"></i>Kalıcı Silme Onayı</h5>
                                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                                    </div>
                                                                    <form action="<?= base_url('trash/purge') ?>" method="POST">
                                                                        <input type="hidden" name="trash_id" value="<?= $t['id'] ?>">
                                                                        <div class="modal-body p-4">
                                                                            <p class="text-dark fw-semibold mb-2">
                                                                                <strong>[<?= e($t['item_code']) ?>] <?= e($t['item_name']) ?></strong> kaydı veritabanından kalıcı olarak silinecektir.
                                                                            </p>
                                                                            <div class="alert alert-danger border-0 mb-0 p-3 fs-8">
                                                                                <i class="bi bi-shield-x me-1"></i> Bu işlem geri alınamaz. Ancak geçmiş stok hareket ve zimmet kayıtlarının referans bütünlüğü korunacaktır.
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer bg-light py-2">
                                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                                                                            <button type="submit" class="btn btn-danger btn-sm px-4 fw-semibold">
                                                                                <i class="bi bi-trash3-fill me-1"></i> Kalıcı Olarak Sil
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
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

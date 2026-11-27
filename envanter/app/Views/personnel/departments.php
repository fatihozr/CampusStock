<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-4">

            <!-- Flash Alerts -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i> <?= e($flashSuccess) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <?= e($flashError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-diagram-3-fill text-primary me-2"></i>Organizasyon ve Daire Başkanlığı Yönetimi
                    </h4>
                    <p class="text-muted mb-0 fs-7">Belediye Başkanlığı Hiyerarşik Yapısı, Daire Başkanlıkları ve Bağlı Personel Yönetimi</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm rounded-2 px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                        <i class="bi bi-folder-plus me-1"></i> ş Daire Başkanlığı Ekle
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-2 px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
                        <i class="bi bi-person-plus me-1"></i> ş Personel Ekle
                    </button>
                    <a href="<?= base_url('personnel') ?>" class="btn btn-outline-secondary btn-sm rounded-2 px-3">
                        <i class="bi bi-people me-1"></i> Personel Rehberi
                    </a>
                </div>
            </div>

            <!-- Dynamic Interactive KPI Summary Cards -->
            <div class="row g-3 mb-4">
                <!-- 1. TOPLAM MÜDÜRLÜK -->
                <div class="col-12 col-sm-6 col-xl-2.4 col-lg-4">
                    <a href="<?= base_url('departments') ?>" class="text-decoration-none h-100">
                        <div class="card card-stat dashboard-kpi-card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-primary">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fs-8 fw-semibold text-uppercase">Toplam Daire Başkanlığı</span>
                                <div class="bg-primary-subtle text-primary p-2 rounded-circle"><i class="bi bi-building fs-5"></i></div>
                            </div>
                            <h3 class="fw-bold text-dark mb-0"><?= $stats['total_departments'] ?></h3>
                            <small class="text-primary fs-8 fw-semibold">Tümünü Göster &rarr;</small>
                        </div>
                    </a>
                </div>

                <!-- 2. TOPLAM PERSONEL -->
                <div class="col-12 col-sm-6 col-xl-2.4 col-lg-4">
                    <a href="<?= base_url('personnel') ?>" class="text-decoration-none h-100">
                        <div class="card card-stat dashboard-kpi-card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-info">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fs-8 fw-semibold text-uppercase">Toplam Personel</span>
                                <div class="bg-info-subtle text-info p-2 rounded-circle"><i class="bi bi-people fs-5"></i></div>
                            </div>
                            <h3 class="fw-bold text-dark mb-0"><?= $stats['total_personnel'] ?></h3>
                            <small class="text-info fs-8 fw-semibold">Personel Rehberi &rarr;</small>
                        </div>
                    </a>
                </div>

                <!-- 3. AKTİF PERSONEL -->
                <div class="col-12 col-sm-6 col-xl-2.4 col-lg-4">
                    <a href="<?= base_url('personnelğstatus=Aktif') ?>" class="text-decoration-none h-100">
                        <div class="card card-stat dashboard-kpi-card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-success">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fs-8 fw-semibold text-uppercase">Aktif Personel</span>
                                <div class="bg-success-subtle text-success p-2 rounded-circle"><i class="bi bi-person-check fs-5"></i></div>
                            </div>
                            <h3 class="fw-bold text-success mb-0"><?= $stats['active_personnel'] ?></h3>
                            <small class="text-success fs-8 fw-semibold">Aktif Kadro &rarr;</small>
                        </div>
                    </a>
                </div>

                <!-- 4. PASİF PERSONEL -->
                <div class="col-12 col-sm-6 col-xl-2.4 col-lg-4">
                    <a href="<?= base_url('personnelğstatus=Pasif') ?>" class="text-decoration-none h-100">
                        <div class="card card-stat dashboard-kpi-card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-warning">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fs-8 fw-semibold text-uppercase">Pasif Personel</span>
                                <div class="bg-warning-subtle text-warning p-2 rounded-circle"><i class="bi bi-person-exclamation fs-5"></i></div>
                            </div>
                            <h3 class="fw-bold text-dark mb-0"><?= $stats['passive_personnel'] ?></h3>
                            <small class="text-warning-emphasis fs-8 fw-semibold">Pasif Kadro &rarr;</small>
                        </div>
                    </a>
                </div>

                <!-- 5. BOŞ MÜDÜRLÜK -->
                <div class="col-12 col-sm-6 col-xl-2.4 col-lg-4">
                    <a href="javascript:void(0)" id="kpiEmptyDeptsBtn" data-empty-count="<?= $stats['empty_departments'] ?>" class="text-decoration-none h-100">
                        <div class="card card-stat dashboard-kpi-card border-0 shadow-sm rounded-3 bg-white p-3 h-100 border-start border-4 border-secondary">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fs-8 fw-semibold text-uppercase">Boş Daire Başkanlığı</span>
                                <div class="bg-secondary-subtle text-secondary p-2 rounded-circle"><i class="bi bi-folder-x fs-5"></i></div>
                            </div>
                            <h3 class="fw-bold text-dark mb-0"><?= $stats['empty_departments'] ?></h3>
                            <small class="text-secondary fs-8 fw-semibold">Boş Birimleri İncele &rarr;</small>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-3">
                    <div class="row align-items-center g-2">
                        <div class="col-12 col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="deptSearchInput" class="form-control border-start-0 ps-0" placeholder="??ğ? Daire Başkanlığı veya personel ara (?Örn: fen, bil, ahmet)...">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 text-md-end">
                            <span class="text-muted fs-8" id="deptSearchResultCount">Toplam <?= count($departments) ?> daire başkanlığı gösteriliyor.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Cards Grid -->
            <div class="row g-4" id="deptGrid">
                <?php foreach ($departments as $dept): 
                    $staffList = $deptPersonnelMap[$dept['name']] ?? [];
                    $activeStaffCount = count(array_filter($staffList, fn($p) => ($p['status'] ?? '') === 'Aktif'));
                ?>
                    <div class="col-12 col-md-6 col-xl-4 dept-card-wrapper" 
                         data-dept-name="<?= e(mb_strtolower($dept['name'], 'UTF-8')) ?>" 
                         data-dept-code="<?= e(mb_strtolower($dept['code'], 'UTF-8')) ?>"
                         data-staff-names="<?= e(mb_strtolower(implode(' ', array_map(fn($p) => $p['full_name'] ?? ($p['first_name'].' '.$p['last_name']), $staffList)), 'UTF-8')) ?>">
                        <div class="card border shadow-sm rounded-3 h-100 dept-card">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between dept-card-header btn-view-dept-detail"
                                 style="cursor: pointer;"
                                 data-dept="<?= htmlspecialchars(json_encode($dept), ENT_QUOTES, 'UTF-8') ?>"
                                 data-staff="<?= htmlspecialchars(json_encode($staffList), ENT_QUOTES, 'UTF-8') ?>"
                                 title="Daire Başkanlığı Detaylarını Göster">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-folder-fill text-warning me-2 fs-5"></i>
                                        <strong class="text-dark fs-6"><?= e($dept['name']) ?></strong>
                                    </div>
                                    <small class="text-muted font-monospace fs-8"><code><?= e($dept['code']) ?></code> &middot; <?= e($dept['status']) ?></small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-8">
                                    <?= count($staffList) ?> Personel
                                </span>
                            </div>
                            
                            <div class="card-body p-3">
                                <?php if (!empty($dept['description'])): ?>
                                    <p class="text-muted fs-8 mb-3 bg-light p-2 rounded border"><?= e($dept['description']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($staffList)): ?>
                                    <div class="list-group list-group-flush fs-7 max-h-250 overflow-auto">
                                        <?php foreach ($staffList as $stf): ?>
                                            <div class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between staff-item"
                                                 data-staff-name="<?= e(mb_strtolower($stf['full_name'] ?? ($stf['first_name'].' '.$stf['last_name']), 'UTF-8')) ?>">
                                                <div>
                                                    <a href="javascript:void(0)" class="fw-bold text-dark text-decoration-none btn-personnel-detail" data-id="<?= $stf['id'] ?>">
                                                        <?= e($stf['full_name'] ?? ($stf['first_name'].' '.$stf['last_name'])) ?>
                                                    </a>
                                                    <div class="text-muted fs-8"><?= e($stf['title']) ?> &middot; <code class="fs-8"><?= e($stf['unit']) ?></code></div>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-outline-info btn-xs btn-personnel-detail" data-id="<?= $stf['id'] ?>" title="Personel Detayı">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary btn-xs btn-edit-personnel" 
                                                            data-id="<?= $stf['id'] ?>"
                                                            data-first-name="<?= e($stf['first_name']) ?>"
                                                            data-last-name="<?= e($stf['last_name']) ?>"
                                                            data-sicil="<?= e($stf['sicil_no']) ?>"
                                                            data-title="<?= e($stf['title']) ?>"
                                                            data-dept="<?= e($stf['department']) ?>"
                                                            data-unit="<?= e($stf['unit']) ?>"
                                                            data-email="<?= e($stf['email']) ?>"
                                                            data-phone="<?= e($stf['phone']) ?>"
                                                            data-status="<?= e($stf['status']) ?>"
                                                            title="Düzenle">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted fs-8 bg-light rounded border border-dashed">
                                        <i class="bi bi-info-circle me-1"></i> Bu müdürlüşe bağlı personel kaydı bulunmuyor.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer bg-light py-2 px-3 border-top d-flex align-items-center justify-content-between">
                                <small class="text-muted fs-8">
                                    <i class="bi bi-person-check-fill text-success me-1"></i> Aktif: <strong><?= $activeStaffCount ?></strong>
                                </small>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info btn-view-dept-detail border-0 me-1"
                                            data-dept="<?= htmlspecialchars(json_encode($dept), ENT_QUOTES, 'UTF-8') ?>"
                                            data-staff="<?= htmlspecialchars(json_encode($staffList), ENT_QUOTES, 'UTF-8') ?>"
                                            title="Daire Başkanlığı Detay Kartı">
                                        <i class="bi bi-info-circle me-1"></i> Detay
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-edit-dept border-0" 
                                            data-id="<?= $dept['id'] ?>"
                                            data-code="<?= e($dept['code']) ?>"
                                            data-name="<?= e($dept['name']) ?>"
                                            data-description="<?= e($dept['description']) ?>"
                                            data-status="<?= e($dept['status']) ?>">
                                        <i class="bi bi-pencil-square me-1"></i> Düzenle
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- No Dept Found Message -->
            <div id="noDeptFoundMsg" class="d-none text-center py-5 text-muted">
                <i class="bi bi-search fs-1 d-block mb-2 text-secondary"></i>
                <h5>Aranan daire başkanlığı veya personel bulunamadı.</h5>
                <p class="fs-8">Lütfen arama terimini deşiştirip tekrar deneyiniz.</p>
            </div>

        </div>
    </main>
</div>

<!-- MODAL 1: MÜDÜRLÜK EKLE -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-success text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-folder-plus me-2"></i>Yeni Daire Başkanlığı Tanımla</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('departments/store') ?>" method="POST" onsubmit="return validateDeptForm(this)">
                <input type="hidden" name="redirect_to" value="/departments">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Daire Başkanlığı Adı *</label>
                        <input type="text" name="name" class="form-control" placeholder="?Örn: Park ve Bahçeler Müdürlüşü" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Daire Başkanlığı Kodu</label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="?Örn: MDR-110">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Açıklama</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Birim görev tanımı ve faaliyet alanı..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Durum</label>
                        <select name="status" class="form-select">
                            <option value="Aktif">Aktif</option>
                            <option value="Pasif">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: MÜDÜRLÜK DşZENLE -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark-blue text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Daire Başkanlığı Düzenle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('departments/update') ?>" method="POST" onsubmit="return validateDeptForm(this)">
                <input type="hidden" name="id" id="edit_dept_id">
                <input type="hidden" name="redirect_to" value="/departments">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Daire Başkanlığı Adı *</label>
                        <input type="text" name="name" id="edit_dept_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Daire Başkanlığı Kodu</label>
                        <input type="text" name="code" id="edit_dept_code" class="form-control font-monospace">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Açıklama</label>
                        <textarea name="description" id="edit_dept_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Durum</label>
                        <select name="status" id="edit_dept_status" class="form-select">
                            <option value="Aktif">Aktif</option>
                            <option value="Pasif">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: PERSONEL EKLE (Searchable Autocomplete for Department) -->
<div class="modal fade" id="addPersonnelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Yeni Personel Ekle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('personnel/store') ?>" method="POST">
                <input type="hidden" name="redirect_to" value="/departments">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Ad *</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="?Örn: Ahmet">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Soyad *</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="?Örn: Yılmaz">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Sicil No</label>
                            <input type="text" name="sicil_no" class="form-control font-monospace" placeholder="?Örn: SIC-1040">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Unvan</label>
                            <input type="text" name="title" class="form-control" placeholder="?Örn: Bilgi İşlem Uzmanı">
                        </div>
                        <div class="col-12 col-md-6 position-relative">
                            <label class="form-label fw-semibold fs-7 text-secondary">Bağlı Daire Başkanlığı *</label>
                            <select name="department" class="form-select" required>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= e($d['name']) ?>"><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Birim</label>
                            <input type="text" name="unit" class="form-control" placeholder="?Örn: Yazılım Geliştirme Birimi">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">E-Posta</label>
                            <input type="email" name="email" class="form-control" placeholder="personel@belediye.bel.tr">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Telefon</label>
                            <input type="text" name="phone" class="form-control" placeholder="0532 000 0000">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Personel Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 4: PERSONEL DşZENLE -->
<div class="modal fade" id="editPersonnelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark-blue text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Personel Bilgilerini Düzenle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('personnel/update') ?>" method="POST">
                <input type="hidden" name="id" id="p_edit_id">
                <input type="hidden" name="redirect_to" value="/departments">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Ad *</label>
                            <input type="text" name="first_name" id="p_edit_first_name" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Soyad *</label>
                            <input type="text" name="last_name" id="p_edit_last_name" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Sicil No</label>
                            <input type="text" name="sicil_no" id="p_edit_sicil" class="form-control font-monospace">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Unvan</label>
                            <input type="text" name="title" id="p_edit_title" class="form-control">
                        </div>
                        <div class="col-12 col-md-6 position-relative">
                            <label class="form-label fw-semibold fs-7 text-secondary">Bağlı Daire Başkanlığı *</label>
                            <select name="department" id="p_edit_dept" class="form-select" required>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= e($d['name']) ?>"><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Birim</label>
                            <input type="text" name="unit" id="p_edit_unit" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">E-Posta</label>
                            <input type="email" name="email" id="p_edit_email" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Telefon</label>
                            <input type="text" name="phone" id="p_edit_phone" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold fs-7 text-secondary">Durum</label>
                            <select name="status" id="p_edit_status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Pasif">Pasif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold"><i class="bi bi-check-lg me-1"></i> Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 5: PERSONEL DETAY KARTI -->
<div class="modal fade" id="personnelDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark-blue text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-badge me-2"></i>Personel Detay Kartı</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="personnelDetailBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 6: MÜDÜRLÜK DETAY MODALI -->
<div class="modal fade" id="departmentDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark-blue text-white py-3">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-building me-2"></i><span id="md_dept_name">Daire Başkanlığı Detayı</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-dark font-monospace" id="md_dept_code">MDR-000</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" id="md_dept_status">Aktif</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" id="md_dept_title">Daire Başkanlığı Adı</h5>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-7" id="md_staff_count">
                            0 Personel
                        </span>
                    </div>
                </div>

                <div id="md_description_box" class="bg-light p-3 rounded border mb-3 fs-7 text-muted d-none"></div>

                <h6 class="fw-bold text-dark mb-2 fs-7"><i class="bi bi-people text-primary me-1"></i> Bağlı Personel Listesi</h6>
                <div class="table-responsive rounded border mb-3" style="max-height: 300px; overflow-y: auto;">
                    <table class="table align-middle table-hover mb-0 fs-7">
                        <thead class="table-light">
                            <tr>
                                <th>AD SOYAD</th>
                                <th>UNVAN</th>
                                <th>BİRİM / ŞEFLİK</th>
                                <th>DURUM</th>
                                <th class="text-end">İŞLEM</th>
                            </tr>
                        </thead>
                        <tbody id="md_staff_tbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm px-3 fw-semibold" id="md_btn_edit_dept">
                        <i class="bi bi-pencil-square me-1"></i> Düzenle
                    </button>
                    <a href="#" id="md_btn_view_personnel" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                        <i class="bi bi-people-fill me-1"></i> Personel Rehberinde Görüntüle
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function escapeHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Real-time Department & Personnel Filtering
document.addEventListener('DOMContentLoaded', function() {
    const dInput = document.getElementById('deptSearchInput');
    const cards = document.querySelectorAll('.dept-card-wrapper');
    const msg = document.getElementById('noDeptFoundMsg');
    const counter = document.getElementById('deptSearchResultCount');

    if (dInput) {
        let timer = null;
        dInput.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(() => {
                const query = trNormalize(this.value.trim());
                let visibleCount = 0;

                cards.forEach(card => {
                    const deptName = card.dataset.deptName || '';
                    const deptCode = card.dataset.deptCode || '';
                    const staffNames = card.dataset.staffNames || '';

                    if (!query || deptName.includes(query) || deptCode.includes(query) || staffNames.includes(query)) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (counter) {
                    counter.innerText = query ? visibleCount + ' daire başkanlığı eşleşti.' : 'Toplam ' + cards.length + ' daire başkanlığı gösteriliyor.';
                }

                if (msg) {
                    if (visibleCount === 0) {
                        msg.classList.remove('d-none');
                    } else {
                        msg.classList.add('d-none');
                    }
                }
            }, 250);
        });
    }

    // KPI Empty Department Filter Handler
    const emptyBtn = document.getElementById('kpiEmptyDeptsBtn');
    if (emptyBtn) {
        emptyBtn.addEventListener('click', function() {
            const count = parseInt(this.dataset.emptyCount || '0');
            if (count === 0) {
                alert('Boş daire başkanlığı bulunmuyor. Tüm daire başkanlığılere personel atanmıştır.');
            } else {
                let visibleCount = 0;
                cards.forEach(card => {
                    const staffItems = card.querySelectorAll('.staff-item');
                    if (staffItems.length === 0) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                if (counter) counter.innerText = visibleCount + ' personelsiz (boş) daire başkanlığı filtrelendi.';
                if (msg) {
                    if (visibleCount === 0) msg.classList.remove('d-none');
                    else msg.classList.add('d-none');
                }
            }
        });
    }

    // View Department Detail Modal Trigger
    document.querySelectorAll('.btn-view-dept-detail').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit-dept') || e.target.closest('.btn-edit-personnel') || e.target.closest('.btn-personnel-detail')) {
                return;
            }
            try {
                const deptData = JSON.parse(this.dataset.dept || '{}');
                const staffList = JSON.parse(this.dataset.staff || '[]');
                openDepartmentDetailModal(deptData, staffList);
            } catch(err) {
                console.error('Dept Detail Parse Error:', err);
            }
        });
    });

    function openDepartmentDetailModal(deptData, staffList) {
        document.getElementById('md_dept_name').innerText = deptData.name || 'Daire Başkanlığı Detayı';
        document.getElementById('md_dept_title').innerText = deptData.name || 'Daire Başkanlığı Adı';
        document.getElementById('md_dept_code').innerText = deptData.code || '-';
        document.getElementById('md_dept_status').innerText = deptData.status || 'Aktif';
        document.getElementById('md_staff_count').innerText = (staffList ? staffList.length : 0) + ' Personel';

        const descBox = document.getElementById('md_description_box');
        if (deptData.description && deptData.description.trim()) {
            descBox.innerText = deptData.description;
            descBox.classList.remove('d-none');
        } else {
            descBox.classList.add('d-none');
        }

        const tbody = document.getElementById('md_staff_tbody');
        tbody.innerHTML = '';

        if (staffList && staffList.length > 0) {
            staffList.forEach(stf => {
                const fullName = stf.full_name || ((stf.first_name || '') + ' ' + (stf.last_name || ''));
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="fw-bold text-dark">${escapeHtml(fullName)}</td>
                    <td class="text-secondary">${escapeHtml(stf.title || '-')}</td>
                    <td class="text-muted fs-8 font-monospace">${escapeHtml(stf.unit || '-')}</td>
                    <td><span class="badge bg-success-subtle text-success border border-success-subtle">${escapeHtml(stf.status || 'Aktif')}</span></td>
                    <td class="text-end">
                        <a href="<?= base_url('personnelğsearch=') ?>${encodeURIComponent(stf.sicil_no || fullName)}" class="btn btn-outline-info btn-xs px-2 py-1" title="Personel Rehberinde Göster">
                            <i class="bi bi-eye me-1"></i> Göster
                        </a>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted fs-8">Bu müdürlüşe bağlı personel kaydı bulunmuyor.</td></tr>';
        }

        const viewBtn = document.getElementById('md_btn_view_personnel');
        if (viewBtn) {
            viewBtn.href = '<?= base_url('personnelğdepartment=') ?>' + encodeURIComponent(deptData.name || '');
        }

        const editBtn = document.getElementById('md_btn_edit_dept');
        if (editBtn) {
            editBtn.onclick = function() {
                const detailModalEl = document.getElementById('departmentDetailModal');
                const bsDetailModal = bootstrap.Modal.getInstance(detailModalEl);
                if (bsDetailModal) bsDetailModal.hide();

                document.getElementById('edit_dept_id').value = deptData.id || '';
                document.getElementById('edit_dept_code').value = deptData.code || '';
                document.getElementById('edit_dept_name').value = deptData.name || '';
                document.getElementById('edit_dept_description').value = deptData.description || '';
                document.getElementById('edit_dept_status').value = deptData.status || 'Aktif';

                const editModal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
                editModal.show();
            };
        }

        const modal = new bootstrap.Modal(document.getElementById('departmentDetailModal'));
        modal.show();
    }

    // Edit Department Modal Trigger
    document.querySelectorAll('.btn-edit-dept').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_dept_id').value = this.dataset.id;
            document.getElementById('edit_dept_code').value = this.dataset.code;
            document.getElementById('edit_dept_name').value = this.dataset.name;
            document.getElementById('edit_dept_description').value = this.dataset.description;
            document.getElementById('edit_dept_status').value = this.dataset.status;
            const modal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
            modal.show();
        });
    });

    // Edit Personnel Modal Trigger
    document.querySelectorAll('.btn-edit-personnel').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('p_edit_id').value = this.dataset.id;
            document.getElementById('p_edit_first_name').value = this.dataset.firstName;
            document.getElementById('p_edit_last_name').value = this.dataset.lastName;
            document.getElementById('p_edit_sicil').value = this.dataset.sicil;
            document.getElementById('p_edit_title').value = this.dataset.title;
            document.getElementById('p_edit_dept').value = this.dataset.dept;
            document.getElementById('p_edit_unit').value = this.dataset.unit;
            document.getElementById('p_edit_email').value = this.dataset.email;
            document.getElementById('p_edit_phone').value = this.dataset.phone;
            document.getElementById('p_edit_status').value = this.dataset.status;
            const modal = new bootstrap.Modal(document.getElementById('editPersonnelModal'));
            modal.show();
        });
    });

    // Personnel Detail Modal Trigger
    document.querySelectorAll('.btn-personnel-detail').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const body = document.getElementById('personnelDetailBody');
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
            const modal = new bootstrap.Modal(document.getElementById('personnelDetailModal'));
            modal.show();

            fetch('<?= base_url('personnel/detailğid=') ?>' + id)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const p = res.data;
                        const fullName = p.full_name || ((p.first_name || '') + ' ' + (p.last_name || ''));
                        const initials = (p.first_name ? p.first_name.substr(0,1) : 'P') + (p.last_name ? p.last_name.substr(0,1) : '');

                        body.innerHTML = `
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center me-3" style="width:48px; height:48px; font-size:1.1rem;">
                                    ${escapeHtml(initials)}
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">${escapeHtml(fullName)}</h5>
                                    <small class="text-muted fs-7">${escapeHtml(p.title || 'Memur')} &middot; ${escapeHtml(p.department || '')}</small>
                                </div>
                            </div>
                            
                            <table class="table table-sm table-borderless fs-7 mb-3">
                                <tr><th class="w-35 text-secondary">Kurum Sicil No:</th><td><span class="badge bg-secondary font-monospace">${escapeHtml(p.sicil_no || '-')}</span></td></tr>
                                <tr><th class="text-secondary">Birim / Şeflik:</th><td><code>${escapeHtml(p.unit || '-')}</code></td></tr>
                                <tr><th class="text-secondary">Oda / Lokasyon:</th><td>${escapeHtml(p.room_no || '-')}</td></tr>
                                <tr><th class="text-secondary">E-Posta:</th><td>${escapeHtml(p.email || '-')}</td></tr>
                                <tr><th class="text-secondary">Telefon:</th><td>${escapeHtml(p.phone || '-')}</td></tr>
                                <tr><th class="text-secondary">İşe Başlama:</th><td>${escapeHtml(p.hire_date || '-')}</td></tr>
                                <tr><th class="text-secondary">Görev Durumu:</th><td><span class="badge bg-success-subtle text-success border border-success-subtle">${escapeHtml(p.status || 'Aktif')}</span></td></tr>
                            </table>

                            <a href="<?= base_url('personnelğsearch=') ?>${encodeURIComponent(p.sicil_no || fullName)}" class="btn btn-outline-primary btn-sm w-100 fw-semibold d-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-people-fill"></i> Personel Rehberinde Görüntüle
                            </a>
                        `;
                    } else {
                        body.innerHTML = '<div class="alert alert-danger mb-0">Personel detayları yüklenemedi.</div>';
                    }
                })
                .catch(err => {
                    console.error('Personnel Detail Error:', err);
                    body.innerHTML = '<div class="alert alert-danger mb-0">Personel detayları alınırken bir hata oluştu.</div>';
                });
        });
    });
});

function validateDeptForm(form) {
    const nameInput = form.querySelector('input[name="name"]');
    if (!nameInput || !nameInput.value.trim()) {
        alert('Lütfen daire başkanlığı adını giriniz!');
        return false;
    }
    return true;
}
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>


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

            <!-- Page Title & Header Actions -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Kullanıcı ve Rol Yönetimi (RBAC)</h4>
                    <p class="text-muted mb-0 fs-7">Belediye Kullanıcı Hesapları, Rol Tanımları ve Modül Yetki Matrisi</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <button class="btn btn-primary btn-sm rounded-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#userFormModal" onclick="prepareAddUserForm()">
                        <i class="bi bi-person-plus"></i> Yeni Kullanıcı Hesabı Ekle
                    </button>
                </div>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
                    <ul class="nav nav-tabs card-header-tabs border-bottom-0" id="userTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'users') ? 'active fw-bold' : '' ?>" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-pane" type="button">
                                <i class="bi bi-person-workspace text-primary me-1"></i> Kullanıcı Hesapları (<?= count($usersList) ?>)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'roles') ? 'active fw-bold' : '' ?>" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-pane" type="button">
                                <i class="bi bi-shield-check text-success me-1"></i> Rol ve Yetki Matrisi (<?= count($rolesList) ?> Rol)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'smtp') ? 'active fw-bold' : '' ?>" id="smtp-tab" data-bs-toggle="tab" data-bs-target="#smtp-pane" type="button">
                                <i class="bi bi-envelope-at text-info me-1"></i> E-Posta / SMTP Ayarları
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content" id="userTabContent">
                        
                        <!-- TAB 1: KULLANICI HESAPLARI -->
                        <div class="tab-pane fade <?= ($activeTab === 'users') ? 'show active' : '' ?>" id="users-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table align-middle table-hover mb-0">
                                    <thead class="table-light fs-7 text-muted">
                                        <tr>
                                            <th>KULLANICI ADI</th>
                                            <th>AD SOYAD & UNVAN</th>
                                            <th>E-POSTA</th>
                                            <th>MÜDÜRLÜK</th>
                                            <th>ATANAN YETKİ ROLÜ</th>
                                            <th>DURUM</th>
                                            <th class="text-end pe-4">İŞLEMLER</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fs-7">
                                        <?php foreach ($usersList as $u): ?>
                                            <?php 
                                                $roleBadge = match($u['role_slug']) {
                                                    'admin'             => 'bg-danger text-white',
                                                    'it_staff'          => 'bg-primary text-white',
                                                    'warehouse_manager' => 'bg-warning text-dark',
                                                    'director'          => 'bg-info text-white',
                                                    default             => 'bg-secondary text-white'
                                                };
                                            ?>
                                            <tr>
                                                <td><span class="badge bg-light text-dark border font-monospace fs-7"><?= e($u['username']) ?></span></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></div>
                                                    <small class="text-muted fs-8"><?= e($u['title']) ?></small>
                                                </td>
                                                <td class="text-muted"><?= e($u['email']) ?></td>
                                                <td><span class="badge bg-light text-dark border"><?= e($u['department']) ?></span></td>
                                                <td><span class="badge <?= $roleBadge ?> px-2.5 py-1 rounded-pill"><?= e($u['role_name']) ?></span></td>
                                                <td>
                                                    <?php if ($u['status'] === 'active'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Kilitli</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-primary border-0" onclick='prepareEditUserForm(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' data-bs-toggle="modal" data-bs-target="#userFormModal" title="Düzenle">
                                                            <i class="bi bi-pencil fs-6"></i>
                                                        </button>
                                                        <?php if ($u['id'] !== $currentUser['id']): ?>
                                                            <button class="btn btn-outline-danger border-0" onclick="confirmDeleteUser(<?= $u['id'] ?>, '<?= e($u['username']) ?>')" title="Sil">
                                                                <i class="bi bi-trash fs-6"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: ROL VE YETKİ MATRİSİ -->
                        <div class="tab-pane fade <?= ($activeTab === 'roles') ? 'show active' : '' ?>" id="roles-pane" role="tabpanel">
                            <div class="p-3 bg-light border-bottom text-muted fs-7">
                                <i class="bi bi-info-circle me-1"></i> Her rolün erişebileceği modülleri aşağıdan düzenleyip kaydedebilirsiniz.
                            </div>
                            
                            <div class="p-4">
                                <div class="row g-4">
                                    <?php foreach ($rolesList as $role): ?>
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <div class="card border shadow-sm h-100 rounded-3">
                                                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                                    <h6 class="fw-bold mb-0 text-dark"><?= e($role['name']) ?></h6>
                                                    <span class="badge bg-dark font-monospace"><?= e($role['slug']) ?></span>
                                                </div>
                                                <form action="<?= base_url('roles/permissions') ?>" method="POST">
                                                    <input type="hidden" name="role_id" value="<?= $role['id'] ?>">
                                                    
                                                    <div class="card-body p-3">
                                                        <small class="text-muted d-block mb-3 fs-8"><?= e($role['description']) ?></small>
                                                        
                                                        <div class="fw-semibold fs-7 mb-2 text-dark border-bottom pb-1">Modül Erişim İzinleri:</div>
                                                        <div class="d-flex flex-column gap-2">
                                                            <?php foreach ($allModules as $modKey => $modLabel): ?>
                                                                <?php $hasPerm = in_array($modKey, $role['permissions'], true); ?>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $modKey ?>" id="perm_<?= $role['id'] ?>_<?= $modKey ?>" <?= $hasPerm ? 'checked' : '' ?> <?= $role['slug'] === 'admin' ? 'disabled' : '' ?>>
                                                                    <label class="form-check-label fs-7" for="perm_<?= $role['id'] ?>_<?= $modKey ?>">
                                                                        <?= e($modLabel) ?>
                                                                    </label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-light py-2 text-end">
                                                        <?php if ($role['slug'] !== 'admin'): ?>
                                                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Yetkileri Kaydet</button>
                                                        <?php else: ?>
                                                            <small class="text-muted fs-8">Admin tüm yetkilere sahiptir.</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: E-POSTA / SMTP AYARLARI -->
                        <div class="tab-pane fade <?= ($activeTab === 'smtp') ? 'show active' : '' ?>" id="smtp-pane" role="tabpanel">
                            <div class="p-4">
                                <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-gear-wide-connected text-primary me-2"></i>E-Posta & SMTP Yapılandırma Paneli</h5>
                                        <p class="text-muted mb-0 fs-7">Belediye e-posta sunucusu (SMTP) erişim ve gönderen bilgilerini buradan yapılandırabilirsiniz.</p>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-7">
                                        <i class="bi bi-shield-lock me-1"></i> Sadece Yönetici Erişimi
                                    </span>
                                </div>

                                <div id="smtpTestAlert" class="d-none"></div>

                                <form id="smtpSettingsForm" action="<?= base_url('settings/smtp/store') ?>" method="POST">
                                    <div class="row g-3">
                                        <!-- SMTP Host -->
                                        <div class="col-12 col-md-8">
                                            <label for="smtp_host" class="form-label fw-semibold fs-7 text-secondary">SMTP Sunucusu / Host *</label>
                                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?= e($smtpSettings['smtp_host'] ?? '') ?>" placeholder="Örn: smtp.gmail.com veya mail.belediye.bel.tr" required>
                                        </div>

                                        <!-- SMTP Port -->
                                        <div class="col-12 col-md-4">
                                            <label for="smtp_port" class="form-label fw-semibold fs-7 text-secondary">SMTP Port *</label>
                                            <input type="number" class="form-control font-monospace" id="smtp_port" name="smtp_port" value="<?= e($smtpSettings['smtp_port'] ?? '587') ?>" required>
                                        </div>

                                        <!-- SMTP Username -->
                                        <div class="col-12 col-md-6">
                                            <label for="smtp_username" class="form-label fw-semibold fs-7 text-secondary">SMTP Kullanıcı Adı *</label>
                                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?= e($smtpSettings['smtp_username'] ?? '') ?>" placeholder="Örn: envanter@belediye.bel.tr" required>
                                        </div>

                                        <!-- SMTP Password -->
                                        <div class="col-12 col-md-6">
                                            <label for="smtp_password" class="form-label fw-semibold fs-7 text-secondary">SMTP Parolası *</label>
                                            <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="<?= !empty($smtpSettings['smtp_password']) ? '********' : '' ?>" placeholder="SMTP Parolası" required>
                                        </div>

                                        <!-- Encryption -->
                                        <div class="col-12 col-md-4">
                                            <label for="smtp_encryption" class="form-label fw-semibold fs-7 text-secondary">Şifreleme Türü (Encryption) *</label>
                                            <?php $enc = strtolower($smtpSettings['smtp_encryption'] ?? 'tls'); ?>
                                            <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                                <option value="tls" <?= $enc === 'tls' ? 'selected' : '' ?>>TLS (Varsayılan - Port 587)</option>
                                                <option value="ssl" <?= $enc === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                                                <option value="none" <?= $enc === 'none' ? 'selected' : '' ?>>Yok (Güvenliksiz - Port 25)</option>
                                            </select>
                                        </div>

                                        <!-- Gönderen E-Posta Adresi -->
                                        <div class="col-12 col-md-4">
                                            <label for="smtp_from_address" class="form-label fw-semibold fs-7 text-secondary">Gönderen E-posta Adresi *</label>
                                            <input type="email" class="form-control" id="smtp_from_address" name="smtp_from_address" value="<?= e($smtpSettings['smtp_from_address'] ?? 'envanter@belediye.bel.tr') ?>" required>
                                        </div>

                                        <!-- Gönderen Adı -->
                                        <div class="col-12 col-md-4">
                                            <label for="smtp_from_name" class="form-label fw-semibold fs-7 text-secondary">Gönderen İsmi / Adı *</label>
                                            <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" value="<?= e($smtpSettings['smtp_from_name'] ?? 'T.C. Belediye Envanter Sistem Raporu') ?>" required>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                                        <button type="button" id="btnTestSmtp" class="btn btn-outline-info btn-sm px-3 fw-semibold">
                                            <i class="bi bi-plug me-1"></i> 🔌 Bağlantıyı Test Et
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                            <i class="bi bi-check-lg me-1"></i> Ayarları Kaydet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- 1. KULLANICI EKLE / DÜZENLE MODALI         -->
<!-- ========================================== -->
<div class="modal fade" id="userFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark-blue text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="uFormTitle"><i class="bi bi-person-plus me-2"></i>Yeni Kullanıcı Hesabı Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="userForm" action="<?= base_url('users/store') ?>" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="id" id="u_id" value="">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Kullanıcı Adı -->
                        <div class="col-12 col-md-6">
                            <label for="u_username" class="form-label fw-semibold fs-7 text-secondary">Kullanıcı Adı *</label>
                            <input type="text" class="form-control" id="u_username" name="username" placeholder="Örn: bt_ahmet" required>
                        </div>

                        <!-- E-Posta -->
                        <div class="col-12 col-md-6">
                            <label for="u_email" class="form-label fw-semibold fs-7 text-secondary">E-Posta Adresi *</label>
                            <input type="email" class="form-control" id="u_email" name="email" placeholder="ahmet@belediye.bel.tr" required>
                        </div>

                        <!-- Şifre -->
                        <div class="col-12 col-md-6">
                            <label for="u_password" class="form-label fw-semibold fs-7 text-secondary">Şifre</label>
                            <input type="password" class="form-control" id="u_password" name="password" placeholder="Yeni şifre (Varsayılan: admin123)">
                        </div>

                        <!-- Atanan Yetki Rolü -->
                        <div class="col-12 col-md-6">
                            <label for="u_role_id" class="form-label fw-semibold fs-7 text-secondary">Atanacak Yetki Rolü *</label>
                            <select class="form-select" id="u_role_id" name="role_id" required>
                                <?php foreach ($rolesList as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= e($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Ad -->
                        <div class="col-12 col-md-6">
                            <label for="u_first_name" class="form-label fw-semibold fs-7 text-secondary">Adı *</label>
                            <input type="text" class="form-control" id="u_first_name" name="first_name" placeholder="Örn: Ahmet" required>
                        </div>

                        <!-- Soyad -->
                        <div class="col-12 col-md-6">
                            <label for="u_last_name" class="form-label fw-semibold fs-7 text-secondary">Soyadı *</label>
                            <input type="text" class="form-control" id="u_last_name" name="last_name" placeholder="Örn: Yılmaz" required>
                        </div>

                        <!-- Müdürlük -->
                        <div class="col-12 col-md-6">
                            <label for="u_department" class="form-label fw-semibold fs-7 text-secondary">Müdürlük</label>
                            <select class="form-select" id="u_department" name="department">
                                <option value="Bilgi İşlem Müdürlüğü">Bilgi İşlem Müdürlüğü</option>
                                <option value="Yazı İşleri Müdürlüğü">Yazı İşleri Müdürlüğü</option>
                                <option value="İmar ve Şehircilik Müdürlüğü">İmar ve Şehircilik Müdürlüğü</option>
                                <option value="Fen İşleri Müdürlüğü">Fen İşleri Müdürlüğü</option>
                                <option value="Kültür ve Sosyal İşler">Kültür ve Sosyal İşler</option>
                                <option value="Destek Hizmetleri">Destek Hizmetleri</option>
                                <option value="Zabıta Müdürlüğü">Zabıta Müdürlüğü</option>
                                <option value="Mali Hizmetler">Mali Hizmetler</option>
                                <option value="İnsan Kaynakları">İnsan Kaynakları</option>
                            </select>
                        </div>

                        <!-- Hesap Durumu -->
                        <div class="col-12 col-md-6">
                            <label for="u_status" class="form-label fw-semibold fs-7 text-secondary">Hesap Durumu</label>
                            <select class="form-select" id="u_status" name="status">
                                <option value="active">Aktif</option>
                                <option value="inactive">Kilitli / Pasif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold" id="u_btnSubmit">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. KULLANICI SİLME ONAY MODALI             -->
<!-- ========================================== -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3"><i class="bi bi-exclamation-octagon fs-1"></i></div>
                <h6 class="fw-bold mb-2">Kullanıcı Hesabını Sil?</h6>
                <p class="text-muted fs-7 mb-4"><strong id="deleteUsername"></strong> kullanıcı adlı hesap sistemden silinecektir!</p>
                <form action="<?= base_url('users/delete') ?>" method="POST">
                    <input type="hidden" name="id" id="delete_user_id" value="">
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger btn-sm px-4 fw-semibold">Evet, Sil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function prepareAddUserForm() {
    document.getElementById('uFormTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Yeni Kullanıcı Hesabı Ekle';
    document.getElementById('userForm').action = '<?= base_url('users/store') ?>';
    document.getElementById('u_id').value = '';
    document.getElementById('u_username').value = '';
    document.getElementById('u_email').value = '';
    document.getElementById('u_password').value = '';
    document.getElementById('u_first_name').value = '';
    document.getElementById('u_last_name').value = '';
    document.getElementById('u_department').value = '';
    document.getElementById('u_btnSubmit').innerText = 'Kaydet';
}

function prepareEditUserForm(u) {
    document.getElementById('uFormTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Kullanıcı Hesabını Düzenle (' + u.username + ')';
    document.getElementById('userForm').action = '<?= base_url('users/update') ?>';
    document.getElementById('u_id').value = u.id;
    document.getElementById('u_username').value = u.username;
    document.getElementById('u_email').value = u.email;
    document.getElementById('u_password').value = '';
    document.getElementById('u_role_id').value = u.role_id;
    document.getElementById('u_first_name').value = u.first_name;
    document.getElementById('u_last_name').value = u.last_name;
    document.getElementById('u_department').value = u.department || 'Bilgi İşlem Müdürlüğü';
    document.getElementById('u_status').value = u.status;
    document.getElementById('u_btnSubmit').innerText = 'Güncelle';
}

function confirmDeleteUser(id, username) {
    document.getElementById('delete_user_id').value = id;
    document.getElementById('deleteUsername').innerText = username;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    deleteModal.show();
}
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

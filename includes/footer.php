    <footer class="footer bg-white border-top py-3 mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 small text-muted">
                <div>
                    &copy; <?= date('Y') ?> <strong>CampusStock</strong> — Üniversite Kırtasiye Stok &amp; Satış Platformu. Tüm Hakları Saklıdır.
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Kırtasiye v1.0</span>
                    <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted p-0" data-bs-toggle="modal" data-bs-target="#privacyInfoModal">Gizlilik &amp; Güvenlik</button>
                    <span>&middot;</span>
                    <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted p-0" data-bs-toggle="modal" data-bs-target="#helpGuideModal">Kullanım Kılavuzu</button>
                </div>
            </div>
        </div>
    </footer>

    <!-- Gizlilik & Güvenlik Modal -->
    <div class="modal fade" id="privacyInfoModal" tabindex="-1" aria-labelledby="privacyInfoTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ds-card border-0">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="privacyInfoTitle"><i class="bi bi-shield-check text-primary me-2"></i>Gizlilik ve Veri Güvenliği</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body small text-muted">
                    <p class="mb-2"><strong>CampusStock</strong>, kullanıcı parolalarını düz metin olarak saklamaz; tüm parolalar endüstri standardı <code>bcrypt</code> algoritması ile hash'lenir.</p>
                    <p class="mb-2">Oturum çerezleri <code>HttpOnly</code> ve <code>SameSite=Lax</code> bayraklarıyla korunur. API erişimleri süreli <code>Bearer Token</code> ile yetkilendirilir.</p>
                    <p class="mb-0">Form işlemleri CSRF doğrulaması, çıktı alanları ise XSS korumasıyla güvence altına alınmıştır.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Kullanım Kılavuzu Modal -->
    <div class="modal fade" id="helpGuideModal" tabindex="-1" aria-labelledby="helpGuideTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ds-card border-0">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="helpGuideTitle"><i class="bi bi-question-circle text-primary me-2"></i>Hızlı Kullanım Kılavuzu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body small text-muted">
                    <ul class="mb-0 ps-3 d-flex flex-column gap-2">
                        <li><strong>Hızlı Satış (POS):</strong> Barkod okutarak veya ürün seçerek sepet oluşturup nakit/kart/veresiye satış yapabilirsiniz.</li>
                        <li><strong>Stok Giriş/Çıkış:</strong> Sol menüdeki hızlı işlem butonlarıyla anında stok güncelleyebilirsiniz.</li>
                        <li><strong>Siparişler &amp; İadeler:</strong> Satış detayından iade alabilir, İade Talepleri ekranından onaylayabilirsiniz.</li>
                        <li><strong>Üniversite &amp; Kırtasiyeler:</strong> Kampüs şubelerini ve çalışma saatlerini yönetebilirsiniz.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" defer></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js" defer></script>

    <!-- Vanilla JS Custom Application Logic -->
    <script src="<?= asset('js/app.js') ?>?v=<?= filemtime(dirname(__DIR__) . '/public/assets/js/app.js') ?>" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TomSelect !== 'undefined') {
                document.querySelectorAll('.ds-select-search').forEach(function(el) {
                    new TomSelect(el, {
                        create: true,
                        sortField: { field: "text", direction: "asc" },
                        placeholder: el.getAttribute('placeholder') || 'Seçiniz...',
                    });
                });
            }
        });
    </script>
</body>
</html>

    <footer class="footer bg-white border-top py-3 mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small text-muted">
                <div>
                    &copy; <?= date('Y') ?> <strong>Stationery Flow</strong> - Kurumsal Envanter & Stok Takip Sistemi - Tüm Hakları Saklıdır.
                </div>
                <div>
                    <span class="badge bg-light text-dark border me-2">v1.0.0-alpha</span>
                    <a href="#" class="text-decoration-none text-muted me-2">Gizlilik Politikası</a> &middot; 
                    <a href="#" class="text-decoration-none text-muted ms-2">Destek Kılavuzu</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- SweetAlert2 for beautiful popups -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- QRCode.js for generating QR codes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Chart.js for Dashboard Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tom Select JS for searchable dropdowns -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <!-- Vanilla JS Custom Application Logic -->
    <script src="<?= asset('js/app.js') ?>?v=<?= filemtime(dirname(__DIR__) . '/public/assets/js/app.js') ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.ds-select-search').forEach(function(el) {
                new TomSelect(el, {
                    create: true, // Allow user to type something that is not in the list
                    sortField: { field: "text", direction: "asc" },
                    placeholder: el.getAttribute('placeholder') || 'Seçiniz...',
                });
            });
        });
    </script>
</body>
</html>

    <footer class="footer bg-white border-top py-3 mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small text-muted">
                <div>
                    &copy; <?= date('Y') ?> <strong>T.C. Belediye Destek Hizmetleri Müdürlüğü</strong> - Tüm Hakları Saklıdır.
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Vanilla JS Custom Application Logic -->
    <script src="<?= asset('js/app.js') ?>?v=<?= filemtime(dirname(__DIR__) . '/public/assets/js/app.js') ?>"></script>
</body>
</html>

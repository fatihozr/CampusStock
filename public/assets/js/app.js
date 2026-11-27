/**
 * Belediye Destek Birimi Envanter & Stok Takip Sistemi
 * Vanilla JavaScript App Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Collapse / Toggle Mechanism
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const body = document.body;

    // Load saved sidebar state from localStorage
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        body.classList.add('sidebar-collapsed');
    }

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', () => {
            body.classList.toggle('sidebar-collapsed');
            const isCollapsed = body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);
        });
    }

    // 2. Initialize Bootstrap Tooltips if any exist
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0 && typeof bootstrap !== 'undefined') {
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }

    // 3. Client-side Form Validation Example
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 4. Initialize Autocomplete on Select Controls
    initAutocomplete();
    document.addEventListener('shown.bs.modal', initAutocomplete);

    // 5. Initialize Global Top Bar Search Engine
    initGlobalHeaderSearch();

    // 6. Initialize Dark Mode Theme Manager
    initThemeManager();

    // 7. Initialize SweetAlert2 handlers
    initSweetAlertHandlers();

    // 8. Convert Flash Messages to SweetAlert Toasts
    convertFlashMessagesToToasts();

    // 9. Initialize Lightbox for Image Previews
    initLightbox();
});

/**
 * Initializes Lightbox functionality using SweetAlert2
 */
function initLightbox() {
    if (typeof Swal === 'undefined') return;

    document.querySelectorAll('.image-lightbox').forEach(img => {
        img.addEventListener('click', function() {
            Swal.fire({
                imageUrl: this.src,
                imageAlt: this.alt,
                title: this.alt,
                showConfirmButton: false,
                showCloseButton: true,
                customClass: {
                    image: 'img-fluid rounded shadow-sm'
                },
                width: 'auto',
                padding: '1em',
                background: 'rgba(255,255,255,0.95)'
            });
        });
    });
}

/**
 * Initializes SweetAlert2 confirmation popups
 */
function initSweetAlertHandlers() {
    if (typeof Swal === 'undefined') return;

    // Handle delete buttons or generic confirm actions
    document.querySelectorAll('.confirm-action, .btn-delete').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.href;
            const message = this.getAttribute('data-confirm-msg') || 'Bu kaydı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.';
            const formId = this.getAttribute('data-form-id');

            Swal.fire({
                title: 'Emin misiniz?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Evet, Sil!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (targetUrl && targetUrl !== 'javascript:void(0)') {
                        window.location.href = targetUrl;
                    } else if (formId) {
                        document.getElementById(formId).submit();
                    } else if (this.closest('form')) {
                        this.closest('form').submit();
                    }
                }
            });
        });
    });
}

/**
 * Converts PHP Flash Messages (Alerts) to SweetAlert Toasts
 */
function convertFlashMessagesToToasts() {
    if (typeof Swal === 'undefined') return;

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        let msg = successAlert.innerText.trim();
        Toast.fire({ icon: 'success', title: msg });
        successAlert.style.display = 'none';
    }

    const errorAlert = document.querySelector('.alert-danger');
    if (errorAlert) {
        let msg = errorAlert.innerText.trim();
        Toast.fire({ icon: 'error', title: msg });
        errorAlert.style.display = 'none';
    }
}


/**
 * Universal Autocomplete & Searchable Selection Component
 */
function initAutocomplete() {
    function trNormalize(str) {
        if (!str) return '';
        return str
            .replace(/Ğ/g, 'g').replace(/ğ/g, 'g')
            .replace(/Ü/g, 'u').replace(/ü/g, 'u')
            .replace(/Ş/g, 's').replace(/ş/g, 's')
            .replace(/İ/g, 'i').replace(/I/g, 'i').replace(/ı/g, 'i')
            .replace(/Ö/g, 'o').replace(/ö/g, 'o')
            .replace(/Ç/g, 'c').replace(/ç/g, 'c')
            .toLowerCase();
    }

    const searchableSelects = document.querySelectorAll('select.form-select-searchable, select[data-searchable="true"], select.form-select:not(.no-autocomplete)');
    
    searchableSelects.forEach(selectEl => {
        if (selectEl.dataset.autocompleteInitialized === 'true' || selectEl.options.length < 2) return;
        selectEl.dataset.autocompleteInitialized = 'true';

        const isRequired = selectEl.hasAttribute('required') || selectEl.required;
        if (isRequired) {
            selectEl.removeAttribute('required');
            selectEl.required = false;
        }

        const options = Array.from(selectEl.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            subtext: opt.getAttribute('data-subtext') || '',
            selected: opt.selected,
            disabled: opt.disabled
        }));

        selectEl.style.display = 'none';

        const wrapper = document.createElement('div');
        wrapper.className = 'autocomplete-wrapper position-relative w-100';

        const emptyOption = Array.from(selectEl.options).find(o => o.value === '');
        const dynamicPlaceholder = emptyOption ? emptyOption.text : 'Aramak veya seçmek için tıklayınız...';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = selectEl.className.replace('form-select-searchable', '').replace('form-select', '') + ' form-control form-control-sm autocomplete-input';
        input.placeholder = selectEl.getAttribute('data-placeholder') || dynamicPlaceholder;
        input.autocomplete = 'off';
        if (isRequired) {
            input.required = true;
        }

        let highlightedIndex = -1;

        const initialSelected = options.find(o => o.value === selectEl.value && o.value !== '');
        if (initialSelected) {
            input.value = initialSelected.text;
        } else {
            const defaultOpt = options.find(o => o.value === selectEl.value && o.value !== '');
            input.value = defaultOpt ? defaultOpt.text : '';
            if (!defaultOpt) {
                selectEl.value = '';
            }
        }

        const dropdown = document.createElement('div');
        dropdown.className = 'autocomplete-dropdown d-none shadow-sm rounded-2 border bg-white position-absolute w-100 mt-1';
        dropdown.style.zIndex = '1090';
        dropdown.style.maxHeight = '250px';
        dropdown.style.overflowY = 'auto';

        wrapper.appendChild(input);
        wrapper.appendChild(dropdown);
        selectEl.parentNode.insertBefore(wrapper, selectEl.nextSibling);

        function renderResults(query = '') {
            dropdown.innerHTML = '';
            highlightedIndex = -1;
            const normalizedQuery = trNormalize(query.trim());

            const filtered = options.filter(opt => {
                if (opt.value === '' && opt.text.includes('--')) return false; // Hide only explicit dashed placeholders
                if (!normalizedQuery) return true; // Show all choices if query is empty or on click
                const matchText = trNormalize(opt.text);
                const matchSub = trNormalize(opt.subtext);
                return matchText.includes(normalizedQuery) || matchSub.includes(normalizedQuery);
            });

            if (filtered.length === 0) {
                const noRes = document.createElement('div');
                noRes.className = 'p-2 text-muted fs-8 text-center';
                noRes.innerHTML = '<i class="bi bi-search me-1"></i> Eşleşen kayıt bulunamadı';
                dropdown.appendChild(noRes);
                return;
            }

            filtered.forEach((opt, idx) => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item px-3 py-2 border-bottom fs-8 cursor-pointer d-flex justify-content-between align-items-center';
                item.dataset.index = idx;
                if (opt.value === selectEl.value) {
                    item.classList.add('bg-primary-subtle', 'fw-bold', 'text-primary');
                }

                item.innerHTML = `
                    <div>
                        <div class="fw-semibold">${escapeHtml(opt.text)}</div>
                        ${opt.subtext ? `<small class="text-muted d-block">${escapeHtml(opt.subtext)}</small>` : ''}
                    </div>
                    ${opt.value === selectEl.value ? '<i class="bi bi-check2 text-primary"></i>' : ''}
                `;

                item.addEventListener('click', () => {
                    selectOption(opt);
                });

                dropdown.appendChild(item);
            });
        }

        function selectOption(opt) {
            selectEl.value = opt.value;
            input.value = opt.text;
            input.setCustomValidity('');
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
            dropdown.classList.add('d-none');
        }

        function escapeHtml(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Show all results when clicking or focusing on input
        function showAllDropdown() {
            renderResults('');
            dropdown.classList.remove('d-none');
        }

        input.addEventListener('focus', () => {
            showAllDropdown();
            input.select();
        });

        input.addEventListener('click', () => {
            if (dropdown.classList.contains('d-none')) {
                showAllDropdown();
            }
        });

        input.addEventListener('input', () => {
            renderResults(input.value);
            dropdown.classList.remove('d-none');
        });

        // Keyboard Navigation (ArrowUp, ArrowDown, Enter, Escape)
        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('.autocomplete-item');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlightedIndex = (highlightedIndex + 1) % items.length;
                updateHighlight(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlightedIndex = (highlightedIndex - 1 + items.length) % items.length;
                updateHighlight(items);
            } else if (e.key === 'Enter') {
                if (highlightedIndex >= 0 && items[highlightedIndex]) {
                    e.preventDefault();
                    items[highlightedIndex].click();
                }
            } else if (e.key === 'Escape') {
                dropdown.classList.add('d-none');
            }
        });

        function updateHighlight(items) {
            items.forEach((item, idx) => {
                if (idx === highlightedIndex) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Validate selection on blur: if user typed invalid string, restore previous selected text or clear
        input.addEventListener('blur', () => {
            setTimeout(() => {
                dropdown.classList.add('d-none');
                const selectedOpt = options.find(o => o.value === selectEl.value && o.value !== '');
                if (selectedOpt) {
                    input.value = selectedOpt.text;
                    input.setCustomValidity('');
                } else {
                    const typedText = input.value.trim();
                    const exactMatch = options.find(o => o.text.toLowerCase() === typedText.toLowerCase());
                    if (exactMatch) {
                        selectOption(exactMatch);
                    } else if (isRequired) {
                        selectEl.value = '';
                        input.value = '';
                        input.setCustomValidity('Lütfen listeden geçerli bir seçim yapınız.');
                        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }, 200);
        });

        // Form Submit Validation Handler
        const parentForm = selectEl.closest('form');
        if (parentForm) {
            parentForm.addEventListener('submit', (e) => {
                if (isRequired && (!selectEl.value || selectEl.value === '')) {
                    e.preventDefault();
                    input.setCustomValidity('Lütfen listeden geçerli bir seçim yapınız.');
                    input.reportValidity();
                    return false;
                } else {
                    input.setCustomValidity('');
                }
            });
        }

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.add('d-none');
            }
        });
    });
}

/**
 * Global Top Header Search Engine (with 300ms Debounce)
 */
function initGlobalHeaderSearch() {
    const searchInput = document.getElementById('globalSearchInput');
    const searchDropdown = document.getElementById('globalSearchDropdown');
    if (!searchInput || !searchDropdown) return;

    let debounceTimer = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim().toLowerCase();

        if (query.length < 2) {
            searchDropdown.classList.add('d-none');
            return;
        }

        debounceTimer = setTimeout(() => {
            const apiPath = (window.location.pathname.startsWith('/envanter') ? '/envanter/api/global-search' : '/api/global-search');
            fetch(apiPath + '?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    const totalMatches = (data.stocks?.length || 0) + 
                                         (data.personnel?.length || 0) +
                                         (data.users?.length || 0);

                    if (!data.success || totalMatches === 0) {
                        searchDropdown.innerHTML = '<div class="p-3 text-muted fs-8 text-center"><i class="bi bi-search me-1"></i> Eşleşen kayıt bulunamadı</div>';
                        searchDropdown.classList.remove('d-none');
                        return;
                    }

                    let html = '';

                    if (data.stocks && data.stocks.length) {
                        html += '<div class="px-3 py-1.5 bg-light fw-bold fs-8 text-success border-bottom"><i class="bi bi-boxes me-1"></i> STOKLAR</div>';
                        data.stocks.forEach(item => {
                            html += `
                                <a href="` + (window.location.pathname.startsWith('/envanter') ? '/envanter/stocks' : '/stocks') + `?search=${encodeURIComponent(item.code)}" class="dropdown-item py-2 border-bottom">
                                    <div class="fw-bold text-dark fs-8">${escapeHtml(item.name)}</div>
                                    <div class="text-muted fs-8 font-monospace"><span class="badge bg-dark text-white me-1">${escapeHtml(item.code)}</span> ${item.quantity} ${escapeHtml(item.unit)} • <span class="badge bg-secondary-subtle text-dark border">Raf: ${escapeHtml(item.rack_location)}</span></div>
                                </a>
                            `;
                        });
                    }

                    if (data.personnel && data.personnel.length) {
                        html += '<div class="px-3 py-1.5 bg-light fw-bold fs-8 text-info border-bottom"><i class="bi bi-people me-1"></i> PERSONEL</div>';
                        data.personnel.forEach(item => {
                            html += `
                                <a href="` + (window.location.pathname.startsWith('/envanter') ? '/envanter/personnel' : '/personnel') + `?search=${encodeURIComponent(item.sicil_no)}" class="dropdown-item py-2 border-bottom">
                                    <div class="fw-bold text-dark fs-8">${escapeHtml(item.full_name)}</div>
                                    <div class="text-muted fs-8 font-monospace"><span class="badge bg-info text-dark me-1">${escapeHtml(item.sicil_no)}</span> ${escapeHtml(item.department)}</div>
                                </a>
                            `;
                        });
                    }

                    searchDropdown.innerHTML = html;
                    searchDropdown.classList.remove('d-none');
                })
                .catch(() => {
                    searchDropdown.classList.add('d-none');
                });
        }, 300);
    });

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            searchDropdown.classList.add('d-none');
        }
    });
}

/**
 * Global Dark Mode / Theme Manager
 */
function initThemeManager() {
    const themeBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');

    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.classList.remove('bg-light');
            document.body.classList.add('bg-dark');
            if (themeIcon) {
                themeIcon.className = 'bi bi-sun-fill fs-5 text-warning';
            }
            if (themeBtn) {
                themeBtn.setAttribute('title', 'Açık Temaya Geç');
            }
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            document.documentElement.setAttribute('data-theme', 'light');
            document.body.classList.remove('bg-dark');
            document.body.classList.add('bg-light');
            if (themeIcon) {
                themeIcon.className = 'bi bi-moon-stars fs-5';
            }
            if (themeBtn) {
                themeBtn.setAttribute('title', 'Koyu Temaya Geç');
            }
        }
    }

    const currentTheme = localStorage.getItem('campusstock_theme') || localStorage.getItem('belediye_theme') || 'light';
    applyTheme(currentTheme);

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const activeTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('campusstock_theme', newTheme);
            applyTheme(newTheme);
        });
    }
}

// Form gönderimlerinde çift tıklamayı önleyen loading durumu
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || form.dataset.noLoading === 'true') return;
    if (form.classList.contains('needs-validation') && !form.checkValidity()) return;

    const btn = form.querySelector('button[type="submit"]');
    if (btn && !btn.disabled) {
        btn.dataset.originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>İşleniyor...';
    }
});


# Sunucu ve Dağıtım Rehberi (Deployment Guide)

## Proje: Belediye Bilgi İşlem Müdürlüğü Envanter ve Stok Takip Sistemi

---

## 🚀 Sunucu Gereksinimleri

- **İşletim Sistemi:** Windows Server / Linux (Ubuntu 22.04 LTS veya RHEL)
- **Web Sunucu:** Apache 2.4+ (`mod_rewrite` ve `mod_ssl` etkin)
- **PHP Sürümü:** PHP 8.2+ (Gerekli eklentiler: `pdo_mysql`, `mbstring`, `openssl`, `json`, `gd`)
- **Veritabanı:** MySQL 8.0+ / MariaDB 10.5+

---

## 🔧 Canlıya Alma Adımları

1. Kök dizindeki `.env` dosyasını sunucu ortamına göre yapılandırın.
2. `storage/logs/` ve `storage/uploads/` klasörlerine yazma izinlerini (Chmod 775 / Write permissions) tanımlayın.
3. Apache konfigürasyonunda `AllowOverride All` seçeneğinin aktif olduğundan emin olun.

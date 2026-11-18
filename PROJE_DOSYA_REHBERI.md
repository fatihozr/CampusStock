# 📂 Proje Dizin Yapısı ve Dosya Rehberi

Bu rehber, **Kırtasiye Stok ve Satış Yönetim Sistemi** içerisindeki klasörlerin, servislerin ve dosyaların işlevlerini detaylı Türkçe açıklamalarla sunmaktadır.

---

## 🗂️ 1. Ana Dizin Yapısı

- **`/app/`** : Uygulamanın MVC (Model-View-Controller) omurgası, iş kuralları, veritabanı modelleri ve yardımcı servisleri.
  - `Config/` : Uygulama ve veritabanı ayar sınıfları.
    - `Database.php` : MySQL ve SQLite PDO bağlantı yöneticisi.
    - `Router.php` : RESTful rota yönlendirici (GET, POST, PUT, DELETE, regex parametre desteği ve CORS yönetimi).
    - `App.php` : Ortam (.env) değişkenleri ve hata yakalama motoru.
  - `Controllers/` : Web ve arayüz denetleyicileri.
    - `PosController.php` : Kırtasiye barkodlu hızlı satış ekranı denetleyicisi.
    - `SaleController.php` : Satış geçmişi, fiş dökümü ve ürün iade kontrolcüsü.
    - `StockController.php` : Kırtasiye ürün kataloğu, stok giriş/çıkış ve sayım kontrolcüsü.
    - `CustomerController.php` : Müşteriler, öğrenciler, öğretmenler ve veresiye takibi.
    - `BrandController.php` : Kırtasiye marka yönetimi (Faber-Castell, Rotring, Adel vb.).
    - `CategoryController.php` : Kırtasiye kategori ağacı.
    - `DashboardController.php` : Yönetim paneli ve istatistik özetleri.
    - `AuthController.php` : Güvenli giriş/çıkış işlemleri.
    - `Api/` : Frontend geliştiricileri için RESTful JSON API denetleyicileri.
      - `BaseApiController.php` : Standart JSON yanıt formatı ve CORS desteği.
      - `ProductApiController.php` : Ürün listeleme, filtreleme, barkod ile bulma, stok düzenleme API'si.
      - `PosApiController.php` : Sepet hesaplama, checkout satış tamamlama, satış iade API'si.
      - `CategoryApiController.php` & `BrandApiController.php` : Kategori ve marka API'leri.
      - `CustomerApiController.php` : Müşteri arama ve cari tahsilat API'si.
      - `ReportApiController.php` : Günlük Z raporu ve çok satanlar API'si.
  - `Models/` : Veritabanı tabloları ve veri işleme modelleri.
    - `Product.php` : Kırtasiye ürünleri, barkod, fiyat, raf ve stok metotları.
    - `Sale.php` & `SaleItem.php` : Satış ve satılan kalem modelleri.
    - `Customer.php` : Müşteri, iskonto ve veresiye bakiye modeli.
    - `Brand.php` & `Category.php` : Marka ve kategori modelleri.
    - `StockMovement.php` : Tüm stok hareketlerinin kaydı (alış, satış, iade, fire).
    - `CashMovement.php` : Kasa hareketleri (nakit, kredi kartı giriş/çıkış).
    - `User.php` & `Role.php` : Kullanıcı ve rol yönetimi (RBAC).
  - `Services/` : İş mantığı motorları.
    - `PosService.php` : Atomik satış tamamlama, stok düşümü ve iade işleme motoru.
  - `Views/` : Arayüz PHP şablonları.
    - `pos/index.php` : Barkodlu hızlı satış ekranı.
    - `sales/index.php` & `detail.php` : Satış geçmişi ve termal fiş dökümü.
    - `customer/index.php` : Müşteriler ve cari tahsilat ekranı.
    - `brand/index.php` : Markalar ekranı.
    - `stock/` : Ürün kataloğu ve hareket detayları.
    - `auth/login.php` : Modern giriş ekranı.
  - `Helpers/` : Genel yardımcı fonksiyonlar ve kimlik doğrulama (`Auth.php`, `functions.php`).

- **`/veritabani_ve_kurulum/`** : Veritabanı kurulum şemaları ve tohumlayıcılar.
  - `kirtasiye_veritabani.sql` : Kırtasiye stok, satış, cari ve POS MySQL şeması.
  - `kirtasiye_ornek_veriler.sql` : 20+ gerçek kırtasiye ürünü, markalar, kategoriler ve örnek cariler.
  - `kirtasiye_kurulum.php` : Tek tıkla MySQL veya SQLite veritabanını kuran çalıştırıcı.

- **`/routes/`** :
  - `web.php` : Web ve `/api/v1/...` REST API rotaları.

- **`API_DOKUMANTASYONU_FRONTEND.md`** : Frontend ekibi için tüm endpoint'ler ve örnek istek/yanıt dökümü.
- **`index.php`** : Uygulamanın ana giriş noktası (Front Controller).

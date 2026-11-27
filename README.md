# 📚 CampusStock - Üniversiteye Özel Kırtasiye Sipariş ve Stok Yönetim Platformu

**CampusStock**, üniversite kampüslerinde öğrencilerin kendi üniversitelerindeki kırtasiyelerden ürün seçebildiği, ders notu ve PDF baskı/fotokopi talebi oluşturabildiği, sıra beklemeden Gel-Al (Pickup) koduyla siparişlerini teslim alabildiği; kampüs kırtasiye yöneticilerinin ise stoklarını, kasalarını ve satışlarını yönetebildiği hibrit mimarili bir platformdur.

Ayrıca harici Frontend (React, Vue, mobil) ekipleri için **kapsamlı RESTful JSON API** ve tam CORS desteği sunar.

---

## 🚀 Öne Çıkan Özellikler

1. **🎓 Çoklu Üniversite ve Kampüs Mağazaları:**
   - Üniversite ve kampüs bazında mağaza ve reyon yönetimi.
   - Her üniversitenin kendi öğrencilerine özel ürün listesi ve stok takibi.

2. **📦 Öğrenci Online Siparişleri & "Gel-Al" (Pickup) Motoru:**
   - Öğrenciler sepetlerini oluşturup çevrimiçi sipariş verir.
   - Sistem anında benzersiz bir **Gel-Al Teslimat Kodu** (Örn: `CS-1310`) üretir.
   - Kasiyer ekranından kod girilerek sipariş saniyeler içinde teslim edilir.

3. **🖨️ PDF & Belge Fotokopi/Baskı Hizmeti:**
   - Ders notları, vize özetleri ve tezler için sayfa sayısı, siyah-beyaz/renkli, önlü-arkalı ve spiral cilt seçenekleri.
   - Anlık dinamik maliyet hesaplama motoru ve hazır Gel-Al koduyla teslim alma.

4. **⚡ Barkodlu Hızlı Satış Terminali (POS):**
   - USB / Kablosuz barkod okuyucu desteği ile saniyeler içinde ürün okutma ve sepete ekleme.
   - Gerçek zamanlı dinamik sepet hesaplama (KDV oranları, ara toplam, iskonto).
   - Nakit, Kredi Kartı ve Veresiye / Açık Hesap ödeme yöntemleri.
   - Para üstü otomatik hesaplayıcı ve termal fiş yazdırma motoru.
   - Satış iadelerini kalem bazında alma ve otomatik stok geri yükleme.

5. **🔐 Bearer Token Tabanlı Güvenli API (Auth):**
   - Frontend ve mobil uygulamalar için `POST /api/v1/auth/login`, `register`, `me` ve `logout` uç noktaları.
   - `Authorization: Bearer <token>` standart kimlik denetimi.

6. **📊 Kasa & Gün Sonu (Z Raporu):**
   - Günlük ciro, kâr marjı, nakit/kredi kartı ayrımı.
   - En çok satan ürünler ve kritik stok seviyesi bildirimleri.

7. **💾 Hibrit Veritabanı Desteği (MySQL & SQLite):**
   - MySQL (Production/XAMPP) ve SQLite (Sıfır konfigürasyon taşınabilir yerel DB) tam uyumu.
   - `CampusStockDb.drawio` veritabanı şema tasarımı.

---

## 🛠️ Teknoloji Yığını

- **Backend:** PHP 8.2+ (MVC & RESTful Service Layer)
- **Veritabanı:** MySQL (InnoDB, utf8mb4) / SQLite Fallback
- **API Mimarisi:** RESTful JSON API (v1) & CORS Enabled
- **Ön Yüz (Varsayılan):** Bootstrap 5.3, Bootstrap Icons, Modern JavaScript
- **Kimlik Doğrulama:** Bearer API Token & Session Hibrit Doğrulama

---

## 📂 Proje Dizin Yapısı

```
├── app/
│   ├── Config/              # Veritabanı (PDO), REST Router ve Uygulama Ayarları
│   ├── Controllers/
│   │   ├── Api/             # Auth, Product, Pos, Store, University, Order, PrintJob, Customer
│   │   ├── PosController.php        # Barkodlu POS Hızlı Satış Ekranı
│   │   ├── SaleController.php       # Satış Geçmişi & Fiş Görüntüleme
│   │   ├── StockController.php      # Kırtasiye Ürün & Stok Yönetimi
│   │   └── AuthController.php       # Giriş / Çıkış İşlemleri
│   ├── Models/              # Product, Order, PrintJob, Store, University, ApiToken, Sale, User
│   ├── Services/            # PosService, OrderService, PrintJobService
│   ├── Helpers/             # Auth, functions, view yardımcıları
│   └── Views/               # POS terminali, Satış geçmişi, Ürünler, Cariler vb.
├── veritabani_ve_kurulum/
│   ├── kirtasiye_veritabani.sql      # Ana Veritabanı Şeması (MySQL)
│   ├── kirtasiye_ornek_veriler.sql   # Zengin Örnek Kırtasiye Verileri (Seeder)
│   └── kirtasiye_kurulum.php         # Veritabanını kurup başlatan script
├── routes/
│   └── web.php              # Web ve REST API Rotaları
├── tests/
│   └── test_api_endpoints.php        # Uçtan uca otomatik test süiti
├── CampusStockDb.drawio      # Veritabanı mimarisi ve ER diyagramı
├── API_DOKUMANTASYONU_FRONTEND.md   # Frontend geliştirici ekibi için tam API rehberi
└── index.php                # Front Controller (Uygulama Giriş Noktası)
```

---

## ⚡ Hızlı Kurulum & Çalıştırma

### 1. Depoyu Klonlayın veya Açın
```bash
git clone https://github.com/fatihozr/CampusStock.git
cd CampusStock
```

### 2. Yerel Sunucuyu Başlatın
```bash
php -S localhost:8000 router.php
```

### 3. Testleri Çalıştırın
```bash
php tests/test_api_endpoints.php
```

### 4. Giriş Bilgileri
- **Yönetici (Admin):** Kullanıcı Adı: `admin` | Şifre: `admin123`
- **Kasiyer:** Kullanıcı Adı: `kasiyer` | Şifre: `admin123`
- **Öğrenci:** Kullanıcı Adı: `ogrenci_ali` | Şifre: `admin123`

---

## 📖 API Dokümantasyonu
Frontend entegrasyonu için tüm uç noktalar, parametreler ve Fetch örnekleri [API_DOKUMANTASYONU_FRONTEND.md](API_DOKUMANTASYONU_FRONTEND.md) dosyasında eksiksiz listelenmiştir.

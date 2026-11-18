# 📚 Kırtasiye Stok ve Satış Yönetim Sistemi (Stationery POS & ERP)

Kırtasiye mağazaları, okul/ofis tedarikçileri ve e-ticaret siteleri için sıfırdan geliştirilmiş; barkodlu hızlı satış (POS), stok takibi, müşteri/öğretmen cari hesapları, veresiye yönetimi ve ön yüz (Frontend) ekipleri için kapsamlı RESTful JSON API altyapısı sunan modern web uygulaması.

---

## 🚀 Öne Çıkan Özellikler

1. **Barkodlu Hızlı Satış Terminali (POS):**
   - USB/Kablosuz barkod okuyucu desteği ile saniyeler içinde ürün okutma ve sepete ekleme.
   - Gerçek zamanlı dinamik sepet hesaplama (KDV oranları, ara toplam, iskonto).
   - Nakit, Kredi Kartı ve Veresiye / Açık Hesap ödeme yöntemleri.
   - Para üstü otomatik hesaplayıcı ve termal fiş / fatura yazdırma motoru.
   - Satış iadelerini kalem bazında alma ve otomatik stok geri yükleme.

2. **Kırtasiye Ürün & Kategori & Marka Mimarisi:**
   - Defter & Kağıt, Kalem & Yazı, Boya & Sanat, Ofis, Dosyalama, Çanta ve Geometri kategorileri.
   - Faber-Castell, Rotring, Adel, Bic, Stabilo, Gıpta, Leitz, Mas gibi sektörün öncü markaları.
   - Barkod (EAN-13), SKU kod, raf/reyon konumu (örn: `Reyon A-1 / Raf 2`), alış fiyatı, satış fiyatı, toptan fiyat ve KDV tanımları.
   - Minimum stok eşiği ile otomatik kritik stok uyarıları.

3. **Müşteri & Cari & Veresiye Takibi:**
   - Müşteri tipleri: Bireysel, Öğrenci, Öğretmen, Okul Aile Birliği, Kurumsal Firma.
   - Müşteri tipine göre otomatik tanımlı indirim oranları (Örn: Öğretmen %10, Okul %15).
   - Veresiye limiti ve anlık borç/alacak bakiye takibi ile tek tıkla tahsilat alma.

4. **Kasa & Gün Sonu (Z Raporu):**
   - Günlük toplam ciro, maliyet, brüt kâr ve kâr marjı hesaplamaları.
   - Nakit ve Kredi Kartı satışlarının ayrıştırılmış rapor dökümü.

5. **Frontend Ekipleri İçin RESTful JSON API (`/api/v1`):**
   - React, Vue, Next.js, mobil app veya özel frontend tasarlayan geliştiriciler için tam donanımlı JSON API endpoints.
   - Otomatik CORS desteği (`Access-Control-Allow-Origin: *`).
   - Detaylı kılavuz için [API_DOKUMANTASYONU_FRONTEND.md](API_DOKUMANTASYONU_FRONTEND.md) dosyasını inceleyin.

6. **Hibrit Veritabanı Desteği (MySQL & SQLite):**
   - MySQL (XAMPP / Production) ve SQLite (Hemen kurulup çalışabilen taşınabilir yerel DB) desteği.
   - Tek komutla sıfırdan kurulum ve zengin kırtasiye tohum verileri.

---

## 🛠️ Teknoloji Yığını

- **Backend:** PHP 8.2+ (Hızlı, temiz MVC & OOP Mimarisi)
- **Veritabanı:** MySQL (InnoDB, utf8mb4) / SQLite Fallback
- **API Mimarisi:** RESTful JSON API (v1)
- **Arayüz (Varsayılan):** Bootstrap 5.3, Bootstrap Icons, Vanilla JavaScript
- **Kimlik Doğrulama:** Güvenli Session & Bcrypt Şifreleme

---

## 📂 Proje Dizin Yapısı

```
├── app/
│   ├── Config/              # Veritabanı (PDO), REST Router ve Uygulama Ayarları
│   ├── Controllers/
│   │   ├── Api/             # Frontend için REST API Denetleyicileri (Product, Pos, Category, Brand, Customer, Report)
│   │   ├── PosController.php        # Barkodlu POS Hızlı Satış Ekranı
│   │   ├── SaleController.php       # Satış Geçmişi & Fiş Görüntüleme
│   │   ├── StockController.php      # Kırtasiye Ürün & Stok Yönetimi
│   │   ├── CustomerController.php   # Müşteriler & Cari Hesaplar
│   │   ├── BrandController.php      # Kırtasiye Markaları
│   │   └── AuthController.php       # Giriş / Çıkış İşlemleri
│   ├── Models/              # Product, Sale, SaleItem, Customer, Brand, Category, StockMovement, CashMovement
│   ├── Services/            # PosService.php (Atomik Satış & Stok Motoru)
│   ├── Helpers/             # Auth, functions, view yardımcıları
│   └── Views/               # POS terminali, Satış geçmişi, Ürünler, Cariler vb.
├── veritabani_ve_kurulum/
│   ├── kirtasiye_veritabani.sql      # Ana Veritabanı Şeması (MySQL)
│   ├── kirtasiye_ornek_veriler.sql   # Zengin Örnek Kırtasiye Verileri (Seeder)
│   └── kirtasiye_kurulum.php         # Tek tıkla veritabanını kurup başlatan script
├── routes/
│   └── web.php              # Web ve REST API Rotaları
├── public/                  # CSS, JS, font ve resim varlıkları
├── .env.example             # Ortam değişkenleri şablonu
├── API_DOKUMANTASYONU_FRONTEND.md  # Frontend geliştirici ekibi için tam API rehberi
└── index.php                # Front Controller (Uygulama Giriş Noktası)
```

---

## ⚡ Hızlı Kurulum

### 1. Dosyaları Yerleştirin
Projeyi sunucunuza veya XAMPP `htdocs` klasörüne kopyalayın.

### 2. Veritabanını Kurun
İster komut satırından:
```bash
php veritabani_ve_kurulum/kirtasiye_kurulum.php
```
İster tarayıcınızdan:
```
http://localhost/kurulum
```
adresine girerek tüm tabloları ve örnek kırtasiye ürünlerini tek tıkla yükleyebilirsiniz.

### 3. Giriş Bilgileri
- **Yönetici (Admin):** Kullanıcı Adı: `admin` | Şifre: `admin123`
- **Kasiyer:** Kullanıcı Adı: `kasiyer` | Şifre: `admin123`
- **Depo Sorumlusu:** Kullanıcı Adı: `depo` | Şifre: `admin123`

---

## 📄 Lisans
Bu proje özel kırtasiye stok ve satış yönetim projesi olarak geliştirilmiştir.

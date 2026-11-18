# Belediye Destek Birimi Envanter ve Stok Takip Sistemi

T.C. Belediye Destek Hizmetleri Müdürlüğü için özel olarak tasarlanmış; envanter, demirbaş, stok takibi, zimmet işlemleri ve birim yönetimi sağlayan nesne yönelimli (OOP) web uygulaması altyapısı.

---

## 🛠️ Kullanılan Teknolojiler

- **PHP 8.2+** (Framework'süz, Saf MVC & OOP Mimarisi)
- **Apache (XAMPP)** / HTTP Server
- **MySQL / PDO** (Sürücü Bağlantı Katmanı)
- **Bootstrap 5.3** & **Bootstrap Icons** (Responsive Arayüz)
- **Vanilla JavaScript** (İstemci Tarafı Etkileşimleri)
- **Composer** (PSR-4 Autoloading)

---

## 📁 Proje Klasör Yapısı

```
Belediye-envanter 1/
├── app/
│   ├── Config/          # Uygulama ve Veritabanı (PDO, Router, App) Yapılandırmaları
│   ├── Controllers/     # İstekleri Karşılayan Kontrolcü Sınıfları (AuthController, DashboardController)
│   ├── Helpers/         # Genel Yardımcı Fonksiyonlar (view, asset, redirect, e vb.)
│   ├── Models/          # Veri Modelleri (Gelecekteki DB Nesneleri)
│   ├── Repositories/    # Veri Erişim Katmanı (Veritabanı Sorguları)
│   ├── Services/        # İş Mantığı Servis Sınıfları
│   └── Views/           # Arayüz Görünümleri (auth/login, dashboard/index)
├── includes/            # Sayfa Bileşenleri (header, navbar, sidebar, footer)
├── modules/             # Gelecekteki Modüler Eklentiler İçin Dizin
├── public/
│   └── assets/
│       ├── css/         # Özel Temalandırma (style.css)
│       ├── js/          # Uygulama JS Kodları (app.js)
│       └── img/         # Logo ve Görseller
├── routes/              # Rota Tanımları (web.php)
├── scripts/             # Kurulum ve CLI Kodları
├── storage/
│   ├── logs/            # Sistem Hata ve Hareket Logları
│   └── uploads/         # Yüklenen Dosyalar ve Dokümanlar
├── .env.example         # Ortam Değişkenleri Şablonu
├── .htaccess            # Apache Yönlendirme Kuralları
├── composer.json        # Otomatik Yükleme Yapılandırması
├── index.php            # Ön Yönlendirici (Front Controller)
└── README.md            # Proje Dokümantasyonu
```

---

## 🚀 Kurulum ve Çalıştırma

1. **XAMPP / Apache Dizini:**
   Proje dosyalarını `c:/xampp/htdocs/Belediye-envanter 1/` dizinine yerleştirin.

2. **Composer Autoload:**
   Terminal veya Komut Satırında proje kök dizininde aşağıdaki komutu çalıştırın:
   ```bash
   composer dump-autoload
   ```
   *(Not: Composer olmasa dahi `index.php` dahili PSR-4 yedek yükleyicisi sayesinde uygulama doğrudan çalışır.)*

3. **Ortam Dosyası:**
   `.env.example` dosyasının bir kopyasını oluşturarak `.env` adıyla kaydedin ve veritabanı bilgilerinizi ayarlayın.

4. **Tarayıcıda Erişim:**
   Apache servisini başlattıktan sonra tarayıcınızdan:
   - **Gösterge Paneli:** `http://localhost/Belediye-envanter%201/`
   - **Giriş Ekranı:** `http://localhost/Belediye-envanter%201/login`

---

## ⚙️ Mimarisi ve Gelecek Geliştirmeler

- **CRUD İşlemleri:** Gelecekteki aşamada `Repositories` ve `Services` sınıfları üzerinden PDO kullanılarak yazılacaktır.
- **SQL / Veritabanı Tabloları:** `scripts/` dizinine eklenecek migration scriptleri ile oluşturulacaktır.
- **Modülerlik:** Yeni modüller `modules/` veya `app/` dizini altına standart MVC kurallarına uyarak kolayca entegre edilebilir.

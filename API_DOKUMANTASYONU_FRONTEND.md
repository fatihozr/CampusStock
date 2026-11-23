# 📚 Kırtasiye Stok ve Satış Sistemi - RESTful API Dokümantasyonu (Frontend Ekibi İçin)

Bu doküman, frontend (React, Vue, Next.js, mobil veya HTML5/JavaScript) geliştirecek ekip arkadaşımız için **Kırtasiye Stok ve Satış Yönetim Sistemi** arka plan servislerini (Backend REST API) eksiksiz olarak açıklamaktadır.

---

## 🌐 1. Genel Bilgiler ve Bağlantı Kuralları

- **Base URL:** `http://localhost/api/v1` (veya `http://localhost:8000/api/v1`)
- **İçerik Türü (Content-Type):** `application/json; charset=utf-8`
- **CORS Desteği:** Sistemde otomatik CORS aktiftir (`Access-Control-Allow-Origin: *`, `GET, POST, PUT, DELETE, OPTIONS`). Frontend'i `localhost:3000` (React) veya `localhost:5173` (Vite/Vue) üzerinde çalıştırdığınızda herhangi bir CORS engeline takılmazsınız.
- **Standart Yanıt Formatı (JSON):**
  Tüm uç noktalar tutarlı bir JSON yapısıyla döner:

```json
{
  "success": true,
  "message": "İşlem başarılı açıklama mesajı",
  "data": { ... },
  "timestamp": "2026-11-18T13:40:00+03:00"
}
```

Hata durumunda:
```json
{
  "success": false,
  "message": "Hatanın açıklaması",
  "errors": null,
  "timestamp": "2026-11-18T13:40:00+03:00"
}
```

---

## 📦 2. Ürün ve Stok API'leri (`/products`)

### 2.1 Tüm Ürünleri Listele & Filtrele
- **Metot:** `GET`
- **Endpoint:** `/api/v1/products`
- **Query Parametreleri (İsteğe Bağlı):**
  - `q` veya `search`: İsim, barkod, marka veya ürün koduna göre arama (Örn: `?q=faber` veya `?q=8690846001015`)
  - `category_id`: Belirli bir kategorideki ürünler (Örn: `?category_id=2`)
  - `brand_id`: Belirli bir markadaki ürünler (Örn: `?brand_id=1`)
  - `low_stock`: Kritik stok seviyesinin altına inen ürünler (`?low_stock=true`)
  - `limit`: Sayfa başı kayıt (Varsayılan: `100`)
  - `offset`: Sayfalama başlangıcı (Varsayılan: `0`)

**Başarılı Yanıt Örneği (`200 OK`):**
```json
{
  "success": true,
  "message": "Ürünler başarıyla listelendi",
  "data": {
    "products": [
      {
        "id": 1,
        "code": "KRT-FC-001",
        "barcode": "8690846001015",
        "name": "Faber-Castell Grip 2011 0.7mm Versatil Kalem - Gece Mavisi",
        "category_name": "Mekanik & Versatil Kalemler",
        "brand_name": "Faber-Castell",
        "unit": "Adet",
        "purchase_price": 95.00,
        "sale_price": 165.00,
        "wholesale_price": 130.00,
        "tax_rate": 20.00,
        "current_stock": 48.00,
        "min_stock": 10.00,
        "shelf_location": "Reyon A-1 / Raf 2",
        "stock_status_label": "Yeterli",
        "badge_class": "bg-success-subtle text-success"
      }
    ],
    "total": 20,
    "limit": 100,
    "offset": 0
  }
}
```

---

### 2.2 Barkod ile Hızlı Ürün Getir (POS Barkod Okuyucu İçin)
- **Metot:** `GET`
- **Endpoint:** `/api/v1/products/barcode/{barcode}`
- **Açıklama:** Barkod tarayıcı tetiğe bastığında okunan barkodu anında bu endpoint'e göndererek ürün nesnesini alırsınız.
- **Örnek:** `GET /api/v1/products/barcode/8690846001015`

---

### 2.3 Tekil Ürün Detayı
- **Metot:** `GET`
- **Endpoint:** `/api/v1/products/{id}`

---

### 2.4 Yeni Ürün Ekle
- **Metot:** `POST`
- **Endpoint:** `/api/v1/products`
- **Request Body (JSON):**
```json
{
  "name": "Rotring Tikky 0.7mm Mekanik Kalem Siyah",
  "barcode": "8690846001999",
  "code": "KRT-ROT-099",
  "category_id": 12,
  "brand_id": 2,
  "unit": "Adet",
  "purchase_price": 110.00,
  "sale_price": 185.00,
  "wholesale_price": 145.00,
  "tax_rate": 20.00,
  "current_stock": 50.00,
  "min_stock": 10.00,
  "shelf_location": "Reyon A-1 / Raf 3"
}
```

---

### 2.5 Ürün Güncelle
- **Metot:** `PUT` (veya `POST /api/v1/products/update`)
- **Endpoint:** `/api/v1/products/{id}`

---

### 2.6 Ürünü Sil / Pasife Al
- **Metot:** `DELETE`
- **Endpoint:** `/api/v1/products/{id}`

---

### 2.7 Hızlı Stok Artırma / Azaltma (Manuel Sayım & Giriş/Çıkış)
- **Metot:** `POST`
- **Endpoint:** `/api/v1/products/adjust-stock`
- **Request Body (JSON):**
```json
{
  "product_id": 1,
  "quantity": 10,
  "movement_type": "purchase",
  "notes": "Toptancıdan yeni koli girişi"
}
```
*(Çıkış yapmak için quantity değerini negatif gönderin: `quantity: -2`)*

---

## 🏷️ 3. Kategoriler ve Markalar API'si

### 3.1 Kategorileri Listele
- **Metot:** `GET`
- **Endpoint:** `/api/v1/categories` (Hiyerarşik ağaç için: `?tree=true`)

### 3.2 Markaları Listele
- **Metot:** `GET`
- **Endpoint:** `/api/v1/brands`

---

## 🛒 4. Hızlı Satış (POS) ve Sepet Motoru API'leri

### 4.1 Sepet Hesaplama (Canlı Hesaplama)
Frontend'de sepete ürün eklendikçe veya müşteri değiştirildikçe bu servise sepet dizisi gönderilir. Servis tüm ara toplamları, müşteri iskonto oranını, KDV dağılımını hesaplayıp döner.

- **Metot:** `POST`
- **Endpoint:** `/api/v1/pos/calculate`
- **Request Body:**
```json
{
  "items": [
    { "barcode": "8690846001015", "quantity": 2 },
    { "product_id": 4, "quantity": 1 }
  ],
  "customer_id": 1,
  "discount_rate": 0
}
```
**Dönen Yanıt:**
```json
{
  "success": true,
  "data": {
    "customer": { "id": 1, "full_name": "Caner Yıldız", "type_label": "Öğrenci" },
    "applied_discount_rate": 5,
    "items": [ ... ],
    "subtotal": 395.00,
    "tax_total": 62.54,
    "tax_breakdown": { "20": 62.54 },
    "discount_total": 19.75,
    "grand_total": 375.25
  }
}
```

---

### 4.2 Satışı Tamamla (Checkout & Fiş Kesme)
Satışı onaylar, stoktan atomik olarak düşer, stok hareketini kaydeder, kasa hareketini işler ve termal fiş çıktısı için JSON üretir.

- **Metot:** `POST`
- **Endpoint:** `/api/v1/pos/checkout`
- **Request Body:**
```json
{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 4, "quantity": 1 }
  ],
  "customer_id": 1,
  "discount_rate": 5,
  "payment_method": "cash",
  "paid_amount": 400.00,
  "sale_type": "pos",
  "notes": "Öğrenci nakit satışı"
}
```
*`payment_method` seçenekleri:* `'cash'` (Nakit), `'credit_card'` (Kredi Kartı), `'open_account'` (Veresiye), `'bank_transfer'` (Havale).

**Dönen Yanıt:**
```json
{
  "success": true,
  "message": "Satış başarıyla tamamlandı ve fiş oluşturuldu",
  "data": {
    "sale_id": 3,
    "receipt_no": "KRT-20261118-0003",
    "grand_total": 375.25,
    "paid_amount": 400.00,
    "change_amount": 24.75,
    "receipt": {
      "receipt_no": "KRT-20261118-0003",
      "customer_display": "Caner Yıldız",
      "cashier_display": "Elif Demir",
      "payment_label": "Nakit",
      "items": [ ... ]
    }
  }
}
```

---

### 4.3 Geçmiş Satışlar & Fiş Detayı
- **Metot:** `GET`
- **Endpoint:** `/api/v1/pos/sales` (Filtreler: `?status=completed&date_start=2026-11-01`)
- **Detay:** `/api/v1/pos/sales/{id}`

### 4.4 Satış İadesi (Ürün İade Al)
- **Metot:** `POST`
- **Endpoint:** `/api/v1/pos/sales/{id}/return`
- **Request Body:**
```json
{
  "sale_item_id": 1,
  "quantity": 1,
  "reason": "Müşteri yanlış renk almış, ambalajı sağlam"
}
```

---

## 👥 5. Müşteriler / Cariler API'si

### 5.1 Müşterileri Listele & Ara
- **Metot:** `GET`
- **Endpoint:** `/api/v1/customers` (Filtreler: `?q=ahmet`, `?has_balance=true`, `?type=teacher`)

### 5.2 Yeni Müşteri Kaydet
- **Metot:** `POST`
- **Endpoint:** `/api/v1/customers`
- **Request Body:**
```json
{
  "customer_type": "teacher",
  "first_name": "Seda",
  "last_name": "Aksoy",
  "company_name": "Mehmet Akif Ortaokulu",
  "phone": "05321114477",
  "email": "seda.aksoy@meb.k12.tr",
  "discount_rate": 10.00,
  "credit_limit": 2500.00
}
```

### 5.3 Veresiye / Cari Tahsilatı Al
- **Metot:** `POST`
- **Endpoint:** `/api/v1/customers/{id}/payment`
- **Request Body:**
```json
{
  "amount": 500.00,
  "payment_method": "cash",
  "notes": "Ekim ayı kırtasiye borç kapatması"
}
```

---

## 📊 6. Raporlar API'si

- **Günlük Z Raporu / Ciro:** `GET /api/v1/reports/daily-z-report?date=2026-11-18`
- **En Çok Satan Ürünler:** `GET /api/v1/reports/top-selling?limit=10`
- **Kritik Stok Uyarısı:** `GET /api/v1/reports/critical-stock`
- **Stok Envanter Değeri:** `GET /api/v1/reports/inventory-valuation`

---

## 🔐 7. Kimlik Doğrulama & Kullanıcı API'si (`/auth`)

Tüm kullanıcılar ve öğrenciler bu API üzerinden oturum açar. Oturum açıldığında bir `Bearer` token döndürülür. Korumalı uç noktalara istek atarken bu token `Authorization: Bearer <token>` başlığıyla iletilmelidir.

### 7.1 Giriş Yap (Login)
- **Metot:** `POST`
- **Endpoint:** `/api/v1/auth/login`
- **Request Body:**
```json
{
  "username": "kasiyer",
  "password": "admin123"
}
```
*(veya `"email": "admin@kirtasiye.local"` kullanılabilir)*

**Başarılı Yanıt Örneği (`200 OK`):**
```json
{
  "success": true,
  "message": "Giriş başarılı",
  "data": {
    "token": "cs_f48e50c668127d0fb154405c07e813fc27b0aeb5443b210cbf575d10842d9b40",
    "token_type": "Bearer",
    "expires_at": "2026-12-23 19:58:25",
    "user": {
      "id": 2,
      "username": "kasiyer",
      "email": "kasiyer@campusstock.com",
      "first_name": "Ayşe",
      "last_name": "Demir",
      "phone": "05552223344",
      "title": "Kırtasiye Kasiyeri",
      "role_id": 4,
      "role_slug": "cashier",
      "role_name": "Kasiyer"
    }
  }
}
```

### 7.2 Öğrenci / Müşteri Kaydı (Register)
- **Metot:** `POST`
- **Endpoint:** `/api/v1/auth/register`
- **Request Body:**
```json
{
  "username": "ogrenci_ali",
  "email": "ali.kaya@itu.edu.tr",
  "password": "GucluSifre123!",
  "first_name": "Ali",
  "last_name": "Kaya",
  "phone": "05441234567"
}
```

### 7.3 Aktif Kullanıcı Profilini Getir (Me)
- **Metot:** `GET`
- **Endpoint:** `/api/v1/auth/me`
- **Header:** `Authorization: Bearer <token>`

### 7.4 Çıkış Yap (Logout)
- **Metot:** `POST`
- **Endpoint:** `/api/v1/auth/logout`
- **Header:** `Authorization: Bearer <token>`

---

## 🎓 8. Üniversiteler & Kampüsler API'si (`/universities`)

Öğrenciler uygulamaya girdiğinde kendi üniversitelerini ve kampüslerini seçer.

### 8.1 Üniversiteleri Listele
- **Metot:** `GET`
- **Endpoint:** `/api/v1/universities`

### 8.2 Üniversite Detayı
- **Metot:** `GET`
- **Endpoint:** `/api/v1/universities/{id}`

### 8.3 Üniversiteye Ait Mağazaları Listele
- **Metot:** `GET`
- **Endpoint:** `/api/v1/universities/{id}/stores`

---

## 🏪 9. Kampüs Mağazaları API'si (`/stores`)

Kampüs içinde yer alan kırtasiye mağazalarını listeler.

### 9.1 Mağazaları Listele
- **Metot:** `GET`
- **Endpoint:** `/api/v1/stores`
- **Query Parametreleri:** `?university_id=1`

### 9.2 Mağaza Detayı
- **Metot:** `GET`
- **Endpoint:** `/api/v1/stores/{id}`

### 9.3 Mağazadaki Ürünleri Listele (Vitrin / Arama)
- **Metot:** `GET`
- **Endpoint:** `/api/v1/stores/{id}/products`
- **Query Parametreleri:** `?search=defter`, `?category_id=1`, `?page=1`

---

## 📦 10. Öğrenci Online Siparişleri & Gel-Al Motoru (`/orders`)

Öğrenciler ders aralarında sıraya girmemek için sepetlerindeki ürünleri sipariş verir, kendilerine verilen kısa **Gel-Al Teslimat Kodu (Örn: `CS-1310`)** ile kırtasiyeden teslim alırlar.

### 10.1 Yeni Sipariş Oluştur (Gel-Al Checkout)
- **Metot:** `POST`
- **Endpoint:** `/api/v1/orders`
- **Header:** `Authorization: Bearer <token>` *(isteğe bağlı, misafir öğrenci için varsayılan öğrenci kullanılır)*
- **Request Body:**
```json
{
  "store_id": 1,
  "payment_method": "cash_pickup",
  "notes": "Ders çıkışı 14:00 gibi gelip teslim alacağım.",
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 5,
      "quantity": 1
    }
  ]
}
```

**Başarılı Yanıt Örneği (`201 Created`):**
```json
{
  "success": true,
  "message": "Siparişiniz başarıyla alındı. Kampüs kırtasiyesinden Gel-Al kodunuzla teslim alabilirsiniz.",
  "data": {
    "id": 12,
    "order_no": "ORD-20261123-5D521",
    "student_id": 1,
    "store_id": 1,
    "total_amount": 330.00,
    "discount_amount": 0.00,
    "final_amount": 330.00,
    "payment_status": "pending",
    "payment_method": "cash_pickup",
    "order_status": "received",
    "pickup_code": "CS-1310",
    "notes": "Ders çıkışı 14:00 gibi gelip teslim alacağım.",
    "items": [
      {
        "id": 24,
        "product_id": 1,
        "product_name": "Faber-Castell Grip 2011",
        "quantity": 2.0,
        "unit_price": 165.00,
        "line_total": 330.00
      }
    ]
  }
}
```

### 10.2 Gel-Al Teslimat Kodunu Doğrula (Kasiyer Ekranı İçin)
Kasiyer öğrencinin söylediği kodu girdiğinde sipariş açılır:
- **Metot:** `POST`
- **Endpoint:** `/api/v1/orders/verify-pickup`
- **Request Body:**
```json
{
  "pickup_code": "CS-1310"
}
```

### 10.3 Siparişleri Listele & Filtrele
- **Metot:** `GET`
- **Endpoint:** `/api/v1/orders` (Öğrenci sadece kendi siparişlerini görür; Kasiyer/Yönetici tüm mağaza siparişlerini listeler)
- **Query Parametreleri:** `?status=received`, `?store_id=1`

### 10.4 Sipariş Durumunu Güncelle (Kasiyer / Görevli)
- **Metot:** `PUT` veya `POST`
- **Endpoint:** `/api/v1/orders/{id}/status`
- **Header:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "status": "ready"
}
```
*(Durumlar: `received`, `preparing`, `ready`, `delivered`, `cancelled`)*

---

## 🖨️ 11. Fotokopi & Belge Baskı Hizmeti API'si (`/print-jobs`)

Öğrenciler slaytları, PDF ders notlarını veya tezlerini sistemden yükleyip baskı talebi oluşturabilirler.

### 11.1 Güncel Baskı Fiyat Tarifesini Getir
Frontend'de dinamik fiyat hesaplayıcı bileşeni için kullanılır:
- **Metot:** `GET`
- **Endpoint:** `/api/v1/print-jobs/pricing`

**Yanıt:**
```json
{
  "success": true,
  "data": {
    "bw": { "single_sided": 1.0, "double_sided": 1.5 },
    "color": { "single_sided": 5.0, "double_sided": 8.0 },
    "binding": { "none": 0.0, "staple": 5.0, "spiral": 35.0 },
    "currency": "TRY"
  }
}
```

### 11.2 Canlı Baskı Maliyeti Hesapla (Dry-run)
- **Metot:** `POST`
- **Endpoint:** `/api/v1/print-jobs/calculate`
- **Request Body:**
```json
{
  "page_count": 40,
  "color_mode": "bw",
  "double_sided": 1,
  "binding": "spiral",
  "copies": 1
}
```
**Yanıt:**
```json
{
  "success": true,
  "data": {
    "page_count": 40,
    "sheet_count": 20,
    "copies": 1,
    "color_mode": "bw",
    "double_sided": true,
    "binding": "spiral",
    "print_cost_per_copy": 30.00,
    "binding_cost": 35.00,
    "total_price": 65.00,
    "currency": "TRY"
  }
}
```

### 11.3 Baskı Talebi Gönder
- **Metot:** `POST`
- **Endpoint:** `/api/v1/print-jobs`
- **Request Body:**
```json
{
  "store_id": 1,
  "document_name": "Diferansiyel Denklemler Vize Notları.pdf",
  "page_count": 40,
  "color_mode": "bw",
  "double_sided": 1,
  "binding": "spiral",
  "copies": 1,
  "notes": "Arka kapak şeffaf olsun lütfen"
}
```

---

## 💻 12. Frontend Entegrasyon Örneği (JavaScript / Fetch)

```javascript
const API_URL = "http://localhost:8000/api/v1";

// 1. Giriş yapma ve Token saklama:
async function login(username, password) {
    const res = await fetch(`${API_URL}/auth/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password })
    });
    const result = await res.json();
    if (result.success) {
        localStorage.setItem("token", result.data.token);
        localStorage.setItem("user", JSON.stringify(result.data.user));
    }
}

// 2. Token ile Korumalı İstek Atma:
async function getMyProfile() {
    const token = localStorage.getItem("token");
    const res = await fetch(`${API_URL}/auth/me`, {
        headers: { "Authorization": `Bearer ${token}` }
    });
    return await res.json();
}

// 3. Gel-Al Siparişi Oluşturma:
async function submitCampusOrder(storeId, cartItems) {
    const token = localStorage.getItem("token");
    const res = await fetch(`${API_URL}/orders`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify({
            store_id: storeId,
            payment_method: "cash_pickup",
            items: cartItems.map(item => ({ product_id: item.id, quantity: item.qty }))
        })
    });
    const data = await res.json();
    if (data.success) {
        alert(`Siparişiniz alındı! Gel-Al Teslimat Kodunuz: ${data.data.pickup_code}`);
    }
}
```


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

## 💻 7. Frontend Entegrasyon Örneği (JavaScript / Fetch)

```javascript
// Barkod okuyucu tetiklendiğinde veya arama yapıldığında:
async function handleBarcodeScan(barcode) {
    const res = await fetch(`http://localhost/api/v1/products/barcode/${barcode}`);
    const json = await res.json();
    if (json.success) {
        console.log("Bulunan Ürün:", json.data);
        // Sepete ekle state'ini tetikle
    }
}

// Satış tamamlama:
async function submitCheckout(cartItems, customerId, paymentType) {
    const response = await fetch('http://localhost/api/v1/pos/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            items: cartItems,
            customer_id: customerId,
            payment_method: paymentType
        })
    });
    const result = await response.json();
    if (result.success) {
        alert("Satış Fişi No: " + result.data.receipt_no);
        // Fiş yazdırma penceresini aç
    }
}
```

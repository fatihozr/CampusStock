# API ve Servis Mimari Dokümanı (API Documentation)

## Proje: Belediye Bilgi İşlem Müdürlüğü Envanter ve Stok Takip Sistemi

---

## 1. Genel Bilgiler
Bu doküman, sistem içi AJAX / RESTful JSON servisleri ve harici entegrasyon uç noktalarını tanımlar.

---

## 2. Planlanan Servis Uç Noktaları (Endpoints)

- `GET /api/inventory/search` - Seri No veya Barkod ile hızlı envanter arama
- `GET /api/stock/low-levels` - Kritik seviyedeki sarf malzemeleri
- `POST /api/assignment/create` - Hızlı zimmet atama servisi
- `GET /api/reports/export` - PDF ve Excel rapor döküm servisleri

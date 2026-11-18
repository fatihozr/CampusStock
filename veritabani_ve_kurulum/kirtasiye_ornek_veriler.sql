-- ========================================================
-- KIRTASİYE SİSTEMİ ÖRNEK VE BAŞLANGIÇ VERİLERİ (SEEDER)
-- ========================================================

-- 1. ROLLER
INSERT INTO roles (id, slug, name, description, permissions) VALUES
(1, 'admin', 'Sistem Yöneticisi', 'Tam yetkili kırtasiye mağaza yöneticisi', '["dashboard","inventory","stocks","stock_in","stock_out","pos","sales","customers","categories","brands","reports","users","settings"]'),
(2, 'mudur', 'Mağaza Müdürü', 'Stok, satış, raporlar ve cari yönetimi yetkileri', '["dashboard","inventory","stocks","stock_in","stock_out","pos","sales","customers","categories","brands","reports"]'),
(3, 'kasiyer', 'Kasiyer / Satış Temsilcisi', 'POS hızlı satış, ürün arama ve müşteri kaydı yetkileri', '["dashboard","pos","sales","inventory","customers"]'),
(4, 'depo', 'Depo & Mal Kabul Sorumlusu', 'Stok giriş/çıkış, sayım ve irsaliye kabul yetkileri', '["dashboard","inventory","stocks","stock_in","stock_out"]')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 2. KULLANICILAR (Şifreler: admin123)
INSERT INTO users (id, role_id, username, email, password_hash, first_name, last_name, phone, title, status) VALUES
(1, 1, 'admin', 'admin@kirtasiye.local', '$2y$10$cJg6MRVf0V6kXHoy1VJpWe6m/hZJ9yIk8NtRFc9zdVbfwR4LyOaLK', 'Ahmet', 'Yönetici', '05551112233', 'Genel Koordinatör', 'active'),
(2, 3, 'kasiyer', 'kasiyer@kirtasiye.local', '$2y$10$cJg6MRVf0V6kXHoy1VJpWe6m/hZJ9yIk8NtRFc9zdVbfwR4LyOaLK', 'Elif', 'Demir', '05552223344', 'Kasa Sorumlusu', 'active'),
(3, 4, 'depo', 'depo@kirtasiye.local', '$2y$10$cJg6MRVf0V6kXHoy1VJpWe6m/hZJ9yIk8NtRFc9zdVbfwR4LyOaLK', 'Murat', 'Kaya', '05553334455', 'Depo Sorumlusu', 'active')
ON DUPLICATE KEY UPDATE first_name=VALUES(first_name);

-- 3. KIRTASİYE KATEGORİLERİ
INSERT INTO categories (id, parent_id, name, slug, description, icon, is_active, sort_order) VALUES
(1, NULL, 'Defter & Kağıt Ürünleri', 'defter-kagit', 'Okul, ofis, spiralli ve ciltli defter çeşitleri ile kağıt grubu', 'bi-journal-bookmark', 1, 1),
(2, NULL, 'Kalem & Yazı Gereçleri', 'kalem-yazi-gerecleri', 'Kurşun, tükenmez, versatil, fosforlu ve dolma kalemler', 'bi-pen', 1, 2),
(3, NULL, 'Boya & Sanatsal Malzemeler', 'boya-sanatsal', 'Kuru boya, sulu boya, pastel ve akrilik boya çeşitleri', 'bi-palette', 1, 3),
(4, NULL, 'Ofis & Masaüstü Gereçleri', 'ofis-masaustu', 'Zımba, delgeç, masaüstü organizer, makas ve yapıştırıcılar', 'bi-briefcase', 1, 4),
(5, NULL, 'Dosyalama & Arşivleme', 'dosyalama-arsivleme', 'Klasörler, telli dosyalar, poşet dosyalar ve ayraçlar', 'bi-folder', 1, 5),
(6, NULL, 'Okul & Çanta / Beslenme', 'okul-canta', 'Sırt çantaları, beslenme çantaları, kalem kutuları', 'bi-backpack', 1, 6),
(7, NULL, 'Geometri & Teknik Çizim', 'geometri-teknik-cizim', 'Cetveller, pergel takımları, iletki ve gönyeler', 'bi-rulers', 1, 7),
(8, NULL, 'Hobi & El Becerileri', 'hobi-el-becerileri', 'Fon kartonları, eva, elişi kağıtları, oyun hamurları', 'bi-scissors', 1, 8),
-- Alt Kategoriler
(9, 1, 'Kareli Defterler', 'kareli-defterler', 'A4 ve A5 boyutunda kareli okul defterleri', 'bi-grid-3x3', 1, 9),
(10, 1, 'Çizgili Defterler', 'cizgili-defterler', 'A4 ve A5 boyutunda çizgili defterler', 'bi-card-text', 1, 10),
(11, 1, 'Fotokopi Kağıtları', 'fotokopi-kagitlari', '80 gr A4, A3 ve renkli fotokopi kağıtları', 'bi-file-earmark-text', 1, 11),
(12, 2, 'Mekanik & Versatil Kalemler', 'versatil-kalemler', '0.5, 0.7, 0.9 mm uçlu kalemler ve minler', 'bi-pencil', 1, 12),
(13, 2, 'Tükenmez & Jel Kalemler', 'tukenmez-jel-kalemler', 'Mavi, siyah, kırmızı jel ve tükenmez kalemler', 'bi-pencil-fill', 1, 13),
(14, 2, 'Fosforlu & İşaretleme Kalemleri', 'fosforlu-kalemler', 'Vurgulayıcı ve textmarker kalem setleri', 'bi-highlighter', 1, 14)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 4. KIRTASİYE MARKALARI
INSERT INTO brands (id, name, slug, country, website, is_active) VALUES
(1, 'Faber-Castell', 'faber-castell', 'Almanya', 'https://www.faber-castell.com.tr', 1),
(2, 'Rotring', 'rotring', 'Almanya', 'https://www.rotring.com', 1),
(3, 'Adel', 'adel', 'Türkiye', 'https://www.adel.com.tr', 1),
(4, 'Stabilo', 'stabilo', 'Almanya', 'https://www.stabilo.com', 1),
(5, 'BIC', 'bic', 'Fransa', 'https://www.bicworld.com', 1),
(6, 'Gıpta', 'gipta', 'Türkiye', 'https://www.gipta.com.tr', 1),
(7, 'Keskin Color', 'keskin-color', 'Türkiye', 'https://www.keskincolor.com', 1),
(8, 'Leitz', 'leitz', 'Almanya', 'https://www.leitz.com', 1),
(9, 'Mas', 'mas', 'Türkiye', 'https://www.mas.com.tr', 1),
(10, 'Pritt', 'pritt', 'Almanya', 'https://www.prittworld.com.tr', 1),
(11, 'Uhu', 'uhu', 'Almanya', 'https://www.uhu.com', 1),
(12, 'Maped', 'maped', 'Fransa', 'https://www.maped.com', 1),
(13, 'Tombow', 'tombow', 'Japonya', 'https://www.tombow.com', 1),
(14, 'Pensan', 'pensan', 'Türkiye', 'https://www.pensan.com.tr', 1),
(15, 'Scrikss', 'scrikss', 'Türkiye', 'https://www.scrikss.com.tr', 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 5. KIRTASİYE ÜRÜNLERİ (Barkodlu, Alış-Satış Fiyatlı, Raf Konumlu)
INSERT INTO products (id, category_id, brand_id, code, barcode, name, description, unit, purchase_price, sale_price, wholesale_price, tax_rate, current_stock, min_stock, shelf_location, status, is_featured) VALUES
(1, 12, 1, 'KRT-FC-001', '8690846001015', 'Faber-Castell Grip 2011 0.7mm Versatil Kalem - Gece Mavisi', 'Ergonomik üçgen gövde, kaymayan tutuş noktaları, yaylı metal klips', 'Adet', 95.00, 165.00, 130.00, 20.00, 48.00, 10.00, 'Reyon A-1 / Raf 2', 'active', 1),
(2, 12, 2, 'KRT-ROT-001', '8690846001022', 'Rotring Tikky 0.5mm Mekanik Kurşun Kalem - Bordo', 'Plastik gövde, ergonomik kauçuk tutaç, sabit uç borusu', 'Adet', 110.00, 185.00, 145.00, 20.00, 36.00, 8.00, 'Reyon A-1 / Raf 3', 'active', 1),
(3, 14, 4, 'KRT-STB-001', '8690846001039', 'Stabilo Boss Original Pastel Fosforlu Kalem 4\'lü Set', '4 saat kurumaya karşı koruma, 2 ve 5 mm çizgi kalınlığı, pastel tonlar', 'Set', 85.00, 145.00, 115.00, 20.00, 52.00, 12.00, 'Reyon A-2 / Raf 1', 'active', 1),
(4, 9, 6, 'KRT-GPT-001', '8690846001046', 'Gıpta A4 80 Yaprak Spiralli Plastik Kapak Kareli Defter', 'Kaliteli 70 gr beyaz kağıt, kırılmaz polipropilen kapak, perforeli sayfalar', 'Adet', 35.00, 65.00, 50.00, 20.00, 120.00, 25.00, 'Reyon B-1 / Raf 1', 'active', 1),
(5, 10, 6, 'KRT-GPT-002', '8690846001053', 'Gıpta A4 80 Yaprak Spiralli Plastik Kapak Çizgili Defter', 'Kaliteli 70 gr kağıt, dayanıklı plastik kapak, çizgili okul defteri', 'Adet', 35.00, 65.00, 50.00, 20.00, 95.00, 25.00, 'Reyon B-1 / Raf 2', 'active', 0),
(6, 11, 6, 'KRT-NAV-001', '8690846001060', 'Navigator Universal A4 80 gr Fotokopi Kağıdı (500 Yaprak)', 'Yüksek beyazlık, lazer ve mürekkep püskürtmeli yazıcılarla uyumlu 1 top', 'Paket', 125.00, 180.00, 155.00, 20.00, 85.00, 20.00, 'Reyon B-3 / Zemin', 'active', 1),
(7, 3, 3, 'KRT-ADL-001', '8690846001077', 'Adel 12 Renk Tam Boy Kuru Boya Kalemi Karton Kutu', 'Parlak renkler, kolay açılan yumuşak uç, kırılmaya dayanıklı kurşun', 'Kutu', 45.00, 75.00, 60.00, 20.00, 64.00, 15.00, 'Reyon C-1 / Raf 2', 'active', 1),
(8, 3, 1, 'KRT-FC-002', '8690846001084', 'Faber-Castell 12 Renk Sulu Boya Seti + Fırça Hediyeli', 'Zengin pigmentli, su ile kolayca çözünen canlı renk tabletleri', 'Set', 65.00, 110.00, 88.00, 20.00, 40.00, 10.00, 'Reyon C-1 / Raf 3', 'active', 0),
(9, 4, 10, 'KRT-PRT-001', '8690846001091', 'Pritt 22 gr Stick Yapıştırıcı Solvent İçermez', 'Kağıt, karton ve kumaş için ideal, solvent içermeyen, yıkanabilir güvenli formül', 'Adet', 26.00, 45.00, 36.00, 20.00, 110.00, 20.00, 'Reyon D-1 / Raf 1', 'active', 1),
(10, 4, 11, 'KRT-UHU-001', '8690846001107', 'UHU Sıvı Çok Amaçlı Yapıştırıcı 35 ml', 'Ahşap, kağıt, kumaş, deri ve keçe yapıştırma için güçlü tüp yapıştırıcı', 'Adet', 30.00, 52.00, 42.00, 20.00, 75.00, 15.00, 'Reyon D-1 / Raf 1', 'active', 0),
(11, 5, 8, 'KRT-LTZ-001', '8690846001114', 'Leitz 180 Derece Geniş Plastik Klasör - Mavi (8 cm)', 'Patentli 180 derece mekanizma, 600 sayfa kapasiteli arşiv klasörü', 'Adet', 55.00, 95.00, 78.00, 20.00, 60.00, 15.00, 'Reyon E-1 / Raf 2', 'active', 0),
(12, 5, 9, 'KRT-MAS-001', '8690846001121', 'Mas A4 100\'lü Şeffaf Poşet Dosya 45 Mikron', 'Statik elektrik tutmayan, fotokopi çekilebilir, A4 standart delikli poşet', 'Paket', 40.00, 70.00, 55.00, 20.00, 150.00, 30.00, 'Reyon E-2 / Raf 1', 'active', 1),
(13, 13, 14, 'KRT-PEN-001', '8690846001138', 'Pensan My-Tech 0.7mm Tükenmez Kalem - Mavi (50\'li Kutu)', 'İsviçre tungsten karbid bilye uç, akıcı ve lekesiz yazım sağlayan mürekkep', 'Kutu', 180.00, 290.00, 240.00, 20.00, 25.00, 5.00, 'Reyon A-3 / Raf 1', 'active', 1),
(14, 13, 5, 'KRT-BIC-001', '8690846001145', 'BIC Cristal 1.0mm Mavi Klasik Tükenmez Kalem Tekli', 'Dünyanın 1 numaralı tükenmez kalemi, şeffaf gövde, 3 km yazım uzunluğu', 'Adet', 6.00, 12.00, 9.00, 20.00, 350.00, 50.00, 'Reyon A-3 / Kasa Yanı', 'active', 1),
(15, 7, 12, 'KRT-MAP-001', '8690846001152', 'Maped Geometrik Çizim Takımı 4 Parça (Cetvel, Gönye, İletki)', 'Kırılmaz esnek plastikten üretilmiş 30 cm cetvel, 2 gönye ve 1 iletki', 'Set', 42.00, 75.00, 58.00, 20.00, 45.00, 10.00, 'Reyon F-1 / Raf 2', 'active', 0),
(16, 4, 9, 'KRT-MAS-002', '8690846001169', 'Mas No:10 Zımba Makinesi + 1000\'li Zımba Teli Hediyeli', 'Metal gövdeli, 15 sayfa zımbalama kapasiteli kompakt masaüstü zımba', 'Set', 48.00, 85.00, 68.00, 20.00, 32.00, 6.00, 'Reyon D-2 / Raf 2', 'active', 0),
(17, 12, 1, 'KRT-FC-003', '8690846001176', 'Faber-Castell 0.7mm 2B Kalem Ucu Min (12\'li Tüp)', 'Kırılmaya karşı dirençli yüksek polimer grafit uç, koyu ve yumuşak yazım', 'Adet', 12.00, 22.00, 16.00, 20.00, 220.00, 40.00, 'Reyon A-1 / Raf 1', 'active', 1),
(18, 2, 1, 'KRT-FC-004', '8690846001183', 'Faber-Castell Dust-Free Siyah Silgi (Kırıntı Bırakmaz)', 'Kağıdı yıpratmayan, tortuları bir arada toplayan yüksek performanslı silgi', 'Adet', 10.00, 18.00, 14.00, 20.00, 180.00, 30.00, 'Reyon A-2 / Raf 3', 'active', 1),
(19, 8, 3, 'KRT-ADL-002', '8690846001190', 'Adel 10 Renk 100 gr Kaliteli Oyun Hamuru Seti', 'Doğal buğday unundan üretilmiş, kurumayan, çocuk sağlığına uygun hamur', 'Set', 55.00, 95.00, 75.00, 20.00, 28.00, 8.00, 'Reyon G-1 / Raf 1', 'active', 0),
(20, 6, 6, 'KRT-GPT-003', '8690846001206', 'Gıpta Geniş Çift Bölmeli Fermuarlı Okul Kalemliği - Lacivert', 'Dayanıklı su geçirmez kumaş, 50 kalem kapasitesi, çift fermuarlı göz', 'Adet', 75.00, 135.00, 105.00, 20.00, 3.00, 8.00, 'Reyon F-2 / Raf 1', 'active', 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 6. MÜŞTERİLER / CARİLER
INSERT INTO customers (id, customer_type, company_name, first_name, last_name, phone, email, address, tax_office, tax_number, discount_rate, credit_limit, balance, status, notes) VALUES
(1, 'student', NULL, 'Caner', 'Yıldız', '05441112233', 'caner.yildiz@gmail.com', 'Bahçelievler Mah. No:12 İstanbul', NULL, NULL, 5.00, 500.00, 0.00, 'active', 'Üniversite öğrencisi, öğrenci indirimli'),
(2, 'teacher', NULL, 'Merve', 'Kaya', '05332223344', 'merve.ogretmen@meb.k12.tr', 'Cumhuriyet Cad. Öğretmen Lojmanları', NULL, NULL, 10.00, 1500.00, 0.00, 'active', 'Atatürk İlkokulu Sınıf Öğretmeni, %10 özel indirim'),
(3, 'school', 'Atatürk İlkokulu Okul Aile Birliği', 'Ahmet', 'Müdür', '02123334455', 'ataturk.ilkokulu@meb.k12.tr', 'Merkez Mah. Okul Sok. No:1 Kadıköy/İstanbul', 'Kadıköy', '1234567890', 15.00, 25000.00, 3200.00, 'active', 'Toplu kırtasiye ve fotokopi kağıdı alımı yapar'),
(4, 'corporate', 'Akademi Mühendislik ve Mimarlık Ltd.', 'Selin', 'Öztürk', '02124445566', 'muhasebe@akademimuh.com', 'Maslak İş Kuleleri B Blok K:5 Sarıyer/İstanbul', 'Maslak', '9876543210', 12.00, 15000.00, 1450.00, 'active', 'Aylık ofis ve teknik çizim kırtasiye cari hesabı'),
(5, 'individual', NULL, 'Zeynep', 'Demir (Veli)', '05325556677', 'zeynep.demir@hotmail.com', 'Moda Cad. No:45 Kadıköy/İstanbul', NULL, NULL, 0.00, 1000.00, 0.00, 'active', 'Düzenli veli müşterisi')
ON DUPLICATE KEY UPDATE first_name=VALUES(first_name);

-- 7. TEDARİKÇİLER / DAĞITICILAR
INSERT INTO suppliers (id, company_name, contact_person, phone, email, address, tax_office, tax_number, balance, status) VALUES
(1, 'Barkod Dağıtım ve Kırtasiye Pazarlama A.Ş.', 'Hakan Bey', '02125550101', 'siparis@barkodkirtasiye.com', 'İstoç Toptancılar Çarşısı 12. Ada No:45 Bağcılar/İstanbul', 'İkitelli', '1122334455', 0.00, 'active'),
(2, 'Hedef Toptan Okul ve Ofis Gereçleri Ltd.', 'Kemal Güler', '02125550202', 'info@hedeftoptan.com', 'Topkapı Matbaacılar Sitesi 4. Blok No:88 Zeytinburnu/İstanbul', 'Topkapı', '5566778899', 0.00, 'active'),
(3, 'Nezih Kitap & Kırtasiye Dağıtım', 'Ali Çetin', '02165550303', 'dagitim@nezihkirtasiye.com', 'Dudullu Organize Sanayi Bölgesi 2. Cad. Ümraniye/İstanbul', 'Ümraniye', '9988776655', 0.00, 'active')
ON DUPLICATE KEY UPDATE company_name=VALUES(company_name);

-- 8. ÖRNEK SATIŞLAR (POS Fişleri)
INSERT INTO sales (id, receipt_no, customer_id, user_id, sale_type, payment_method, subtotal, tax_total, discount_total, grand_total, paid_amount, change_amount, status, notes, created_at) VALUES
(1, 'KRT-20261118-0001', 1, 2, 'pos', 'cash', 230.00, 38.33, 11.50, 218.50, 250.00, 31.50, 'completed', 'Öğrenci indirimi uygulandı', NOW() - INTERVAL 2 HOUR),
(2, 'KRT-20261118-0002', 2, 2, 'pos', 'credit_card', 325.00, 54.17, 32.50, 292.50, 292.50, 0.00, 'completed', 'Öğretmen kartı ile ödendi', NOW() - INTERVAL 1 HOUR)
ON DUPLICATE KEY UPDATE receipt_no=VALUES(receipt_no);

-- 9. SATIŞ KALEMLERİ
INSERT INTO sale_items (id, sale_id, product_id, product_name, barcode, unit, quantity, unit_price, cost_price, tax_rate, tax_amount, discount_amount, line_total, returned_quantity) VALUES
(1, 1, 1, 'Faber-Castell Grip 2011 0.7mm Versatil Kalem - Gece Mavisi', '8690846001015', 'Adet', 1.00, 165.00, 95.00, 20.00, 27.50, 8.25, 156.75, 0.00),
(2, 1, 4, 'Gıpta A4 80 Yaprak Spiralli Plastik Kapak Kareli Defter', '8690846001046', 'Adet', 1.00, 65.00, 35.00, 20.00, 10.83, 3.25, 61.75, 0.00),
(3, 2, 6, 'Navigator Universal A4 80 gr Fotokopi Kağıdı (500 Yaprak)', '8690846001060', 'Paket', 1.00, 180.00, 125.00, 20.00, 30.00, 18.00, 162.00, 0.00),
(4, 2, 3, 'Stabilo Boss Original Pastel Fosforlu Kalem 4\'lü Set', '8690846001039', 'Set', 1.00, 145.00, 85.00, 20.00, 24.17, 14.50, 130.50, 0.00)
ON DUPLICATE KEY UPDATE product_name=VALUES(product_name);

-- 10. ÖRNEK STOK HAREKETLERİ
INSERT INTO stock_movements (product_id, movement_type, quantity, prev_stock, next_stock, unit_cost, total_cost, reference_type, reference_id, notes, created_by, created_at) VALUES
(1, 'purchase', 50.00, 0.00, 50.00, 95.00, 4750.00, 'purchase', 'IRS-2026-001', 'Barkod Dağıtım açılış mal alımı', 1, NOW() - INTERVAL 1 DAY),
(1, 'sale', -1.00, 50.00, 49.00, 95.00, 95.00, 'sale', 'KRT-20261118-0001', 'Kasiyer POS satışı', 2, NOW() - INTERVAL 2 HOUR),
(4, 'purchase', 125.00, 0.00, 125.00, 35.00, 4375.00, 'purchase', 'IRS-2026-001', 'Açılış toptan defter girişi', 1, NOW() - INTERVAL 1 DAY),
(4, 'sale', -1.00, 125.00, 124.00, 35.00, 35.00, 'sale', 'KRT-20261118-0001', 'Kasiyer POS satışı', 2, NOW() - INTERVAL 2 HOUR),
(6, 'purchase', 100.00, 0.00, 100.00, 125.00, 12500.00, 'purchase', 'IRS-2026-002', 'Fotokopi kağıdı toptan alımı', 1, NOW() - INTERVAL 1 DAY),
(6, 'sale', -1.00, 100.00, 99.00, 125.00, 125.00, 'sale', 'KRT-20261118-0002', 'Kasiyer POS satışı', 2, NOW() - INTERVAL 1 HOUR);

-- 11. ÖRNEK KASA HAREKETLERİ
INSERT INTO cash_movements (user_id, movement_type, amount, payment_method, reference_id, description, created_at) VALUES
(2, 'opening_balance', 1000.00, 'cash', 'KASA-DEVIR-01', 'Güne başlama açılış nakit avansı', NOW() - INTERVAL 5 HOUR),
(2, 'sale_cash', 218.50, 'cash', 'KRT-20261118-0001', 'POS Fiş No: KRT-20261118-0001 Nakit Satış', NOW() - INTERVAL 2 HOUR),
(2, 'sale_card', 292.50, 'credit_card', 'KRT-20261118-0002', 'POS Fiş No: KRT-20261118-0002 Kredi Kartı Satış', NOW() - INTERVAL 1 HOUR);

# 🗄️ WooCommerce Kilitlenmesiz Veritabanı Temizleyici (Deadlock-Free Vacuum)

[🇬🇧 English](README.md) · [🇹🇷 Türkçe](README.tr.md)

> Canlı sipariş anında veritabanı tablolarını kilitlemeden (deadlock riskini sıfırlayarak) çalışan, yetim transient ve log kayıtlarını 500'lük güvenli paketler halinde temizleyen kurumsal SQL optimizasyon aracı.

Geliştirici ve Bakım Sorumlusu: **[Harun Sekmen](https://github.com/harunsekmen)** — E-Ticaret Sistem Mühendisi & WooCommerce Uzmanı.

[✉️ İletişime Geç](mailto:sekmenwp@gmail.com) · [💼 Upwork Profili](https://www.upwork.com/freelancers/~01a7a8b58043e6e6d9) · [🔗 GitHub Ana Profil](https://github.com/harunsekmen)

---

## 📸 Görsel Önizlemeler ve Yönetim Paneli

### 1. Canlı Yönetici Veritabanı Sağlık Paneli
Tablo boyutlarını, şişkinlik oranlarını ve temizleme geçmişini gösteren yönetim arayüzü:

![Veritabanı Temizleme Paneli Önizlemesi](docs/assets/admin-vacuum-dashboard.png)

---

## ⚡ Çözdüğü Kritik Sorun

Binlerce sipariş alan WooCommerce mağazalarında wp_options ve wp_woocommerce_order_items tabloları milyonlarca yetim satırla dolar:
- **Canlı Sipariş Kilitlenmesi (Deadlock):** Standart temizleme eklentileri (WP-Optimize vb.) tüm tabloyu tek bir dev SQL sorgusuyla kilitleyerek o anda sipariş veren müşterilerin ödemesini patlatır.
- **Yavaşlayan Veritabanı:** Şişen wp_options tablosu autoload süresini 1-2 saniyenin üzerine çıkarır.

**WooCommerce Deadlock-Free Vacuum**, Action Scheduler üzerinden 500'lük küçük parçalar halinde arka planda çalışarak canlı siparişleri asla kesintiye uğratmaz.

---

## 🛠️ Öne Çıkan Özellikler

- **Sıfır Tablo Kilitlenmesi:** Küçük parçalı (chunked) SQL işlemleri sayesinde okuma ve yazma kilitlerini engeller.
- **Arka Plan Zamanlayıcısı:** Action Scheduler kuyruğunu kullanarak temizliği sunucunun boş olduğu anlarda sessizce tamamlar.
- **WP-CLI Desteği:** Sunucu yöneticileri için wp woo vacuum komut satırı desteği sunar.
- **HPOS Uyumlu:** Yeni sipariş tablolarındaki yetim kayıtları güvenle temizler.

---

## 🏗️ Teknik Mimari ve Standartlar

`PHP 8.1+` • `MySQL 8.0+ / MariaDB` • `WooCommerce Action Scheduler` • `WP-CLI Command Engine`

---

## 🚀 Kurulum ve Test

1. Eklentiyi klonlayın ve test edin:
   ```bash
   git clone https://github.com/harunsekmen/woo-deadlock-vacuum.git wp-content/plugins/woo-deadlock-vacuum
   ```
2. Testi çalıştırın:
   ```bash
   php wp-content/plugins/woo-deadlock-vacuum/tests/test-vacuum.php
   ```

---

## 📄 Lisans

Bu proje **GPL-3.0 Lisansı** ile açık kaynak olarak sunulmaktadır.

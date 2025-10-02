# 📊 Laravel Reporting Dashboard

Laravel Reporting Dashboard adalah modul reporting modern berbasis Laravel dengan fitur:

- 🔎 **Filter & Sorting** data menggunakan **DateRangePicker** dan **Select2**
- 📈 **Executive Summary** (statistik singkat: total order, gross amount, rata-rata order, growth %)
- 📊 **Grafik interaktif** dengan **Chart.js** (Orders & Amount per hari)
- 📑 **Pivot Table drag-and-drop** dengan **PivotTable.js** + jQuery UI
- 🎨 Desain **UX friendly** dengan Bootstrap 5 + Bootstrap Icons

---

## 🚀 Instalasi

Untuk menjalankan project ini, lakukan langkah berikut secara berurutan:

```bash
# Clone atau buat project Laravel baru
composer create-project laravel/laravel reporting-app
cd reporting-app

# Salin file .env.example ke .env lalu sesuaikan konfigurasi database
# Contoh:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=reporting_app
# DB_USERNAME=root
# DB_PASSWORD=

# Generate key aplikasi
php artisan key:generate

# Jalankan migrasi dan seeder (isi dengan data dummy)
php artisan migrate --seed
# atau reset database
php artisan migrate:fresh --seed

# Jalankan server Laravel
php artisan serve

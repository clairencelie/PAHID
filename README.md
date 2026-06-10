# PAHID — AI-Assisted Health Insurance Prospect Verification

Demo aplikasi verifikasi prospect A&H Insurance dengan AI-assisted Single Support enforcement.

## Tech Stack

- **Laravel** (PHP 8.4)
- **MySQL 8.0**
- **Laravel Blade** + **Bootstrap 5**
- **Gemini API** (dengan Mock AI fallback)
- **Docker Compose**

## Setup

### Prasyarat
- Docker Desktop (dengan Colima atau Docker Engine)

### Instalasi

```bash
# 1. Clone project
git clone <repo-url> pahid
cd pahid

# 2. Copy environment file
cp .env.example .env

# 3. Jalankan Docker containers
docker-compose up -d

# 4. Tunggu database siap (~10 detik), jalankan migrasi dan seeding
docker-compose exec app php artisan migrate --seed
```

### Akses Aplikasi

| Service     | URL                           |
|-------------|-------------------------------|
| App         | http://localhost:8080         |
| phpMyAdmin  | http://localhost:8081         |

### Demo Accounts

Semua akun menggunakan password: **`password`**

| Role        | Email                    | Cabang           |
|-------------|--------------------------|------------------|
| Admin       | admin@pahid.test         | Kantor Pusat     |
| Supervisor  | supervisor@pahid.test    | Kantor Pusat     |
| BC Surabaya | bc.sby@pahid.test        | Cabang Surabaya  |
| Marketing A | marketing.a@pahid.test   | Cabang Surabaya  |
| Marketing B | marketing.b@pahid.test   | Cabang Jakarta   |
| Underwriter | uw@pahid.test            | Kantor Pusat     |

---

## Demo Flow

### Scenario 1 — Konflik Single Support (Main Demo)
1. Login sebagai **BC Surabaya** (`bc.sby@pahid.test`)
2. Buka **Daftar Prospect** → lihat prospect `PRO-DEMO002` "Saripuri Permai Hotel" (Jakarta)
3. Sistem sudah mendeteksi konflik dengan assignment aktif Cabang Surabaya (score 91/VERY_HIGH)
4. Buka menu **Konflik** → review dan resolve konflik

### Scenario 2 — Alur Lengkap Prospect Baru
1. Login sebagai **Marketing A** → buat prospect baru "Logisly" (Brand)
2. Submit → login sebagai **BC Surabaya** → buka prospect
3. Klik **Jalankan Verifikasi AI** → AI mendeteksi brand Logisly = PT Logistik Canggih Indonesia
4. Review hasil → Setujui → Buat Single Support Assignment
5. Sistem otomatis generate protected aliases

### Scenario 3 — LOA Checker  
- Buka prospect `PRO-DEMO004` → lihat LOA dengan status SUSPICIOUS dan red flags

---

## Konfigurasi AI

### Mock AI (Default)
```env
AI_PROVIDER=mock
```

### Gemini API
```env
AI_PROVIDER=gemini
GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL=gemini-1.5-flash
```

---

## Docker Commands

```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# Artisan commands
docker-compose exec app php artisan <command>

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

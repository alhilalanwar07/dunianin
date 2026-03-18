# Dunia Anin

Game edukasi anak usia <= 7 tahun dengan pendekatan SDUI berbasis Laravel + Livewire.

## Stack
- Laravel 13
- Livewire 4
- Alpine.js
- Tailwind CSS
- MySQL (dev) / SQLite memory (test)
- Queue: database
- Integrasi opsional: Nvidia NIM, Telegram Bot

## Fitur Utama (saat ini)
- Session pemain tanpa login tradisional (username + token localStorage)
- Two-state UI: level map dan game arena tanpa page reload
- Tiga engine game v1:
	- tap_collector
	- macro_dnd
	- binary_choice
- Aturan progress: 3 jawaban benar untuk naik level
- Salah jawaban: soal di-roll ulang (bukan retry soal yang sama)
- Leaderboard publik
- Generator bank soal via command + job
- Monitoring Telegram (new player, correct, wrong opsional, level up, generate)

## Setup Lokal
1. Install dependency backend
```bash
composer install
```

2. Install dependency frontend
```bash
npm install
```

3. Siapkan environment
```bash
copy .env.example .env
php artisan key:generate
```

4. Atur konfigurasi di .env
```env
APP_NAME="Dunia Anin"
APP_URL=http://fun_game_ai.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fun_game_ai
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

NVIDIA_NIM_API_KEY=...
NVIDIA_NIM_MODEL=meta/llama-3.1-70b-instruct
NVIDIA_NIM_BASE_URL=https://integrate.api.nvidia.com/v1

TELEGRAM_BOT_TOKEN=...
TELEGRAM_CHAT_ID=...
TELEGRAM_NOTIFY_WRONG=false
```

5. Migrasi database
```bash
php artisan migrate
```

6. Jalankan worker queue
```bash
php artisan queue:work --queue=ai-generate,telegram,database
```

7. Jalankan aplikasi
```bash
php artisan serve
npm run dev
```

## Command Operasional
1. Generate level tunggal
```bash
php artisan game:generate 1 --count=15 --sync
```

2. Generate range level
```bash
php artisan game:generate 1 --to=3 --count=15
```

3. Bootstrap seeding awal (default level 1-3)
```bash
php artisan game:seed-initial --sync
```

4. Bootstrap seeding range custom
```bash
php artisan game:seed-initial --from=1 --to=5 --count=15
```

5. Cek status operasional game
```bash
php artisan game:status
```

## Testing
Jalankan seluruh test:
```bash
php artisan test
```

Ruang lingkup test saat ini mencakup:
- halaman root dapat diakses
- register pemain lewat komponen session
- level up setelah 3 jawaban benar
- leaderboard dapat diakses
- jawaban salah tercatat dan tidak mengurangi skor

## Alur Pakai Singkat
1. Buka aplikasi pada route root.
2. Isi username pada layar awal.
3. Masuk ke map level, klik node level aktif.
4. Mainkan challenge sampai 3 kali benar untuk naik level.
5. Cek ranking di halaman leaderboard.

## Catatan Update README
Setiap selesai update fitur, README ini harus ikut diperbarui agar:
- command baru terdokumentasi
- perubahan alur pakai selalu sinkron
- status fitur dan test tetap jelas

## Update Log
### 2026-03-18
- Menambahkan game:generate dengan dukungan range level via opsi --to.
- Menambahkan game:seed-initial untuk bootstrap bank soal multi-level.
- Menambahkan game:status untuk ringkasan operasional.
- Menambahkan test skenario jawaban salah tetap level dan skor.
- Menambahkan polish map connector dan transisi visual arena pada iterasi sebelumnya.

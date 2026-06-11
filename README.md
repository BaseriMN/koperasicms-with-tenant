# BLUEPRINT SISTEM — KoperasiCMS

> Tampal dokumen ini di awal sesi chat baru supaya AI terus faham konteks penuh projek.
> Ini sistem **pengurusan koperasi** sebenar, **LIVE di production**.

---

## 1. RINGKASAN PROJEK

- **Nama:** Sistem Pengurusan Koperasi (KoperasiCMS)
- **Untuk:** Koperasi Perniagaan Melayu Baling Berhad (~1000 ahli)
- **Status:** LIVE di production — **www.koperasicms.site**
- **Bahasa perbualan:** Bahasa Malaysia (casual — "bro", "ko", "aku")
- **Jenis koperasi:** Berasaskan **SAHAM** (simpanan dimatikan — lihat toggle produk)

---

## 2. STACK TEKNIKAL

| Lapisan | Teknologi |
|---------|-----------|
| Framework | Laravel 12 |
| PHP | 8.4 (production & dev) |
| Database (production) | PostgreSQL |
| Database (dev/local) | SQLite (untuk test) |
| Web server | nginx (port 80/443) |
| Frontend | Blade + Alpine.js (CDN) |
| Fonts | Fraunces (display/heading) + Outfit (body) |

### Persekitaran
- **Dev:** `muham@BASERIMN` (laptop, kadang guna SQLite, `DB_DATABASE=laravel`)
- **Production:** server VPS (`root@216.126.236.127` port 2234, user `baseri@ubuntu`), PostgreSQL + nginx + php8.4-fpm
- **Git:** push/pull guna **terminal Linux sahaja** (SSH key ed25519). JANGAN guna git VS Code Windows (config BOM rosak).
- **Repo:** github.com/BaseriMN/koperasicms

---

## 3. REKA BENTUK (DESIGN)

- **Tema:** Korporat koperasi — hijau gelap (ink `#0c1f1c`, teal `#1f6f5c`) + emas (`#c0962c`)
- **Layout:** sidebar accordion (collapsible, dark) + topbar + content. Master di `resources/views/layouts/master.blade.php`
- **CSS:** dalam `<style>` master, guna CSS variables `:root` (dinamik ikut tema tersimpan)
- **White-label:** logo, nama, warna tema boleh ubah per koperasi (settings table + config/themes.php, 6 palet)
- **Komponen:** `.panel`, `.stat`, `.btn` (btn-gold/ghost/danger), `.badge` (gold/teal/ok/off), `.field`, `.check`, `.alert`

---

## 4. RBAC (ROLE-BASED ACCESS CONTROL)

### Role slugs (PENTING — guna slug, bukan nama)
- `super-user` — kuasa penuh (Muhamad Baseri / muhamad.baseri@gmail.com)
- `admin-koperasi` — admin (CONTOH: Saadiah binti Zainol). **⚠️ SLUG = `admin-koperasi`, BUKAN `admin`!**
- `pengurus`, `kerani`, `jk`, `auditor`, `ahli`

### ⚠️ GOTCHA KRITIKAL — slug admin
Banyak bug berlaku sebab kod check `hasAnyRole(['admin', ...])` sedangkan slug sebenar **`admin-koperasi`**. SENTIASA guna `'admin-koperasi'`. Pernah jadi di: loan approval, MeetingController (`$canCreate`).

### Struktur
- Tables: `roles`, `permissions`, `role_user`, `permission_role`
- Model User: `hasRole(string $slug)`, `hasAnyRole(array $slugs)`, `hasPermission(...)`
- Role dikenal via lajur **`slug`**

### Module Access (akses modul dinamik)
- Table `module_role` (role_id → module_key)
- Config: `config/modules.php` (keys: `pengurusan_staff`, `pengurusan_member`, `lejar_transaksi`, `permohonan_pinjaman`, `mesyuarat_minit`, `laporan_audit`, `akaun`, `tetapan_sistem`)
- Middleware: `module:<key>` (EnsureModuleAccess), `role:<slug>` (EnsureUserHasRole)
- Logik: `App\Support\ModuleAccess::userCan($user, $key)` + `allowedFor($user)`. Super-user sentiasa lulus.
- Matrix boleh edit di **Tetapan → Akses Modul**
- Daftar alias middleware di `bootstrap/app.php` (Laravel 12 style: `$middleware->alias([...])`)

---

## 5. MODUL & STATUS

### ✅ Pengurusan Ahli (Keahlian)
- No ahli auto `AXXXX` (A0001...), status enum (aktif/tidak_aktif/berhenti)
- Foto ahli (`foto_path`), waris (next of kin)
- Pindah Milik Keahlian (no ahli kekal, pemilik tukar) — DENGAN kelulusan mesyuarat (meeting_id, pencadang_id, penyokong_id, catatan_kelulusan)
- Export CSV
- Form WAJIB `enctype="multipart/form-data"` untuk upload foto

### ✅ Pengurusan Staff (Users)
- CRUD user + assign roles
- **Perlindungan super-user:** padam super-user DISEKAT TOTAL (sesiapa pun); sunting super-user hanya super-user boleh. Sekat di UI + controller (edit/update/destroy).

### ✅ Lejar Transaksi (Saham & Simpanan)
- Table `transactions`: jenis (saham/simpanan), arah (masuk/keluar), amaun, baki (running), recorded_by
- Pindah Saham — dengan kelulusan mesyuarat
- Penapis tarikh (julat Dari→Hingga + pilihan cepat: Bulan Ini/Lalu, Tahun Ini/Lalu)
- Kad jumlah (Masuk/Keluar/Bersih) + Export CSV
- Baki ahli: `Member::bakiJenis('saham'/'simpanan')`, `bakiSaham()`, `bakiSimpanan()`

### ✅ Permohonan Pinjaman
- Model Loan: member_id, dimohon_oleh, amount, tempoh, tujuan, status (pending/approved/rejected), reviewed_by
- Kelulusan mesyuarat (meeting_id, pencadang_id, penyokong_id, catatan)
- Block self-approval (dimohon_oleh ≠ pelulus)
- ⚠️ BELUM SIAP: kadar faedah, ansuran, jadual bayaran, rekod bayaran, had kelayakan ikut saham, penjamin, no rujukan, penyata

### ✅ Mesyuarat & Minit
- Model Meeting: tajuk, tarikh, lokasi, minit, created_by (relationship `pencipta`)
- ⚠️ Ada bug: MeetingController `$canCreate` guna `'admin'` — patut `'admin-koperasi'`

### ✅ Akaun
- Pendapatan & Perbelanjaan (account_categories, account_entries — kategori dinamik)
- Penyata Untung Rugi
- Dividen (lihat bawah)

### ✅ Dividen (MODUL KOMPLEKS — accounting-correct)
- Tabung (Rizab 25%, KWAPK 2%) dikira atas **untung bersih** (Akta Koperasi 1993 s.56-57, Rizab min 15%)
- **Untung Boleh Agih = Untung Bersih − Σ Tabung**
- **Dividen = saham × kadar diluluskan TERUS** (BUKAN ratio). Cth: RM1000 × 7% = RM70
- Cutoff = 31 Dis (hujung tahun kewangan). Ahli sertai selepas cutoff TAK layak tahun itu. Tiada pro-rata.
- 2 kadar: `peratus_auditor` (cadangan juruaudit) + `peratus_diluluskan` (AGM — yang dipakai)
- Untung bersih = SILING (warn kalau dividen > untung boleh agih, tak block)
- Baki Dibawa Ke Hadapan = Untung Boleh Agih − Jumlah Dividen
- Service: `DividendService` (kiraRingkasan, kiraJumlahSahamSetakat, agihMengikutSaham, muktamad, sahamLayakSemua)
- Draf vs Muktamad (watermark DRAF pada penyata sebelum muktamad)

### ✅ Laporan Audit
- Stat: simpanan, saham, pinjaman_lulus, rekod transaksi, pinjaman pending + Export CSV

### ✅ Log Aktiviti (Audit Trail)
- **Super-user sahaja**
- Himpun dari rekod sedia ada (TIADA table baru, "lite", baca je bila buka)
- Sumber: Transaction(recorder), ShareTransfer/OwnershipTransfer(processor), Loan(pemohon+reviewer), DividendRun(pengira), AccountEntry(recorder), Meeting(pencipta)
- Had 500/sumber, pagination 40, penapis modul+tarikh, Export CSV
- Controller: `ActivityLogController` (index + exportCsv + private kumpulLog)

### ✅ Tetapan Koperasi
- Logo, nama, no pendaftaran, tema warna (white-label)
- **Toggle Produk:** `produk_simpanan` (default OFF) + `produk_pinjaman` (default OFF)

### ✅ Profil Sendiri
- Edit info + avatar (`avatar_path`) + tukar password
- Semua user yang login (bukan guest)
- ⚠️ Avatar (users, `avatar_path`) ≠ Foto ahli (members, `foto_path`) — DUA benda berasingan

---

## 6. KONVENSYEN & HELPER PENTING

### Helper global (`app/helpers.php`, daftar di composer.json `files`)
- `wang($nilai): float` — bulatkan wang ke 2 decimal. **Guna untuk SEMUA kira wang** (elak float bug RM50000→49999.99). `round((float)$x, 2)`.
- `simpanan_aktif(): bool` — toggle produk simpanan (Setting `produk_simpanan` === '1')
- `pinjaman_aktif(): bool` — toggle produk pinjaman (Setting `produk_pinjaman` === '1')
- **WAJIB `composer dump-autoload` selepas edit helpers.php** (kalau tak: "undefined function")

### Toggle produk (config-based custom)
- Simpanan OFF → sorok UI (dashboard, profil, borang, lejar penapis, audit)
- Pinjaman OFF → sorok UI + SEKAT ROUTE (middleware `pinjaman_aktif` / EnsurePinjamanAktif → abort 404)
- Code kekal utuh (untuk tenant lain yang nak guna) — JANGAN buang backend

### Money
- DB: decimal(12,2) / (16,2) — tepat
- PHP float perlu `wang()` / `round(...,2)` bila ada +−×÷ atau simpan sebagai baki
- `sum()` tunggal dari decimal column biasanya selamat; risiko bila ada aritmetik PHP
- Display: `number_format($x, 2)`

### Setting (white-label / config)
- Model `Setting`: `get($key, $default)`, `put($key, $val)`, `putMany([...])`, `all_cached()`
- Key-value dalam table `settings`

### Audit trail (siapa buat)
- Setiap proses simpan WHO: Transaction(recorded_by), ShareTransfer/OwnershipTransfer(processed_by), Loan(dimohon_oleh+reviewed_by), AccountEntry(recorded_by), DividendRun(dikira_oleh), Meeting(created_by)

### Database
- PostgreSQL LIKE case-sensitive → guna LOWER() + whereRaw dalam where(closure)
- Migration order penting untuk FK (table rujuk members/meetings/loans perlu nombor lebih tinggi)

---

## 7. GOTCHA / PELAJARAN (PENTING UNTUK ELAK BUG)

1. **⚠️ Slug `admin-koperasi` bukan `admin`** — punca #1 bug akses. Check semua `hasAnyRole`.
2. **Route/config cache di production** — selepas deploy DAN tambah route/view baru, WAJIB `php artisan route:clear` (atau optimize:clear). Kalau tak: "Route [x] not defined" → 500 di SEMUA page (sebab master.blade.php load route tu).
3. **composer dump-autoload** selepas edit `app/helpers.php`.
4. **Storage symlink** — `php artisan storage:link` di server (symlink tak masuk git). "Image missing" = symlink/serve issue. config/filesystems disk 'local' `serve` => false.
5. **Permission konflik** (artisan jalan as `baseri` vs web as `www-data`): `usermod -aG www-data baseri` + `chown baseri:www-data` + `chmod 775` + `find -type d -exec chmod g+s`.
6. **Git via terminal Linux sahaja** (bukan VS Code Windows — SSH config BOM rosak). Jangan commit fail database SQLite (`laravel`) — dah masuk .gitignore.
7. **Defense in depth** — sekat di UI (view) DAN controller. Jangan UI je (URL boleh bypass).
8. **enctype multipart** wajib untuk borang upload fail (foto/avatar/logo).

### Rutin deploy standard (production)
```
git pull
composer dump-autoload
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

---

## 8. BACKUP

- Pakej `spatie/laravel-backup` (DB-only)
- Disk: local + google (Google Drive)
- Config ikut git (auto ada di production). `env('DB_CONNECTION')` → auto suai (dev sqlite, prod pgsql)
- Jadual: harian (routes/console.php), rotation 30 hari
- Google Drive: adapter `masbug/flysystem-google-drive-ext` + Storage::extend di AppServiceProvider. Credentials di `.env` (PENDING isi: GOOGLE_DRIVE_CLIENT_ID/SECRET/REFRESH_TOKEN/FOLDER_ID)
- `continue_on_failure => true` (local jadi walau google gagal)
- Production perlu `postgresql-client` (pg_dump), cron: `* * * * * cd /path && php artisan schedule:run`
- User ada backup Google Drive sendiri di server (berasingan)

---

## 9. HALA TUJU: MULTI-TENANT SaaS (DIRANCANG — BELUM MULA)

### Keputusan dipersetujui
- **Model:** SaaS — banyak koperasi guna satu sistem, jual/sewa
- **Pengasingan:** DATABASE-PER-TENANT (bukan single-DB tenant_id) — data wajib terpisah, keselamatan tinggi
- **Pengenalan tenant:** via DOMAIN (cth koperasibaling.com) — BUKAN port, BUKAN user masuk ID
- **Daftar tenant:** manual oleh pemilik SaaS
- **Pakej:** `stancl/tenancy`
- **Custom = CONFIG** (logo/warna/modul on-off/setting per tenant), BUKAN kod berbeza. Satu code base — semua tenant kongsi feature/bugfix. (White-label + module access + toggle produk sedia ada = asas untuk ni.)
- **Strategi:** buat dalam BRANCH/SALINAN baru. Live Baling jangan diganggu.

### Seni bina
- **Central DB:** senarai tenants + domains (tiada data koperasi)
- **Tenant DB:** satu per koperasi (kop_baling, kop_kedah) — struktur sama, data terpisah
- Satu app Laravel + nginx multi-domain → app pilih DB ikut domain

### 6 Fasa
1. Install stancl/tenancy + central tables
2. Pisah migration (central vs tenant/)
3. Domain routing
4. Provisioning command (auto cipta DB + migrate + seed)
5. Migrate Baling jadi tenant pertama
6. Test isolation

### Cabaran khas
- PostgreSQL (sokong, setup beza dari MySQL)
- Storage per tenant (logo/foto asingkan)
- Backup per tenant DB
- wang()/config tak terjejas

**Status: NOT STARTED. Mula bila user kata "jom mula Fasa 1".**

---

## 10. CARA KERJA DENGAN USER (PREFERENCES)

- **JANGAN code semua** — user banyak nak GUIDE step-by-step, atau CARI/TUKAR (cari kod lama → tukar kod baru), bukan rewrite penuh.
- **Format edit:** "📄 path/fail" → CARI: (kod lama) → TUKAR JADI: (kod baru).
- **Brainstorm DULU sebelum code** bila user signal — bertindak sebagai senior dev + system designer + DB admin + juruakaun terbaik.
- User suka faham SEBAB, bukan terima je. Terangkan trade-off.
- Workspace AI (sandbox) ≠ fail sebenar user. Bagi CARI/TUKAR untuk user apply sendiri.
- Output deliverable (docx, dll) → guna fail, present untuk download.

---

## 11. PENDING / NEXT (kalau user nak sambung)

1. **[BUG]** MeetingController `$canCreate` guna `'admin'` → patutnya `'admin-koperasi'` (sebab tu admin-koperasi kena 403 di mesyuarat) — line ~13/17
2. **[OPTIONAL]** Modul pinjaman lengkap (faedah, ansuran, bayaran, jadual, penjamin, penyata)
3. **[OPTIONAL]** Penyata tahunan ahli (gabung saham + dividen + pinjaman)
4. **[OPTIONAL]** Google Drive backup credentials (OAuth setup + .env)
5. **[OPTIONAL]** Multi-tenant SaaS (Fasa 1 — bila user ready, buat di branch baru)
6. **[NOTE]** Dividen untung_bersih kadang tersimpan 738999.99 vs 739000 — workaround retype di draf

---

# BLUEPRINT DATABASE — KoperasiCMS

> Schema table sistem KoperasiCMS. Dijana berdasarkan modul yang dibina.
> **Nota:** Mungkin ada lajur kecil yang berbeza dari DB sebenar. Untuk schema 100% tepat,
> jalankan `php artisan db:show --counts` atau semak fail migration sebenar.
> DB production: **PostgreSQL** | DB dev: SQLite.

---

## DIAGRAM HUBUNGAN (ERD RINGKAS)

```
                          ┌─────────────┐
                          │   roles     │
                          └──────┬──────┘
                                 │
              ┌──────────────────┼──────────────────┐
              │ (role_user)      │ (permission_role)│ (module_role)
              ▼                  ▼                   ▼
        ┌──────────┐      ┌─────────────┐    ┌──────────────┐
        │  users   │      │ permissions │    │ (module_key) │  ← config/modules.php
        └────┬─────┘      └─────────────┘    └──────────────┘
             │
             │ (user_id, nullable — akaun login ahli)
             ▼
        ┌──────────┐
        │ members  │◄──────────────┐
        └────┬─────┘                │
             │                      │
   ┌─────────┼──────────┬───────────┼─────────────┬──────────────┐
   ▼         ▼          ▼           ▼             ▼              ▼
┌────────┐┌──────┐┌──────────┐┌─────────────┐┌──────────┐┌──────────────┐
│next_of ││trans ││  loans   ││share_       ││ownership ││dividend_     │
│_kins   ││action││          ││transfers    ││_transfers││shares        │
└────────┘└──────┘└────┬─────┘└──────┬──────┘└────┬─────┘└──────┬───────┘
                       │             │            │             │
                       └─────────────┴────────────┴─────────────┘
                                     │ (meeting_id, pencadang_id, penyokong_id)
                                     ▼
                              ┌─────────────┐
                              │  meetings   │
                              └─────────────┘

┌──────────────────┐   ┌──────────────────┐   ┌─────────────────┐
│ account_         │──▶│ account_entries  │   │ dividend_runs   │──┐
│ categories       │   └──────────────────┘   └─────────────────┘  │
└──────────────────┘                                 │             │
                                                      ▼             │
                                              ┌─────────────┐       │
                                              │dividend_    │◄──────┘
                                              │shares       │ (run_id)
                                              └─────────────┘

┌──────────┐   ┌─────────────┐   ┌──────────┐
│ settings │   │ savings     │   │ tabung   │  (savings = LAMA/mati, 0 rekod)
└──────────┘   │ (DEPRECATED)│   │(dividen) │
               └─────────────┘   └──────────┘
```

---

## TABLE TERAS

### `users` — Akaun login (staff + ahli yang ada login)
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| phone | string nullable | |
| password | string | hashed |
| avatar_path | string nullable | foto profil user (≠ foto member) |
| is_active | boolean | default true |
| email_verified_at | timestamp nullable | |
| remember_token | string nullable | |
| timestamps | | created_at, updated_at |

**Relationship:** belongsToMany `roles` (role_user) · hasOne `member` (jika ahli)

---

### `roles` — Peranan
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| name | string | cth "Admin Koperasi" |
| slug | string unique | **cth `admin-koperasi`, `super-user`** ⚠️ guna slug |
| description | string nullable | |
| timestamps | | |

**Slugs:** super-user, admin-koperasi, pengurus, kerani, jk, auditor, ahli
**Relationship:** belongsToMany `users`, `permissions`

---

### `permissions` — Kebenaran
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| name | string | |
| slug | string unique | |
| description | string nullable | |
| timestamps | | |

**Relationship:** belongsToMany `roles` (permission_role)

---

### `role_user` — Pivot (user ↔ role)
| Lajur | Jenis |
|-------|-------|
| role_id | FK → roles |
| user_id | FK → users |

### `permission_role` — Pivot (role ↔ permission)
| Lajur | Jenis |
|-------|-------|
| permission_id | FK → permissions |
| role_id | FK → roles |

### `module_role` — Akses modul (matrix dinamik)
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| role_id | FK → roles | |
| module_key | string | cth `mesyuarat_minit`, `akaun` (rujuk config/modules.php) |

> Module_key TIADA table sendiri — definisi dalam `config/modules.php`. Super-user tak perlu rekod (akses penuh automatik).

---

## TABLE KEAHLIAN

### `members` — Ahli koperasi
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| no_ahli | string unique | auto `AXXXX` (A0001...) |
| nama | string | |
| no_kp | string nullable | |
| telefon | string nullable | |
| alamat | text nullable | |
| foto_path | string nullable | foto ahli (≠ avatar user) |
| user_id | FK → users nullable | akaun login (jika ada) |
| status | enum | aktif / tidak_aktif / berhenti |
| tarikh_sertai | date nullable | |
| timestamps | | |

**Relationship:** belongsTo `user` · hasOne `nextOfKin` · hasMany `transactions`, `ownershipTransfers` · belongsToMany (pencadang/penyokong dalam transfers)
**Method:** `bakiSaham()`, `bakiSimpanan()`, `bakiJenis($jenis)`

---

### `next_of_kins` — Waris
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| member_id | FK → members | |
| nama | string | |
| no_kp | string nullable | |
| telefon | string nullable | |
| alamat | text nullable | |
| hubungan | string | cth Isteri, Anak |
| timestamps | | |

---

## TABLE TRANSAKSI & PINDAH MILIK

### `transactions` — Lejar saham & simpanan
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| member_id | FK → members | |
| jenis | string/enum | saham / simpanan |
| arah | string/enum | masuk / keluar |
| amaun | decimal(12,2) | guna `wang()` |
| baki | decimal(12,2) | running balance |
| sumber | string nullable | |
| rujukan | string nullable | no resit/rujukan |
| keterangan | text nullable | |
| recorded_by | FK → users | siapa rekod (relationship `recorder`) |
| timestamps | | |

---

### `share_transfers` — Pindah milik saham
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| from_member_id | FK → members | |
| to_member_id | FK → members | |
| amaun | decimal(12,2) | |
| sebab | string nullable | |
| tarikh_pindah | date | |
| processed_by | FK → users | relationship `processor` |
| meeting_id | FK → meetings nullable | kelulusan |
| pencadang_id | FK → members nullable | |
| penyokong_id | FK → members nullable | |
| catatan_kelulusan | text nullable | |
| timestamps | | |

---

### `ownership_transfers` — Pindah milik keahlian (no ahli kekal)
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| member_id | FK → members | ahli yang ditukar milik |
| from_user_id | FK → users nullable | |
| from_nama | string | pemilik lama |
| to_user_id | FK → users nullable | |
| to_nama | string | pemilik baharu |
| to_no_kp | string nullable | |
| sebab | string nullable | cth Kematian/Serahan |
| tarikh_pindah | date | |
| processed_by | FK → users | relationship `processor` |
| meeting_id | FK → meetings nullable | kelulusan |
| pencadang_id | FK → members nullable | |
| penyokong_id | FK → members nullable | |
| catatan_kelulusan | text nullable | |
| timestamps | | |

---

## TABLE PINJAMAN

### `loans` — Permohonan pinjaman
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| member_id | FK → members | |
| user_id | FK → users nullable | (jika rujuk user) |
| dimohon_oleh | FK → users | pemohon/key-in (relationship `pemohon`) |
| amount | decimal(12,2) | |
| tempoh | integer nullable | bulan |
| tujuan | text nullable | |
| status | string/enum | pending / approved / rejected |
| catatan | text nullable | |
| reviewed_by | FK → users nullable | pelulus (relationship `reviewer`) |
| reviewed_at | timestamp nullable | |
| meeting_id | FK → meetings nullable | kelulusan |
| pencadang_id | FK → members nullable | |
| penyokong_id | FK → members nullable | |
| timestamps | | |

> ⚠️ BELUM SIAP: kadar faedah, ansuran, jadual bayaran, rekod bayaran, penjamin, no rujukan, penyata.

---

## TABLE MESYUARAT

### `meetings` — Mesyuarat & minit
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| tajuk | string | |
| tarikh | date | |
| lokasi | string nullable | |
| minit | text nullable | |
| created_by | FK → users | relationship `pencipta` |
| timestamps | | |

> Dirujuk oleh loans, share_transfers, ownership_transfers (meeting_id) untuk kelulusan.

---

## TABLE AKAUN

### `account_categories` — Kategori pendapatan/perbelanjaan (dinamik)
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| nama | string | |
| jenis | string/enum | pendapatan / perbelanjaan |
| parent_id | FK → account_categories nullable | kategori induk |
| timestamps | | |

**Method:** jumlah kategori guna `wang()`/round

### `account_entries` — Entri akaun
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| category_id | FK → account_categories | |
| jenis | string/enum | pendapatan / perbelanjaan |
| member_id | FK → members nullable | |
| amaun | decimal(12,2) | guna `wang()` |
| tarikh | date | |
| rujukan | string nullable | |
| penerima_pembayar | string nullable | |
| keterangan | text nullable | |
| recorded_by | FK → users | relationship `recorder` |
| timestamps | | |

---

## TABLE DIVIDEN

### `dividend_runs` — Sesi pengiraan dividen (per tahun)
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| tahun | integer/string | tahun kewangan |
| tarikh_mula | date nullable | label tahun kewangan (display) |
| tarikh_cutoff | date | hujung tahun (lazim 31 Dis) |
| untung_bersih | decimal(16,2) | SILING |
| jumlah_peruntukan | decimal(16,2) | Σ tabung |
| untung_boleh_agih | decimal(16,2) | untung_bersih − peruntukan |
| jumlah_saham_anggota | decimal(16,2) | auto dari ledger, boleh edit |
| peratus_auditor | decimal(5,2) | cadangan juruaudit |
| peratus_diluluskan | decimal(5,2) | AGM — yang DIPAKAI untuk kira |
| peratus_dividen | decimal(5,2) | alias = diluluskan (legacy) |
| jumlah_dividen | decimal(16,2) | saham × peratus_diluluskan |
| baki_dibawa_hadapan | decimal(16,2) | boleh_agih − jumlah_dividen |
| status | string/enum | draf / dimuktamadkan |
| tarikh_muktamad | timestamp nullable | |
| dikira_oleh | FK → users | relationship `pengira` |
| catatan | text nullable | |
| timestamps | | |

### `dividend_shares` — Bahagian dividen setiap ahli
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| dividend_run_id | FK → dividend_runs | |
| member_id | FK → members | |
| saham_layak | decimal(16,2) | baki saham ≤ cutoff |
| peratus | decimal | ratio (display only) |
| amaun_dividen | decimal(16,2) | saham_layak × kadar |
| timestamps | | |

### `tabung` — Tabung dinamik (Rizab, KWAPK, dll) untuk dividen run
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| dividend_run_id | FK → dividend_runs | |
| nama | string | cth Rizab, KWAPK |
| peratus | decimal(5,2) | % atas untung bersih |
| amaun | decimal(16,2) | |
| timestamps | | |

> Nama table tabung mungkin berbeza (cth `dividend_tabung` / `tabungs`) — sahkan di migration.

---

## TABLE SISTEM

### `settings` — White-label + config (key-value)
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| key | string unique | cth nama_koperasi, logo_path, tema_palet, tema_mode, produk_simpanan, produk_pinjaman |
| value | text nullable | |
| timestamps | | |

**Key penting:** `nama_koperasi`, `nama_pendek`, `logo_path`, `no_pendaftaran`, `tema_palet`, `tema_mode`, `produk_simpanan` (default '0'), `produk_pinjaman` (default '0')
**Model:** `Setting::get/put/putMany/all_cached`

---

## TABLE DEPRECATED / TIDAK GUNA

### `savings` — ⚠️ MATI (0 rekod, digantikan oleh `transactions`)
> SavingController + model Saving + view simpanan/ ialah modul LAMA yang bertindih dengan TransactionController. **0 rekod** — selamat dibuang. Sistem sebenar guna `transactions` (jenis=simpanan/saham). Jika belum dibuang, ia redundant.

---

## TABLE LARAVEL DEFAULT (biasa)
- `password_reset_tokens`
- `sessions` (jika SESSION_DRIVER=database)
- `cache`, `cache_locks`
- `jobs`, `job_batches`, `failed_jobs`
- `migrations`

---

## NOTA SCHEMA PENTING

1. **Money:** decimal(12,2) atau (16,2) — tepat di DB. PHP guna `wang()` untuk elak float bug.
2. **FK kelulusan:** loans, share_transfers, ownership_transfers semua ada `meeting_id` + `pencadang_id` + `penyokong_id` + `catatan_kelulusan` (nullOnDelete).
3. **Audit trail:** setiap table proses ada lajur "siapa buat" (recorded_by / processed_by / dimohon_oleh / reviewed_by / dikira_oleh / created_by).
4. **PostgreSQL:** LIKE case-sensitive → guna LOWER() + whereRaw untuk carian.
5. **Migration order:** table yang rujuk members/meetings/loans perlu nombor migration lebih tinggi (FK constraint).
6. **avatar_path (users) ≠ foto_path (members)** — dua benda berasingan.
7. **Toggle produk:** kawal UI/route via `settings` (produk_simpanan/produk_pinjaman), bukan struktur DB — data simpanan/pinjaman tetap dalam transactions/loans.

---

*Tamat blueprint database. Untuk schema 100% tepat, semak fail `database/migrations/` atau `php artisan db:show`.*





<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

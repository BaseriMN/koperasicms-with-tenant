# BLUEPRINT SISTEM — KoperasiCMS

> Tampal dokumen ini di awal sesi chat baru supaya AI terus faham konteks penuh projek.
> Ini sistem **pengurusan koperasi** sebenar, **LIVE di production** + sedang ditransformasi ke **multi-tenant SaaS** (Fasa 1-4 siap di dev).
> Kemaskini terakhir: Sesi 2 — 12 Jun 2026.

---

## 1. RINGKASAN PROJEK

- **Nama:** Sistem Pengurusan Koperasi (KoperasiCMS)
- **Untuk:** Koperasi Perniagaan Melayu Baling Berhad (~1000 ahli)
- **Status:** LIVE di production — **www.koperasicms.site** (app lama, single-koperasi)
- **Hala tuju:** Multi-tenant SaaS — **Fasa 1-4 SIAP & VERIFIED di dev** (lihat Seksyen 9). Fasa 5 (migrate Baling ke production tenancy) belum mula.
- **Bahasa perbualan:** Bahasa Malaysia (casual — "bro", "ko", "aku")
- **Jenis koperasi:** Berasaskan **SAHAM** (simpanan dimatikan — lihat toggle produk)

---

## 2. STACK TEKNIKAL

| Lapisan | Teknologi |
|---------|-----------|
| Framework | Laravel 12 |
| PHP | 8.4 (production & dev) |
| Database (production) | PostgreSQL |
| Database (dev/local) | PostgreSQL local (v2/tenancy) — SQLite hanya projek lama |
| Multi-tenancy | stancl/tenancy v3.10.0 (database-per-tenant, domain identification) |
| Web server | nginx (port 80/443) |
| Frontend | Blade + Alpine.js (CDN) |
| Fonts | Fraunces (display/heading) + Outfit (body) |

### Persekitaran
- **Dev:** `muham@BASERIMN` (laptop). DUA projek: `~/koperasicms` (lama, Baling live) + `~/koperasicmsv2` (tenancy, PostgreSQL local — central DB dev: `koperasi_tenant`)
- **Production:** server VPS (`root@216.126.236.127` port 2234, user `baseri@ubuntu`), PostgreSQL + nginx + php8.4-fpm
- **Git:** push/pull guna **terminal Linux sahaja** (SSH key ed25519). JANGAN guna git VS Code Windows (config BOM rosak).
- **Repo LAMA (Baling live):** github.com/BaseriMN/koperasicms
- **Repo V2 (tenancy — bakal jadi THE code base):** github.com/BaseriMN/koperasicms-with-tenant

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
4. **Storage symlink** — `php artisan storage:link` di server (symlink tak masuk git). "Image missing" = symlink/serve issue. config/filesystems disk 'local' `serve` => false. (NOTA: dalam v2 tenancy, fail tenant guna `tenant_asset()` — TIADA symlink per tenant. Lihat 9.4.)
5. **Permission konflik** (artisan jalan as `baseri` vs web as `www-data`): `usermod -aG www-data baseri` + `chown baseri:www-data` + `chmod 775` + `find -type d -exec chmod g+s`.
6. **Git via terminal Linux sahaja** (bukan VS Code Windows — SSH config BOM rosak). Jangan commit fail database SQLite (`laravel`) — dah masuk .gitignore.
7. **Defense in depth** — sekat di UI (view) DAN controller. Jangan UI je (URL boleh bypass).
8. **enctype multipart** wajib untuk borang upload fail (foto/avatar/logo).

### Rutin deploy standard (production — APP LAMA Baling sahaja; untuk v2 tenancy rujuk 9.7)
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
- Config ikut git (auto ada di production). `env('DB_CONNECTION')` → auto suai
- Jadual: harian (routes/console.php), rotation 30 hari
- Google Drive: adapter `masbug/flysystem-google-drive-ext` + Storage::extend di AppServiceProvider. Credentials di `.env` (PENDING isi: GOOGLE_DRIVE_CLIENT_ID/SECRET/REFRESH_TOKEN/FOLDER_ID)
- `continue_on_failure => true` (local jadi walau google gagal)
- Production perlu `postgresql-client` (pg_dump), cron: `* * * * * cd /path && php artisan schedule:run`
- User ada backup Google Drive sendiri di server (berasingan)
- ⚠️ **PENDING TENANCY:** config backup masih single-DB — perlu jadi per-tenant (lihat 9.9 #5)

---

## 9. MULTI-TENANT SaaS — PROGRESS

### 9.0 Status Fasa

| Fasa | Perkara | Status |
|------|---------|--------|
| 1 | Install stancl/tenancy + central tables | ✅ SIAP |
| 2 | Pisah migration (central vs tenant) + DB-per-tenant verified | ✅ SIAP |
| 3 | Domain routing + seeding + storage per-tenant (tenant_asset) | ✅ SIAP |
| 4 | Provisioning command `tenant:cipta` (auto-cleanup) | ✅ SIAP |
| 5 | Migrate Baling jadi tenant pertama (PRODUCTION) | ⏳ BELUM — Sesi 3 |
| 6 | Test isolation menyeluruh | ⏳ BELUM |

### 9.1 Projek & Repo

- **Projek tenancy:** `~/koperasicmsv2` — SALINAN berasingan dari projek asal
- **Repo:** `github.com/BaseriMN/koperasicms-with-tenant` (branch `main`)
- **Projek lama** (`koperasicms`) = Baling LIVE, TIDAK disentuh langsung
- ⚠️ **Hutang teknikal sedar:** bug fix di repo lama TAK auto masuk v2 (dan sebaliknya). Apply dua kali / cherry-pick. Bila Fasa 5 siap, **repo v2 jadi THE code base**, repo lama bersara.
- **Pakej:** `stancl/tenancy` **v3.10.0** (Laravel 12 OK)
- **DB dev:** PostgreSQL local — central DB dev: `koperasi_tenant`

### 9.2 Seni Bina (Implemented)

```
SATU CODE BASE (1 repo)
        │  request masuk → InitializeTenancyByDomain check domain
        ▼
┌─────────────────┬──────────────────┬──────────────────┐
│ central DB      │ tenantdemo       │ tenantkedah      │
│ (koperasi_      │ (DB penuh        │ (DB penuh        │
│  tenant):       │  koperasi demo)  │  koperasi kedah) │
│ tenants,        │                  │                  │
│ domains, cache, │ 27 migration     │ 27 migration     │
│ sessions        │ + seed essential │ + seed essential │
└─────────────────┴──────────────────┴──────────────────┘
```

- **Pengasingan:** database-per-tenant. Nama DB = `tenant` + id (config prefix).
- **Pengenalan tenant:** domain (table `domains`).
- **Tenant sedia ada (dev):** `demo` → demo.localhost, `kedah` → kedah.localhost.
- **Update code:** `git pull` → SEMUA tenant dapat serta-merta. Migration jadi 2 step (lihat 9.7).
- **Driver `.env`:** `SESSION_DRIVER / CACHE_STORE / QUEUE_CONNECTION = database` — pengasingan cache & session datang PERCUMA via connection switch (DatabaseTenancyBootstrapper tukar default connection → cache/session store ikut sekali).

### 9.3 Fail Penting & Perubahan

| Fail | Perubahan |
|------|-----------|
| `app/Models/Tenant.php` | BARU — extends BaseTenant, `use HasDatabase, HasDomains;` implements TenantWithDatabase |
| `config/tenancy.php` | `tenant_model => \App\Models\Tenant::class` (FQCN!); central_domains += koperasicms.site; `CacheTenancyBootstrapper` OFF; `asset_helper_tenancy => false` |
| `bootstrap/providers.php` | + `App\Providers\TenancyServiceProvider::class` |
| `app/Providers/TenancyServiceProvider.php` | `Jobs\SeedDatabase::class` di-UNCOMMENT (auto-seed bila tenant dicipta) |
| `routes/web.php` | BARU — central sahaja, loop `Route::domain()` atas central_domains, landing ringkas |
| `routes/app.php` | = web.php LAMA (di-rename via `git mv`, 176 line route app TIDAK diubah) |
| `routes/tenant.php` | group middleware tenancy → `require __DIR__.'/app.php';` |
| `app/Console/Commands/TenantCipta.php` | BARU — command provisioning (lihat 9.6) |
| `database/migrations/` | CENTRAL: tenants, domains, cache, **sessions (migration baru)** |
| `database/migrations/tenant/` | 26 migration app + 1 cache = **27 fail** |
| `resources/views/*` (9 fail, 12 tempat) | `asset('storage/' . $x)` → `tenant_asset($x)` (mass sed) |

### 9.4 Storage Per-Tenant

- Lokasi fail tenant: `storage/tenant<ID>/app/public/...` (auto oleh FilesystemTenancyBootstrapper, `suffix_storage_path => true`)
- Paparan fail upload: **WAJIB `tenant_asset($path)`** — serve via route `/tenancy/assets/...` (TenantAssetsController). Tiada symlink per tenant diperlukan. Tenant hanya nampak fail sendiri.
- `asset()` biasa = public/ central sahaja (helper hijack OFF).

### 9.5 Seeding

- `DatabaseSeeder` = **TenantSeeder de facto**: 6 essential sahaja —
  Role → Permission → SuperUser → ModuleAccess → AccountCategory → Setting.
  Sample seeders (Member/AccountEntry/Loan/MeetingSample) kekal COMMENTED.
- Auto-seed: pipeline `TenantCreated` → CreateDatabase → MigrateDatabase → SeedDatabase.
- Super-user (muhamad.baseri@gmail.com) di-seed dalam SETIAP tenant = jalan masuk pemilik SaaS.
- Seeder bersifat **idempotent** (selamat re-run; verified tiada duplikasi selepas double-seed kedah).

### 9.6 Command `tenant:cipta`

```bash
php artisan tenant:cipta <id> <domain> --nama="Nama Koperasi"
# cth: php artisan tenant:cipta kedah kedah.localhost --nama="Koperasi Kedah Berhad"
```

Buat: validasi (regex id `^[a-z0-9][a-z0-9-]{1,30}$`, tenant unik, domain unik) → create tenant (auto DB+migrate+seed) → pasang domain → set `nama_koperasi` via `$tenant->run()`.
**Auto-cleanup bila gagal:** `tenancy()->end()` → delete raw row domains+tenants → `DROP DATABASE IF EXISTS` (BUKAN `$tenant->delete()` — pipeline delete sendiri boleh fail).
Failure tests lulus: tenant wujud / domain dipakai / id tak sah.

### 9.7 Rutin Deploy BAHARU (bila v2 ke production)

```bash
git pull
composer dump-autoload
php artisan migrate --force            # central DB
php artisan tenants:migrate --force    # loop SEMUA tenant DB
php artisan optimize:clear
php artisan optimize
```

⚠️ `tenants:migrate` gagal separuh jalan = sesetengah tenant migrated, sesetengah tidak. Test migration di dev SEBELUM deploy. (Strategi handle penuh: bincang Fasa 5.)

### 9.8 GOTCHA BARU (Sesi 2 — darah & air mata)

1. **`tenant_model` WAJIB FQCN `\App\Models\Tenant::class`** dalam config/tenancy.php. Silap guna `Tenant::class` (ikut import Stancl base di atas fail) → error `Call to undefined method ...Tenant::database()` masa create tenant. Punca bug #1 sesi ni.
2. **CacheTenancyBootstrapper MESTI OFF** bila `CACHE_STORE=database` — dia guna cache tags (Redis/Memcached sahaja). Kalau ON → crash "does not support tagging". Pengasingan cache datang dari connection switch, bukan bootstrapper ni.
3. **`optimize:clear` selepas install stancl** — kalau tak: `Route [stancl.tenancy.asset] not defined`. (Gotcha #2 lama menyerang balik dalam konteks baru.)
4. **Half-state bila create tenant GAGAL:** row-tanpa-DB atau DB-tanpa-row. Cuci: delete raw `domains` + `tenants`, `DROP DATABASE IF EXISTS "tenantXXX"`. Command `tenant:cipta` dah handle auto.
5. **Table `sessions`:** versi tenant terbundle dalam migration users (folder tenant/) — central DB perlukan **migration sessions BERASINGAN** (dah dibuat). Simptom kalau tiada: central page error `relation "sessions" does not exist`.
6. **`tenant_asset($path)` BUKAN `asset('storage/'.$path)`** untuk semua fail upload (logo/avatar/foto). Dan set `asset_helper_tenancy => false` (kalau true, SEMUA asset() di-hijack → URL rosak).
7. **`migrate:fresh` = DROP SEMUA TABLE.** Guna di DB disposable sahaja. JANGAN SESEKALI di production.
8. **Edit tak save / VS Code buka folder salah** — bila grep tak match dengan apa yang "dah diedit", 99% tengok fail lain dari yang terminal tengok. Verify dengan `grep -n` SELEPAS setiap edit penting. (Berlaku 3 kali sesi ni 😅)
9. **Tinker: paste SATU line, tunggu hasil, baru line seterusnya.** Multi-line paste tersangkut/terpotong.
10. **`$tenant->run(closure)`** = jalankan code "dalam" tenant (initialize → execute → end). Berguna untuk set setting per tenant dari central context.
11. **`tenancy()->end()` sebelum DROP DATABASE** — PostgreSQL refuse drop DB yang ada active connection.

### 9.9 PENDING KHUSUS TENANCY (sebelum/semasa Fasa 5)

1. **[PARITY GAP]** `QUEUE_CONNECTION=database` tapi TIADA migration `create_jobs_table` dalam set v2. Check production Baling ada table `jobs` ke. Pilihan: tambah migration jobs ke tenant/, atau tukar `QUEUE_CONNECTION=sync`.
2. **[PARITY CHECK — KRITIKAL]** Banding schema migration v2 vs DB production Baling sebenar (`php artisan db:show` di production / pg_dump --schema-only) SEBELUM import. Migration v2 dah dirombak/direnumber — wajib sahkan tiada column tertinggal.
3. **[CLEANUP]** `create_savings_table` (deprecated, 0 rekod) masih dalam tenant migrations — decide buang sebelum Fasa 5 supaya tenant baru tak dapat table mati.
4. **[KEPUTUSAN TERBUKA]** Strategi domain production: subdomain wildcard (`*.koperasicms.site` — DNS wildcard + SSL wildcard, senang) vs custom domain per koperasi (perlu DNS + certbot per domain) vs dua-dua. Cadangan: subdomain default, custom domain optional.
5. **[BACKUP]** spatie/laravel-backup masih config single-DB — perlu jadi per-tenant (loop tenants / pg_dump per DB). Bincang Fasa 5.
6. **[STORAGE BALING]** Foto/logo/avatar Baling sedia ada perlu PINDAH ke `storage/tenantbaling/app/public/` masa migrate.

### 9.10 FASA 5 — RANGKA (Sesi 3)

> Fasa paling berisiko: production + data sebenar ~1000 ahli + downtime window. JANGAN mula tanpa plan penuh + backup verified.

Langkah kasar (akan diperhalusi awal Sesi 3):
1. Settle pending 9.9 #1–#4 di dev dulu
2. Backup penuh production (DB + storage) — verified restore
3. Deploy v2 ke server side-by-side (folder/vhost berasingan, JANGAN ganti yang live)
4. `pg_dump` DB Baling → restore sebagai `tenantbaling` + daftar row tenant + domain
5. Pindah storage files → `storage/tenantbaling/`
6. nginx multi-domain + DNS + SSL
7. Test menyeluruh atas domain staging (login, lejar, dividen, laporan — banding angka dengan live)
8. Switch domain sebenar + monitoring. Rollback plan: tukar balik nginx ke app lama (app lama TIDAK dipadam sehingga stabil berminggu)

---

## 10. CARA KERJA DENGAN USER (PREFERENCES)

- **JANGAN code semua** — user banyak nak GUIDE step-by-step, atau CARI/TUKAR (cari kod lama → tukar kod baru), bukan rewrite penuh.
- **Format edit:** "📄 path/fail" → CARI: (kod lama) → TUKAR JADI: (kod baru).
- **Brainstorm DULU sebelum code** bila user signal — bertindak sebagai senior dev + system designer + DB admin + juruakaun terbaik.
- User suka faham SEBAB, bukan terima je. Terangkan trade-off.
- Workspace AI (sandbox) ≠ fail sebenar user. Bagi CARI/TUKAR untuk user apply sendiri.
- Output deliverable (docx, md, dll) → guna fail, present untuk download.
- **Verify selepas edit** — minta user `grep -n` selepas edit penting (pengalaman: edit tak save 3 kali Sesi 2 😅).

---

## 11. PENDING / NEXT (kalau user nak sambung)

1. **[BUG]** MeetingController `$canCreate` guna `'admin'` → patutnya `'admin-koperasi'` (sebab tu admin-koperasi kena 403 di mesyuarat) — line ~13/17
2. **[OPTIONAL]** Modul pinjaman lengkap (faedah, ansuran, bayaran, jadual, penjamin, penyata)
3. **[OPTIONAL]** Penyata tahunan ahli (gabung saham + dividen + pinjaman)
4. **[OPTIONAL]** Google Drive backup credentials (OAuth setup + .env)
5. **[IN PROGRESS]** Multi-tenant SaaS — Fasa 1-4 SIAP di dev (lihat Seksyen 9). NEXT: Fasa 5 (migrate Baling, production) — mula Sesi 3 dengan "jom plan Fasa 5", settle 9.9 dulu
6. **[NOTE]** Dividen untung_bersih kadang tersimpan 738999.99 vs 739000 — workaround retype di draf

---

# BLUEPRINT DATABASE — KoperasiCMS

> Schema table sistem KoperasiCMS. Dijana berdasarkan modul yang dibina.
> **Nota:** Mungkin ada lajur kecil yang berbeza dari DB sebenar. Untuk schema 100% tepat,
> jalankan `php artisan db:show --counts` atau semak fail migration sebenar.
> DB production: **PostgreSQL** | DB dev: **PostgreSQL local** (v2).
> **Mulai Sesi 2:** sistem ada DUA jenis database — **CENTRAL** (1 sahaja) dan **TENANT** (1 per koperasi).
> Seksyen D0–D5 = struktur tenancy. Seksyen "TABLE TERAS" ke bawah = **struktur DB TENANT** (setiap koperasi).

---

## D0. SENI BINA DATABASE (TENANCY)

```
┌────────────────────────── CENTRAL DB ──────────────────────────┐
│  Nama (dev): koperasi_tenant                                   │
│  Isi: senarai tenant + infra. TIADA DATA KOPERASI LANGSUNG.    │
│                                                                 │
│  ┌─────────┐ 1   N ┌─────────┐   ┌───────┐ ┌──────────┐        │
│  │ tenants ├───────│ domains │   │ cache │ │ sessions │        │
│  └─────────┘       └─────────┘   └───────┘ └──────────┘        │
└────────────────────────────────────────────────────────────────┘
            │ id "demo"                │ id "kedah"
            ▼                          ▼
┌── TENANT DB: tenantdemo ──┐  ┌── TENANT DB: tenantkedah ──┐
│ STRUKTUR SAMA SEMUA:      │  │ (struktur SERUPA, data     │
│ users, roles, permissions,│  │  BERBEZA & TERPISAH TOTAL) │
│ members, transactions,    │  │                            │
│ loans, meetings, akaun,   │  │  + cache, sessions,        │
│ dividend, settings, ...   │  │    password_reset_tokens   │
│ (= seksyen TABLE bawah)   │  │    (per tenant!)           │
└───────────────────────────┘  └────────────────────────────┘
```

**Peraturan emas:**
- Nama DB tenant = `tenant` + id tenant (config `tenancy.database.prefix`). Cth: id `baling` → DB `tenantbaling`.
- Migration central: `database/migrations/` (4 fail). Migration tenant: `database/migrations/tenant/` (27 fail).
- Setiap DB (central & setiap tenant) ada table `migrations` SENDIRI — rekod migration masing-masing.
- Query merentas tenant TIDAK boleh berlaku secara semula jadi — itulah point pengasingan.

---

## D1. TABLE CENTRAL DB

### `tenants` — Senarai koperasi (stancl)
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | string PK | ID tenant, cth `demo`, `kedah`, `baling` (BUKAN auto-increment) |
| data | json nullable | VirtualColumn stancl — atribut custom tenant disimpan sini (cth `tenancy_db_name`) |
| timestamps | | created_at, updated_at |

**Model:** `App\Models\Tenant` (extends Stancl BaseTenant + `HasDatabase`, `HasDomains`)
⚠️ Atribut custom (selain id/timestamps) auto masuk lajur `data` (json) — sifat VirtualColumn. `$tenant->tenancy_db_name` = nama DB tenant.

### `domains` — Domain → tenant mapping (stancl)
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | bigint PK | |
| domain | string unique | cth `demo.localhost`, `kedah.localhost`. TANPA http:// atau port |
| tenant_id | string FK → tenants | cascade delete |
| timestamps | | |

> `InitializeTenancyByDomain` middleware lookup table ni pada SETIAP request domain bukan-central. 1 tenant boleh ada banyak domain (subdomain + custom domain serentak — berguna untuk strategi domain production nanti).

### `cache` + `cache_locks` — Cache CENTRAL sahaja
| Lajur | Jenis |
|-------|-------|
| key | string PK |
| value | mediumText |
| expiration | integer |

(cache_locks: key, owner, expiration)

### `sessions` — Session untuk domain CENTRAL
| Lajur | Jenis | Nota |
|-------|-------|------|
| id | string PK | |
| user_id | FK nullable, index | |
| ip_address | string(45) nullable | |
| user_agent | text nullable | |
| payload | longText | |
| last_activity | integer, index | |

> **Migration BARU dibuat Sesi 2** (`create_sessions_table` di root migrations). Tanpa ni, central page error `relation "sessions" does not exist` sebab versi tenant terbundle dalam migration users yang dah pindah ke folder tenant/.

### `migrations` — Rekod migration central (Laravel default)

---

## D2. TABLE TENANT DB (setiap koperasi)

**= SEMUA table dalam seksyen "TABLE TERAS" ke bawah**, tiada perubahan struktur:

- **RBAC:** users, roles, permissions, role_user, permission_role, module_role
- **Keahlian:** members, next_of_kins, ownership_transfers
- **Lejar:** transactions, share_transfers
- **Pinjaman:** loans
- **Mesyuarat:** meetings
- **Akaun:** account_categories, account_entries
- **Dividen:** dividend_runs, dividend_allocations, dividend_shares (+ tabung)
- **Sistem:** settings
- **Deprecated:** savings (⚠️ pending buang dari migration set sebelum Fasa 5)

**TAMBAHAN per tenant (infra, dulu "kongsi" — sekarang SETIAP tenant ada sendiri):**

| Table | Sumber migration | Nota |
|-------|------------------|------|
| cache + cache_locks | copy `create_cache_table` dalam tenant/ | Cache TERPISAH per tenant — `Setting::all_cached()` selamat, tiada kebocoran antara koperasi |
| sessions | terbundle dalam `create_users_table` | Login session per tenant |
| password_reset_tokens | terbundle dalam `create_users_table` | |
| migrations | auto | rekod 27 migration tenant |

**Kiraan:** 27 fail migration dalam `database/migrations/tenant/` (26 app + 1 cache).

---

## D3. MEKANISME PENGASINGAN (macam mana data tak bercampur)

| Lapisan | Mekanisme |
|---------|-----------|
| Query Eloquent/DB | `DatabaseTenancyBootstrapper` tukar DEFAULT connection ke DB tenant — semua model app automatik hala ke DB betul, TIADA ubah code model |
| Cache | `CACHE_STORE=database` + connection switch → table cache tenant sendiri. (`CacheTenancyBootstrapper` OFF — tak perlu) |
| Session | `SESSION_DRIVER=database` + connection switch → table sessions tenant sendiri |
| Storage/fail | `storage/tenant<ID>/app/public/...` — folder fizikal berasingan per tenant |
| Paparan fail | `tenant_asset($path)` — controller stancl serve dari storage tenant SEMASA sahaja; tenant A mustahil nampak fail tenant B |
| Akses central dari tenant route | `PreventAccessFromCentralDomains` middleware |

**Cross-tenant secara sengaja (untuk admin SaaS):** `$tenant->run(fn() => ...)` atau `tenancy()->initialize($tenant)` — guna dengan SEDAR sahaja (cth command provisioning, laporan SaaS).

---

## D4. KONVENSYEN OPERASI DB (TENANCY)

1. **Migrate:** `php artisan migrate` = CENTRAL sahaja. `php artisan tenants:migrate` = loop SEMUA tenant. Dua-dua perlu dalam rutin deploy.
2. **Seed tenant:** auto masa cipta (pipeline SeedDatabase) atau manual `php artisan tenants:seed --tenants=<id>`. Seeder essential idempotent (selamat re-run).
3. **Tinker dalam tenant:** `tenancy()->initialize(Tenant::find('id'))` dulu, baru query model app. Verify dengan `DB::connection()->getDatabaseName()`.
4. **Run command artisan untuk tenant:** `php artisan tenants:run "<command>" --tenants=<id>`.
5. **PostgreSQL user perlu hak `CREATEDB`** untuk provisioning (`ALTER USER xxx CREATEDB;`).
6. **DROP DB tenant manual:** `tenancy()->end()` dulu (Postgres refuse drop DB dengan active connection), nama DB di-quote: `DROP DATABASE IF EXISTS "tenantxxx"`.
7. **Half-state cleanup** (row tanpa DB / DB tanpa row): delete raw `domains` + `tenants`, DROP DATABASE — JANGAN `$tenant->delete()` (pipeline boleh fail).
8. **Backup:** WAJIB jadi per-tenant DB (pg_dump setiap `tenant*`) + central — config spatie sedia ada belum cover ni (pending Fasa 5).
9. Semua nota schema lama KEKAL terpakai dalam setiap tenant DB: decimal money + `wang()`, LOWER() untuk LIKE pgsql, migration order FK, audit trail "siapa buat".

---

## D5. CARA SAHKAN SCHEMA SEBENAR

```bash
# Central
php artisan db:show --counts

# Tenant tertentu
php artisan tenants:run "db:show --counts" --tenants=demo

# Senarai semua tenant + domain
php artisan tenants:list
```

---

## DIAGRAM HUBUNGAN DB TENANT (ERD RINGKAS)

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

> Nama table tabung mungkin berbeza (cth `dividend_tabung` / `tabungs`) — sahkan di migration. Migration set v2 juga ada `dividend_allocations` (000018) — sahkan fungsi di fail migration.

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
> SavingController + model Saving + view simpanan/ ialah modul LAMA yang bertindih dengan TransactionController. **0 rekod** — selamat dibuang. Sistem sebenar guna `transactions` (jenis=simpanan/saham). Migration `create_savings_table` masih dalam set tenant — pending buang sebelum Fasa 5 (lihat 9.9 #3).

---

## TABLE LARAVEL DEFAULT (per tenant DB)
- `password_reset_tokens` (terbundle dalam migration users)
- `sessions` (terbundle dalam migration users — SESSION_DRIVER=database)
- `cache`, `cache_locks` (copy migration dalam tenant/)
- `migrations`
- ⚠️ `jobs`, `job_batches`, `failed_jobs` — TIADA dalam migration set v2 walaupun QUEUE_CONNECTION=database (parity gap, lihat 9.9 #1)

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

*Tamat blueprint. Sesi 3: mula dengan "jom plan Fasa 5" — settle 9.9 dulu sebelum sentuh production. Untuk schema 100% tepat, semak `database/migrations/tenant/` atau `php artisan tenants:run "db:show" --tenants=demo`.*

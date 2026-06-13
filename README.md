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
- **Driver `.env`:** `SESSION_DRIVER / CACHE_STORE / QUEUE_CONNECTION = database` — pengasingan cache & session datang PERCUMA cache: lihat gotcha 9.8 #12 via connection switch (DatabaseTenancyBootstrapper tukar default connection → cache/session store ikut sekali). 

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
12. **CACHE LEAK — pengasingan cache via connection switch TAK percuma untuk web request!**
    Laravel memoize cache store SEKALI per request. Kalau store ter-resolve sebelum
    tenancy init (framework/middleware/vendor), dia terikat ke connection CENTRAL
    sampai habis request — cache SEMUA tenant masuk satu table cache central
    (simptom: settings/tema satu tenant "berjangkit" ke tenant lain, sebab
    `Setting::all_cached()` baca periuk yang sama). DB sebenar TIDAK bocor.
    **FIX (dah apply):** `Cache::forgetDriver(config('cache.default'))` pada event
    `TenancyInitialized` + `TenancyEnded` dalam TenancyServiceProvider::boot()
    (selepas makeTenancyMiddlewareHighestPriority). Listener didaftar SELEPAS
    bootEvents() supaya jalan selepas BootstrapTenancy (connection switch dulu,
    baru re-bind cache).
    **Cara diagnose:** `DB::table('cache')->pluck('key')` di central vs dalam
    setiap tenant — tengok cache key duduk DB mana. (Ditemui Sesi 2.5, tenant
    test kpmbb + kopetro.)

### 9.9 PENDING KHUSUS TENANCY (sebelum/semasa Fasa 5)

1. **[PARITY GAP]** `QUEUE_CONNECTION=database` tapi TIADA migration `create_jobs_table` dalam set v2. Check production Baling ada table `jobs` ke. Pilihan: tambah migration jobs ke tenant/, atau tukar `QUEUE_CONNECTION=sync`.
2. **[PARITY CHECK — KRITIKAL]** Banding schema migration v2 vs DB production Baling sebenar (`php artisan db:show` di production / pg_dump --schema-only) SEBELUM import. Migration v2 dah dirombak/direnumber — wajib sahkan tiada column tertinggal.
3. **[CLEANUP]** `create_savings_table` (deprecated, 0 rekod) masih dalam tenant migrations — decide buang sebelum Fasa 5 supaya tenant baru tak dapat table mati.
4. **[KEPUTUSAN TERBUKA]** Strategi domain production: subdomain wildcard (`*.koperasicms.site` — DNS wildcard + SSL wildcard, senang) vs custom domain per koperasi (perlu DNS + certbot per domain) vs dua-dua. Cadangan: subdomain default, custom domain optional.
5. **[BACKUP]** spatie/laravel-backup masih config single-DB — perlu jadi per-tenant (loop tenants / pg_dump per DB). Bincang Fasa 5.
6. **[STORAGE BALING]** Foto/logo/avatar Baling sedia ada perlu PINDAH ke `storage/tenantbaling/app/public/` masa migrate.

# UPDATE BLUEPRINT — SESI 2.5 (12 Jun 2026, malam)

> Tampal dokumen ini BERSAMA blueprint utama di awal sesi baru.
> Sesi ini: fix cache leak antara tenant + settle pending 9.9 #1-#4.
> Semua perubahan code DAH commit & push ke repo v2 (koperasicms-with-tenant).

---

## 1. STATUS PENDING 9.9 (SEMUA #1-#4 SELESAI ✅)

| # | Item | Keputusan / Hasil |
|---|------|-------------------|
| 1 | Parity gap `jobs` | ✅ SELESAI — Production pun TIADA table jobs, grep sahkan ZERO kod guna queue. `.env` dev dah tukar `QUEUE_CONNECTION=sync`. ⚠️ `.env` production v2 nanti WAJIB sync jugak (masuk checklist Fasa 5) |
| 2 | Parity check schema | ✅ LULUS PENUH — diff column-level Baling production vs tenantkpmbb (fresh v2): **IDENTICAL 214=214 baris** (table+column+type). Kaveat: index/default/FK detail tak dibanding (risiko rendah). Fail rujukan: `~/koperasicmsv2/baling_schema.sql`, `v2_schema.sql`, `baling_cols.txt`, `v2_cols.txt` |
| 3 | Buang savings | ✅ SELESAI — `git rm` 4 item: migration `0001_01_01_000006_create_savings_table.php`, `app/Models/Saving.php`, `app/Http/Controllers/SavingController.php`, `resources/views/simpanan/` (2 blade). Verified: tenant ujian baru = **24 table, tiada savings**, route:list OK. ⚠️ JANGAN sentuh `Events\SavingTenant` / `SavingDomain` dalam TenancyServiceProvider — itu event stancl, bukan modul savings! |
| 4 | Strategi domain | ✅ KEPUTUSAN: **Subdomain wildcard** `*.koperasicms.site` (default) + custom domain optional kemudian (stancl support multi-domain per tenant). DNS pindah ke **Cloudflare** (free, nameserver sahaja — domain kekal Namecheap). SSL = wildcard Let's Encrypt via DNS-01 + plugin `dns-cloudflare`. Status execution: **TERGANTUNG** — Cloudflare dashboard DOWN (incident 12 Jun 10:27PM GMT+8) masa nak add site. SAMBUNG: add site → tukar nameserver Namecheap → 3 rekod A (`@`, `*`, `www` → IP VPS, semua DNS-only/kelabu dulu) → API token (Zone:DNS:Edit) untuk certbot |

**Struktur domain production yang dipersetujui:**
```
koperasicms.site / www      → CENTRAL (landing SaaS)
baling.koperasicms.site     → tenant Baling
<id>.koperasicms.site       → tenant lain (auto via wildcard)
```

---

## 2. GOTCHA BARU — TAMBAH KE 9.8

**#12 — CACHE LEAK: pengasingan cache via connection switch TAK percuma untuk web request!**
Laravel memoize cache store SEKALI per request. Kalau store ter-resolve sebelum tenancy init, dia terikat ke connection CENTRAL sampai habis request — cache SEMUA tenant masuk satu table cache central. Simptom: settings/tema satu tenant "berjangkit" ke tenant lain (`Setting::all_cached()` baca periuk sama). DB sebenar TIDAK bocor.
**FIX (DAH APPLY + commit):** `Cache::forgetDriver(config('cache.default'))` pada event `TenancyInitialized` + `TenancyEnded`, didaftar di hujung `TenancyServiceProvider::boot()` (selepas bootEvents → jalan selepas BootstrapTenancy/connection switch).
**Cara diagnose:** `DB::table('cache')->pluck('key')` di central vs setiap tenant — tengok cache key duduk DB mana.
**Verified:** cache `settings.all` kini duduk dalam table cache tenant masing-masing; ujian browser tukar setting kpmbb → kopetro TAK terjejas.

**Kemaskini jadual D3 (baris Cache):** `CACHE_STORE=database` + connection switch + **WAJIB forgetDriver pada TenancyInitialized/Ended (gotcha 9.8 #12)**. CacheTenancyBootstrapper kekal OFF. Ayat "pengasingan cache datang PERCUMA" dalam 9.2 = SALAH separuh, rujuk gotcha ni.

---

## 3. PEMBETULAN / FAKTA BARU BLUEPRINT

1. **Table waris = `next_of_kin` (SINGULAR)** — blueprint tulis `next_of_kins`, yang betul singular (sahkan dari schema production & v2).
2. **Module key route lejar = `simpanan_saham`** (routes/app.php line 113), bukan `lejar_transaksi` macam dalam blueprint. BELUM verify config/modules.php — sahkan dengan `grep simpanan_saham config/modules.php` bila sempat. Bukan blocker.
3. **STATUS PRODUCTION SEBENAR: masih TESTING** — www.koperasicms.site belum ada ahli aktif yang bergantung harian. Implikasi BESAR untuk Fasa 5: risiko/tekanan downtime jauh berkurang, boleh **rehearse migrate 2 kali** (kali 1 latihan, reset, kali 2 ikut runbook diperhalusi). Kerumitan "siapa dapat www" pun hilang — www terus jadi central.
4. **⚠️ SECURITY:** password DB production = `123456` (user `baseri`, db `koperasi`). pgsql bind 127.0.0.1 (tak terdedah terus) TAPI **WAJIB tukar ke password kuat sebelum Fasa 5** — selaras dengan `.env` + restart. Masuk checklist.
5. **Periuk api Fasa 5 — table `migrations`:** pg_dump Baling bawa rekod migration dengan NAMA LAMA; v2 migration dah direnumber. Lepas restore jadi `tenantbaling`, `tenants:migrate` akan cuba run migration v2 atas table sedia ada → meletup. **Plan: lepas import, sync table migrations** (truncate + insert nama fail migration v2 sebagai "dah run"). Perlu masuk runbook Fasa 5.
6. Tenant dev semasa: `demo`, `kedah`, `kpmbb`, `kopetro`. Tenant ujian `ujiansavings` dah dicuci (SOP: delete raw domains+tenants, DROP DATABASE).
7. Dev tenant lama (kpmbb/kopetro/demo/kedah) masih ADA table `savings` + rekod migration fail yang dah dipadam — tak harmful, Laravel tolerate. Tenant baru selepas commit ni = 24 table bersih.
8. Extension `intl` tiada di PHP dev — `db:show` error kosmetik bahagian format saiz. Optional: `sudo apt install php8.4-intl`.
9. VPS provider: Cloudzy(?) — domain di Namecheap. Username pgsql dev = `baseri` jugak (connect guna `-h 127.0.0.1` + password, peer auth gagal sebab OS user `muham`).

---

## 4. NEXT / SAMBUNGAN (ikut turutan)

1. **[SAMBUNG] Cloudflare DNS** — tunggu incident pulih → add site `koperasicms.site` → plan Free → tukar nameserver di Namecheap → rekod A `@`/`*`/`www` → IP VPS (DNS-only kelabu) → tunggu zone Active.
2. **[SERVER] Wildcard SSL** — create Cloudflare API token (Zone:DNS:Edit, zone ni sahaja) → pasang certbot + plugin dns-cloudflare di VPS → issue cert `*.koperasicms.site` + `koperasicms.site` → auto-renew test.
3. **[#5] Checklist test isolation menyeluruh** — belum draft. Cakupan minimum: session login cross-tenant, storage/fail (`tenant_asset`), cache (dah lulus hari ni), route central vs tenant, data CRUD cross-check, queue (sync — N/A).
4. **[FASA 5] Runbook penuh** — guna rangka 9.10 + tambahan sesi ni: sync table migrations (#5 atas), tukar password DB production, QUEUE sync di .env production, drop table savings dalam tenantbaling lepas import, nginx 2 server block (central + wildcard), strategi rehearse 2x.
5. **[KEKAL PENDING]** Bug MeetingController `'admin'`→`'admin-koperasi'`; backup spatie per-tenant; Google Drive credentials.

---

*Mula sesi baru dengan: "sambung dari Update Sesi 2.5" — terus ke item Next #1 (Cloudflare) atau #3 (checklist isolation) ikut keadaan.*


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
| Cache | `CACHE_STORE=database` + connection switch + **WAJIB forgetDriver pada TenancyInitialized/Ended** (gotcha 9.8 #12 — tanpa ni cache bocor ke central). `CacheTenancyBootstrapper` kekal OFF |
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



# KoperasiCMS — Blueprint Induk & Roadmap (Master README)

> **Dokumen tunggal sumber kebenaran (single source of truth).**
> Tampal dokumen ini di awal setiap sesi baru. Mula sesi dengan: *"sambung dari README induk"*.
> Bahasa: Melayu Malaysia. Versi terakhir dikemaskini: **akhir Sesi 3 (13 Jun 2026)**.

---

## 0. RINGKASAN PROJEK

**KoperasiCMS** — sistem pengurusan koperasi yang sedang ditransformasi daripada aplikasi single-koperasi kepada **platform SaaS multi-tenant**.

- **Asal:** sistem untuk Koperasi Perniagaan Melayu Baling (KPMBB), ~1000 ahli, koperasi berasaskan **SAHAM** (modul simpanan toggle OFF).
- **Matlamat:** SaaS multi-tenant — satu code base, banyak koperasi, data setiap koperasi terasing sepenuhnya (DB-per-tenant).
- **Model bisnes:** onboard banyak koperasi (subdomain sendiri), tier/plan, suspend, billing.

### Stack teknikal
| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 12 (12.61.0) |
| PHP | 8.4 |
| Database | PostgreSQL 16.14 |
| Multi-tenancy | stancl/tenancy v3.10 (DB-per-tenant, kenal via domain) |
| Frontend | Blade + Alpine.js |
| Web server | nginx 1.24.0 (Ubuntu 24.04) |
| Reverse proxy / SSL / DNS | Cloudflare (proxy + Origin cert) |
| Backup | pg_dumpall + rclone → Google Drive (cron harian 2 pagi) |

### Persekitaran
| Env | Lokasi | Nota |
|---|---|---|
| **Local (dev)** | `~/koperasicmsv2` (Windows/WSL `muham@BASERIMN`) | Mesin pembangunan |
| **Server (production)** | VPS `216.126.236.127`, `baseri@ubuntu-Utah-1gb`, path `/home/baseri/projek/koperasicms-with-tenant` | Ubuntu 24.04, SSH port **2234** |
| **Repo** | GitHub `BaseriMN/koperasicms-with-tenant` (branch `main`) | repo v2 = THE code base |

### Struktur domain (production)
```
koperasicms.site / www      → CENTRAL (landing SaaS + panel admin — belum bina)
<id>.koperasicms.site       → tenant (auto via wildcard *)
```
- DNS di **Cloudflare** (nameserver `alina` + `mike.ns.cloudflare.com`), domain didaftar di Namecheap.
- Wildcard `*.koperasicms.site` → semua subdomain tenant auto-resolve (tak perlu tambah DNS record setiap onboard).

---

## 1. STATUS SEMASA (akhir Sesi 3)

### Yang dah SIAP & verified
- ✅ **Fasa 1–4** (dev): central DB + tenant DB, routing domain, auto-seed, storage per-tenant (`tenant_asset()`), command `tenant:cipta` dengan auto-cleanup.
- ✅ **Fasa A — Verify Deployment** (Sesi 3): server diverifikasi penuh (lihat §6).
- ✅ **Fasa B — Test Isolation 15/15 LULUS** (Sesi 3): sistem terbukti selamat untuk multi-koperasi (lihat §7).
- ✅ Cloudflare DNS + SSL (Proxy + Origin cert + Full strict).
- ✅ Firewall UFW (443 hanya dari range Cloudflare).
- ✅ Backup harian berfungsi (pg_dumpall → Google Drive).

### Tenant aktif sekarang
- `ujian1`, `ujian2` (tenant ujian — boleh cuci bila-bila ikut SOP §11).
- Tenant lama `kpmbb`, `kpkbaling`, `demo`, `kedah`, `kopetro` — **semua dah dipadam/sampah testing**, tiada data sebenar.

### Hutang/pending utama (lihat §10 untuk senarai penuh)
- ⏳ Deploy code terbaru ke server (commit `bootstrap/app.php` trustProxies belum `git pull`).
- ⏳ Fix `config/tenancy.php` `--force` (seed production) — masih guna jalan tengah.
- ⏳ Hutang #2 DNS sudah **SELESAI** (record redundant dipadam).

### Roadmap (urutan mutlak — JANGAN langkau)
```
A ✅ → B ✅ → C → E → F
(Fasa D — Migrate Baling — DIPADAM, tiada data Baling sebenar)
```

---

## 2. KEPUTUSAN SENI BINA (locked-in)

1. **DB-per-tenant**, kenal tenant **via domain** (stancl/tenancy). Central DB = `koperasi_tenant`.
2. **Subdomain wildcard** `*.koperasicms.site` (default) + custom domain optional kemudian (stancl support multi-domain per tenant).
3. **SSL strategi = Cloudflare Proxy + Origin Certificate** (BUKAN certbot Let's Encrypt). Lihat §5.
4. **Suspend ≠ Delete** — data tenant TIDAK pernah dipadam sebab tak bayar; pintu dikunci sahaja.
5. **Wang = `decimal`** + helper `wang()` (elak float bug). Audit "siapa buat" pada setiap tindakan kritikal.
6. **White-label + module toggle** dikawal via `settings` per-tenant (bukan struktur DB).

---

## 3. GOTCHA & PERATURAN KRITIKAL (jangan lupa)

### Gotcha pengaturcaraan
1. **Slug role = `admin-koperasi`, BUKAN `admin`** — punca #1 bug akses. (Bug pending: MeetingController `$canCreate` masih guna `'admin'` — perlu fix.)
2. **Table waris = `next_of_kin` (SINGULAR)** — bukan `next_of_kins`. (Disahkan dari schema sebenar Sesi 3.)
3. **Module key lejar = `simpanan_saham`** (routes/app.php), bukan `lejar_transaksi`. (Belum verify `config/modules.php`.)
4. **`avatar_path` (users) ≠ `foto_path` (members)** — dua benda berasingan.
5. **PostgreSQL LIKE case-sensitive** → guna `LOWER()` + `whereRaw` untuk carian.
6. **Migration order** — table yang rujuk members/meetings/loans perlu nombor migration lebih tinggi (FK).
7. **`tenant_asset()`** untuk SEMUA fail upload tenant (logo/avatar/foto).
8. **`composer dump-autoload`** lepas edit `helpers.php`.

### Gotcha #12 — CACHE LEAK antara tenant (KRITIKAL)
- **Masalah:** Laravel memoize cache store sekali per request. Kalau store ter-resolve sebelum tenancy init, ia terikat ke connection CENTRAL sampai habis request → cache semua tenant bocor masuk satu table cache central. Simptom: setting/tema satu tenant "berjangkit" ke tenant lain.
- **FIX (dah apply + verified di dev DAN server):** `Cache::forgetDriver(config('cache.default'))` pada event `TenancyInitialized` + `TenancyEnded`, didaftar dalam `TenancyServiceProvider` (line ~115 & ~119).
- `CacheTenancyBootstrapper` kekal **OFF**. `CACHE_STORE=database`.
- ⚠️ JANGAN sentuh `Events\SavingTenant` / `SavingDomain` dalam TenancyServiceProvider — itu event stancl, bukan modul savings.

### Peraturan deploy & kerja
- **Edit di LOCAL** → commit → push → `git pull` di server. JANGAN edit code terus di server (elak git conflict).
- **Pengecualian:** `.env`, firewall, DNS, `db_backup.py`, data DB = server-only (tak masuk git).
- **`optimize:clear` / `config:clear`** lepas deploy. (Alias `cuci` di server = clear + cache config + cache routes.)
- **Rutin deploy v2 = 2-step migration:** `migrate` (central) + `tenants:migrate` (semua tenant).
- **Verify dengan `grep -n` selepas edit** (pengalaman pahit: edit tak save).
- **Backup sebelum edit fail penting** (`cp fail fail.bak-$(date ...)`).
- **`DROP DATABASE ... WITH (FORCE)`** untuk elak masalah connection pool masa padam tenant.

---

## 4. CARA KERJA DENGAN CLAUDE (preferensi user)

- **Bahasa:** Melayu Malaysia, santai (panggil "bro" ok).
- **Bukan rewrite penuh** — guide step-by-step, atau format CARI/TUKAR (📄 path → kod lama → kod baru).
- **Brainstorm dulu** — terang sebab & trade-off sebelum laksana.
- **Satu langkah satu masa** untuk operasi berisiko (DB, firewall) — verify output sebelum gerak.
- **Push back bila perlu** — kalau user nak over-engineer atau ada cara lebih baik, cakap.
- **Guna `ask_user_input`** untuk pilihan/keputusan supaya senang di mobile.

---

## 5. INFRASTRUKTUR SERVER (server-only, TAK masuk git)

### Cloudflare + SSL (Sesi 3)
- **Strategi:** Cloudflare Proxy (orange cloud) + **Cloudflare Origin Certificate** dipasang di nginx.
- Cert di VPS: `/etc/ssl/cloudflare/koperasicms.site.pem` + `.key` (expire **2041** — set-and-forget).
- **SSL mode di Cloudflare = Full (strict).**
- **certbot TAK diperlukan** (keputusan asal Sesi 2.5 guna certbot Let's Encrypt — DITUKAR).
- DNS records (semua **Proxied/orange**): `*.koperasicms.site`, `koperasicms.site`, `www` → `216.126.236.127`.
- Cara verify trafik lalu CF: cari header `cf-ray` + `server: cloudflare` dalam `curl -sI`.

### Firewall UFW (Sesi 3)
- Port **2234** (SSH) — ALLOW.
- Port **80** — ALLOW (HTTP redirect).
- Port **443** — ALLOW **hanya dari range IP Cloudflare** (15 IPv4 + 7 IPv6, fetch dari `cloudflare.com/ips-v4` & `ips-v6`).
- Default: `deny incoming`. Akses terus ke IP VPS port 443 dari bukan-CF → **timeout** (terbukti).
- Sebab penting: melindungi `trustProxies('*')` daripada IP spoofing.

### Real IP (trustProxies) — hutang #1 SELESAI
- **App side:** `bootstrap/app.php` set `$middleware->trustProxies(at: '*', headers: X_FORWARDED_FOR|HOST|PORT|PROTO)`. Commit dah di GitHub, **belum pull ke server**.
- **Server side:** firewall UFW (di atas) — selesai.
- Kesan: `central_activity_logs.ip` nanti rekod IP pelawat sebenar (bukan IP CF). Header CF: `CF-Connecting-IP` / `X-Forwarded-For`.

### Database PostgreSQL (server)
- User app: **`baseri`** (bukan `postgres` lagi — ditukar Sesi 3) + password kuat (dalam `.env`, BUKAN `123456` lagi).
- `baseri` ada `CREATEDB` (untuk stancl cipta tenant DB baru).
- DB sedia ada: `koperasi_tenant` (central), `tenantujian1`, `tenantujian2` — owner `baseri`.
- DB `koperasi` (lama, kosong, sampah) — owner `postgres`, **belum dipadam** (boleh padam bila-bila).
- Tenant DB lama owned by `postgres` perlu `GRANT ALL` + `ALTER DEFAULT PRIVILEGES` ke `baseri`. Tenant BARU (dicipta selepas tukar `.env`) auto-owned `baseri` (takde isu).
- Auth: connect via TCP `127.0.0.1` (md5/password), BUKAN peer auth.

### `.env` server (kunci penting — `chmod 640`, group `www-data`)
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://koperasicms.site
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=koperasi_tenant
DB_USERNAME=baseri
DB_PASSWORD=<password kuat — simpan dalam password manager>
QUEUE_CONNECTION=sync
CACHE_STORE=database
SESSION_DRIVER=database
```

### Backup automatik (`/home/baseri/scripts/db_backup.py`)
- Cron: `0 2 * * *` (setiap hari 2 pagi) + `* * * * * schedule:run`.
- **Direka semula Sesi 3:** baca credential dari `.env` Laravel (satu sumber kebenaran) — BUKAN hardcode password lagi.
- `ENV_FILE = /home/baseri/projek/koperasicms-with-tenant/.env`.
- `pg_dumpall` semua DB → gzip → `rclone move` ke Google Drive `gdrive:Koperasi_ServerBackups`.
- Permission: script `700`, `.env` `640`.
- ⚠️ Belum ada: rotation backup, notifikasi gagal, logrotate untuk `backup.log`.
- ⚠️ Backup belum pernah **di-restore-test** (Fasa C2).

### Permission storage
- `storage/` + `bootstrap/cache/` → mode `775`, owner `www-data:www-data`.
- ⚠️ Tiada setgid (`g+s`) — polish Fasa C kalau nak konsisten SOP penuh.

---

## 6. FASA A — VERIFY DEPLOYMENT (✅ SELESAI Sesi 3)

| # | Item | Hasil |
|---|---|---|
| A1 | DB lama selamat | ⏭️ SKIP — app lama dah dibuang, tiada DB lama nak jaga |
| A2 | `.env` v2 server | ✅ DB user/password/url betul, GRANT semua DB |
| A3 | `tenants:list` | ✅ |
| A4 | Cache leak fix di server | ✅ `forgetDriver` ada (line 115 & 119) |
| A5 | SSL | ✅ CF Proxy + Origin + Full strict |
| A6 | Cron + permission | ✅ schedule:run + backup, storage 775 |
| A7 | nginx | ✅ 2 server block (central + wildcard regex) |

**Penemuan besar Sesi 3:** App lama (Baling) sudah dibuang dari VPS. Tiada data koperasi sebenar — semua testing. Maka **Fasa D (Migrate Baling) dipadam** dari roadmap.

### nginx config (rujukan)
- Server block 1: `server_name koperasicms.site www.koperasicms.site` (central).
- Server block 2: `server_name ~^(?<tenant>.+)\.koperasicms\.site$` (wildcard tenant, regex).
- Kedua-dua: `listen 443 ssl http2`, cert sama (`/etc/ssl/cloudflare/...`), HTTP→HTTPS redirect.

---

## 7. FASA B — TEST ISOLATION (✅ 15/15 LULUS Sesi 3)

| Kumpulan | Ujian | Hasil |
|---|---|---|
| Session | 1–3 (login serentak, logout, tukar password) | ✅ |
| Data CRUD | 4–7 (member, transaksi, mesyuarat+pinjaman, akaun+dividen) | ✅ tiada bocor |
| Settings/Cache | 8 (tukar nama+tema) | ✅ cache fix kerja di server |
| Storage | 9–11 (upload, folder isolation, cross-access 404) | ✅ `tenant_asset` betul |
| Route | 12–14 (central vs tenant, domain tak wujud) | ✅ error bersih |
| No Ahli | 15 (turutan `A0001` sendiri) | ✅ dua-dua tenant mula A0001 |

**Bukti storage isolation:** fail dalam `storage/tenant<id>/app/public/{logos,avatars,members}/`. Cross-access dari domain lain → 404.
**Kredensial seeder super-user:** `muhamad.baseri@gmail.com` / `@Password12345` (role `super-user`).

---

## 8. ROADMAP BERPRIORITI (Sesi 3 → seterusnya)

> Peraturan emas: JANGAN mula fasa sebelum kriteria fasa sebelumnya dicapai.

```
FASA A: Verify Deployment      ✅ SELESAI (Sesi 3)
FASA B: Test Isolation (15)    ✅ SELESAI (Sesi 3)
FASA C: Kemas & Kunci          ← SETERUSNYA
FASA E: SaaS Panel             (suspend/tier/billing)
FASA F: Modul Koperasi         (pinjaman penuh, portal ahli, dll)
(FASA D: Migrate Baling        — DIPADAM, tiada data sebenar)
```

### FASA C — Kemas & Kunci (SETERUSNYA)
> Sebahagian dah selesai awal sebab kerja Sesi 3.

- **C1.** Tukar password DB production — ✅ **SELESAI** (Sesi 3: `123456` → kuat, user `postgres` → `baseri`).
- **C2.** Backup + **VERIFIED restore** — separuh: backup berfungsi ✅, tapi belum pernah test restore. *"Backup yang tak pernah di-restore = bukan backup."* Perlu: restore ke DB temp, banding kiraan row.
- **C3.** Bug MeetingController `'admin'` → `'admin-koperasi'` (5 minit, commit).
- **C4.** Sahkan module key — `grep simpanan_saham config/modules.php`; selaras blueprint.
- **C5.** Failed-job/error visibility — `storage/logs` terjaga; optional notifikasi error (telegram/email). Tambah logrotate untuk `backup.log`.
- **C6 (baru).** Deploy code terbaru ke server: `git pull` (`bootstrap/app.php` trustProxies + `config/tenancy.php` `--force`).
- **C7 (baru).** Padam DB sampah `koperasi`.
- **C8 (polish).** setgid `g+s` pada storage; kemas backup `.py.bak` lama.

### FASA E — SaaS Panel (~3-5 sesi)
> Rujuk §9 untuk schema DB penuh. Sub-fasa boleh siap berasingan:
- **E1. Asas:** 6 migration central (urutan: `central_users` → `plans` → `subscriptions` → `invoices` → `payments` → `central_activity_logs`) + guard `auth:central` + login panel + seeder owner & 3 plan.
- **E2. Pengurusan tenant:** senarai tenant + stat + butang ON/OFF (is_active + log WAJIB) + middleware `CheckTenantAktif` + view "Akaun Digantung". ← **fungsi suspend, siap awal sengaja.**
- **E3. Cipta tenant dari UI:** borang panel → guna semula logik `TenantCipta` (refactor command jadi service supaya CLI & UI kongsi kod).
- **E4. Billing manual:** jana invois (auto no siri), rekod bayaran (+bukti), senarai tunggakan, auto-tanda `lewat` (scheduler), suspend automatik (optional toggle).
- **E5. Tier enforcement:** plan→modul (guna `ModuleAccess` sedia ada) + had `max_ahli` + paparan "Naik taraf plan".
- **E6. Landing marketing:** page statik (features, pricing dari table `plans`, borang lead) — last.

### FASA F — Modul Koperasi (ikut permintaan)
- **F1.** Pinjaman lengkap — kadar faedah, jadual ansuran, rekod bayaran, penjamin, had kelayakan ikut saham, no rujukan, penyata. (Perlu sesi brainstorm design sendiri.)
- **F2.** Portal ahli self-service — role `ahli` tengok baki saham/dividen/pinjaman sendiri.
- **F3.** Resit rasmi PDF (no siri).
- **F4.** Penyata tahunan ahli (cetak AGM).
- **F5.** Fi/yuran keahlian + tracking tunggakan.
- **F6.** Backup spatie per-tenant (selain pg_dumpall global).

---

## 9. BLUEPRINT DB — SAAS PANEL (CENTRAL) — untuk Fasa E

> Reka bentuk SAHAJA (belum implement). Semua table ni di **CENTRAL DB** (`koperasi_tenant`) — milik pemilik SaaS, bukan data koperasi.

### Prinsip
1. `tenants.id` = **string** → semua FK ke tenant ikut jenis string.
2. Wang = `decimal(10,2)` + `wang()`. Audit "siapa buat" pada tindakan kritikal.
3. **Suspend ≠ Delete.** Data tenant tak pernah dipadam — pintu dikunci.
4. Subscription berasingan dari Plan (sejarah kekal); Payment berasingan dari Invoice (boleh bayar separa).

### Table baru (CENTRAL)
**1. `central_users`** — admin SaaS (owner + staff). Guard berasingan `auth:central`. Lajur: id, name, email (unique), password, role (`owner`/`staff`), is_active, remember_token, timestamps.

**2. `plans`** — definisi tier. Lajur: id, kod (unique: `basic`/`pro`/`enterprise`), nama, harga_bulanan, harga_tahunan (nullable), max_ahli (nullable=unlimited), max_staff (nullable), modul_dibenarkan (json array module_key), features (json — expansion slot), is_active, susunan, timestamps.

**3. `subscriptions`** — langganan tenant + sejarah. Lajur: id, tenant_id (string FK cascade), plan_id (FK restrict), status (`trial`/`aktif`/`digantung`/`tamat`/`batal`), tarikh_mula, tarikh_tamat (nullable), **harga_terkunci** (harga masa subscribe), kitaran (`bulanan`/`tahunan`), auto_renew, catatan, timestamps.
- Satu tenant boleh BANYAK row (sejarah) tapi SATU aktif/trial/digantung pada satu masa — partial unique index:
  `CREATE UNIQUE INDEX ON subscriptions (tenant_id) WHERE status IN ('trial','aktif','digantung');`

**4. `invoices`** — bil. Lajur: id, no_invois (unique auto `INV-2026-0001`), tenant_id (string FK cascade), subscription_id (FK nullable, nullOnDelete), amaun, tarikh_invois, tarikh_due (INDEX), tempoh_dari, tempoh_hingga, status (`belum_bayar`/`dibayar`/`lewat`/`batal` — INDEX (tenant_id,status)), catatan, timestamps.

**5. `payments`** — rekod bayaran (berasingan dari invoice!). Lajur: id, invoice_id (FK restrict), amaun (boleh < invois — bayar separa), tarikh_bayar, kaedah (`tunai`/`transfer`/`fpx`/`gateway`), rujukan (nullable), resit_path (nullable), disahkan_oleh (FK central_users nullable — audit), catatan, timestamps.
- Σ payments >= amaun invois → status invois jadi `dibayar` (kira di app/service, BUKAN trigger DB).

**6. `central_activity_logs`** — audit trail. Lajur: id, central_user_id (FK nullable=sistem), tenant_id (string FK nullable), tindakan (`suspend`/`activate`/`cipta_tenant`/`tukar_plan`/`sah_bayaran`...), detail (json snapshot), ip (nullable), created_at (tiada updated_at — log tak diedit).
- ⭐ KRITIKAL: bila suspend koperasi orang, MESTI ada rekod siapa/bila/kenapa.

**7. Status tenant (on/off)** — BUKAN table baru. Guna VirtualColumn `tenants.data` (zero migration): `is_active` (kill-switch manual), `suspended_at`, `suspend_sebab`.
- **Middleware `CheckTenantAktif`** (selepas `InitializeTenancyByDomain`): BLOCK jika `tenant->is_active === false` ATAU subscription status `digantung`/`tamat` → paparkan view "Akaun Digantung". Dua lapisan: `is_active` (manual override) + subscription status (automatik ikut billing).

### Enforcement tier dalam app tenant
1. `max_ahli` → check sebelum simpan member baru.
2. `modul_dibenarkan` → suntik ke `ModuleAccess::userCan()` — **guna semula sistem module/toggle sedia ada**.
3. ⚠️ Cache info plan per tenant: ingat gotcha #12! Cache dalam cache TENANT dengan TTL pendek (5-15 min), atau baca terus tiap request (1 query indexed).

### Turutan migration (WAJIB — sebab FK)
`central_users` → `plans` → `subscriptions` → `invoices` → `payments` → `central_activity_logs`

### Expansion masa depan (rancang RUANG, jangan bina lagi)
`leads` (borang demo), `announcements` + `announcement_tenant` (broadcast), `gateway_webhooks` (log callback toyyibPay/Billplz), `support_tickets`, `central_roles` (RBAC). Slot json (`plans.features`, `logs.detail`, `tenants.data`) = penyerap perubahan kecil tanpa migration.

---

## 10. HUTANG & PENDING (senarai penuh)

### Code (dalam repo — perlu local→push→pull)
- ⏳ **`bootstrap/app.php`** — trustProxies dah commit di GitHub, **belum `git pull` ke server**.
- ⏳ **`config/tenancy.php`** — `--force` masih dikomen (line ~201). Punca seed gagal di production (prompt "Application In Production"). Sekarang guna jalan tengah `tenants:seed --tenants=X --force`. **Fix kekal:** uncomment `--force` → push → pull. Tenant baru lepas tu auto-seed betul.
- ⏳ Bug MeetingController `'admin'` → `'admin-koperasi'` (C3).

### Server-only
- ⏳ Padam DB sampah `koperasi` (C7).
- ⏳ Test restore backup (C2).
- ⏳ Rotation + notifikasi gagal backup; logrotate `backup.log` (C5).
- ⏳ setgid `g+s` storage; kemas `db_backup.py.bak` lama (C8).

### SELESAI Sesi 3
- ✅ Hutang #1 (Real IP) — trustProxies + firewall.
- ✅ Hutang #2 (DNS record redundant `kpmbb`/`kpkbaling`) — dipadam di Cloudflare.
- ✅ Tukar password DB + user `postgres`→`baseri` (C1).
- ✅ Refactor `db_backup.py` baca dari `.env`.

---

## 11. SOP — OPERASI BIASA

### Cipta tenant baru
```bash
php artisan tenant:cipta <id> <id>.koperasicms.site --nama="Nama Koperasi"
# Kalau production prompt block seed (sebelum fix --force):
php artisan tenants:seed --tenants=<id> --force
```
Kredensial super-user default: `muhamad.baseri@gmail.com` / `@Password12345`.

### Padam tenant (SOP — urutan WAJIB: domains → tenants → DROP DB → storage)
```bash
# 1. Padam rekod central
sudo -u postgres psql -d koperasi_tenant <<'EOF'
DELETE FROM domains WHERE tenant_id IN ('<id>');
DELETE FROM tenants WHERE id IN ('<id>');
EOF
# 2. Drop DB (FORCE untuk elak masalah connection)
sudo -u postgres psql -c "DROP DATABASE tenant<id> WITH (FORCE);"
# 3. Padam storage
sudo rm -rf storage/tenant<id>
# 4. Verify
php artisan tenants:list
```

### Tukar password DB
```bash
read -s -p "Password baru: " NEWPASS && echo
sudo -u postgres psql -c "ALTER USER baseri WITH PASSWORD '$NEWPASS' CREATEDB;"
# Update DB_PASSWORD dalam .env, lepas tu:
php artisan config:clear   # atau alias `cuci`
```

### Grant akses DB tenant lama (kalau owner masih postgres)
```bash
sudo -u postgres psql -d <db> <<'EOF'
GRANT ALL ON ALL TABLES IN SCHEMA public TO baseri;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO baseri;
GRANT ALL ON SCHEMA public TO baseri;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO baseri;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO baseri;
EOF
```

### Verify trafik melalui Cloudflare
```bash
curl -sI https://<id>.koperasicms.site   # cari: cf-ray, server: cloudflare, HTTP/2 302
```

### Test backup manual
```bash
python3 /home/baseri/scripts/db_backup.py
rclone ls gdrive:Koperasi_ServerBackups | tail -5
```

---

## 12. BLUEPRINT DB — TENANT (rujukan ringkas modul koperasi)

> Setiap tenant DB ada ~24 table. Koperasi berasaskan SAHAM.

**Teras RBAC:** `users`, `roles`, `permissions`, `role_user`, `permission_role`, `module_role`.
**7 role:** super-user, admin-koperasi, pengurus, kerani, jk, auditor, ahli.
**Keahlian:** `members` (no_ahli unik `A0001`...), `next_of_kin` (singular!).
**Transaksi/saham:** `account_categories`, `account_entries`, share-related.
**Pinjaman:** `loans` (+ pencadang_id, penyokong_id, meeting_id).
**Mesyuarat:** `meetings`.
**Dividen:** `dividend_runs`, `dividend_shares`, `dividend_allocations`.
**Pemilikan:** `ownership_transfers`.
**Sistem:** `settings` (key-value white-label + toggle produk), `cache`, `cache_locks`, `sessions`, `migrations`.

### Nota schema penting
- Money: `decimal` tepat di DB; PHP guna `wang()`.
- FK kelulusan: loans/transfers ada `meeting_id` + `pencadang_id` + `penyokong_id` + `catatan_kelulusan` (nullOnDelete).
- Audit: setiap proses ada lajur "siapa buat".
- Toggle produk: kawal via `settings`, bukan struktur DB.
- Untuk schema 100% tepat: `php artisan tenants:run "db:show" --tenants=ujian1`.

---

## 13. SEEDER (`DatabaseSeeder` — urutan)
```
1. RoleSeeder            (7 peranan)
2. PermissionSeeder      (kebenaran + petakan ke peranan)
3. SuperUserSeeder       (muhamad.baseri@gmail.com / @Password12345)
4. ModuleAccessSeeder    (akses modul lalai per peranan)
5. AccountCategorySeeder (kategori akaun)
6. SettingSeeder         (logo, nama, tema)
// Sample seeder (Member/AccountEntry/Loan/Meeting) — dikomen, untuk demo sahaja
```
⚠️ `SuperUserSeeder` cipta user dulu, lepas tu cari role `super-user` — urutan penting (Role mesti dahulu).

# KoperasiCMS — Blueprint Induk & Roadmap (Master README)

> **Dokumen tunggal sumber kebenaran (single source of truth).**
> Tampal dokumen ini di awal setiap sesi baru. Mula sesi dengan: *"sambung dari README induk"*.
> Bahasa: Melayu Malaysia. Versi terakhir dikemaskini: **akhir Sesi 3 (13 Jun 2026)**.

---

## 0. RINGKASAN PROJEK

**KoperasiCMS** — sistem pengurusan koperasi yang sedang ditransformasi daripada aplikasi single-koperasi kepada **platform SaaS multi-tenant**.

- **Asal:** sistem untuk Koperasi Perniagaan Melayu Baling (KPMBB), ~1000 ahli, koperasi berasaskan **SAHAM** (modul simpanan toggle OFF).
- **Matlamat:** SaaS multi-tenant — satu code base, banyak koperasi, data setiap koperasi terasing sepenuhnya (DB-per-tenant).
- **Model bisnes:** onboard banyak koperasi (subdomain sendiri), tier/plan, suspend, billing.

### Stack teknikal
| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 12 (12.61.0) |
| PHP | 8.4 |
| Database | PostgreSQL 16.14 |
| Multi-tenancy | stancl/tenancy v3.10 (DB-per-tenant, kenal via domain) |
| Frontend | Blade + Alpine.js |
| Web server | nginx 1.24.0 (Ubuntu 24.04) |
| Reverse proxy / SSL / DNS | Cloudflare (proxy + Origin cert) |
| Backup | pg_dumpall + rclone → Google Drive (cron harian 2 pagi) |

### Persekitaran
| Env | Lokasi | Nota |
|---|---|---|
| **Local (dev)** | `~/koperasicmsv2` (Windows/WSL `muham@BASERIMN`) | Mesin pembangunan |
| **Server (production)** | VPS `216.126.236.127`, `baseri@ubuntu-Utah-1gb`, path `/home/baseri/projek/koperasicms-with-tenant` | Ubuntu 24.04, SSH port **2234** |
| **Repo** | GitHub `BaseriMN/koperasicms-with-tenant` (branch `main`) | repo v2 = THE code base |

### Struktur domain (production)
```
koperasicms.site / www      → CENTRAL (landing SaaS + panel admin — belum bina)
<id>.koperasicms.site       → tenant (auto via wildcard *)
```
- DNS di **Cloudflare** (nameserver `alina` + `mike.ns.cloudflare.com`), domain didaftar di Namecheap.
- Wildcard `*.koperasicms.site` → semua subdomain tenant auto-resolve (tak perlu tambah DNS record setiap onboard).

---

## 1. STATUS SEMASA (akhir Sesi 3)

### Yang dah SIAP & verified
- ✅ **Fasa 1–4** (dev): central DB + tenant DB, routing domain, auto-seed, storage per-tenant (`tenant_asset()`), command `tenant:cipta` dengan auto-cleanup.
- ✅ **Fasa A — Verify Deployment** (Sesi 3): server diverifikasi penuh (lihat §6).
- ✅ **Fasa B — Test Isolation 15/15 LULUS** (Sesi 3): sistem terbukti selamat untuk multi-koperasi (lihat §7).
- ✅ Cloudflare DNS + SSL (Proxy + Origin cert + Full strict).
- ✅ Firewall UFW (443 hanya dari range Cloudflare).
- ✅ Backup harian berfungsi (pg_dumpall → Google Drive).

### Tenant aktif sekarang
- `ujian1`, `ujian2` (tenant ujian — boleh cuci bila-bila ikut SOP §11).
- Tenant lama `kpmbb`, `kpkbaling`, `demo`, `kedah`, `kopetro` — **semua dah dipadam/sampah testing**, tiada data sebenar.

### Hutang/pending utama (lihat §10 untuk senarai penuh)
- ⏳ Deploy code terbaru ke server (commit `bootstrap/app.php` trustProxies belum `git pull`).
- ⏳ Fix `config/tenancy.php` `--force` (seed production) — masih guna jalan tengah.
- ⏳ Hutang #2 DNS sudah **SELESAI** (record redundant dipadam).

### Roadmap (urutan mutlak — JANGAN langkau)
```
A ✅ → B ✅ → C → E → F
(Fasa D — Migrate Baling — DIPADAM, tiada data Baling sebenar)
```

---

## 2. KEPUTUSAN SENI BINA (locked-in)

1. **DB-per-tenant**, kenal tenant **via domain** (stancl/tenancy). Central DB = `koperasi_tenant`.
2. **Subdomain wildcard** `*.koperasicms.site` (default) + custom domain optional kemudian (stancl support multi-domain per tenant).
3. **SSL strategi = Cloudflare Proxy + Origin Certificate** (BUKAN certbot Let's Encrypt). Lihat §5.
4. **Suspend ≠ Delete** — data tenant TIDAK pernah dipadam sebab tak bayar; pintu dikunci sahaja.
5. **Wang = `decimal`** + helper `wang()` (elak float bug). Audit "siapa buat" pada setiap tindakan kritikal.
6. **White-label + module toggle** dikawal via `settings` per-tenant (bukan struktur DB).

---

## 3. GOTCHA & PERATURAN KRITIKAL (jangan lupa)

### Gotcha pengaturcaraan
1. **Slug role = `admin-koperasi`, BUKAN `admin`** — punca #1 bug akses. (Bug pending: MeetingController `$canCreate` masih guna `'admin'` — perlu fix.)
2. **Table waris = `next_of_kin` (SINGULAR)** — bukan `next_of_kins`. (Disahkan dari schema sebenar Sesi 3.)
3. **Module `simpanan_saham` DIKOMEN (OFF)** — koperasi berasaskan saham, produk simpanan toggle off (transaksi saham masih direkod dalam `transactions`). **7 modul aktif:** `pengurusan_staff`, `pengurusan_member`, `permohonan_pinjaman`, `mesyuarat_minit`, `laporan_audit`, `akaun`, `tetapan_sistem`. (Lihat §12B.)
4. **`avatar_path` (users) ≠ `foto_path` (members)** — dua benda berasingan.
5. **PostgreSQL LIKE case-sensitive** → guna `LOWER()` + `whereRaw` untuk carian.
6. **Migration order** — table yang rujuk members/meetings/loans perlu nombor migration lebih tinggi (FK).
7. **`tenant_asset()`** untuk SEMUA fail upload tenant (logo/avatar/foto).
8. **`composer dump-autoload`** lepas edit `helpers.php`.

### Gotcha #12 — CACHE LEAK antara tenant (KRITIKAL)
- **Masalah:** Laravel memoize cache store sekali per request. Kalau store ter-resolve sebelum tenancy init, ia terikat ke connection CENTRAL sampai habis request → cache semua tenant bocor masuk satu table cache central. Simptom: setting/tema satu tenant "berjangkit" ke tenant lain.
- **FIX (dah apply + verified di dev DAN server):** `Cache::forgetDriver(config('cache.default'))` pada event `TenancyInitialized` + `TenancyEnded`, didaftar dalam `TenancyServiceProvider` (line ~115 & ~119).
- `CacheTenancyBootstrapper` kekal **OFF**. `CACHE_STORE=database`.
- ⚠️ JANGAN sentuh `Events\SavingTenant` / `SavingDomain` dalam TenancyServiceProvider — itu event stancl, bukan modul savings.

### Peraturan deploy & kerja
- **Edit di LOCAL** → commit → push → `git pull` di server. JANGAN edit code terus di server (elak git conflict).
- **Pengecualian:** `.env`, firewall, DNS, `db_backup.py`, data DB = server-only (tak masuk git).
- **`optimize:clear` / `config:clear`** lepas deploy. (Alias `cuci` di server = clear + cache config + cache routes.)
- **Rutin deploy v2 = 2-step migration:** `migrate` (central) + `tenants:migrate` (semua tenant).
- **Verify dengan `grep -n` selepas edit** (pengalaman pahit: edit tak save).
- **Backup sebelum edit fail penting** (`cp fail fail.bak-$(date ...)`).
- **`DROP DATABASE ... WITH (FORCE)`** untuk elak masalah connection pool masa padam tenant.

---

## 4. CARA KERJA DENGAN CLAUDE (preferensi user)

- **Bahasa:** Melayu Malaysia, santai (panggil "bro" ok).
- **Bukan rewrite penuh** — guide step-by-step, atau format CARI/TUKAR (📄 path → kod lama → kod baru).
- **Brainstorm dulu** — terang sebab & trade-off sebelum laksana.
- **Satu langkah satu masa** untuk operasi berisiko (DB, firewall) — verify output sebelum gerak.
- **Push back bila perlu** — kalau user nak over-engineer atau ada cara lebih baik, cakap.
- **Guna `ask_user_input`** untuk pilihan/keputusan supaya senang di mobile.

---

## 5. INFRASTRUKTUR SERVER (server-only, TAK masuk git)

### Cloudflare + SSL (Sesi 3)
- **Strategi:** Cloudflare Proxy (orange cloud) + **Cloudflare Origin Certificate** dipasang di nginx.
- Cert di VPS: `/etc/ssl/cloudflare/koperasicms.site.pem` + `.key` (expire **2041** — set-and-forget).
- **SSL mode di Cloudflare = Full (strict).**
- **certbot TAK diperlukan** (keputusan asal Sesi 2.5 guna certbot Let's Encrypt — DITUKAR).
- DNS records (semua **Proxied/orange**): `*.koperasicms.site`, `koperasicms.site`, `www` → `216.126.236.127`.
- Cara verify trafik lalu CF: cari header `cf-ray` + `server: cloudflare` dalam `curl -sI`.

### Firewall UFW (Sesi 3)
- Port **2234** (SSH) — ALLOW.
- Port **80** — ALLOW (HTTP redirect).
- Port **443** — ALLOW **hanya dari range IP Cloudflare** (15 IPv4 + 7 IPv6, fetch dari `cloudflare.com/ips-v4` & `ips-v6`).
- Default: `deny incoming`. Akses terus ke IP VPS port 443 dari bukan-CF → **timeout** (terbukti).
- Sebab penting: melindungi `trustProxies('*')` daripada IP spoofing.

### Real IP (trustProxies) — hutang #1 SELESAI
- **App side:** `bootstrap/app.php` set `$middleware->trustProxies(at: '*', headers: X_FORWARDED_FOR|HOST|PORT|PROTO)`. Commit dah di GitHub, **belum pull ke server**.
- **Server side:** firewall UFW (di atas) — selesai.
- Kesan: `central_activity_logs.ip` nanti rekod IP pelawat sebenar (bukan IP CF). Header CF: `CF-Connecting-IP` / `X-Forwarded-For`.

### Database PostgreSQL (server)
- User app: **`baseri`** (bukan `postgres` lagi — ditukar Sesi 3) + password kuat (dalam `.env`, BUKAN `123456` lagi).
- `baseri` ada `CREATEDB` (untuk stancl cipta tenant DB baru).
- DB sedia ada: `koperasi_tenant` (central), `tenantujian1`, `tenantujian2` — owner `baseri`.
- DB `koperasi` (lama, kosong, sampah) — owner `postgres`, **belum dipadam** (boleh padam bila-bila).
- Tenant DB lama owned by `postgres` perlu `GRANT ALL` + `ALTER DEFAULT PRIVILEGES` ke `baseri`. Tenant BARU (dicipta selepas tukar `.env`) auto-owned `baseri` (takde isu).
- Auth: connect via TCP `127.0.0.1` (md5/password), BUKAN peer auth.

### `.env` server (kunci penting — `chmod 640`, group `www-data`)
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://koperasicms.site
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=koperasi_tenant
DB_USERNAME=baseri
DB_PASSWORD=<password kuat — simpan dalam password manager>
QUEUE_CONNECTION=sync
CACHE_STORE=database
SESSION_DRIVER=database
```

### Backup automatik (`/home/baseri/scripts/db_backup.py`)
- Cron: `0 2 * * *` (setiap hari 2 pagi) + `* * * * * schedule:run`.
- **Direka semula Sesi 3:** baca credential dari `.env` Laravel (satu sumber kebenaran) — BUKAN hardcode password lagi.
- `ENV_FILE = /home/baseri/projek/koperasicms-with-tenant/.env`.
- `pg_dumpall` semua DB → gzip → `rclone move` ke Google Drive `gdrive:Koperasi_ServerBackups`.
- Permission: script `700`, `.env` `640`.
- ⚠️ Belum ada: rotation backup, notifikasi gagal, logrotate untuk `backup.log`.
- ⚠️ Backup belum pernah **di-restore-test** (Fasa C2).

### Permission storage
- `storage/` + `bootstrap/cache/` → mode `775`, owner `www-data:www-data`.
- ⚠️ Tiada setgid (`g+s`) — polish Fasa C kalau nak konsisten SOP penuh.

---

## 6. FASA A — VERIFY DEPLOYMENT (✅ SELESAI Sesi 3)

| # | Item | Hasil |
|---|---|---|
| A1 | DB lama selamat | ⏭️ SKIP — app lama dah dibuang, tiada DB lama nak jaga |
| A2 | `.env` v2 server | ✅ DB user/password/url betul, GRANT semua DB |
| A3 | `tenants:list` | ✅ |
| A4 | Cache leak fix di server | ✅ `forgetDriver` ada (line 115 & 119) |
| A5 | SSL | ✅ CF Proxy + Origin + Full strict |
| A6 | Cron + permission | ✅ schedule:run + backup, storage 775 |
| A7 | nginx | ✅ 2 server block (central + wildcard regex) |

**Penemuan besar Sesi 3:** App lama (Baling) sudah dibuang dari VPS. Tiada data koperasi sebenar — semua testing. Maka **Fasa D (Migrate Baling) dipadam** dari roadmap.

### nginx config (rujukan)
- Server block 1: `server_name koperasicms.site www.koperasicms.site` (central).
- Server block 2: `server_name ~^(?<tenant>.+)\.koperasicms\.site$` (wildcard tenant, regex).
- Kedua-dua: `listen 443 ssl http2`, cert sama (`/etc/ssl/cloudflare/...`), HTTP→HTTPS redirect.

---

## 7. FASA B — TEST ISOLATION (✅ 15/15 LULUS Sesi 3)

| Kumpulan | Ujian | Hasil |
|---|---|---|
| Session | 1–3 (login serentak, logout, tukar password) | ✅ |
| Data CRUD | 4–7 (member, transaksi, mesyuarat+pinjaman, akaun+dividen) | ✅ tiada bocor |
| Settings/Cache | 8 (tukar nama+tema) | ✅ cache fix kerja di server |
| Storage | 9–11 (upload, folder isolation, cross-access 404) | ✅ `tenant_asset` betul |
| Route | 12–14 (central vs tenant, domain tak wujud) | ✅ error bersih |
| No Ahli | 15 (turutan `A0001` sendiri) | ✅ dua-dua tenant mula A0001 |

**Bukti storage isolation:** fail dalam `storage/tenant<id>/app/public/{logos,avatars,members}/`. Cross-access dari domain lain → 404.
**Kredensial seeder super-user:** `muhamad.baseri@gmail.com` / `@Password12345` (role `super-user`).

---

## 8. ROADMAP BERPRIORITI (Sesi 3 → seterusnya)

> Peraturan emas: JANGAN mula fasa sebelum kriteria fasa sebelumnya dicapai.

```
FASA A: Verify Deployment      ✅ SELESAI (Sesi 3)
FASA B: Test Isolation (15)    ✅ SELESAI (Sesi 3)
FASA C: Kemas & Kunci          ← SETERUSNYA
FASA E: SaaS Panel             (suspend/tier/billing)
FASA F: Modul Koperasi         (pinjaman penuh, portal ahli, dll)
(FASA D: Migrate Baling        — DIPADAM, tiada data sebenar)
```

### FASA C — Kemas & Kunci (SETERUSNYA)
> Sebahagian dah selesai awal sebab kerja Sesi 3.

- **C1.** Tukar password DB production — ✅ **SELESAI** (Sesi 3: `123456` → kuat, user `postgres` → `baseri`).
- **C2.** Backup + **VERIFIED restore** — separuh: backup berfungsi ✅, tapi belum pernah test restore. *"Backup yang tak pernah di-restore = bukan backup."* Perlu: restore ke DB temp, banding kiraan row.
- **C3.** Bug MeetingController `'admin'` → `'admin-koperasi'` (5 minit, commit).
- **C4.** Sahkan module key — ✅ **SELESAI** (Sesi 3: 7 modul aktif disahkan dari `config/modules.php`, lihat §12B). Tinggal: pastikan `module_role` seeder selaras 7 modul ni.
- **C5.** Failed-job/error visibility — `storage/logs` terjaga; optional notifikasi error (telegram/email). Tambah logrotate untuk `backup.log`.
- **C6 (baru).** Deploy code terbaru ke server: `git pull` (`bootstrap/app.php` trustProxies + `config/tenancy.php` `--force`).
- **C7 (baru).** Padam DB sampah `koperasi`.
- **C8 (polish).** setgid `g+s` pada storage; kemas backup `.py.bak` lama.

### FASA E — SaaS Panel (~3-5 sesi)
> Rujuk §9 untuk schema DB penuh. Sub-fasa boleh siap berasingan:
- **E1. Asas:** 6 migration central (urutan: `central_users` → `plans` → `subscriptions` → `invoices` → `payments` → `central_activity_logs`) + guard `auth:central` + login panel + seeder owner & 3 plan.
- **E2. Pengurusan tenant:** senarai tenant + stat + butang ON/OFF (is_active + log WAJIB) + middleware `CheckTenantAktif` + view "Akaun Digantung". ← **fungsi suspend, siap awal sengaja.**
- **E3. Cipta tenant dari UI:** borang panel → guna semula logik `TenantCipta` (refactor command jadi service supaya CLI & UI kongsi kod).
- **E4. Billing manual:** jana invois (auto no siri), rekod bayaran (+bukti), senarai tunggakan, auto-tanda `lewat` (scheduler), suspend automatik (optional toggle).
- **E5. Tier enforcement:** plan→modul (guna `ModuleAccess` sedia ada) + had `max_ahli` + paparan "Naik taraf plan".
- **E6. Landing marketing:** page statik (features, pricing dari table `plans`, borang lead) — last.

### FASA F — Modul Koperasi (ikut permintaan)
- **F1.** Pinjaman lengkap — kadar faedah, jadual ansuran, rekod bayaran, penjamin, had kelayakan ikut saham, no rujukan, penyata. (Perlu sesi brainstorm design sendiri.)
- **F2.** Portal ahli self-service — role `ahli` tengok baki saham/dividen/pinjaman sendiri.
- **F3.** Resit rasmi PDF (no siri).
- **F4.** Penyata tahunan ahli (cetak AGM).
- **F5.** Fi/yuran keahlian + tracking tunggakan.
- **F6.** Backup spatie per-tenant (selain pg_dumpall global).

---

## 9. BLUEPRINT DB — SAAS PANEL (CENTRAL) — untuk Fasa E

> Reka bentuk SAHAJA (belum implement). Semua table ni di **CENTRAL DB** (`koperasi_tenant`) — milik pemilik SaaS, bukan data koperasi.

### Prinsip
1. `tenants.id` = **string** → semua FK ke tenant ikut jenis string.
2. Wang = `decimal(10,2)` + `wang()`. Audit "siapa buat" pada tindakan kritikal.
3. **Suspend ≠ Delete.** Data tenant tak pernah dipadam — pintu dikunci.
4. Subscription berasingan dari Plan (sejarah kekal); Payment berasingan dari Invoice (boleh bayar separa).

### Table baru (CENTRAL)
**1. `central_users`** — admin SaaS (owner + staff). Guard berasingan `auth:central`. Lajur: id, name, email (unique), password, role (`owner`/`staff`), is_active, remember_token, timestamps.

**2. `plans`** — definisi tier. Lajur: id, kod (unique: `basic`/`pro`/`enterprise`), nama, harga_bulanan, harga_tahunan (nullable), max_ahli (nullable=unlimited), max_staff (nullable), modul_dibenarkan (json array module_key), features (json — expansion slot), is_active, susunan, timestamps.

**3. `subscriptions`** — langganan tenant + sejarah. Lajur: id, tenant_id (string FK cascade), plan_id (FK restrict), status (`trial`/`aktif`/`digantung`/`tamat`/`batal`), tarikh_mula, tarikh_tamat (nullable), **harga_terkunci** (harga masa subscribe), kitaran (`bulanan`/`tahunan`), auto_renew, catatan, timestamps.
- Satu tenant boleh BANYAK row (sejarah) tapi SATU aktif/trial/digantung pada satu masa — partial unique index:
  `CREATE UNIQUE INDEX ON subscriptions (tenant_id) WHERE status IN ('trial','aktif','digantung');`

**4. `invoices`** — bil. Lajur: id, no_invois (unique auto `INV-2026-0001`), tenant_id (string FK cascade), subscription_id (FK nullable, nullOnDelete), amaun, tarikh_invois, tarikh_due (INDEX), tempoh_dari, tempoh_hingga, status (`belum_bayar`/`dibayar`/`lewat`/`batal` — INDEX (tenant_id,status)), catatan, timestamps.

**5. `payments`** — rekod bayaran (berasingan dari invoice!). Lajur: id, invoice_id (FK restrict), amaun (boleh < invois — bayar separa), tarikh_bayar, kaedah (`tunai`/`transfer`/`fpx`/`gateway`), rujukan (nullable), resit_path (nullable), disahkan_oleh (FK central_users nullable — audit), catatan, timestamps.
- Σ payments >= amaun invois → status invois jadi `dibayar` (kira di app/service, BUKAN trigger DB).

**6. `central_activity_logs`** — audit trail. Lajur: id, central_user_id (FK nullable=sistem), tenant_id (string FK nullable), tindakan (`suspend`/`activate`/`cipta_tenant`/`tukar_plan`/`sah_bayaran`...), detail (json snapshot), ip (nullable), created_at (tiada updated_at — log tak diedit).
- ⭐ KRITIKAL: bila suspend koperasi orang, MESTI ada rekod siapa/bila/kenapa.

**7. Status tenant (on/off)** — BUKAN table baru. Guna VirtualColumn `tenants.data` (zero migration): `is_active` (kill-switch manual), `suspended_at`, `suspend_sebab`.
- **Middleware `CheckTenantAktif`** (selepas `InitializeTenancyByDomain`): BLOCK jika `tenant->is_active === false` ATAU subscription status `digantung`/`tamat` → paparkan view "Akaun Digantung". Dua lapisan: `is_active` (manual override) + subscription status (automatik ikut billing).

### Enforcement tier dalam app tenant
1. `max_ahli` → check sebelum simpan member baru.
2. `modul_dibenarkan` → suntik ke `ModuleAccess::userCan()` — **guna semula sistem module/toggle sedia ada**.
3. ⚠️ Cache info plan per tenant: ingat gotcha #12! Cache dalam cache TENANT dengan TTL pendek (5-15 min), atau baca terus tiap request (1 query indexed).

### Turutan migration (WAJIB — sebab FK)
`central_users` → `plans` → `subscriptions` → `invoices` → `payments` → `central_activity_logs`

### Expansion masa depan (rancang RUANG, jangan bina lagi)
`leads` (borang demo), `announcements` + `announcement_tenant` (broadcast), `gateway_webhooks` (log callback toyyibPay/Billplz), `support_tickets`, `central_roles` (RBAC). Slot json (`plans.features`, `logs.detail`, `tenants.data`) = penyerap perubahan kecil tanpa migration.

---

## 10. HUTANG & PENDING (senarai penuh)

### Code (dalam repo — perlu local→push→pull)
- ⏳ **`bootstrap/app.php`** — trustProxies dah commit di GitHub, **belum `git pull` ke server**.
- ⏳ **`config/tenancy.php`** — `--force` masih dikomen (line ~201). Punca seed gagal di production (prompt "Application In Production"). Sekarang guna jalan tengah `tenants:seed --tenants=X --force`. **Fix kekal:** uncomment `--force` → push → pull. Tenant baru lepas tu auto-seed betul.
- ⏳ Bug MeetingController `'admin'` → `'admin-koperasi'` (C3).

### Server-only
- ⏳ Padam DB sampah `koperasi` (C7).
- ⏳ Test restore backup (C2).
- ⏳ Rotation + notifikasi gagal backup; logrotate `backup.log` (C5).
- ⏳ setgid `g+s` storage; kemas `db_backup.py.bak` lama (C8).

### SELESAI Sesi 3
- ✅ Hutang #1 (Real IP) — trustProxies + firewall.
- ✅ Hutang #2 (DNS record redundant `kpmbb`/`kpkbaling`) — dipadam di Cloudflare.
- ✅ Tukar password DB + user `postgres`→`baseri` (C1).
- ✅ Refactor `db_backup.py` baca dari `.env`.

---

## 11. SOP — OPERASI BIASA

### Cipta tenant baru
```bash
php artisan tenant:cipta <id> <id>.koperasicms.site --nama="Nama Koperasi"
# Kalau production prompt block seed (sebelum fix --force):
php artisan tenants:seed --tenants=<id> --force
```
Kredensial super-user default: `muhamad.baseri@gmail.com` / `@Password12345`.

### Padam tenant (SOP — urutan WAJIB: domains → tenants → DROP DB → storage)
```bash
# 1. Padam rekod central
sudo -u postgres psql -d koperasi_tenant <<'EOF'
DELETE FROM domains WHERE tenant_id IN ('<id>');
DELETE FROM tenants WHERE id IN ('<id>');
EOF
# 2. Drop DB (FORCE untuk elak masalah connection)
sudo -u postgres psql -c "DROP DATABASE tenant<id> WITH (FORCE);"
# 3. Padam storage
sudo rm -rf storage/tenant<id>
# 4. Verify
php artisan tenants:list
```

### Tukar password DB
```bash
read -s -p "Password baru: " NEWPASS && echo
sudo -u postgres psql -c "ALTER USER baseri WITH PASSWORD '$NEWPASS' CREATEDB;"
# Update DB_PASSWORD dalam .env, lepas tu:
php artisan config:clear   # atau alias `cuci`
```

### Grant akses DB tenant lama (kalau owner masih postgres)
```bash
sudo -u postgres psql -d <db> <<'EOF'
GRANT ALL ON ALL TABLES IN SCHEMA public TO baseri;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO baseri;
GRANT ALL ON SCHEMA public TO baseri;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO baseri;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO baseri;
EOF
```

### Verify trafik melalui Cloudflare
```bash
curl -sI https://<id>.koperasicms.site   # cari: cf-ray, server: cloudflare, HTTP/2 302
```

### Test backup manual
```bash
python3 /home/baseri/scripts/db_backup.py
rclone ls gdrive:Koperasi_ServerBackups | tail -5
```

---

## 12. BLUEPRINT DB — TENANT (DETAIL PENUH dari schema sebenar)

> Sumber: `pg_dump --schema-only tenantujian1` (Sesi 3). Koperasi berasaskan SAHAM.
> PostgreSQL 16.14. Semua `timestamp(0) without time zone`. Money guna `numeric` (PHP `wang()`).
> **23 table** termasuk sistem. Setiap table ada `id bigint PK` + sequence kecuali pivot & sistem.

### 12.1 RBAC & Pengguna

**`users`**
| Lajur | Jenis | Constraint |
|---|---|---|
| id | bigint | PK |
| name | varchar(255) | NOT NULL |
| email | varchar(255) | UNIQUE NOT NULL |
| phone | varchar(20) | |
| password | varchar(255) | NOT NULL |
| email_verified_at | timestamp | |
| remember_token | varchar(100) | |
| is_active | boolean | DEFAULT true |
| avatar_path | varchar(255) | ⚠️ user avatar (≠ members.foto_path) |
| created_at, updated_at | timestamp | |

**`roles`** — id, name varchar(50) UNIQUE, slug varchar(50) UNIQUE, description text, timestamps. (7 role — lihat §13.)
**`permissions`** — id, name varchar(50) UNIQUE, slug varchar(50) UNIQUE, description text, timestamps.
**`role_user`** (pivot) — user_id + role_id, PK gabungan, dua-dua FK cascade.
**`permission_role`** (pivot) — permission_id + role_id, PK gabungan, dua-dua FK cascade.
**`module_role`** (pivot) — **module_key varchar(50)** + role_id, PK gabungan (module_key, role_id), role_id FK cascade. ⭐ Ini jambatan modul↔role (ModuleAccess).

### 12.2 Keahlian

**`members`**
| Lajur | Jenis | Constraint |
|---|---|---|
| id | bigint | PK |
| no_ahli | varchar(10) | **UNIQUE NOT NULL** (`A0001`...) |
| user_id | bigint | FK → users, SET NULL |
| nama | varchar(255) | NOT NULL |
| no_kp | varchar(20) | |
| telefon | varchar(20) | |
| alamat | text | |
| tarikh_sertai | date | |
| status | varchar | DEFAULT 'aktif' — CHECK (`aktif`/`tidak_aktif`/`berhenti`) |
| foto_path | varchar(255) | ⚠️ foto ahli (≠ users.avatar_path) |
| created_at, updated_at | timestamp | |

**`next_of_kin`** (SINGULAR!) — id, member_id (FK cascade, **UNIQUE** — satu waris satu ahli), nama varchar(255), no_kp varchar(20), hubungan varchar(50) NOT NULL, telefon, alamat, timestamps.

### 12.3 Transaksi & Saham

**`transactions`**
| Lajur | Jenis | Constraint |
|---|---|---|
| id | bigint | PK |
| member_id | bigint | FK → members, CASCADE |
| jenis | varchar | CHECK (`saham`/`simpanan`) |
| arah | varchar | CHECK (`masuk`/`keluar`) |
| amaun | numeric(12,2) | NOT NULL |
| baki | numeric(12,2) | NOT NULL (running balance) |
| sumber | varchar(30) | DEFAULT 'manual' |
| rujukan | varchar(50) | |
| keterangan | text | |
| recorded_by | bigint | FK → users, SET NULL (audit) |
| | | INDEX (member_id, jenis) |

**`share_transfers`** — id, from_member_id (FK cascade), to_member_id (FK cascade), amaun numeric(12,2), sebab varchar(100), tarikh_pindah date NOT NULL, processed_by (FK users SET NULL), **meeting_id** (FK SET NULL), **pencadang_id** (FK members SET NULL), **penyokong_id** (FK members SET NULL), catatan_kelulusan text, timestamps.

**`ownership_transfers`** — id, member_id (FK cascade), from_user_id (FK SET NULL), from_nama varchar(255), to_user_id (FK SET NULL), to_nama varchar(255) NOT NULL, to_no_kp varchar(20), sebab varchar(100), tarikh_pindah date NOT NULL, processed_by (FK users SET NULL), **meeting_id** (FK SET NULL), **pencadang_id** (FK members SET NULL), **penyokong_id** (FK members SET NULL), catatan_kelulusan text, timestamps.

### 12.4 Pinjaman

**`loans`**
| Lajur | Jenis | Constraint |
|---|---|---|
| id | bigint | PK |
| member_id | bigint | FK → members, CASCADE |
| dimohon_oleh | bigint | FK → users, SET NULL |
| amount | numeric(12,2) | NOT NULL |
| tempoh | smallint | NOT NULL (bulan) |
| tujuan | text | NOT NULL |
| status | varchar | DEFAULT 'pending' — CHECK (`pending`/`approved`/`rejected`) |
| catatan | text | |
| reviewed_by | bigint | FK → users, SET NULL |
| reviewed_at | timestamp | |
| meeting_id | bigint | FK → meetings, SET NULL |
| pencadang_id | bigint | FK → members, SET NULL |
| penyokong_id | bigint | FK → members, SET NULL |

> ⚠️ `loans` sekarang ASAS sahaja (amount, tempoh, status). Modul pinjaman LENGKAP (faedah, ansuran, bayaran) = Fasa F1 — perlu tambah table/lajur baru.

### 12.5 Mesyuarat

**`meetings`** — id, tajuk varchar(255) NOT NULL, tarikh date NOT NULL, lokasi varchar(255), minit text, created_by (FK users SET NULL), timestamps.

### 12.6 Akaun (Kira-kira)

**`account_categories`** — id, parent_id (FK self CASCADE — hierarki), jenis varchar CHECK (`pendapatan`/`perbelanjaan`), nama varchar(120) NOT NULL, kod varchar(30), berulang boolean DEFAULT false, is_active boolean DEFAULT true, keterangan text, susunan int DEFAULT 0, timestamps. INDEX (jenis, is_active) + (parent_id).

**`account_entries`** — id, category_id (FK RESTRICT), jenis varchar CHECK (`pendapatan`/`perbelanjaan`), member_id (FK SET NULL), amaun numeric(14,2) NOT NULL, tarikh date NOT NULL, rujukan varchar(60), penerima_pembayar varchar(150), keterangan text, recorded_by (FK users SET NULL), timestamps. INDEX (category_id), (jenis, tarikh), (member_id).

### 12.7 Dividen

**`dividend_runs`**
| Lajur | Jenis | Nota |
|---|---|---|
| id | bigint PK | |
| tahun | smallint | **UNIQUE** (satu run setahun) |
| tarikh_cutoff | date | NOT NULL |
| tarikh_mula | date | |
| untung_bersih | numeric(14,2) | DEFAULT 0 |
| jumlah_peruntukan | numeric(14,2) | DEFAULT 0 |
| untung_boleh_agih | numeric(14,2) | DEFAULT 0 |
| peratus_dividen | numeric(5,2) | DEFAULT 0 |
| jumlah_dividen | numeric(14,2) | DEFAULT 0 |
| jumlah_saham_anggota | numeric(16,2) | DEFAULT 0 |
| peratus_auditor | numeric(5,2) | DEFAULT 0 |
| peratus_diluluskan | numeric(5,2) | DEFAULT 0 |
| baki_dibawa_hadapan | numeric(16,2) | DEFAULT 0 |
| status | varchar | DEFAULT 'draf' — CHECK (`draf`/`dimuktamadkan`) |
| tarikh_muktamad | date | |
| dikira_oleh | bigint | FK → users, SET NULL |
| catatan | text | |

**`dividend_allocations`** — id, dividend_run_id (FK cascade), nama_tabung varchar(120) NOT NULL, jenis_kira varchar DEFAULT 'peratus' CHECK (`peratus`/`amaun`), nilai numeric(14,2) DEFAULT 0, amaun numeric(14,2) DEFAULT 0, susunan int DEFAULT 0, timestamps.

**`dividend_shares`** — id, dividend_run_id (FK cascade), member_id (FK cascade), saham_layak numeric(14,2), saham_auto numeric(14,2), peratus numeric(8,4), amaun_dividen numeric(14,2), override boolean DEFAULT false, timestamps. UNIQUE (dividend_run_id, member_id). INDEX (member_id).

### 12.8 Sistem & White-label

**`settings`** — id, key varchar(80) **UNIQUE**, value text, timestamps. (Key-value: nama koperasi, logo, tema, toggle produk.)
**`cache`** — key varchar(255) PK, value text, expiration bigint. INDEX (expiration).
**`cache_locks`** — key PK, owner varchar(255), expiration bigint.
**`sessions`** — id varchar(255) PK, user_id bigint, ip_address varchar(45), user_agent text, payload text, last_activity int. INDEX (user_id), (last_activity).
**`password_reset_tokens`** — email PK, token varchar(255), created_at.
**`migrations`** — id, migration varchar(255), batch int.

> ⚠️ TIADA table `jobs` (QUEUE=sync, tiada queue). TIADA table `savings` (modul dibuang Sesi 2.5).

### Nota schema penting
- **Money:** `numeric(12,2)` untuk transaksi/pinjaman; `numeric(14,2)` untuk akaun/dividen; `numeric(16,2)` untuk agregat saham besar. PHP guna `wang()`.
- **FK kelulusan:** loans + share_transfers + ownership_transfers semua ada `meeting_id` + `pencadang_id` + `penyokong_id` + `catatan_kelulusan` (SET NULL).
- **Audit:** setiap proses ada lajur "siapa buat" (`recorded_by` / `processed_by` / `dimohon_oleh` / `reviewed_by` / `dikira_oleh` / `created_by`) → semua FK users SET NULL.
- **CHECK constraints** banyak (status enum). Hati-hati bila insert — kena ikut nilai dibenarkan.
- Untuk dump terkini: `sudo -u postgres pg_dump --schema-only --no-owner tenantujian1`.

---

## 12B. MODUL SISTEM (`config/modules.php`)

> Satu sumber kebenaran. Digunakan oleh: middleware `module`, ModuleAccessController (matrix), DashboardController (tile), Sidebar.
> Format: `module_key => [label, desc, route, route_prefix, icon]`.
> `route_prefix` = route dilindungi (wildcard, cth `users.*`).

**7 modul AKTIF + 1 dikomen (jumlah dalam config):**

| # | module_key | Label | Route | Route prefix | Status |
|---|---|---|---|---|---|
| 1 | `pengurusan_staff` | Pengurusan Pekerja | `users.index` | `users.*` | ✅ aktif |
| 2 | `pengurusan_member` | Pengurusan Ahli Koperasi | `members.index` | `members.*` | ✅ aktif |
| 3 | `permohonan_pinjaman` | Permohonan Pinjaman | `pinjaman.index` | `pinjaman.*` | ✅ aktif |
| — | `simpanan_saham` | Simpanan & Saham | `simpanan.index` | `simpanan.*` | ⚠️ **DIKOMEN (OFF)** |
| 4 | `mesyuarat_minit` | Mesyuarat & Minit | `mesyuarat.index` | `mesyuarat.*` | ✅ aktif |
| 5 | `laporan_audit` | Laporan Audit | `audit.index` | `audit.*` | ✅ aktif |
| 6 | `akaun` | Akaun | `akaun.entri.index` | `akaun.*` | ✅ aktif (route_params: jenis=pendapatan) |
| 7 | `tetapan_sistem` | Tetapan Sistem | `roles.index` | `roles.*` | ✅ aktif (peranan, kebenaran, akses modul) |

> **Pembetulan gotcha #3:** module key `simpanan_saham` WUJUD tapi **DIKOMEN** (koperasi berasaskan saham, produk simpanan OFF). Transaksi saham masih direkod (table `transactions` jenis=`saham`), cuma modul UI simpanan tu off. Modul `akaun` (pendapatan/perbelanjaan) + `tetapan_sistem` (RBAC) adalah modul ke-6 & ke-7 yang aktif.
> Setiap modul ada: `label`, `desc`, `route`, `route_prefix` (+ optional `route_params`), `icon` (SVG path).
> Module key disimpan dalam table `module_role.module_key` (per tenant) — kawal role mana boleh akses modul mana.

**Pengguna config ni:** Middleware `module` (kawal akses), `ModuleAccessController` (matrix tetapan), `DashboardController` (tile), Sidebar (menu).

---

## 13. SEEDER (`DatabaseSeeder` — urutan)
```
1. RoleSeeder            (7 peranan)
2. PermissionSeeder      (kebenaran + petakan ke peranan)
3. SuperUserSeeder       (muhamad.baseri@gmail.com / @Password12345)
4. ModuleAccessSeeder    (akses modul lalai per peranan)
5. AccountCategorySeeder (kategori akaun)
6. SettingSeeder         (logo, nama, tema)
// Sample seeder (Member/AccountEntry/Loan/Meeting) — dikomen, untuk demo sahaja
```
⚠️ `SuperUserSeeder` cipta user dulu, lepas tu cari role `super-user` — urutan penting (Role mesti dahulu).

---

*Tamat README induk. Sesi seterusnya: mula dengan **Fasa C** (Kemas & Kunci) — keutamaan C6 (deploy code) + C2 (test restore backup) + C3 (bug MeetingController).*

*Tamat README induk. Sesi seterusnya: mula dengan **Fasa C** (Kemas & Kunci) — keutamaan C6 (deploy code) + C2 (test restore backup) + C3 (bug MeetingController).*


=================================================================================
# KoperasiCMS — Blueprint Induk & Roadmap (Master README)

> **Dokumen tunggal sumber kebenaran (single source of truth).**
> Tampal dokumen ini di awal setiap sesi baru. Mula sesi dengan: *"sambung dari README induk"*.
> Bahasa: Melayu Malaysia. Versi terakhir dikemaskini: **akhir Sesi 3 (13 Jun 2026)**.

---

## 0. RINGKASAN PROJEK

**KoperasiCMS** — sistem pengurusan koperasi yang sedang ditransformasi daripada aplikasi single-koperasi kepada **platform SaaS multi-tenant**.

- **Asal:** sistem untuk Koperasi Perniagaan Melayu Baling (KPMBB), ~1000 ahli, koperasi berasaskan **SAHAM** (modul simpanan toggle OFF).
- **Matlamat:** SaaS multi-tenant — satu code base, banyak koperasi, data setiap koperasi terasing sepenuhnya (DB-per-tenant).
- **Model bisnes:** onboard banyak koperasi (subdomain sendiri), tier/plan, suspend, billing.

### Stack teknikal
| Lapisan | Teknologi |
|---|---|
| Framework | Laravel 12 (12.61.0) |
| PHP | 8.4 |
| Database | PostgreSQL 16.14 |
| Multi-tenancy | stancl/tenancy v3.10 (DB-per-tenant, kenal via domain) |
| Frontend | Blade + Alpine.js |
| Web server | nginx 1.24.0 (Ubuntu 24.04) |
| Reverse proxy / SSL / DNS | Cloudflare (proxy + Origin cert) |
| Backup | pg_dumpall + rclone → Google Drive (cron harian 2 pagi) |

### Persekitaran
| Env | Lokasi | Nota |
|---|---|---|
| **Local (dev)** | `~/koperasicmsv2` (Windows/WSL `muham@BASERIMN`) | Mesin pembangunan |
| **Server (production)** | VPS `216.126.236.127`, `baseri@ubuntu-Utah-1gb`, path `/home/baseri/projek/koperasicms-with-tenant` | Ubuntu 24.04, SSH port **2234** |
| **Repo** | GitHub `BaseriMN/koperasicms-with-tenant` (branch `main`) | repo v2 = THE code base |

### Struktur domain (production)
```
koperasicms.site / www      → CENTRAL (landing SaaS + panel admin — belum bina)
<id>.koperasicms.site       → tenant (auto via wildcard *)
```
- DNS di **Cloudflare** (nameserver `alina` + `mike.ns.cloudflare.com`), domain didaftar di Namecheap.
- Wildcard `*.koperasicms.site` → semua subdomain tenant auto-resolve (tak perlu tambah DNS record setiap onboard).

---

## 1. STATUS SEMASA (akhir Sesi 3)

### Yang dah SIAP & verified
- ✅ **Fasa 1–4** (dev): central DB + tenant DB, routing domain, auto-seed, storage per-tenant (`tenant_asset()`), command `tenant:cipta` dengan auto-cleanup.
- ✅ **Fasa A — Verify Deployment** (Sesi 3): server diverifikasi penuh (lihat §6).
- ✅ **Fasa B — Test Isolation 15/15 LULUS** (Sesi 3): sistem terbukti selamat untuk multi-koperasi (lihat §7).
- ✅ Cloudflare DNS + SSL (Proxy + Origin cert + Full strict).
- ✅ Firewall UFW (443 hanya dari range Cloudflare).
- ✅ Backup harian berfungsi (pg_dumpall → Google Drive).

### Tenant aktif sekarang
- `ujian1`, `ujian2` (tenant ujian — boleh cuci bila-bila ikut SOP §11).
- Tenant lama `kpmbb`, `kpkbaling`, `demo`, `kedah`, `kopetro` — **semua dah dipadam/sampah testing**, tiada data sebenar.

### Hutang/pending utama (lihat §10 untuk senarai penuh)
- ⏳ Deploy code terbaru ke server (commit `bootstrap/app.php` trustProxies belum `git pull`).
- ⏳ Fix `config/tenancy.php` `--force` (seed production) — masih guna jalan tengah.
- ⏳ Hutang #2 DNS sudah **SELESAI** (record redundant dipadam).

### Roadmap (urutan mutlak — JANGAN langkau)
```
A ✅ → B ✅ → C → E → F
(Fasa D — Migrate Baling — DIPADAM, tiada data Baling sebenar)
```

---

## 2. KEPUTUSAN SENI BINA (locked-in)

1. **DB-per-tenant**, kenal tenant **via domain** (stancl/tenancy). Central DB = `koperasi_tenant`.
2. **Subdomain wildcard** `*.koperasicms.site` (default) + custom domain optional kemudian (stancl support multi-domain per tenant).
3. **SSL strategi = Cloudflare Proxy + Origin Certificate** (BUKAN certbot Let's Encrypt). Lihat §5.
4. **Suspend ≠ Delete** — data tenant TIDAK pernah dipadam sebab tak bayar; pintu dikunci sahaja.
5. **Wang = `decimal`** + helper `wang()` (elak float bug). Audit "siapa buat" pada setiap tindakan kritikal.
6. **White-label + module toggle** dikawal via `settings` per-tenant (bukan struktur DB).

---

## 3. GOTCHA & PERATURAN KRITIKAL (jangan lupa)

### Gotcha pengaturcaraan
1. **Slug role = `admin-koperasi`, BUKAN `admin`** — punca #1 bug akses. (Bug pending: MeetingController `$canCreate` masih guna `'admin'` — perlu fix.)
2. **Table waris = `next_of_kin` (SINGULAR)** — bukan `next_of_kins`. (Disahkan dari schema sebenar Sesi 3.)
3. **Module `simpanan_saham` DIKOMEN (OFF)** — koperasi berasaskan saham, produk simpanan toggle off (transaksi saham masih direkod dalam `transactions`). **7 modul aktif:** `pengurusan_staff`, `pengurusan_member`, `permohonan_pinjaman`, `mesyuarat_minit`, `laporan_audit`, `akaun`, `tetapan_sistem`. (Lihat §12B.)
4. **`avatar_path` (users) ≠ `foto_path` (members)** — dua benda berasingan.
5. **PostgreSQL LIKE case-sensitive** → guna `LOWER()` + `whereRaw` untuk carian.
6. **Migration order** — table yang rujuk members/meetings/loans perlu nombor migration lebih tinggi (FK).
7. **`tenant_asset()`** untuk SEMUA fail upload tenant (logo/avatar/foto).
8. **`composer dump-autoload`** lepas edit `helpers.php`.

### Gotcha #12 — CACHE LEAK antara tenant (KRITIKAL)
- **Masalah:** Laravel memoize cache store sekali per request. Kalau store ter-resolve sebelum tenancy init, ia terikat ke connection CENTRAL sampai habis request → cache semua tenant bocor masuk satu table cache central. Simptom: setting/tema satu tenant "berjangkit" ke tenant lain.
- **FIX (dah apply + verified di dev DAN server):** `Cache::forgetDriver(config('cache.default'))` pada event `TenancyInitialized` + `TenancyEnded`, didaftar dalam `TenancyServiceProvider` (line ~115 & ~119).
- `CacheTenancyBootstrapper` kekal **OFF**. `CACHE_STORE=database`.
- ⚠️ JANGAN sentuh `Events\SavingTenant` / `SavingDomain` dalam TenancyServiceProvider — itu event stancl, bukan modul savings.

### Peraturan deploy & kerja
- **Edit di LOCAL** → commit → push → `git pull` di server. JANGAN edit code terus di server (elak git conflict).
- **Pengecualian:** `.env`, firewall, DNS, `db_backup.py`, data DB = server-only (tak masuk git).
- **`optimize:clear` / `config:clear`** lepas deploy. (Alias `cuci` di server = clear + cache config + cache routes.)
- **Rutin deploy v2 = 2-step migration:** `migrate` (central) + `tenants:migrate` (semua tenant).
- **Verify dengan `grep -n` selepas edit** (pengalaman pahit: edit tak save).
- **Backup sebelum edit fail penting** (`cp fail fail.bak-$(date ...)`).
- **`DROP DATABASE ... WITH (FORCE)`** untuk elak masalah connection pool masa padam tenant.

---

## 4. CARA KERJA DENGAN CLAUDE (preferensi user)

- **Bahasa:** Melayu Malaysia, santai (panggil "bro" ok).
- **Bukan rewrite penuh** — guide step-by-step, atau format CARI/TUKAR (📄 path → kod lama → kod baru).
- **Brainstorm dulu** — terang sebab & trade-off sebelum laksana.
- **Satu langkah satu masa** untuk operasi berisiko (DB, firewall) — verify output sebelum gerak.
- **Push back bila perlu** — kalau user nak over-engineer atau ada cara lebih baik, cakap.
- **Guna `ask_user_input`** untuk pilihan/keputusan supaya senang di mobile.

---

## 5. INFRASTRUKTUR SERVER (server-only, TAK masuk git)

### Cloudflare + SSL (Sesi 3)
- **Strategi:** Cloudflare Proxy (orange cloud) + **Cloudflare Origin Certificate** dipasang di nginx.
- Cert di VPS: `/etc/ssl/cloudflare/koperasicms.site.pem` + `.key` (expire **2041** — set-and-forget).
- **SSL mode di Cloudflare = Full (strict).**
- **certbot TAK diperlukan** (keputusan asal Sesi 2.5 guna certbot Let's Encrypt — DITUKAR).
- DNS records (semua **Proxied/orange**): `*.koperasicms.site`, `koperasicms.site`, `www` → `216.126.236.127`.
- Cara verify trafik lalu CF: cari header `cf-ray` + `server: cloudflare` dalam `curl -sI`.

### Firewall UFW (Sesi 3)
- Port **2234** (SSH) — ALLOW.
- Port **80** — ALLOW (HTTP redirect).
- Port **443** — ALLOW **hanya dari range IP Cloudflare** (15 IPv4 + 7 IPv6, fetch dari `cloudflare.com/ips-v4` & `ips-v6`).
- Default: `deny incoming`. Akses terus ke IP VPS port 443 dari bukan-CF → **timeout** (terbukti).
- Sebab penting: melindungi `trustProxies('*')` daripada IP spoofing.

### Real IP (trustProxies) — hutang #1 SELESAI
- **App side:** `bootstrap/app.php` set `$middleware->trustProxies(at: '*', headers: X_FORWARDED_FOR|HOST|PORT|PROTO)`. Commit dah di GitHub, **belum pull ke server**.
- **Server side:** firewall UFW (di atas) — selesai.
- Kesan: `central_activity_logs.ip` nanti rekod IP pelawat sebenar (bukan IP CF). Header CF: `CF-Connecting-IP` / `X-Forwarded-For`.

### Database PostgreSQL (server)
- User app: **`baseri`** (bukan `postgres` lagi — ditukar Sesi 3) + password kuat (dalam `.env`, BUKAN `123456` lagi).
- `baseri` ada `CREATEDB` (untuk stancl cipta tenant DB baru).
- DB sedia ada: `koperasi_tenant` (central), `tenantujian1`, `tenantujian2` — owner `baseri`.
- DB `koperasi` (lama, kosong, sampah) — owner `postgres`, **belum dipadam** (boleh padam bila-bila).
- Tenant DB lama owned by `postgres` perlu `GRANT ALL` + `ALTER DEFAULT PRIVILEGES` ke `baseri`. Tenant BARU (dicipta selepas tukar `.env`) auto-owned `baseri` (takde isu).
- Auth: connect via TCP `127.0.0.1` (md5/password), BUKAN peer auth.

### `.env` server (kunci penting — `chmod 640`, group `www-data`)
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://koperasicms.site
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=koperasi_tenant
DB_USERNAME=baseri
DB_PASSWORD=<password kuat — simpan dalam password manager>
QUEUE_CONNECTION=sync
CACHE_STORE=database
SESSION_DRIVER=database
```

### Backup automatik (`/home/baseri/scripts/db_backup.py`)
- Cron: `0 2 * * *` (setiap hari 2 pagi) + `* * * * * schedule:run`.
- **Direka semula Sesi 3:** baca credential dari `.env` Laravel (satu sumber kebenaran) — BUKAN hardcode password lagi.
- `ENV_FILE = /home/baseri/projek/koperasicms-with-tenant/.env`.
- `pg_dumpall` semua DB → gzip → `rclone move` ke Google Drive `gdrive:Koperasi_ServerBackups`.
- Permission: script `700`, `.env` `640`.
- ⚠️ Belum ada: rotation backup, notifikasi gagal, logrotate untuk `backup.log`.
- ⚠️ Backup belum pernah **di-restore-test** (Fasa C2).

### Permission storage
- `storage/` + `bootstrap/cache/` → mode `775`, owner `www-data:www-data`.
- ⚠️ Tiada setgid (`g+s`) — polish Fasa C kalau nak konsisten SOP penuh.

---

## 6. FASA A — VERIFY DEPLOYMENT (✅ SELESAI Sesi 3)

| # | Item | Hasil |
|---|---|---|
| A1 | DB lama selamat | ⏭️ SKIP — app lama dah dibuang, tiada DB lama nak jaga |
| A2 | `.env` v2 server | ✅ DB user/password/url betul, GRANT semua DB |
| A3 | `tenants:list` | ✅ |
| A4 | Cache leak fix di server | ✅ `forgetDriver` ada (line 115 & 119) |
| A5 | SSL | ✅ CF Proxy + Origin + Full strict |
| A6 | Cron + permission | ✅ schedule:run + backup, storage 775 |
| A7 | nginx | ✅ 2 server block (central + wildcard regex) |

**Penemuan besar Sesi 3:** App lama (Baling) sudah dibuang dari VPS. Tiada data koperasi sebenar — semua testing. Maka **Fasa D (Migrate Baling) dipadam** dari roadmap.

### nginx config (rujukan)
- Server block 1: `server_name koperasicms.site www.koperasicms.site` (central).
- Server block 2: `server_name ~^(?<tenant>.+)\.koperasicms\.site$` (wildcard tenant, regex).
- Kedua-dua: `listen 443 ssl http2`, cert sama (`/etc/ssl/cloudflare/...`), HTTP→HTTPS redirect.

---

## 7. FASA B — TEST ISOLATION (✅ 15/15 LULUS Sesi 3)

| Kumpulan | Ujian | Hasil |
|---|---|---|
| Session | 1–3 (login serentak, logout, tukar password) | ✅ |
| Data CRUD | 4–7 (member, transaksi, mesyuarat+pinjaman, akaun+dividen) | ✅ tiada bocor |
| Settings/Cache | 8 (tukar nama+tema) | ✅ cache fix kerja di server |
| Storage | 9–11 (upload, folder isolation, cross-access 404) | ✅ `tenant_asset` betul |
| Route | 12–14 (central vs tenant, domain tak wujud) | ✅ error bersih |
| No Ahli | 15 (turutan `A0001` sendiri) | ✅ dua-dua tenant mula A0001 |

**Bukti storage isolation:** fail dalam `storage/tenant<id>/app/public/{logos,avatars,members}/`. Cross-access dari domain lain → 404.
**Kredensial seeder super-user:** `muhamad.baseri@gmail.com` / `@Password12345` (role `super-user`).

---

## 8. ROADMAP BERPRIORITI (Sesi 3 → seterusnya)

> Peraturan emas: JANGAN mula fasa sebelum kriteria fasa sebelumnya dicapai.

```
FASA A: Verify Deployment      ✅ SELESAI (Sesi 3)
FASA B: Test Isolation (15)    ✅ SELESAI (Sesi 3)
FASA C: Kemas & Kunci          ← SETERUSNYA
FASA E: SaaS Panel             (suspend/tier/billing)
FASA F: Modul Koperasi         (pinjaman penuh, portal ahli, dll)
(FASA D: Migrate Baling        — DIPADAM, tiada data sebenar)
```

### FASA C — Kemas & Kunci (SETERUSNYA)
> Sebahagian dah selesai awal sebab kerja Sesi 3.

- **C1.** Tukar password DB production — ✅ **SELESAI** (Sesi 3: `123456` → kuat, user `postgres` → `baseri`).
- **C2.** Backup + **VERIFIED restore** — separuh: backup berfungsi ✅, tapi belum pernah test restore. *"Backup yang tak pernah di-restore = bukan backup."* Perlu: restore ke DB temp, banding kiraan row.
- **C3.** Bug MeetingController `'admin'` → `'admin-koperasi'` (5 minit, commit).
- **C4.** Sahkan module key — ✅ **SELESAI** (Sesi 3: 7 modul aktif disahkan dari `config/modules.php`, lihat §12B). Tinggal: pastikan `module_role` seeder selaras 7 modul ni.
- **C5.** Failed-job/error visibility — `storage/logs` terjaga; optional notifikasi error (telegram/email). Tambah logrotate untuk `backup.log`.
- **C6 (baru).** Deploy code terbaru ke server: `git pull` (`bootstrap/app.php` trustProxies + `config/tenancy.php` `--force`).
- **C7 (baru).** Padam DB sampah `koperasi`.
- **C8 (polish).** setgid `g+s` pada storage; kemas backup `.py.bak` lama.

### FASA E — SaaS Panel (~3-5 sesi)
> Rujuk §9 untuk schema DB penuh. Sub-fasa boleh siap berasingan:
- **E1. Asas:** 6 migration central (urutan: `central_users` → `plans` → `subscriptions` → `invoices` → `payments` → `central_activity_logs`) + guard `auth:central` + login panel + seeder owner & 3 plan.
- **E2. Pengurusan tenant:** senarai tenant + stat + butang ON/OFF (is_active + log WAJIB) + middleware `CheckTenantAktif` + view "Akaun Digantung". ← **fungsi suspend, siap awal sengaja.**
- **E3. Cipta tenant dari UI:** borang panel → guna semula logik `TenantCipta` (refactor command jadi service supaya CLI & UI kongsi kod).
- **E4. Billing manual:** jana invois (auto no siri), rekod bayaran (+bukti), senarai tunggakan, auto-tanda `lewat` (scheduler), suspend automatik (optional toggle).
- **E5. Tier enforcement:** plan→modul (guna `ModuleAccess` sedia ada) + had `max_ahli` + paparan "Naik taraf plan".
- **E6. Landing marketing:** page statik (features, pricing dari table `plans`, borang lead) — last.

### FASA F — Modul Koperasi (ikut permintaan)
- **F1.** Pinjaman lengkap — kadar faedah, jadual ansuran, rekod bayaran, penjamin, had kelayakan ikut saham, no rujukan, penyata. (Perlu sesi brainstorm design sendiri.)
- **F2.** Portal ahli self-service — role `ahli` tengok baki saham/dividen/pinjaman sendiri.
- **F3.** Resit rasmi PDF (no siri).
- **F4.** Penyata tahunan ahli (cetak AGM).
- **F5.** Fi/yuran keahlian + tracking tunggakan.
- **F6.** Backup spatie per-tenant (selain pg_dumpall global).

---

## 9. BLUEPRINT DB — SAAS PANEL (CENTRAL) — untuk Fasa E

> Reka bentuk SAHAJA (belum implement). Semua table ni di **CENTRAL DB** (`koperasi_tenant`) — milik pemilik SaaS, bukan data koperasi.

### Prinsip
1. `tenants.id` = **string** → semua FK ke tenant ikut jenis string.
2. Wang = `decimal(10,2)` + `wang()`. Audit "siapa buat" pada tindakan kritikal.
3. **Suspend ≠ Delete.** Data tenant tak pernah dipadam — pintu dikunci.
4. Subscription berasingan dari Plan (sejarah kekal); Payment berasingan dari Invoice (boleh bayar separa).

### Table baru (CENTRAL)
**1. `central_users`** — admin SaaS (owner + staff). Guard berasingan `auth:central`. Lajur: id, name, email (unique), password, role (`owner`/`staff`), is_active, remember_token, timestamps.

**2. `plans`** — definisi tier. Lajur: id, kod (unique: `basic`/`pro`/`enterprise`), nama, harga_bulanan, harga_tahunan (nullable), max_ahli (nullable=unlimited), max_staff (nullable), modul_dibenarkan (json array module_key), features (json — expansion slot), is_active, susunan, timestamps.

**3. `subscriptions`** — langganan tenant + sejarah. Lajur: id, tenant_id (string FK cascade), plan_id (FK restrict), status (`trial`/`aktif`/`digantung`/`tamat`/`batal`), tarikh_mula, tarikh_tamat (nullable), **harga_terkunci** (harga masa subscribe), kitaran (`bulanan`/`tahunan`), auto_renew, catatan, timestamps.
- Satu tenant boleh BANYAK row (sejarah) tapi SATU aktif/trial/digantung pada satu masa — partial unique index:
  `CREATE UNIQUE INDEX ON subscriptions (tenant_id) WHERE status IN ('trial','aktif','digantung');`

**4. `invoices`** — bil. Lajur: id, no_invois (unique auto `INV-2026-0001`), tenant_id (string FK cascade), subscription_id (FK nullable, nullOnDelete), amaun, tarikh_invois, tarikh_due (INDEX), tempoh_dari, tempoh_hingga, status (`belum_bayar`/`dibayar`/`lewat`/`batal` — INDEX (tenant_id,status)), catatan, timestamps.

**5. `payments`** — rekod bayaran (berasingan dari invoice!). Lajur: id, invoice_id (FK restrict), amaun (boleh < invois — bayar separa), tarikh_bayar, kaedah (`tunai`/`transfer`/`fpx`/`gateway`), rujukan (nullable), resit_path (nullable), disahkan_oleh (FK central_users nullable — audit), catatan, timestamps.
- Σ payments >= amaun invois → status invois jadi `dibayar` (kira di app/service, BUKAN trigger DB).

**6. `central_activity_logs`** — audit trail. Lajur: id, central_user_id (FK nullable=sistem), tenant_id (string FK nullable), tindakan (`suspend`/`activate`/`cipta_tenant`/`tukar_plan`/`sah_bayaran`...), detail (json snapshot), ip (nullable), created_at (tiada updated_at — log tak diedit).
- ⭐ KRITIKAL: bila suspend koperasi orang, MESTI ada rekod siapa/bila/kenapa.

**7. Status tenant (on/off)** — BUKAN table baru. Guna VirtualColumn `tenants.data` (zero migration): `is_active` (kill-switch manual), `suspended_at`, `suspend_sebab`.
- **Middleware `CheckTenantAktif`** (selepas `InitializeTenancyByDomain`): BLOCK jika `tenant->is_active === false` ATAU subscription status `digantung`/`tamat` → paparkan view "Akaun Digantung". Dua lapisan: `is_active` (manual override) + subscription status (automatik ikut billing).

### Enforcement tier dalam app tenant
1. `max_ahli` → check sebelum simpan member baru.
2. `modul_dibenarkan` → suntik ke `ModuleAccess::userCan()` — **guna semula sistem module/toggle sedia ada**.
3. ⚠️ Cache info plan per tenant: ingat gotcha #12! Cache dalam cache TENANT dengan TTL pendek (5-15 min), atau baca terus tiap request (1 query indexed).

### Turutan migration (WAJIB — sebab FK)
`central_users` → `plans` → `subscriptions` → `invoices` → `payments` → `central_activity_logs`

### Expansion masa depan (rancang RUANG, jangan bina lagi)
`leads` (borang demo), `announcements` + `announcement_tenant` (broadcast), `gateway_webhooks` (log callback toyyibPay/Billplz), `support_tickets`, `central_roles` (RBAC). Slot json (`plans.features`, `logs.detail`, `tenants.data`) = penyerap perubahan kecil tanpa migration.

---

## 10. HUTANG & PENDING (senarai penuh)

### Code (dalam repo — perlu local→push→pull)
- ⏳ **`bootstrap/app.php`** — trustProxies dah commit di GitHub, **belum `git pull` ke server**.
- ⏳ **`config/tenancy.php`** — `--force` masih dikomen (line ~201). Punca seed gagal di production (prompt "Application In Production"). Sekarang guna jalan tengah `tenants:seed --tenants=X --force`. **Fix kekal:** uncomment `--force` → push → pull. Tenant baru lepas tu auto-seed betul.
- ⏳ Bug MeetingController `'admin'` → `'admin-koperasi'` (C3).

### Server-only
- ⏳ Padam DB sampah `koperasi` (C7).
- ⏳ Test restore backup (C2).
- ⏳ Rotation + notifikasi gagal backup; logrotate `backup.log` (C5).
- ⏳ setgid `g+s` storage; kemas `db_backup.py.bak` lama (C8).

### SELESAI Sesi 3
- ✅ Hutang #1 (Real IP) — trustProxies + firewall.
- ✅ Hutang #2 (DNS record redundant `kpmbb`/`kpkbaling`) — dipadam di Cloudflare.
- ✅ Tukar password DB + user `postgres`→`baseri` (C1).
- ✅ Refactor `db_backup.py` baca dari `.env`.

---

## 11. SOP — OPERASI BIASA

### Cipta tenant baru
```bash
php artisan tenant:cipta <id> <id>.koperasicms.site --nama="Nama Koperasi"
# Kalau production prompt block seed (sebelum fix --force):
php artisan tenants:seed --tenants=<id> --force
```
Kredensial super-user default: `muhamad.baseri@gmail.com` / `@Password12345`.

### Padam tenant (SOP — urutan WAJIB: domains → tenants → DROP DB → storage)
```bash
# 1. Padam rekod central
sudo -u postgres psql -d koperasi_tenant <<'EOF'
DELETE FROM domains WHERE tenant_id IN ('<id>');
DELETE FROM tenants WHERE id IN ('<id>');
EOF
# 2. Drop DB (FORCE untuk elak masalah connection)
sudo -u postgres psql -c "DROP DATABASE tenant<id> WITH (FORCE);"
# 3. Padam storage
sudo rm -rf storage/tenant<id>
# 4. Verify
php artisan tenants:list
```

### Tukar password DB
```bash
read -s -p "Password baru: " NEWPASS && echo
sudo -u postgres psql -c "ALTER USER baseri WITH PASSWORD '$NEWPASS' CREATEDB;"
# Update DB_PASSWORD dalam .env, lepas tu:
php artisan config:clear   # atau alias `cuci`
```

### Grant akses DB tenant lama (kalau owner masih postgres)
```bash
sudo -u postgres psql -d <db> <<'EOF'
GRANT ALL ON ALL TABLES IN SCHEMA public TO baseri;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO baseri;
GRANT ALL ON SCHEMA public TO baseri;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO baseri;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO baseri;
EOF
```

### Verify trafik melalui Cloudflare
```bash
curl -sI https://<id>.koperasicms.site   # cari: cf-ray, server: cloudflare, HTTP/2 302
```

### Test backup manual
```bash
python3 /home/baseri/scripts/db_backup.py
rclone ls gdrive:Koperasi_ServerBackups | tail -5
```

---

## 12. BLUEPRINT DB — TENANT (DETAIL PENUH dari schema sebenar)

> Sumber: `pg_dump --schema-only tenantujian1` (Sesi 3). Koperasi berasaskan SAHAM.
> PostgreSQL 16.14. Semua `timestamp(0) without time zone`. Money guna `numeric` (PHP `wang()`).
> **23 table** termasuk sistem. Setiap table ada `id bigint PK` + sequence kecuali pivot & sistem.

### 12.1 RBAC & Pengguna

**`users`**
| Lajur | Jenis | Constraint |
|---|---|---|
| id | bigint | PK |
| name | varchar(255) | NOT NULL |
| email | varchar(255) | UNIQUE NOT NULL |
| phone | varchar(20) | |
| password | varchar(255) | NOT NULL |
| email_verified_at | timestamp | |
| remember_token | varchar(100) | |
| is_active | boolean | DEFAULT true |
| avatar_path | varchar(255) | ⚠️ user avatar (≠ members.foto_path) |
| created_at, updated_at | timestamp | |

**`roles`** — id, name varchar(50) UNIQUE, slug varchar(50) UNIQUE, description text, timestamps. (7 role — lihat §13.)
**`permissions`** — id, name varchar(50) UNIQUE, slug varchar(50) UNIQUE, description text, timestamps.
**`role_user`** (pivot) — user_id + role_id, PK gabungan, dua-dua FK cascade.
**`permission_role`** (pivot) — permission_id + role_id, PK gabungan, dua-dua FK cascade.
**`module_role`** (pivot) — **module_key varchar(50)** + role_id, PK gabungan (module_key, role_id), role_id FK cascade. ⭐ Ini jambatan modul↔role (ModuleAccess).

### 12.2 Keahlian

**`members`**
| Lajur | Jenis | Constraint |
|---|---|---|
| id | bigint | PK |
| no_ahli | varchar(10) | **UNIQUE NOT NULL** (`A0001`...) |
| user_id | bigint | FK → users, SET NULL |
| nama | varchar(255) | NOT NULL |
| no_kp | varchar(20) | |
| telefon | varchar(20) | |
| alamat | text | |
| tarikh_sertai | date | |
| status | varchar | DEFAULT 'aktif' — CHECK (`aktif`/`tidak_aktif`/`berhenti`) |
| foto_path | varchar(255) | ⚠️ foto ahli (≠ users.avatar_path) |
| created_at, updated_at | timestamp | |

**`next_of_kin`** (SINGULAR!) — id, member_id (FK cascade, **UNIQUE** — satu waris satu ahli), nama varchar(255), no_kp varchar(20), hubungan varchar(50) NOT NULL, telefon, alamat, timestamps.

### 12.3 Transaksi & Saham

**`transactions`**
| Lajur | Jenis | Constraint |
|---|---|---|
| id | bigint | PK |
| member_id | bigint | FK → members, CASCADE |
| jenis | varchar | CHECK (`saham`/`simpanan`) |
| arah | varchar | CHECK (`masuk`/`keluar`) |
| amaun | numeric(12,2) | NOT NULL |
| baki | numeric(12,2) | NOT NULL (running balance) |
| sumber | varchar(30) | DEFAULT 'manual' |
| rujukan | varchar(50) | |
| keterangan | text | |
| recorded_by | bigint | FK → users, SET NULL (audit) |
| | | INDEX (member_id, jenis) |

**`share_transfers`** — id, from_member_id (FK cascade), to_member_id (FK cascade), amaun numeric(12,2), sebab varchar(100), tarikh_pindah date NOT NULL, processed_by (FK users SET NULL), **meeting_id** (FK SET NULL), **pencadang_id** (FK members SET NULL), **penyokong_id** (FK members SET NULL), catatan_kelulusan text, timestamps.

**`ownership_transfers`** — id, member_id (FK cascade), from_user_id (FK SET NULL), from_nama varchar(255), to_user_id (FK SET NULL), to_nama varchar(255) NOT NULL, to_no_kp varchar(20), sebab varchar(100), tarikh_pindah date NOT NULL, processed_by (FK users SET NULL), **meeting_id** (FK SET NULL), **pencadang_id** (FK members SET NULL), **penyokong_id** (FK members SET NULL), catatan_kelulusan text, timestamps.

### 12.4 Pinjaman

**`loans`**
| Lajur | Jenis | Constraint |
|---|---|---|
| id | bigint | PK |
| member_id | bigint | FK → members, CASCADE |
| dimohon_oleh | bigint | FK → users, SET NULL |
| amount | numeric(12,2) | NOT NULL |
| tempoh | smallint | NOT NULL (bulan) |
| tujuan | text | NOT NULL |
| status | varchar | DEFAULT 'pending' — CHECK (`pending`/`approved`/`rejected`) |
| catatan | text | |
| reviewed_by | bigint | FK → users, SET NULL |
| reviewed_at | timestamp | |
| meeting_id | bigint | FK → meetings, SET NULL |
| pencadang_id | bigint | FK → members, SET NULL |
| penyokong_id | bigint | FK → members, SET NULL |

> ⚠️ `loans` sekarang ASAS sahaja (amount, tempoh, status). Modul pinjaman LENGKAP (faedah, ansuran, bayaran) = Fasa F1 — perlu tambah table/lajur baru.

### 12.5 Mesyuarat

**`meetings`** — id, tajuk varchar(255) NOT NULL, tarikh date NOT NULL, lokasi varchar(255), minit text, created_by (FK users SET NULL), timestamps.

### 12.6 Akaun (Kira-kira)

**`account_categories`** — id, parent_id (FK self CASCADE — hierarki), jenis varchar CHECK (`pendapatan`/`perbelanjaan`), nama varchar(120) NOT NULL, kod varchar(30), berulang boolean DEFAULT false, is_active boolean DEFAULT true, keterangan text, susunan int DEFAULT 0, timestamps. INDEX (jenis, is_active) + (parent_id).

**`account_entries`** — id, category_id (FK RESTRICT), jenis varchar CHECK (`pendapatan`/`perbelanjaan`), member_id (FK SET NULL), amaun numeric(14,2) NOT NULL, tarikh date NOT NULL, rujukan varchar(60), penerima_pembayar varchar(150), keterangan text, recorded_by (FK users SET NULL), timestamps. INDEX (category_id), (jenis, tarikh), (member_id).

### 12.7 Dividen

**`dividend_runs`**
| Lajur | Jenis | Nota |
|---|---|---|
| id | bigint PK | |
| tahun | smallint | **UNIQUE** (satu run setahun) |
| tarikh_cutoff | date | NOT NULL |
| tarikh_mula | date | |
| untung_bersih | numeric(14,2) | DEFAULT 0 |
| jumlah_peruntukan | numeric(14,2) | DEFAULT 0 |
| untung_boleh_agih | numeric(14,2) | DEFAULT 0 |
| peratus_dividen | numeric(5,2) | DEFAULT 0 |
| jumlah_dividen | numeric(14,2) | DEFAULT 0 |
| jumlah_saham_anggota | numeric(16,2) | DEFAULT 0 |
| peratus_auditor | numeric(5,2) | DEFAULT 0 |
| peratus_diluluskan | numeric(5,2) | DEFAULT 0 |
| baki_dibawa_hadapan | numeric(16,2) | DEFAULT 0 |
| status | varchar | DEFAULT 'draf' — CHECK (`draf`/`dimuktamadkan`) |
| tarikh_muktamad | date | |
| dikira_oleh | bigint | FK → users, SET NULL |
| catatan | text | |

**`dividend_allocations`** — id, dividend_run_id (FK cascade), nama_tabung varchar(120) NOT NULL, jenis_kira varchar DEFAULT 'peratus' CHECK (`peratus`/`amaun`), nilai numeric(14,2) DEFAULT 0, amaun numeric(14,2) DEFAULT 0, susunan int DEFAULT 0, timestamps.

**`dividend_shares`** — id, dividend_run_id (FK cascade), member_id (FK cascade), saham_layak numeric(14,2), saham_auto numeric(14,2), peratus numeric(8,4), amaun_dividen numeric(14,2), override boolean DEFAULT false, timestamps. UNIQUE (dividend_run_id, member_id). INDEX (member_id).

### 12.8 Sistem & White-label

**`settings`** — id, key varchar(80) **UNIQUE**, value text, timestamps. (Key-value: nama koperasi, logo, tema, toggle produk.)
**`cache`** — key varchar(255) PK, value text, expiration bigint. INDEX (expiration).
**`cache_locks`** — key PK, owner varchar(255), expiration bigint.
**`sessions`** — id varchar(255) PK, user_id bigint, ip_address varchar(45), user_agent text, payload text, last_activity int. INDEX (user_id), (last_activity).
**`password_reset_tokens`** — email PK, token varchar(255), created_at.
**`migrations`** — id, migration varchar(255), batch int.

> ⚠️ TIADA table `jobs` (QUEUE=sync, tiada queue). TIADA table `savings` (modul dibuang Sesi 2.5).

### Nota schema penting
- **Money:** `numeric(12,2)` untuk transaksi/pinjaman; `numeric(14,2)` untuk akaun/dividen; `numeric(16,2)` untuk agregat saham besar. PHP guna `wang()`.
- **FK kelulusan:** loans + share_transfers + ownership_transfers semua ada `meeting_id` + `pencadang_id` + `penyokong_id` + `catatan_kelulusan` (SET NULL).
- **Audit:** setiap proses ada lajur "siapa buat" (`recorded_by` / `processed_by` / `dimohon_oleh` / `reviewed_by` / `dikira_oleh` / `created_by`) → semua FK users SET NULL.
- **CHECK constraints** banyak (status enum). Hati-hati bila insert — kena ikut nilai dibenarkan.
- Untuk dump terkini: `sudo -u postgres pg_dump --schema-only --no-owner tenantujian1`.

---

## 12B. MODUL SISTEM (`config/modules.php`)

> Satu sumber kebenaran. Digunakan oleh: middleware `module`, ModuleAccessController (matrix), DashboardController (tile), Sidebar.
> Format: `module_key => [label, desc, route, route_prefix, icon]`.
> `route_prefix` = route dilindungi (wildcard, cth `users.*`).

**7 modul AKTIF + 1 dikomen (jumlah dalam config):**

| # | module_key | Label | Route | Route prefix | Status |
|---|---|---|---|---|---|
| 1 | `pengurusan_staff` | Pengurusan Pekerja | `users.index` | `users.*` | ✅ aktif |
| 2 | `pengurusan_member` | Pengurusan Ahli Koperasi | `members.index` | `members.*` | ✅ aktif |
| 3 | `permohonan_pinjaman` | Permohonan Pinjaman | `pinjaman.index` | `pinjaman.*` | ✅ aktif |
| — | `simpanan_saham` | Simpanan & Saham | `simpanan.index` | `simpanan.*` | ⚠️ **DIKOMEN (OFF)** |
| 4 | `mesyuarat_minit` | Mesyuarat & Minit | `mesyuarat.index` | `mesyuarat.*` | ✅ aktif |
| 5 | `laporan_audit` | Laporan Audit | `audit.index` | `audit.*` | ✅ aktif |
| 6 | `akaun` | Akaun | `akaun.entri.index` | `akaun.*` | ✅ aktif (route_params: jenis=pendapatan) |
| 7 | `tetapan_sistem` | Tetapan Sistem | `roles.index` | `roles.*` | ✅ aktif (peranan, kebenaran, akses modul) |

> **Pembetulan gotcha #3:** module key `simpanan_saham` WUJUD tapi **DIKOMEN** (koperasi berasaskan saham, produk simpanan OFF). Transaksi saham masih direkod (table `transactions` jenis=`saham`), cuma modul UI simpanan tu off. Modul `akaun` (pendapatan/perbelanjaan) + `tetapan_sistem` (RBAC) adalah modul ke-6 & ke-7 yang aktif.
> Setiap modul ada: `label`, `desc`, `route`, `route_prefix` (+ optional `route_params`), `icon` (SVG path).
> Module key disimpan dalam table `module_role.module_key` (per tenant) — kawal role mana boleh akses modul mana.

**Pengguna config ni:** Middleware `module` (kawal akses), `ModuleAccessController` (matrix tetapan), `DashboardController` (tile), Sidebar (menu).

---

## 13. SEEDER (`DatabaseSeeder` — urutan)
```
1. RoleSeeder            (7 peranan)
2. PermissionSeeder      (kebenaran + petakan ke peranan)
3. SuperUserSeeder       (muhamad.baseri@gmail.com / @Password12345)
4. ModuleAccessSeeder    (akses modul lalai per peranan)
5. AccountCategorySeeder (kategori akaun)
6. SettingSeeder         (logo, nama, tema)
// Sample seeder (Member/AccountEntry/Loan/Meeting) — dikomen, untuk demo sahaja
```
⚠️ `SuperUserSeeder` cipta user dulu, lepas tu cari role `super-user` — urutan penting (Role mesti dahulu).

---

## 14. LOGIK PERNIAGAAN PENTING (dari blueprint asal)

### Helper global (`app/helpers.php` — daftar di composer.json `files`)
- **`wang($nilai): float`** — bulatkan wang ke 2 decimal. Guna untuk SEMUA kira wang (elak float bug RM50000→49999.99). `round((float)$x, 2)`.
- **`simpanan_aktif(): bool`** — toggle produk simpanan (Setting `produk_simpanan` === '1').
- **`pinjaman_aktif(): bool`** — toggle produk pinjaman (Setting `produk_pinjaman` === '1').
- ⚠️ WAJIB `composer dump-autoload` selepas edit helpers.php.

### Toggle produk (config-based)
- Simpanan OFF → sorok UI (dashboard, profil, borang, lejar penapis, audit).
- Pinjaman OFF → sorok UI + **SEKAT ROUTE** (middleware `pinjaman_aktif` / EnsurePinjamanAktif → abort 404).
- Code backend kekal utuh (untuk tenant lain yang nak guna) — JANGAN buang.

### Dividen — accounting-correct (Akta Koperasi 1993 s.56-57)
- **Tabung** (Rizab min 15%, KWAPK 2%) dikira atas **untung bersih**.
- **Untung Boleh Agih = Untung Bersih − Σ Tabung.**
- **Dividen = saham × kadar diluluskan TERUS** (BUKAN ratio). Cth: RM1000 × 7% = RM70.
- **Cutoff = 31 Dis** (hujung tahun kewangan). Ahli sertai selepas cutoff TAK layak tahun itu. Tiada pro-rata.
- **2 kadar:** `peratus_auditor` (cadangan juruaudit) + `peratus_diluluskan` (AGM — yang DIPAKAI).
- Untung bersih = SILING (warn kalau dividen > untung boleh agih, tak block).
- **Baki Dibawa Hadapan = Untung Boleh Agih − Jumlah Dividen.**
- Service: `DividendService` (kiraRingkasan, kiraJumlahSahamSetakat, agihMengikutSaham, muktamad, sahamLayakSemua).
- Draf vs Muktamad (watermark DRAF sebelum muktamad).
- ⚠️ NOTE: untung_bersih kadang tersimpan 738999.99 vs 739000 — workaround retype di draf.

### Perlindungan super-user
- Padam super-user DISEKAT TOTAL (sesiapa pun). Sunting super-user hanya super-user boleh.
- Sekat di UI + controller (edit/update/destroy) — defense in depth.

### Log Aktiviti (Audit Trail) — super-user sahaja
- Himpun dari rekod sedia ada (TIADA table baru, baca je bila buka).
- Sumber: Transaction(recorder), ShareTransfer/OwnershipTransfer(processor), Loan(pemohon+reviewer), DividendRun(pengira), AccountEntry(recorder), Meeting(pencipta).
- Had 500/sumber, pagination 40, penapis modul+tarikh, Export CSV.

### Reka bentuk UI
- **Tema:** hijau gelap (ink `#0c1f1c`, teal `#1f6f5c`) + emas (`#c0962c`).
- **Layout:** sidebar accordion (dark) + topbar + content. Master: `resources/views/layouts/master.blade.php`.
- **Fonts:** Fraunces (heading) + Outfit (body).
- **White-label:** logo, nama, warna tema per koperasi (settings + config/themes.php, 6 palet).
- **Komponen:** `.panel`, `.stat`, `.btn` (gold/ghost/danger), `.badge`, `.field`, `.check`, `.alert`.
- ⚠️ Borang upload WAJIB `enctype="multipart/form-data"`.

### Gotcha tambahan (dari blueprint asal)
- **Route cache production:** selepas tambah route/view baru, WAJIB `route:clear` (atau optimize:clear). Kalau tak: "Route [x] not defined" → 500 SEMUA page (master.blade load route tu).
- **Git via terminal Linux sahaja** (bukan VS Code Windows — SSH config BOM rosak).
- **`migrate:fresh` = DROP SEMUA TABLE** — DB disposable sahaja, JANGAN production.
- **Tinker:** paste SATU line, tunggu hasil, baru next (multi-line tersangkut).

---

*Tamat README induk. Sesi seterusnya: mula dengan **Fasa C** (Kemas & Kunci) — keutamaan C6 (deploy code) + C2 (test restore backup) + C3 (bug MeetingController).*


# PATCH README INDUK — SESI 4 (14 Jun 2026)

> Apply setiap blok CARI/TUKAR ke README induk. Disusun ikut seksyen.
> Ringkasan Sesi 4: C6 + C2 + C3 + C7 SELESAI + ciri baru auto-mampat imej.

═══════════════════════════════════════════════════════════════════
PATCH 1 — Header versi (atas sekali)
═══════════════════════════════════════════════════════════════════

CARI:
> Bahasa: Melayu Malaysia. Versi terakhir dikemaskini: **akhir Sesi 3 (13 Jun 2026)**.

TUKAR:
> Bahasa: Melayu Malaysia. Versi terakhir dikemaskini: **akhir Sesi 4 (14 Jun 2026)**.

═══════════════════════════════════════════════════════════════════
PATCH 2 — §0 Stack teknikal (tambah Intervention Image)
═══════════════════════════════════════════════════════════════════

CARI:
| Frontend | Blade + Alpine.js |

TUKAR:
| Frontend | Blade + Alpine.js |
| Imej | Intervention Image v4 (4.1.3) + ext-gd (auto-mampat upload → WebP) |

═══════════════════════════════════════════════════════════════════
PATCH 3 — §1 Status semasa (kemas kini hutang)
═══════════════════════════════════════════════════════════════════

CARI:
### Hutang/pending utama (lihat §10 untuk senarai penuh)
- ⏳ Deploy code terbaru ke server (commit `bootstrap/app.php` trustProxies belum `git pull`).
- ⏳ Fix `config/tenancy.php` `--force` (seed production) — masih guna jalan tengah.
- ⏳ Hutang #2 DNS sudah **SELESAI** (record redundant dipadam).

TUKAR:
### Hutang/pending utama (lihat §10 untuk senarai penuh)
- ✅ Deploy code (trustProxies + `--force`) — SELESAI Sesi 4 (C6).
- ✅ Fix `config/tenancy.php` `--force` — SELESAI Sesi 4 (uncomment line 201, push, pull).
- ✅ Backup rosak (kosong sejak ~7 Jun) — dijumpai & dibaiki Sesi 4 (C2).
- ✅ Bug slug role `'admin'` — dibaiki di `pinjaman/index.blade.php` Sesi 4 (C3).
- ✅ DB sampah `koperasi` — dipadam Sesi 4 (C7).
- ⏳ Polish baki: rotation backup, logrotate, padam fail backup 215-byte lama di Drive (C5/C8).

═══════════════════════════════════════════════════════════════════
PATCH 4 — §1 Roadmap (tanda C selesai sebahagian)
═══════════════════════════════════════════════════════════════════

CARI:
```
A ✅ → B ✅ → C → E → F
(Fasa D — Migrate Baling — DIPADAM, tiada data Baling sebenar)
```

TUKAR:
```
A ✅ → B ✅ → C (teras siap, tinggal polish) → E → F
(Fasa D — Migrate Baling — DIPADAM, tiada data Baling sebenar)
```

═══════════════════════════════════════════════════════════════════
PATCH 5 — §3 Gotcha #1 (bug MeetingController dah SALAH lokasi)
═══════════════════════════════════════════════════════════════════

CARI:
1. **Slug role = `admin-koperasi`, BUKAN `admin`** — punca #1 bug akses. (Bug pending: MeetingController `$canCreate` masih guna `'admin'` — perlu fix.)

TUKAR:
1. **Slug role = `admin-koperasi`, BUKAN `admin`** — punca #1 bug akses. (Sesi 4: MeetingController sebenarnya DAH betul; bug sebenar di `resources/views/pinjaman/index.blade.php` — dah dibaiki. Sapuan `grep -rn "'admin'"` confirm takde lagi.)

═══════════════════════════════════════════════════════════════════
PATCH 6 — §3 Gotcha baru (tambah selepas gotcha #8 composer dump-autoload)
═══════════════════════════════════════════════════════════════════

CARI:
8. **`composer dump-autoload`** lepas edit `helpers.php`.

TUKAR:
8. **`composer dump-autoload`** lepas edit `helpers.php`.
9. **Upload imej auto-mampat** — guna helper `simpan_imej_mampat($fail, $folder)` (resize 600px + WebP), JANGAN `->store()` mentah. Output sentiasa `.webp` nama random. Gambar lama kekal format asal (OK, tetap papar).
10. **Intervention Image v4 ≠ v3 API** — guna `$manager->decode($path)`, `$imej->scaleDown(w,h)`, `$imej->encode(new WebpEncoder(quality:N))`. BUKAN `read()`/`toWebp()` (itu v3, akan error "undefined method").
11. **GD extension** — server VPS dah ada; mesin local (WSL) kena `sudo apt install php8.4-gd`. Kalau `dpkg interrupted` → `sudo dpkg --configure -a` dulu.

═══════════════════════════════════════════════════════════════════
PATCH 7 — §5 Real IP (trustProxies) — tanda selesai
═══════════════════════════════════════════════════════════════════

CARI:
- **App side:** `bootstrap/app.php` set `$middleware->trustProxies(at: '*', headers: X_FORWARDED_FOR|HOST|PORT|PROTO)`. Commit dah di GitHub, **belum pull ke server**.

TUKAR:
- **App side:** `bootstrap/app.php` set `$middleware->trustProxies(at: '*', headers: X_FORWARDED_FOR|HOST|PORT|PROTO)`. **SELESAI deploy ke server Sesi 4 (C6).**

═══════════════════════════════════════════════════════════════════
PATCH 8 — §5 Backup automatik (REKA SEMULA — bahagian PALING PENTING)
═══════════════════════════════════════════════════════════════════

CARI:
### Backup automatik (`/home/baseri/scripts/db_backup.py`)
- Cron: `0 2 * * *` (setiap hari 2 pagi) + `* * * * * schedule:run`.
- **Direka semula Sesi 3:** baca credential dari `.env` Laravel (satu sumber kebenaran) — BUKAN hardcode password lagi.
- `ENV_FILE = /home/baseri/projek/koperasicms-with-tenant/.env`.
- `pg_dumpall` semua DB → gzip → `rclone move` ke Google Drive `gdrive:Koperasi_ServerBackups`.
- Permission: script `700`, `.env` `640`.
- ⚠️ Belum ada: rotation backup, notifikasi gagal, logrotate untuk `backup.log`.
- ⚠️ Backup belum pernah **di-restore-test** (Fasa C2).

TUKAR:
### Backup automatik (`/home/baseri/scripts/db_backup.py`)
- Cron: `0 2 * * *` (setiap hari 2 pagi) + `* * * * * schedule:run`.
- `ENV_FILE = /home/baseri/projek/koperasicms-with-tenant/.env` (baca credential dari .env, satu sumber kebenaran).
- **⚠️ REKA SEMULA Sesi 4 (C2) — PENEMUAN BESAR:** backup lama guna `pg_dumpall` GAGAL SENYAP sejak ~7 Jun (semua fail 215 bytes = KOSONG). Punca: `pg_dumpall` baca `pg_authid` (superuser-only) tapi `baseri` non-superuser → mati awal; bug pipe `| gzip` telan error (exit code gzip, bukan pg_dumpall) → cron lapor "berjaya" tiap hari.
- **Versi baru:** `pg_dump` PER-DB (tak sentuh `pg_authid`, non-superuser OK) + auto-detect senarai DB + tangkap exit code pg_dump SEBENAR + guard saiz (<500 bytes = gagal) + all-or-nothing (gagal → tak upload + exit 1).
- Output: satu fail per-DB dalam folder bertarikh `gdrive:Koperasi_ServerBackups/<timestamp>/`.
- **Restore TERBUKTI Sesi 4:** restore tenantujian1 ke DB temp, row count padan 9/9 table. Backup hidup balik.
- Permission: script `700`, `.env` `640`.
- ⚠️ Belum ada: rotation backup, notifikasi gagal, logrotate `backup.log`, padam 8 fail 215-byte lama di Drive (C5).

═══════════════════════════════════════════════════════════════════
PATCH 9 — §5 Database PostgreSQL (kemas — DB koperasi dah padam)
═══════════════════════════════════════════════════════════════════

CARI:
- DB sedia ada: `koperasi_tenant` (central), `tenantujian1`, `tenantujian2` — owner `baseri`.
- DB `koperasi` (lama, kosong, sampah) — owner `postgres`, **belum dipadam** (boleh padam bila-bila).

TUKAR:
- DB sedia ada: `koperasi_tenant` (central), `tenantujian1`, `tenantujian2` — owner `baseri`.
- DB `koperasi` (sampah, owner `postgres`) — **SELESAI DIPADAM Sesi 4 (C7)**. Rupanya ada 25 table schema LAMA (termasuk `savings` pra-removal) tapi 0 data sebenar (cuma 1 super-user seed). README §5 lama silap kata "kosong owned postgres" — kini moot.

═══════════════════════════════════════════════════════════════════
PATCH 10 — §8 FASA C (tanda C2/C3/C6/C7 selesai)
═══════════════════════════════════════════════════════════════════

CARI:
- **C2.** Backup + **VERIFIED restore** — separuh: backup berfungsi ✅, tapi belum pernah test restore. *"Backup yang tak pernah di-restore = bukan backup."* Perlu: restore ke DB temp, banding kiraan row.
- **C3.** Bug MeetingController `'admin'` → `'admin-koperasi'` (5 minit, commit).

TUKAR:
- **C2.** ✅ **SELESAI Sesi 4** — backup lama ROSAK (kosong), direka semula (pg_dump per-DB), restore terbukti 9/9 table padan. Lihat §5.
- **C3.** ✅ **SELESAI Sesi 4** — bug sebenar di `pinjaman/index.blade.php` (BUKAN MeetingController), `'admin'` → `'admin-koperasi'`.

CARI:
- **C6 (baru).** Deploy code terbaru ke server: `git pull` (`bootstrap/app.php` trustProxies + `config/tenancy.php` `--force`).
- **C7 (baru).** Padam DB sampah `koperasi`.

TUKAR:
- **C6.** ✅ **SELESAI Sesi 4** — git pull (trustProxies + `--force`), `cuci`, app verified hidup via CF. Set `git config core.fileMode false` (elak noise permission public/*).
- **C7.** ✅ **SELESAI Sesi 4** — DB sampah `koperasi` dipadam (`sudo -u postgres ... DROP DATABASE koperasi WITH (FORCE)`).

═══════════════════════════════════════════════════════════════════
PATCH 11 — §10 Hutang (pindah item ke SELESAI)
═══════════════════════════════════════════════════════════════════

CARI:
### Code (dalam repo — perlu local→push→pull)
- ⏳ **`bootstrap/app.php`** — trustProxies dah commit di GitHub, **belum `git pull` ke server**.
- ⏳ **`config/tenancy.php`** — `--force` masih dikomen (line ~201). Punca seed gagal di production (prompt "Application In Production"). Sekarang guna jalan tengah `tenants:seed --tenants=X --force`. **Fix kekal:** uncomment `--force` → push → pull. Tenant baru lepas tu auto-seed betul.
- ⏳ Bug MeetingController `'admin'` → `'admin-koperasi'` (C3).

TUKAR:
### Code (dalam repo — perlu local→push→pull)
- ✅ **`bootstrap/app.php`** — trustProxies SELESAI deploy (Sesi 4 C6).
- ✅ **`config/tenancy.php`** — `--force` line 201 SELESAI uncomment + deploy (Sesi 4 C6). Tenant baru auto-seed betul sekarang.
- ✅ Bug slug `'admin'` di `pinjaman/index.blade.php` (Sesi 4 C3).

CARI:
### Server-only
- ⏳ Padam DB sampah `koperasi` (C7).
- ⏳ Test restore backup (C2).

TUKAR:
### Server-only
- ✅ Padam DB sampah `koperasi` (Sesi 4 C7).
- ✅ Test restore backup + baiki backup rosak (Sesi 4 C2).

═══════════════════════════════════════════════════════════════════
PATCH 12 — §10 SELESAI (tambah blok Sesi 4)
═══════════════════════════════════════════════════════════════════

CARI:
### SELESAI Sesi 3
- ✅ Hutang #1 (Real IP) — trustProxies + firewall.

TUKAR:
### SELESAI Sesi 4
- ✅ C6 deploy (trustProxies + `--force`), C2 baiki+test backup, C3 bug slug, C7 padam DB sampah.
- ✅ Ciri BARU: auto-mampat imej upload (Intervention Image v4 + GD, resize 600px → WebP). 3 tempat: ProfileController (avatar) + MemberController store/update (foto).

### SELESAI Sesi 3
- ✅ Hutang #1 (Real IP) — trustProxies + firewall.

═══════════════════════════════════════════════════════════════════
PATCH 13 — §14 Logik perniagaan (tambah helper imej baru)
═══════════════════════════════════════════════════════════════════

CARI:
- **`pinjaman_aktif(): bool`** — toggle produk pinjaman (Setting `produk_pinjaman` === '1').
- ⚠️ WAJIB `composer dump-autoload` selepas edit helpers.php.

TUKAR:
- **`pinjaman_aktif(): bool`** — toggle produk pinjaman (Setting `produk_pinjaman` === '1').
- **`simpan_imej_mampat($fail, $folder, $maxSisi=600, $kualiti=85): string`** — resize imej upload (sisi terpanjang max 600px, kekal ratio, tak besarkan) + convert WebP + simpan ke disk public (ter-scope tenant). Pulang path `.webp`. Guna ganti `->store()` untuk semua upload avatar/foto.
- ⚠️ WAJIB `composer dump-autoload` selepas edit helpers.php.

═══════════════════════════════════════════════════════════════════
TAMAT PATCH SESI 4
═══════════════════════════════════════════════════════════════════
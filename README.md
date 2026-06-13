# KoperasiCMS — Blueprint Induk & Roadmap (Master README)

> **Dokumen tunggal sumber kebenaran (single source of truth).**
> Tampal dokumen ini di awal setiap sesi baru. Mula sesi dengan: *"sambung dari README induk"*.
> Bahasa: Melayu Malaysia. Versi terakhir dikemaskini: **akhir Sesi 4 (14 Jun 2026)**.

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
| Imej | Intervention Image v4 (4.1.3) + ext-gd (auto-mampat upload → WebP) |
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

## 1. STATUS SEMASA (akhir Sesi 4)

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
- ✅ Deploy code (trustProxies + `--force`) — SELESAI Sesi 4 (C6).
- ✅ Fix `config/tenancy.php` `--force` — SELESAI Sesi 4 (uncomment line 201, push, pull).
- ✅ Backup rosak (kosong sejak ~7 Jun) — dijumpai & dibaiki Sesi 4 (C2).
- ✅ Bug slug role `'admin'` — dibaiki di `pinjaman/index.blade.php` Sesi 4 (C3).
- ✅ DB sampah `koperasi` — dipadam Sesi 4 (C7).
- ⏳ Polish baki: rotation backup, logrotate, padam fail backup 215-byte lama di Drive (C5/C8).

### Roadmap (urutan mutlak — JANGAN langkau)
```
A ✅ → B ✅ → C (teras siap, tinggal polish) → E → F
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
1. **Slug role = `admin-koperasi`, BUKAN `admin`** — punca #1 bug akses. (Sesi 4: MeetingController sebenarnya DAH betul; bug sebenar di `resources/views/pinjaman/index.blade.php` — dah dibaiki. Sapuan `grep -rn "'admin'"` confirm takde lagi.)
2. **Table waris = `next_of_kin` (SINGULAR)** — bukan `next_of_kins`. (Disahkan dari schema sebenar Sesi 3.)
3. **Module `simpanan_saham` DIKOMEN (OFF)** — koperasi berasaskan saham, produk simpanan toggle off (transaksi saham masih direkod dalam `transactions`). **7 modul aktif:** `pengurusan_staff`, `pengurusan_member`, `permohonan_pinjaman`, `mesyuarat_minit`, `laporan_audit`, `akaun`, `tetapan_sistem`. (Lihat §12B.)
4. **`avatar_path` (users) ≠ `foto_path` (members)** — dua benda berasingan.
5. **PostgreSQL LIKE case-sensitive** → guna `LOWER()` + `whereRaw` untuk carian.
6. **Migration order** — table yang rujuk members/meetings/loans perlu nombor migration lebih tinggi (FK).
7. **`tenant_asset()`** untuk SEMUA fail upload tenant (logo/avatar/foto).
8. **`composer dump-autoload`** lepas edit `helpers.php`.
9. **Upload imej auto-mampat** — guna helper `simpan_imej_mampat($fail, $folder)` (resize 600px + WebP), JANGAN `->store()` mentah. Output sentiasa `.webp` nama random. Gambar lama kekal format asal (OK, tetap papar).
10. **Intervention Image v4 ≠ v3 API** — guna `$manager->decode($path)`, `$imej->scaleDown(w,h)`, `$imej->encode(new WebpEncoder(quality:N))`. BUKAN `read()`/`toWebp()` (itu v3, akan error "undefined method").
11. **GD extension** — server VPS dah ada; mesin local (WSL) kena `sudo apt install php8.4-gd`. Kalau `dpkg interrupted` → `sudo dpkg --configure -a` dulu.

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
- **App side:** `bootstrap/app.php` set `$middleware->trustProxies(at: '*', headers: X_FORWARDED_FOR|HOST|PORT|PROTO)`. **SELESAI deploy ke server Sesi 4 (C6).**
- **Server side:** firewall UFW (di atas) — selesai.
- Kesan: `central_activity_logs.ip` nanti rekod IP pelawat sebenar (bukan IP CF). Header CF: `CF-Connecting-IP` / `X-Forwarded-For`.

### Database PostgreSQL (server)
- User app: **`baseri`** (bukan `postgres` lagi — ditukar Sesi 3) + password kuat (dalam `.env`, BUKAN `123456` lagi).
- `baseri` ada `CREATEDB` (untuk stancl cipta tenant DB baru).
- DB sedia ada: `koperasi_tenant` (central), `tenantujian1`, `tenantujian2` — owner `baseri`.
- DB `koperasi` (sampah, owner `postgres`) — **SELESAI DIPADAM Sesi 4 (C7)**. Rupanya ada 25 table schema LAMA (termasuk `savings` pra-removal) tapi 0 data sebenar (cuma 1 super-user seed). README §5 lama silap kata "kosong owned postgres" — kini moot.
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
- `ENV_FILE = /home/baseri/projek/koperasicms-with-tenant/.env` (baca credential dari .env, satu sumber kebenaran).
- **⚠️ REKA SEMULA Sesi 4 (C2) — PENEMUAN BESAR:** backup lama guna `pg_dumpall` GAGAL SENYAP sejak ~7 Jun (semua fail 215 bytes = KOSONG). Punca: `pg_dumpall` baca `pg_authid` (superuser-only) tapi `baseri` non-superuser → mati awal; bug pipe `| gzip` telan error (exit code gzip, bukan pg_dumpall) → cron lapor "berjaya" tiap hari.
- **Versi baru:** `pg_dump` PER-DB (tak sentuh `pg_authid`, non-superuser OK) + auto-detect senarai DB + tangkap exit code pg_dump SEBENAR + guard saiz (<500 bytes = gagal) + all-or-nothing (gagal → tak upload + exit 1).
- Output: satu fail per-DB dalam folder bertarikh `gdrive:Koperasi_ServerBackups/<timestamp>/`.
- **Restore TERBUKTI Sesi 4:** restore tenantujian1 ke DB temp, row count padan 9/9 table. Backup hidup balik.
- Permission: script `700`, `.env` `640`.
- ⚠️ Belum ada: rotation backup, notifikasi gagal, logrotate `backup.log`, padam 8 fail 215-byte lama di Drive (C5).

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
- **C2.** ✅ **SELESAI Sesi 4** — backup lama ROSAK (kosong), direka semula (pg_dump per-DB), restore terbukti 9/9 table padan. Lihat §5.
- **C3.** ✅ **SELESAI Sesi 4** — bug sebenar di `pinjaman/index.blade.php` (BUKAN MeetingController), `'admin'` → `'admin-koperasi'`.
- **C4.** Sahkan module key — ✅ **SELESAI** (Sesi 3: 7 modul aktif disahkan dari `config/modules.php`, lihat §12B). Tinggal: pastikan `module_role` seeder selaras 7 modul ni.
- **C5.** Failed-job/error visibility — `storage/logs` terjaga; optional notifikasi error (telegram/email). Tambah logrotate untuk `backup.log`.
- **C6.** ✅ **SELESAI Sesi 4** — git pull (trustProxies + `--force`), `cuci`, app verified hidup via CF. Set `git config core.fileMode false` (elak noise permission public/*).
- **C7.** ✅ **SELESAI Sesi 4** — DB sampah `koperasi` dipadam (`sudo -u postgres ... DROP DATABASE koperasi WITH (FORCE)`).
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
- ✅ **`bootstrap/app.php`** — trustProxies SELESAI deploy (Sesi 4 C6).
- ✅ **`config/tenancy.php`** — `--force` line 201 SELESAI uncomment + deploy (Sesi 4 C6). Tenant baru auto-seed betul sekarang.
- ✅ Bug slug `'admin'` di `pinjaman/index.blade.php` (Sesi 4 C3).

### Server-only
- ✅ Padam DB sampah `koperasi` (Sesi 4 C7).
- ✅ Test restore backup + baiki backup rosak (Sesi 4 C2).
- ⏳ Rotation + notifikasi gagal backup; logrotate `backup.log` (C5).
- ⏳ setgid `g+s` storage; kemas `db_backup.py.bak` lama (C8).

### SELESAI Sesi 4
- ✅ C6 deploy (trustProxies + `--force`), C2 baiki+test backup, C3 bug slug, C7 padam DB sampah.
- ✅ Ciri BARU: auto-mampat imej upload (Intervention Image v4 + GD, resize 600px → WebP). 3 tempat: ProfileController (avatar) + MemberController store/update (foto).

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
- **`simpan_imej_mampat($fail, $folder, $maxSisi=600, $kualiti=85): string`** — resize imej upload (sisi terpanjang max 600px, kekal ratio, tak besarkan) + convert WebP + simpan ke disk public (ter-scope tenant). Pulang path `.webp`. Guna ganti `->store()` untuk semua upload avatar/foto.
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

*Tamat README induk. Sesi seterusnya: mula dengan **Fasa C** (polish baki: C5 rotation/logrotate backup + C8 setgid storage) atau gerak ke **Fasa E** (SaaS Panel). Teras Fasa C dah siap Sesi 4.*
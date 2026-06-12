<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantCipta extends Command
{
    protected $signature = 'tenant:cipta
                            {id : ID tenant, huruf kecil (cth: baling, kedah)}
                            {domain : Domain penuh (cth: kedah.localhost)}
                            {--nama= : Nama koperasi (set terus dalam tetapan)}';

    protected $description = 'Cipta tenant baru: DB + migrate + seed + domain (dengan auto-cleanup jika gagal)';

    public function handle(): int
    {
        $id     = strtolower(trim($this->argument('id')));
        $domain = strtolower(trim($this->argument('domain')));
        $dbName = config('tenancy.database.prefix') . $id . config('tenancy.database.suffix');

        // ── VALIDASI ──────────────────────────────────────────────
        if (! preg_match('/^[a-z0-9][a-z0-9-]{1,30}$/', $id)) {
            $this->error("ID tak sah. Guna huruf kecil, nombor, dash sahaja (cth: baling, kop-kedah).");
            return self::FAILURE;
        }

        if (Tenant::find($id)) {
            $this->error("Tenant '{$id}' sudah wujud.");
            return self::FAILURE;
        }

        if (DB::table('domains')->where('domain', $domain)->exists()) {
            $this->error("Domain '{$domain}' sudah dipakai tenant lain.");
            return self::FAILURE;
        }

        // ── CIPTA ─────────────────────────────────────────────────
        $this->info("Mencipta tenant '{$id}' (DB: {$dbName})...");
        $this->info("Sabar — cipta DB + migrate + seed mengambil masa sedikit.");

        try {
            $tenant = Tenant::create(['id' => $id]);   // trigger: CreateDatabase + Migrate + Seed
            $tenant->domains()->create(['domain' => $domain]);

            if ($nama = $this->option('nama')) {
                $tenant->run(function () use ($nama) {
                    \App\Models\Setting::put('nama_koperasi', $nama);
                });
            }
        } catch (\Throwable $e) {
            $this->error("GAGAL: " . $e->getMessage());
            $this->warn("Membersihkan sisa (row + database)...");

            tenancy()->end();
            DB::table('domains')->where('tenant_id', $id)->delete();
            DB::table('tenants')->where('id', $id)->delete();
            DB::statement("DROP DATABASE IF EXISTS \"{$dbName}\"");

            $this->warn("Sisa dibersihkan. Betulkan punca error dan cuba lagi.");
            return self::FAILURE;
        }

        // ── SIAP ──────────────────────────────────────────────────
        $this->newLine();
        $this->info("✅ Tenant '{$id}' siap sepenuhnya!");
        $this->table(['Item', 'Nilai'], [
            ['ID tenant', $id],
            ['Database', $dbName],
            ['Domain', $domain],
            ['Nama koperasi', $this->option('nama') ?: '(default seeder)'],
        ]);
        $this->info("Login: http://{$domain}" . (app()->environment('local') ? ':8000' : '') . " dengan akaun super-user.");

        return self::SUCCESS;
    }
}
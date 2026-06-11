<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoanSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (Loan::exists()) {
            return;
        }

        // Pinjaman terikat pada AHLI aktif. Ambil beberapa ahli aktif rawak.
        $members = Member::where('status', 'aktif')->inRandomOrder()->limit(6)->get();
        if ($members->isEmpty()) {
            return;
        }

        $pelulus = User::whereHas('roles', fn ($q) => $q->where('slug', 'super-user'))->value('id');

        $samples = [
            ['amount' => 5000,  'tempoh' => 24, 'tujuan' => 'Pembelian peralatan rumah',     'status' => 'approved'],
            ['amount' => 12000, 'tempoh' => 36, 'tujuan' => 'Modal perniagaan kecil',         'status' => 'pending'],
            ['amount' => 3000,  'tempoh' => 12, 'tujuan' => 'Perbelanjaan persekolahan anak', 'status' => 'approved'],
            ['amount' => 8000,  'tempoh' => 24, 'tujuan' => 'Pembaikan kenderaan',            'status' => 'rejected'],
            ['amount' => 15000, 'tempoh' => 48, 'tujuan' => 'Ubahsuai rumah',                 'status' => 'pending'],
            ['amount' => 2000,  'tempoh' => 6,  'tujuan' => 'Kecemasan perubatan',            'status' => 'approved'],
        ];

        foreach ($samples as $i => $s) {
            $member = $members[$i % $members->count()];

            Loan::create([
                'member_id'    => $member->id,
                'dimohon_oleh' => $pelulus,   // contoh: staff yang key-in
                'amount'       => $s['amount'],
                'tempoh'       => $s['tempoh'],
                'tujuan'       => $s['tujuan'],
                'status'       => $s['status'],
                'catatan'      => $s['status'] === 'rejected' ? 'Baki saham tidak mencukupi' : null,
                'reviewed_by'  => $s['status'] === 'pending' ? null : $pelulus,
                'reviewed_at'  => $s['status'] === 'pending' ? null : now(),
            ]);
        }
    }
}
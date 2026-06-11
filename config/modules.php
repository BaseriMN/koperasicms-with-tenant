<?php

/*
|--------------------------------------------------------------------------
| Modul Sistem Koperasi
|--------------------------------------------------------------------------
| Satu sumber kebenaran untuk semua modul. Digunakan oleh:
|   - Middleware 'module'        (kawal akses)
|   - ModuleAccessController     (papar matrix tetapan)
|   - DashboardController        (papar tile)
|   - Sidebar (master layout)    (papar menu)
|
| 'route_prefix' = nama route yang dilindungi (guna wildcard, cth 'users.*').
*/

return [

    'modules' => [
        'pengurusan_staff' => [
            'label'        => 'Pengurusan Pekerja',
            'desc'         => 'Daftar & urus Pekerja Koperasi',
            'route'        => 'users.index',
            'route_prefix' => 'users.*',
            'icon'         => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0111 0M16 6.5a3 3 0 010 6M21 20a4.8 4.8 0 00-4-4.7"/>',
        ],
        'pengurusan_member' => [
            'label'        => 'Pengurusan Ahli Koperasi',
            'desc'         => 'Daftar & urus Ahli Koperasi',
            'route'        => 'members.index',
            'route_prefix' => 'members.*',
            'icon'         => '<path d="M12 3L3 9L12 15L21 9L12 3Z"/><path d="M12 15v6"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/>',
        ],
        'permohonan_pinjaman' => [
            'label'        => 'Permohonan Pinjaman',
            'desc'         => 'Mohon & lulus pinjaman',
            'route'        => 'pinjaman.index',
            'route_prefix' => 'pinjaman.*',
            'icon'         => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M7 15h4"/>',
        ],
        /*
        'simpanan_saham' => [
            'label'        => 'Simpanan & Saham',
            'desc'         => 'Rekod transaksi ahli',
            'route'        => 'simpanan.index',
            'route_prefix' => 'simpanan.*',
            'icon'         => '<path d="M12 3v18M7 8h7a3 3 0 010 6H6"/>',
        ],*/
        'mesyuarat_minit' => [
            'label'        => 'Mesyuarat & Minit',
            'desc'         => 'Jadual & minit mesyuarat',
            'route'        => 'mesyuarat.index',
            'route_prefix' => 'mesyuarat.*',
            'icon'         => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/>',
        ],
        'laporan_audit' => [
            'label'        => 'Laporan Audit',
            'desc'         => 'Semakan rekod kewangan',
            'route'        => 'audit.index',
            'route_prefix' => 'audit.*',
            'icon'         => '<path d="M4 4h12l4 4v12H4z"/><path d="M8 13l2.5 2.5L16 10"/>',
        ],
        'akaun' => [
            'label'        => 'Akaun',
            'desc'         => 'Pendapatan & perbelanjaan koperasi',
            'route'        => 'akaun.entri.index',
            'route_params' => ['jenis' => 'pendapatan'],
            'route_prefix' => 'akaun.*',
            'icon'         => '<path d="M3 6h18M3 12h18M3 18h18"/><circle cx="8" cy="6" r="1.5" fill="currentColor"/><circle cx="16" cy="12" r="1.5" fill="currentColor"/><circle cx="10" cy="18" r="1.5" fill="currentColor"/>',
        ],
        'tetapan_sistem' => [
            'label'        => 'Tetapan Sistem',
            'desc'         => 'Peranan, kebenaran & akses modul',
            'route'        => 'roles.index',
            'route_prefix' => 'roles.*',
            'icon'         => '<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 00-.1-1l2-1.5-2-3.4-2.3 1a7 7 0 00-1.7-1l-.4-2.5h-4l-.4 2.5a7 7 0 00-1.7 1l-2.3-1-2 3.4 2 1.5a7 7 0 000 2l-2 1.5 2 3.4 2.3-1a7 7 0 001.7 1l.4 2.5h4l.4-2.5a7 7 0 001.7-1l2.3 1 2-3.4-2-1.5c.06-.32.1-.66.1-1z"/>',
        ],
    ],

];

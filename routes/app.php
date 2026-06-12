<?php

use App\Http\Controllers\AuditReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ModuleAccessController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountCategoryController;
use App\Http\Controllers\AccountEntryController;
use App\Http\Controllers\AccountReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DividendController;
use App\Http\Controllers\ProfileController;


/*
| Web Routes — Sistem Pengurusan Koperasi
|--------------------------------------------------------------------------
| Akses modul dikawal oleh middleware 'module:<key>' yang membaca matrix
| dalam DB (jadual module_role). Super-user sentiasa dibenarkan.
*/

Route::get('/', fn () => redirect()->route('dashboard'));

/*
| Tetamu
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    

});

/*
| Pengguna log masuk
*/
Route::middleware('auth')->group(function () {

    // Log Aktiviti (super-user sahaja)
    Route::middleware('role:super-user')->group(function () {
        Route::get('log-aktiviti', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('log.index');
        Route::get('log-aktiviti/export/csv', [\App\Http\Controllers\ActivityLogController::class, 'exportCsv'])->name('log.export.csv');
    });
    
    // Tetapan Koperasi (logo, nama, no pendaftaran, tema)
    Route::get('tetapan/koperasi', [SettingController::class, 'edit'])->name('tetapan.koperasi');
    Route::put('tetapan/koperasi', [SettingController::class, 'update'])->name('tetapan.koperasi.update');
    Route::delete('tetapan/koperasi/logo', [SettingController::class, 'buangLogo'])->name('tetapan.koperasi.logo.buang');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil sendiri (semua user yang login)
    Route::get('profil', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::put('profil', [ProfileController::class, 'update'])->name('profil.update');
    Route::put('profil/password', [ProfileController::class, 'updatePassword'])->name('profil.password');
    Route::delete('profil/avatar', [ProfileController::class, 'buangAvatar'])->name('profil.avatar.buang');

    // Pengurusan Staff
    Route::middleware('module:pengurusan_staff')->group(function () {
    Route::resource('users', UserController::class);
    });

    // Pengurusan Ahli (Keahlian & Waris)
    Route::middleware('module:pengurusan_member')->group(function () {
        // Keahlian (AXXXX) + waris
        Route::get('members/export/csv', [MemberController::class, 'exportCsv'])->name('members.export.csv');
        Route::resource('members', MemberController::class);

        // Lejar transaksi saham & simpanan
        Route::get('transaksi', [TransactionController::class, 'index'])->name('transaksi.index');
        Route::get('transaksi/export/csv', [TransactionController::class, 'exportCsv'])->name('transaksi.export.csv');
        Route::get('transaksi/create', [TransactionController::class, 'create'])->name('transaksi.create');
        Route::post('transaksi', [TransactionController::class, 'store'])->name('transaksi.store');

        // Pindah milik saham
        Route::get('pindah-saham', [TransactionController::class, 'shareTransferForm'])->name('saham.pindah.form');
        Route::post('pindah-saham', [TransactionController::class, 'shareTransfer'])->name('saham.pindah');

        // Pindah milik keahlian (nombor ahli kekal)
        Route::get('members/{member}/pindah-milik', [TransactionController::class, 'ownershipTransferForm'])->name('member.pindah.form');
        Route::post('members/{member}/pindah-milik', [TransactionController::class, 'ownershipTransfer'])->name('member.pindah');
    });

    // Tetapan Sistem (Peranan, Kebenaran, Akses Modul)
    Route::middleware('module:tetapan_sistem')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::resource('permissions', PermissionController::class)->except(['show']);

        // Matrix akses modul
        Route::get('tetapan/modul', [ModuleAccessController::class, 'index'])->name('tetapan.modul');
        Route::put('tetapan/modul', [ModuleAccessController::class, 'update'])->name('tetapan.modul.update');
    });

    // Permohonan Pinjaman
    Route::middleware(['module:permohonan_pinjaman', 'pinjaman_aktif'])->group(function () {
        Route::get('pinjaman', [LoanApplicationController::class, 'index'])->name('pinjaman.index');
        Route::get('pinjaman/create', [LoanApplicationController::class, 'create'])->name('pinjaman.create');
        Route::post('pinjaman', [LoanApplicationController::class, 'store'])->name('pinjaman.store');
        Route::post('pinjaman/{loan}/decide', [LoanApplicationController::class, 'decide'])->name('pinjaman.decide');
    });

    // Simpanan & Saham (lejar transaksi)
    Route::middleware('module:simpanan_saham')->group(function () {
        Route::get('simpanan', [TransactionController::class, 'index'])->name('simpanan.index');
        Route::get('simpanan/create', [TransactionController::class, 'create'])->name('simpanan.create');
        Route::post('simpanan', [TransactionController::class, 'store'])->name('simpanan.store');
    });

    // Mesyuarat & Minit
    Route::middleware('module:mesyuarat_minit')->group(function () {
        Route::get('mesyuarat', [MeetingController::class, 'index'])->name('mesyuarat.index');
        Route::get('mesyuarat/create', [MeetingController::class, 'create'])->name('mesyuarat.create');
        Route::post('mesyuarat', [MeetingController::class, 'store'])->name('mesyuarat.store');
    });

    // Akaun — Penyata Untung Rugi (TANPA parameter jenis — liputi kedua-dua)
    Route::middleware('module:akaun')->group(function () {
        Route::get('akaun/penyata', [AccountReportController::class, 'untungRugi'])->name('akaun.penyata');
        // Imbangan Duga (Trial Balance) - ringkasan debit/kredit
        Route::get('akaun/imbangan-duga', [AccountCategoryController::class, 'trialBalance'])->name('akaun.imbangan_duga');
    });

    // Dividen
    Route::middleware('module:akaun')->group(function () {
        Route::get('akaun/dividen', [DividendController::class, 'index'])->name('akaun.dividen.index');
        Route::get('akaun/dividen/create', [DividendController::class, 'create'])->name('akaun.dividen.create');
        Route::post('akaun/dividen', [DividendController::class, 'store'])->name('akaun.dividen.store');
        Route::get('akaun/dividen/{dividen}', [DividendController::class, 'show'])->name('akaun.dividen.show');
        Route::put('akaun/dividen/{dividen}', [DividendController::class, 'update'])->name('akaun.dividen.update');
        Route::post('akaun/dividen/{dividen}/tabung', [DividendController::class, 'tambahTabung'])->name('akaun.dividen.tabung.tambah');
        Route::delete('akaun/dividen/{dividen}/tabung/{tabung}', [DividendController::class, 'buangTabung'])->name('akaun.dividen.tabung.buang');
        Route::put('akaun/dividen/{dividen}/bahagian/{bahagian}', [DividendController::class, 'overrideSaham'])->name('akaun.dividen.bahagian.override');
        Route::post('akaun/dividen/{dividen}/muktamad', [DividendController::class, 'muktamad'])->name('akaun.dividen.muktamad');
        Route::get('akaun/dividen/{dividen}/penyata/{bahagian}', [DividendController::class, 'penyata'])->name('akaun.dividen.penyata');
    });
    // Akaun — Pendapatan & Perbelanjaan ({jenis} = pendapatan | perbelanjaan)
    // whereIn mengunci {jenis} kepada dua nilai sah sahaja di peringkat route.
    Route::middleware('module:akaun')
        ->prefix('akaun/{jenis}')
        ->whereIn('jenis', ['pendapatan', 'perbelanjaan'])
        ->group(function () {
            // Entri pendapatan/perbelanjaan
            Route::get('/', [AccountEntryController::class, 'index'])->name('akaun.entri.index');
            Route::get('export/csv', [AccountEntryController::class, 'exportCsv'])->name('akaun.entri.export.csv');
            Route::get('entri/create', [AccountEntryController::class, 'create'])->name('akaun.entri.create');
            Route::post('entri', [AccountEntryController::class, 'store'])->name('akaun.entri.store');
            Route::get('entri/{entri}/edit', [AccountEntryController::class, 'edit'])->name('akaun.entri.edit');
            Route::put('entri/{entri}', [AccountEntryController::class, 'update'])->name('akaun.entri.update');
            Route::delete('entri/{entri}', [AccountEntryController::class, 'destroy'])->name('akaun.entri.destroy');
 
            // Pengurusan kategori dinamik
            Route::get('kategori', [AccountCategoryController::class, 'index'])->name('akaun.kategori.index');
            Route::get('kategori/create', [AccountCategoryController::class, 'create'])->name('akaun.kategori.create');
            Route::post('kategori', [AccountCategoryController::class, 'store'])->name('akaun.kategori.store');
            Route::get('kategori/{kategori}/edit', [AccountCategoryController::class, 'edit'])->name('akaun.kategori.edit');
            Route::put('kategori/{kategori}', [AccountCategoryController::class, 'update'])->name('akaun.kategori.update');
            Route::delete('kategori/{kategori}', [AccountCategoryController::class, 'destroy'])->name('akaun.kategori.destroy');
    });
    

    
    // Laporan Audit
    Route::middleware('module:laporan_audit')->group(function () {
        Route::get('audit', [AuditReportController::class, 'index'])->name('audit.index');
        Route::get('audit/export/csv', [AuditReportController::class, 'exportCsv'])->name('audit.export.csv');
    });
});
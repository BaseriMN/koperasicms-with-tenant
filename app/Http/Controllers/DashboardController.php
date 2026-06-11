<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Transaction;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $roles = $user->roles->pluck('slug')->toArray();
        
        // Modul dibenarkan dibaca dari matrix DB (super-user = semua)
        $allowedModules = ModuleAccess::allowedFor($user);
        
        $stats = [
            'staff'             => User::where('is_active', true)->count(),
            'ahli'             => Member::where('status', 'aktif')->count(),
            'simpanan'         => Transaction::where('jenis', 'simpanan')->sum('amaun'),
            'pinjaman_pending' => Loan::where('status', 'pending')->count(),
            'pinjaman_total'   => Loan::sum('amount'),
            'pinjaman_approved' => Loan::where('status', 'approved')->sum('amount'),
            'jumlah_saham_terkumpul'  => Transaction::where('jenis', 'saham')->sum('amaun'),
        ];
        
        return view('dashboard', [
            'user'           => $user,
            'roles'          => $roles,
            'allowedModules' => $allowedModules,
            'stats'          => $stats,
        ]);
    }
}

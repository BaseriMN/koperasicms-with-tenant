<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\User;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    private array $allowed = ['kerani', 'admin', 'pengurus', 'super-user'];

    public function index(Request $request)
    {
        if (! $request->user()->hasAnyRole($this->allowed)) {
            abort(403);
        }

        $savings = Saving::with('user')->latest()->paginate(15);

        $stats = [
            'simpanan' => Saving::where('jenis', 'simpanan')->sum('amaun'),
            'saham'    => Saving::where('jenis', 'saham')->sum('amaun'),
        ];

        return view('simpanan.index', compact('savings', 'stats'));
    }

    public function create(Request $request)
    {
        if (! $request->user()->hasAnyRole($this->allowed)) {
            abort(403);
        }

        $members = User::orderBy('name')->get(['id', 'name']);

        return view('simpanan.create', compact('members'));
    }

    public function store(Request $request)
    {
        if (! $request->user()->hasAnyRole($this->allowed)) {
            abort(403);
        }

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'jenis'   => ['required', 'in:simpanan,saham'],
            'amaun'   => ['required', 'numeric', 'min:1'],
        ]);

        Saving::create([
            'user_id'     => $data['user_id'],
            'jenis'       => $data['jenis'],
            'amaun'       => $data['amaun'],
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('simpanan.index')
            ->with('success', 'Transaksi simpanan/saham berjaya direkodkan.');
    }
}

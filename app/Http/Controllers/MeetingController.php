<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->hasAnyRole(['kerani', 'pengurus', 'admin-koperasi', 'super-user'])) {
            abort(403);
        }

        $meetings = Meeting::with('creator')->orderBy('tarikh', 'desc')->paginate(12);
        $canCreate = $request->user()->hasAnyRole(['kerani', 'pengurus', 'admin-koperasi', 'super-user']);

        return view('mesyuarat.index', compact('meetings', 'canCreate'));
    }

    public function create(Request $request)
    {
        if (! $request->user()->hasAnyRole(['kerani', 'pengurus', 'admin-koperasi', 'super-user'])) {
            abort(403, 'Tidak dibenarkan mencipta mesyuarat.');
        }

        return view('mesyuarat.create');
    }

    public function store(Request $request)
    {
        if (! $request->user()->hasAnyRole(['kerani', 'pengurus', 'admin-koperasi', 'super-user'])) {
            abort(403, 'Tidak dibenarkan mencipta mesyuarat.');
        }

        $data = $request->validate([
            'tajuk'  => ['required', 'string', 'max:255'],
            'tarikh' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'minit'  => ['nullable', 'string'],
        ]);

        Meeting::create($data + ['created_by' => $request->user()->id]);

        return redirect()->route('mesyuarat.index')
            ->with('success', 'Mesyuarat berjaya direkodkan.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\OwnershipTransfer;
use App\Models\ShareTransfer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Lejar penuh (semua ahli) atau ditapis ikut ahli.
     */
    public function index(Request $request)
    {
        // Query asas dengan semua penapis (guna semula untuk senarai + jumlah)
        $asas = fn () => Transaction::query()
            ->when($request->member_id, fn ($q, $id) => $q->where('member_id', $id))
            ->when($request->jenis, fn ($q, $j) => $q->where('jenis', $j))
            ->when($request->dari, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->hingga, fn ($q, $h) => $q->whereDate('created_at', '<=', $h));

        $transactions = $asas()->with(['member', 'recorder'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        // Jumlah masuk / keluar dalam tempoh ditapis
        $jumlah = [
            'masuk'  => wang((float) $asas()->where('arah', 'masuk')->sum('amaun')),
            'keluar' => wang((float) $asas()->where('arah', 'keluar')->sum('amaun')),
        ];
        $jumlah['bersih'] = wang($jumlah['masuk'] - $jumlah['keluar']);

        $members = Member::orderBy('no_ahli')->get(['id', 'no_ahli', 'nama']);

        return view('transaksi.index', compact('transactions', 'members', 'jumlah'));
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $namaFail = 'lejar-transaksi-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$namaFail}\"",
        ];

        $query = Transaction::with(['member', 'recorder'])
            ->when($request->member_id, fn ($q, $id) => $q->where('member_id', $id))
            ->when($request->jenis, fn ($q, $j) => $q->where('jenis', $j))
            ->when($request->dari, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->hingga, fn ($q, $h) => $q->whereDate('created_at', '<=', $h))
            ->latest();

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($out, ['Tarikh', 'No. Ahli', 'Nama', 'Jenis', 'Arah', 'Amaun (RM)', 'Baki (RM)', 'Sumber', 'Keterangan', 'Direkod Oleh']);

            $query->chunk(300, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        $t->created_at->format('Y-m-d H:i'),
                        $t->member->no_ahli ?? '',
                        $t->member->nama ?? '',
                        ucfirst($t->jenis),
                        ucfirst($t->arah),
                        number_format($t->amaun, 2, '.', ''),
                        number_format($t->baki, 2, '.', ''),
                        $t->sumber ?? '',
                        $t->keterangan ?? '',
                        $t->recorder->name ?? '',
                    ]);
                }
            });
            fclose($out);
        }, $namaFail, $headers);
    }




    public function create()
    {
        $members = Member::where('status', 'aktif')->orderBy('no_ahli')->get(['id', 'no_ahli', 'nama']);

        return view('transaksi.create', compact('members'));
    }

    /**
     * Rekod transaksi tunggal (deposit/pengeluaran saham atau simpanan).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id'  => ['required', 'exists:members,id'],
            'jenis'      => ['required', Rule::in(['saham', 'simpanan'])],
            'arah'       => ['required', Rule::in(['masuk', 'keluar'])],
            'amaun'      => ['required', 'numeric', 'min:0.01'],
            'rujukan'    => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $member = Member::findOrFail($data['member_id']);

        $this->recordTransaction(
            member: $member,
            jenis: $data['jenis'],
            arah: $data['arah'],
            amaun: (float) $data['amaun'],
            sumber: $data['arah'] === 'masuk' ? 'deposit' : 'pengeluaran',
            rujukan: $data['rujukan'] ?? null,
            keterangan: $data['keterangan'] ?? null,
            userId: $request->user()->id,
        );

        return redirect()->route('members.show', $member)
            ->with('success', 'Transaksi berjaya direkodkan.');
    }

    /**
     * Borang pindah milik SAHAM.
     */
    public function shareTransferForm()
    {
        $members = Member::where('status', 'aktif')->orderBy('no_ahli')->get(['id', 'no_ahli', 'nama']);
        $meetings = \App\Models\Meeting::latest('tarikh')->get(['id', 'tajuk', 'tarikh']);

        return view('transaksi.pindah_saham', compact('members', 'meetings'));
    }

    /**
     * Proses pindah milik saham: kurang dari pemberi, tambah ke penerima.
     */
    public function shareTransfer(Request $request)
    {
        $data = $request->validate([
            'from_member_id' => ['required', 'exists:members,id'],
            'to_member_id'   => ['required', 'exists:members,id', 'different:from_member_id'],
            'amaun'          => ['required', 'numeric', 'min:0.01'],
            'sebab'          => ['nullable', 'string', 'max:100'],
            'tarikh_pindah'  => ['required', 'date'],
            'meeting_id'     => ['required', 'exists:meetings,id'],
            'pencadang_id'   => ['required', 'exists:members,id'],
            'penyokong_id'   => ['required', 'exists:members,id', 'different:pencadang_id'],
            'catatan_kelulusan' => ['required', 'string', 'max:500'],
        ], [
            'meeting_id.required'   => 'Sila pilih mesyuarat kelulusan.',
            'pencadang_id.required' => 'Sila pilih pencadang.',
            'penyokong_id.required' => 'Sila pilih penyokong.',
            'penyokong_id.different' => 'Penyokong mesti berbeza daripada pencadang.',
            'catatan_kelulusan.required' => 'Sila isi catatan kelulusan.',
        ]);

        $from = Member::findOrFail($data['from_member_id']);
        $to   = Member::findOrFail($data['to_member_id']);

        if ($from->bakiSaham() < $data['amaun']) {
            throw ValidationException::withMessages([
                'amaun' => "Baki saham {$from->no_ahli} tidak mencukupi (RM " . number_format($from->bakiSaham(), 2) . ").",
            ]);
        }

        DB::transaction(function () use ($from, $to, $data, $request) {
            $ref = 'TFR-' . now()->format('YmdHis');

            $this->recordTransaction($from, 'saham', 'keluar', (float) $data['amaun'],
                'pindah_milik', $ref, "Pindah saham ke {$to->no_ahli}", $request->user()->id);

            $this->recordTransaction($to, 'saham', 'masuk', (float) $data['amaun'],
                'pindah_milik', $ref, "Terima saham dari {$from->no_ahli}", $request->user()->id);

            ShareTransfer::create([
                'from_member_id' => $from->id,
                'to_member_id'   => $to->id,
                'amaun'          => $data['amaun'],
                'sebab'          => $data['sebab'] ?? null,
                'tarikh_pindah'  => $data['tarikh_pindah'],
                'processed_by'   => $request->user()->id,
                'meeting_id'        => $data['meeting_id'],
                'pencadang_id'      => $data['pencadang_id'],
                'penyokong_id'      => $data['penyokong_id'],
                'catatan_kelulusan' => $data['catatan_kelulusan'],
            ]);
        });

        return redirect()->route('members.show', $to)
            ->with('success', "Saham RM " . number_format($data['amaun'], 2) . " berjaya dipindah dari {$from->no_ahli} ke {$to->no_ahli}.");
    }

    /**
     * Borang pindah milik KEAHLIAN (nombor ahli kekal).
     */
    public function ownershipTransferForm(Member $member)
    {
        $member->load('user');
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);
        $meetings = \App\Models\Meeting::latest('tarikh')->get(['id', 'tajuk', 'tarikh']);
        $ahliList = Member::where('status', 'aktif')->orderBy('no_ahli')->get(['id', 'no_ahli', 'nama']);

        return view('transaksi.pindah_milik', compact('member', 'users', 'meetings', 'ahliList'));
    }

    /**
     * Proses pindah milik keahlian: nombor ahli kekal, pemilik & data peribadi bertukar.
     */
    public function ownershipTransfer(Request $request, Member $member)
    {
        $data = $request->validate([
            'to_user_id'    => ['nullable', 'exists:users,id'],
            'to_nama'       => ['required', 'string', 'max:255'],
            'to_no_kp'      => ['nullable', 'string', 'max:20'],
            'to_telefon'    => ['nullable', 'string', 'max:20'],
            'to_alamat'     => ['nullable', 'string'],
            'sebab'         => ['nullable', 'string', 'max:100'],
            'tarikh_pindah' => ['required', 'date'],
            'meeting_id'        => ['required', 'exists:meetings,id'],
            'pencadang_id'      => ['required', 'exists:members,id'],
            'penyokong_id'      => ['required', 'exists:members,id', 'different:pencadang_id'],
            'catatan_kelulusan' => ['required', 'string', 'max:500'],
        ], [
            'meeting_id.required'        => 'Sila pilih mesyuarat kelulusan.',
            'pencadang_id.required'      => 'Sila pilih pencadang.',
            'penyokong_id.required'      => 'Sila pilih penyokong.',
            'penyokong_id.different'     => 'Penyokong mesti berbeza daripada pencadang.',
            'catatan_kelulusan.required' => 'Sila isi catatan kelulusan.',
        ]);

        DB::transaction(function () use ($member, $data, $request) {
            // Rekod sejarah (snapshot pemilik lama)
            OwnershipTransfer::create([
                'member_id'    => $member->id,
                'from_user_id' => $member->user_id,
                'from_nama'    => $member->nama,
                'to_user_id'   => $data['to_user_id'] ?? null,
                'to_nama'      => $data['to_nama'],
                'to_no_kp'     => $data['to_no_kp'] ?? null,
                'sebab'        => $data['sebab'] ?? null,
                'tarikh_pindah'=> $data['tarikh_pindah'],
                'processed_by' => $request->user()->id,
                'meeting_id'        => $data['meeting_id'],
                'pencadang_id'      => $data['pencadang_id'],
                'penyokong_id'      => $data['penyokong_id'],
                'catatan_kelulusan' => $data['catatan_kelulusan'],
            ]);

            // Kemaskini pemilik semasa — NOMBOR AHLI KEKAL
            $member->update([
                'user_id' => $data['to_user_id'] ?? null,
                'nama'    => $data['to_nama'],
                'no_kp'   => $data['to_no_kp'] ?? null,
                'telefon' => $data['to_telefon'] ?? null,
                'alamat'  => $data['to_alamat'] ?? null,
            ]);
        });

        return redirect()->route('members.show', $member)
            ->with('success', "Keahlian {$member->no_ahli} berjaya dipindah milik kepada {$data['to_nama']}.");
    }

    /**
     * Helper: rekod satu baris lejar + kira baki terkini.
     */
    private function recordTransaction(
        Member $member, string $jenis, string $arah, float $amaun,
        string $sumber, ?string $rujukan, ?string $keterangan, int $userId
    ): Transaction {
        $bakiSemasa = $member->bakiJenis($jenis);
        $bakiBaru   = wang($arah === 'masuk' ? $bakiSemasa + $amaun : $bakiSemasa - $amaun);
        $amaun      = wang($amaun);
        return $member->transactions()->create([
            'jenis'       => $jenis,
            'arah'        => $arah,
            'amaun'       => $amaun,
            'baki'        => $bakiBaru,
            'sumber'      => $sumber,
            'rujukan'     => $rujukan,
            'keterangan'  => $keterangan,
            'recorded_by' => $userId,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\LoanRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class LoanRequestController extends Controller
{
    public function index(): View
    {
        $LoanRequests = LoanRequest::with('buku')
            ->latest()
            ->paginate(10);

        return view('loan_request.index', compact('LoanRequests'));
    }

    public function create(Request $request): View
    {
        $equipments = equipments::query()->orderBy('name')->get();

        return view('loan_request.create', [
            'equipments' => $equipments,
            'equipmentsTerpilih' => $request->integer('equipments_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'equipments_id' => ['required', 'integer', 'exists:bukus,id'],
            'borrower_name' => ['required', 'string', 'min:6', 'max:15'],
            'borrower_nim' => ['required', 'string', 'min:3', 'max:150'],
        ]);
        $validated['borrower_nim'] = strtoupper(trim($validated['borrower_nim']));

        try {
            DB::transaction(function () use ($validated): void {
                $equipments = equipments::where('id', $validated['equipments_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($equipments->stok < 1) {
                    throw new RuntimeException('Stok equipments sedang habis.');
                }

                $sudahMeminjam = $equipments->loan_request()
                    ->where('borrower_nim', $validated['borrower_nim'])
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($sudahMeminjam) {
                    throw new RuntimeException('NIM tersebut masih meminjam <?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command("inspire", function () {
    $this->comment(Inspiring::quote());
})->purpose("Display an inspiring quote");
 ini.');
                }

                $equipments->decrement('stok');
                $equipments->peminjamans()->create([
                    'equipment_id' => 'PJM-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                    'borrower_nim' => $validated['borrower_nim'],
                    'expected_return_date_' => $validated['borrower_nim'],
                    'borrower_name' => $validated['borrower_name'],
                    'borrower_emai' => today(),
                    'status' => 'dipinjam',
                ]);
            });
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (QueryException $exception) {
            Log::error('[DB EXCEPTION] Gagal mencatat peminjaman.', [
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Peminjaman gagal disimpan. Silakan coba kembali.');
        }

        $request->session()->put('last_borrower_nim', $validated['borrower_nim']);

        return redirect()->route('loan_requestloan_request.index')
            ->with('success', 'equipments berhasil dipinjam.');
    }

    public function returnBook(loan_request $loan_request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($loan_request): void {
                $loan_request = loan_request::where('id', $peminjaman->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($peminjaman->status !== 'dipinjam') {
                    throw new RuntimeException('Peminjaman ini sudah dikembalikan.');
                }

                $buku = equipments::where('id', $loan_request->equipments_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $loan_request->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => today(),
                ]);
                $equipments->increment('stok');
            });
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (QueryException $exception) {
            Log::error('[DB EXCEPTION] Gagal mencatat pengembalian.', [
                'loan_request_id' => $loan_request->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Pengembalian gagal disimpan. Silakan coba kembali.');
        }

        return redirect()->route('loan_request.index')
            ->with('success', 'equipments berhasil dikembalikan.');
    }
}

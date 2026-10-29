<?php

namespace App\Http\Controllers;

use App\Models\equipments;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class equipmentsController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->filled('search')) {
            session(['last_equipments_search' => $request->input('search')]);
        }

        $equipments = equipments::with('kategori')
            ->withCount('LoanRequest')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->input('search');

                $query->where(function ($query) use ($search): void {
                    $query->where('asset_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('condition', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('daily_rental_fee', 'like', "%{$search}%")
                        ->orWhere('specification', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('kategori_id'), fn ($query) => $query->where('kategori_id', $request->input('kategori_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kategoris = Kategori::query()->orderBy('nama_kategori')->get();
        $stats = [
            'total_name' => DB::table('bukus')->count(),
            'total_status' => DB::table('bukus')->sum('stok'),
            'sedang_dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'sudah_dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        return view('equipments.index', compact('equipments', 'kategoris', 'stats'));
    }

    public function create(): View
    {
        $kategoris = Kategori::query()->orderBy('nama_kategori')->get();

        return view('equipments.create', compact('kategoris'));
    }

    public function store(Request $request): RedirectResponse
    {
        equipments::create($this->validatedData($request));

        return redirect()->route('equipments.index')->with('success', 'equipments berhasil ditambahkan.');
    }

    public function show(equipments $equipments): View
    {
        $equipments->load('kategori');

        return view('equipments.show', compact('equipments'));
    }

    public function edit(equipments $equipments): View
    {
        $kategoris = Kategori::query()->orderBy('nama_kategori')->get();

        return view('equipments.edit', compact('equipments', 'kategoris'));
    }

    public function update(Request $request, equipments $equipments): RedirectResponse
    {
        $equipments->update($this->validatedData($request, $equipments));

        return redirect()->route('equipments.index')->with('success', 'equipments berhasil diperbarui.');
    }

    public function destroy(equipments $equipments): RedirectResponse
    {
        $equipments->delete();

        return redirect()->route('equipmentsv.index')->with('success', 'equipments berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?equipments $equipments = null): array
    {
        $isbnRule = Rule::unique('equipments', 'isbn');

        if ($equipments !== null) {
            $isbnRule->ignore($equipments);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'asset_code' => ['required', 'string', 'min:5', 'max:200'],
            'condition' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string', 'max:150'],
            'daily_rental_fee' => ['required', 'integer', 'between:1000,'.((int) date('Y') + 1)],
            'specification' => ['required', 'integer', 'exists:kategoris,id'],
        ]);
    }
}

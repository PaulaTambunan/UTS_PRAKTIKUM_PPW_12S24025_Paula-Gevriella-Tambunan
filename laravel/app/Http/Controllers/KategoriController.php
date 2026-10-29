<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        $kategoris = Kategori::query()->withCount('equipments')->orderBy('nama_kategori')->paginate(10);

        return view('kategori.index', compact('kategoris'));
    }

    public function create(): View
    {
        return view('kategori.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Kategori::create($this->validatedData($request));

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Kategori $kategori): View
    {
        $equipments = $kategori->equipments()->with('kategori')->latest()->paginate(10);

        return view('kategori.show', compact('kategori', 'equipments'));
    }

    public function edit(Kategori $kategori): View
    {
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $kategori->update($this->validatedData($request, $kategori));

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->equipments()->exists()) {
            return back()->with('error', 'Kategori yang masih memiliki buku tidak dapat dihapus.');
        }

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Kategori $kategori = null): array
    {
        $kodeRule = Rule::unique('kategoris', 'kode_kategori');
        $namaRule = Rule::unique('kategoris', 'nama_kategori');

        if ($kategori !== null) {
            $kodeRule->ignore($kategori);
            $namaRule->ignore($kategori);
        }

        return $request->validate([
            'kode_kategori' => [
                'required',
                'string',
                'max:10',
                $kodeRule,
            ],
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                $namaRule,
            ],
        ]);
    }
}

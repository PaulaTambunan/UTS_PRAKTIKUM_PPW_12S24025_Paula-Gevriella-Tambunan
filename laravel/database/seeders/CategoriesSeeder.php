<?php

namespace Database\Seeders;

use App\Models\perangkatLaboratorium; 
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = collect([
            ['kode_kategori' => 'FIK-001', 'nama_kategori' => 'Server & Cloud'],
            ['kode_kategori' => 'KOM-001', 'nama_kategori' => 'Internet of Things'],
            ['kode_kategori' => 'SAI-001', 'nama_kategori' => 'Multimedia & Drone'],
        ])->mapWithKeys(fn (array $kategori) => [
            $kategori['kode_kategori'] => Kategori::firstOrCreate(
                ['kode_kategori' => $kategori['kode_kategori']],
                ['nama_kategori' => $kategori['nama_kategori']],
            ),
        ]);

        $perangkatLaboratorium = [
            ['diajukan', 'Laptop', 'LAP-001'],
            ['disetujui', 'Drone Observasi', 'DRN-001'],
            ['ditolak', 'Kit Sensor IoT', 'KSI-001'],
            ['selesai', 'Server Mini', 'SRI-001'],
            ['dijaukan', 'Tablet Digital', 'TDI-001'],
            ['disetujui', 'Laptop', 'LAP-001'],
        ];

        foreach ($bukuContoh as [$isbn, $judul, $kodeKategori]) {   //ganti jangan buku
            Buku::updateOrCreate(
                ['isbn' => $isbn],
                [
                    'judul' => $judul,
                    'penulis' => fake('id_ID')->name(),
                    'penerbit' => fake('id_ID')->company(),
                    'tahun_terbit' => fake()->numberBetween(2018, (int) date('Y')),
                    'kategori_id' => $kategoris[$kodeKategori]->id,
                    'stok' => fake()->numberBetween(0, 50),
                    'sinopsis' => "Buku ini membahas {$judul} secara bertahap, dilengkapi konsep dasar dan contoh penerapan.",
                ],
            );
        }
    }
}

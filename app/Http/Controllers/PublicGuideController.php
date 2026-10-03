<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicGuideController extends Controller
{
    /**
     * Menampilkan Halaman Pusat Panduan & Tutorial Interaktif Modul Rapor Sekolah HadirYuk.
     * Rute ini dapat diakses secara publik oleh siapa saja (Guest-friendly tanpa login).
     */
    public function rapor(): View
    {
        return view('guide.rapor');
    }
}

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('vocational.internships.index', $class) }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Penempatan PKL
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Kelas {{ $class->nama_kelas }}</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Uji Kompetensi Keahlian (UKK): Kelas {{ $class->nama_kelas }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Rekapitulasi Nilai Teori, Praktik Kejuruan, Lembaga Sertifikasi (LSP/DUDI), & Nomor Sertifikat
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" form="ukkBatchForm"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span>Simpan Nilai UKK</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-[96rem] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Sub-Navigation Menu Tabs -->
            <div class="flex items-center gap-2 border-b border-brand-border pb-3 overflow-x-auto text-sm font-bold">
                <a href="{{ route('vocational.internships.index', $class) }}"
                    class="px-4 py-2 rounded-xl bg-white text-gray-600 hover:text-brand-primary hover:bg-gray-50 transition border border-brand-border flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">business_center</span>
                    <span>Praktik Kerja Lapangan (PKL)</span>
                </a>
                <a href="{{ route('vocational.ukk.index', $class) }}"
                    class="px-4 py-2 rounded-xl bg-brand-primary text-white shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">verified_user</span>
                    <span>Uji Kompetensi Keahlian (UKK)</span>
                </a>
            </div>

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <span class="material-symbols-outlined text-red-600 text-base">error</span>
                        <span>Terdapat kesalahan input:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Tabel Form Penilaian Massal UKK -->
            <form id="ukkBatchForm" method="POST" action="{{ route('vocational.ukk.batch-store', $class) }}">
                @csrf

                <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-brand-border flex items-center justify-between bg-gray-50/70">
                        <div>
                            <h3 class="font-bold text-sm text-brand-text-main">Lembar Penilaian UKK Siswa</h3>
                            <p class="text-[11px] text-gray-500">Bobot Akhir: 70% Praktik + 30% Teori (atau 100% Praktik jika tanpa ujian teori).</p>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 bg-white border rounded-lg text-gray-600">
                            {{ $students->count() }} Siswa
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 text-gray-700 font-extrabold uppercase border-b border-gray-200">
                                <tr>
                                    <th class="p-3 text-center w-8">No</th>
                                    <th class="p-3 min-w-[180px]">Nama Siswa</th>
                                    <th class="p-3 min-w-[200px]">Skema Sertifikasi / Klaster</th>
                                    <th class="p-3 min-w-[150px]">Asesor Penguji</th>
                                    <th class="p-3 min-w-[150px]">Lembaga (LSP / DUDI)</th>
                                    <th class="p-3 text-center w-24">Teori (0-100)</th>
                                    <th class="p-3 text-center w-24">Praktik (0-100)</th>
                                    <th class="p-3 text-center w-28">Nilai Akhir & Status</th>
                                    <th class="p-3 min-w-[150px]">No. Sertifikat</th>
                                    <th class="p-3 text-center w-14">Cetak</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($students as $idx => $student)
                                    @php
                                        $ukk = $assessments->get($student->id);
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="p-3 text-center text-gray-500 font-semibold">{{ $idx + 1 }}</td>
                                        <td class="p-3 font-bold text-gray-900">
                                            <div class="text-xs">{{ $student->name }}</div>
                                            <div class="text-[10px] font-mono text-gray-400">{{ $student->nisn ?? '-' }}</div>
                                        </td>
                                        <td class="p-2">
                                            <input type="text" name="assessments[{{ $student->id }}][scheme_name]" required
                                                value="{{ old("assessments.{$student->id}.scheme_name", $ukk?->scheme_name ?? 'Klaster Kompetensi Kejuruan') }}"
                                                placeholder="Nama Skema..."
                                                class="w-full text-xs rounded-lg border-gray-300">
                                        </td>
                                        <td class="p-2">
                                            <input type="text" name="assessments[{{ $student->id }}][assessor_name]" required
                                                value="{{ old("assessments.{$student->id}.assessor_name", $ukk?->assessor_name ?? 'Tim Asesor LSP') }}"
                                                placeholder="Nama Asesor..."
                                                class="w-full text-xs rounded-lg border-gray-300">
                                        </td>
                                        <td class="p-2">
                                            <input type="text" name="assessments[{{ $student->id }}][institution_name]" required
                                                value="{{ old("assessments.{$student->id}.institution_name", $ukk?->institution_name ?? 'LSP-P1 SMK') }}"
                                                placeholder="Lembaga LSP/DUDI..."
                                                class="w-full text-xs rounded-lg border-gray-300">
                                        </td>
                                        <td class="p-2 text-center">
                                            <input type="number" step="0.01" min="0" max="100" name="assessments[{{ $student->id }}][theory_score]"
                                                value="{{ old("assessments.{$student->id}.theory_score", $ukk?->theory_score) }}"
                                                placeholder="Opsional"
                                                class="w-20 text-center text-xs font-bold rounded-lg border-gray-300 mx-auto">
                                        </td>
                                        <td class="p-2 text-center">
                                            <input type="number" step="0.01" min="0" max="100" name="assessments[{{ $student->id }}][practice_score]" required
                                                value="{{ old("assessments.{$student->id}.practice_score", $ukk?->practice_score ?? 85) }}"
                                                placeholder="0-100"
                                                class="w-20 text-center text-xs font-bold rounded-lg border-gray-300 mx-auto">
                                        </td>
                                        <td class="p-3 text-center whitespace-nowrap">
                                            @if($ukk)
                                                <div class="font-black text-xs text-gray-900">{{ number_format($ukk->final_score, 1) }}</div>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold {{ $ukk->final_score >= 85 ? 'bg-emerald-100 text-emerald-800' : ($ukk->final_score >= 70 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ $ukk->predicate }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 italic text-[11px]">Belum Ada</span>
                                            @endif
                                        </td>
                                        <td class="p-2">
                                            <input type="text" name="assessments[{{ $student->id }}][certificate_number]"
                                                value="{{ old("assessments.{$student->id}.certificate_number", $ukk?->certificate_number) }}"
                                                placeholder="No. Sertifikat (opsional)"
                                                class="w-full text-xs rounded-lg border-gray-300 font-mono">
                                        </td>
                                        <td class="p-3 text-center whitespace-nowrap">
                                            @if($ukk)
                                                <a href="{{ route('vocational.ukk.print', [$class, $student]) }}" target="_blank"
                                                    title="Cetak Transkrip UKK A4"
                                                    class="p-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition inline-block">
                                                    <span class="material-symbols-outlined text-[16px]">print</span>
                                                </a>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50/70 border-t border-brand-border flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            Pastikan menekan tombol <strong>Simpan Nilai UKK</strong> setelah mengisi nilai ujian kejuruan.
                        </div>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-md transition">
                            <span class="material-symbols-outlined text-[16px]">save</span>
                            <span>Simpan Nilai UKK</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>

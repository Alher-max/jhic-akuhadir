<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('p5.projects.index', $project->schoolClass) }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Projek Kelas {{ $project->schoolClass->nama_kelas }}
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Detail Projek</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    {{ $project->title }}
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Tema: <span class="font-bold text-gray-800">{{ $project->theme }}</span> &bull; 
                    Fasilitator: <span class="font-bold text-gray-800">{{ $project->coordinator?->name ?? 'Belum Ditentukan' }}</span> &bull; 
                    Kelas: <span class="font-bold text-gray-800">{{ $project->schoolClass->nama_kelas }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('p5.reports.print-batch', $project) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>Cetak Massal (A4)</span>
                </a>
                <a href="{{ route('p5.assessments.matrix', $project) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <span class="material-symbols-outlined text-[16px]">draw</span>
                    <span>Buka Matriks Penilaian</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if (session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            <!-- Deskripsi & Status Progress Card -->
            <div class="bg-white rounded-2xl border border-brand-border p-6 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <span class="px-3 py-1 text-xs font-extrabold rounded-lg bg-indigo-50 text-indigo-700">
                        Tema: {{ $project->theme }}
                    </span>
                    <div class="text-xs text-gray-500 font-semibold">
                        Tahun Ajaran: {{ $project->academicYear?->formatted_period }} (Semester {{ $project->academicYear?->semester }})
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-sm text-gray-700 mb-1">Deskripsi Projek:</h3>
                    <p class="text-sm text-gray-600 leading-relaxed bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                        {{ $project->description }}
                    </p>
                </div>

                <div class="pt-2">
                    <div class="flex justify-between items-center text-xs font-bold mb-1.5">
                        <span class="text-gray-700">Progres Penilaian Siswa:</span>
                        <span class="text-brand-primary">{{ $progressPercent }}% Selesai ({{ $project->assessments->count() }} Asesmen Terisi)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-brand-primary h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Target Dimensi & Sub-elemen -->
            <div class="bg-white rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                <div class="p-5 border-b border-brand-border flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base text-brand-text-main">Target Dimensi & Sub-elemen Projek</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Rubrik capaian pembelajaran yang dinilai untuk setiap siswa</p>
                    </div>
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg">
                        Total {{ $project->targets->count() }} Target
                    </span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($project->targets as $target)
                        <div class="p-4 hover:bg-gray-50/60 transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded {{ $target->target_type === 'rahmatan_lil_alamin' ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">
                                        {{ $target->target_type === 'rahmatan_lil_alamin' ? 'P5RA Kemenag' : 'Pancasila' }}
                                    </span>
                                    <span class="text-xs font-bold text-gray-900">{{ $target->dimension }}</span>
                                    @if($target->element)
                                        <span class="text-xs text-gray-400">&bull;</span>
                                        <span class="text-xs text-gray-600">{{ $target->element }}</span>
                                    @endif
                                </div>
                                <h4 class="text-sm font-black text-brand-text-main">
                                    {{ $target->sub_element }}
                                </h4>
                                @if($target->target_description)
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        {{ $target->target_description }}
                                    </p>
                                @endif
                            </div>

                            <div class="text-right whitespace-nowrap self-end md:self-center">
                                <span class="text-xs font-semibold text-gray-500">
                                    Dinilai: {{ $target->assessments->count() }}/{{ $students->count() }} Siswa
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Daftar Siswa & Akses Cetak Rapor Mandiri -->
            <div class="bg-white rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                <div class="p-5 border-b border-brand-border flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base text-brand-text-main">Daftar Siswa Kelas {{ $project->schoolClass->nama_kelas }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Cetak lembar rapor projek P5 individual per siswa</p>
                    </div>
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg">
                        {{ $students->count() }} Siswa
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/70 text-xs uppercase font-extrabold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">NISN / NIS</th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3 text-center">Target Terisi</th>
                                <th class="px-4 py-3">Catatan Fasilitator</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($students as $idx => $student)
                                @php
                                    $studentAssessCount = $project->assessments->where('student_id', $student->id)->count();
                                    $note = $project->studentNotes->where('student_id', $student->id)->first();
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-3 text-xs font-mono text-gray-600">
                                        {{ $student->nisn ?? $student->nis ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        {{ $student->name }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold {{ $studentAssessCount === $project->targets->count() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $studentAssessCount }} / {{ $project->targets->count() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600 max-w-xs truncate">
                                        {{ $note?->process_notes ? Str::limit($note->process_notes, 60) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <a href="{{ route('p5.reports.print-single', [$project, $student]) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition">
                                            <span class="material-symbols-outlined text-[15px]">print</span>
                                            <span>Cetak Rapor A4</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Danger Zone: Hapus Projek -->
            <div class="p-6 rounded-2xl border border-red-200 bg-red-50/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h4 class="font-bold text-sm text-red-900">Hapus Projek P5 Ini</h4>
                    <p class="text-xs text-red-700 mt-0.5">
                        Menghapus projek ini akan menghapus seluruh target sub-elemen, capaian nilai siswa, dan catatan fasilitator yang terkait secara permanen.
                    </p>
                </div>
                <form method="POST" action="{{ route('p5.projects.destroy', $project) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus projek P5 ini beserta seluruh data nilainya? Tindakan ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition shadow-sm whitespace-nowrap flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                        <span>Hapus Projek</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>

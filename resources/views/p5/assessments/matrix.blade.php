<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-gray-500 mb-1">
                    <a href="{{ route('p5.projects.show', $project) }}" class="hover:text-brand-primary transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        {{ $project->title }}
                    </a>
                    <span>/</span>
                    <span class="text-brand-primary">Matriks Penilaian</span>
                </div>
                <h2 class="font-extrabold text-2xl text-brand-text-main leading-tight">
                    Matriks Penilaian Capaian Projek P5 & P5RA
                </h2>
                <p class="text-sm text-brand-text-muted mt-0.5">
                    Kelas {{ $project->schoolClass->nama_kelas }} &bull; Tema: {{ $project->theme }} &bull; {{ $students->count() }} Siswa &bull; {{ $targets->count() }} Sub-elemen Target
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('p5.reports.print-batch', $project) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white text-gray-700 text-xs font-bold rounded-xl border border-gray-300 hover:bg-gray-50 shadow-sm transition">
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>Cetak Rapor A4</span>
                </a>
                <button type="submit" form="assessmentMatrixForm"
                    class="inline-flex items-center gap-1.5 px-5 py-2 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span>Simpan Penilaian</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-brand-bg min-h-screen">
        <div class="max-w-[96rem] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

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
                        <span>Terdapat kesalahan pengisian:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Legend & Panduan Rubrik Predikat -->
            <div class="bg-white p-4 rounded-2xl border border-brand-border shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs font-bold text-gray-700">
                    <span class="material-symbols-outlined text-brand-primary text-base">help</span>
                    <span>Panduan Predikat Rubrik P5:</span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded font-black text-[11px] bg-red-100 text-red-800 border border-red-200">MB</span>
                        <span class="text-gray-600 font-semibold">Mulai Berkembang</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded font-black text-[11px] bg-amber-100 text-amber-800 border border-amber-200">SB</span>
                        <span class="text-gray-600 font-semibold">Sedang Berkembang</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded font-black text-[11px] bg-blue-100 text-blue-800 border border-blue-200">BSH</span>
                        <span class="text-gray-600 font-semibold">Berkembang Sesuai Harapan</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded font-black text-[11px] bg-emerald-100 text-emerald-800 border border-emerald-200">SAB</span>
                        <span class="text-gray-600 font-semibold">Sangat Berkembang</span>
                    </div>
                </div>
            </div>

            <!-- Matriks Penilaian Table -->
            <form id="assessmentMatrixForm" method="POST" action="{{ route('p5.assessments.batch-store', $project) }}">
                @csrf

                <div class="bg-brand-surface rounded-2xl border border-brand-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-gray-100 text-gray-700 border-b border-gray-300">
                                <tr>
                                    <th rowspan="2" class="p-3 text-center border-r border-gray-300 w-10 font-black">No</th>
                                    <th rowspan="2" class="p-3 border-r border-gray-300 min-w-[200px] font-black sticky left-0 bg-gray-100 z-10">
                                        Nama Siswa
                                    </th>
                                    @foreach($targets as $idx => $target)
                                        <th class="p-2.5 text-center border-r border-gray-300 min-w-[170px] bg-gray-50">
                                            <div class="text-[10px] font-extrabold uppercase tracking-wider {{ $target->target_type === 'rahmatan_lil_alamin' ? 'text-emerald-700' : 'text-indigo-700' }}">
                                                Target {{ $idx + 1 }}
                                            </div>
                                            <div class="font-black text-gray-900 line-clamp-1" title="{{ $target->sub_element }}">
                                                {{ $target->sub_element }}
                                            </div>
                                            <div class="text-[10px] font-normal text-gray-500 truncate" title="{{ $target->dimension }}">
                                                {{ $target->dimension }}
                                            </div>
                                        </th>
                                    @endforeach
                                    <th rowspan="2" class="p-3 border-l border-gray-300 min-w-[280px] font-black bg-gray-50">
                                        Catatan Proses Fasilitator
                                    </th>
                                </tr>
                                <tr>
                                    @foreach($targets as $target)
                                        <th class="p-1 text-center border-r border-gray-300 bg-gray-100 text-[10px] text-gray-500 font-semibold">
                                            MB / SB / BSH / SAB
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($students as $index => $student)
                                    @php
                                        $note = $studentNotesMap->get($student->id);
                                    @endphp
                                    <tr class="hover:bg-indigo-50/20 transition">
                                        <td class="p-3 text-center text-gray-500 font-semibold border-r border-gray-200">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="p-3 border-r border-gray-200 font-bold text-gray-900 sticky left-0 bg-white z-10 shadow-sm">
                                            <div class="text-xs text-brand-text-main">{{ $student->name }}</div>
                                            <div class="text-[10px] font-mono text-gray-400">NISN: {{ $student->nisn ?? '-' }}</div>
                                        </td>

                                        <!-- Penilaian per Target -->
                                        @foreach($targets as $target)
                                            @php
                                                $currentAssessment = $assessmentsMap[$student->id][$target->id] ?? null;
                                                $selected = old("assessments.{$student->id}.{$target->id}", $currentAssessment?->predicate);
                                            @endphp
                                            <td class="p-2 text-center border-r border-gray-200">
                                                <div class="inline-flex items-center gap-1 bg-gray-50 p-1 rounded-lg border border-gray-200">
                                                    @foreach(['MB', 'SB', 'BSH', 'SAB'] as $pCode)
                                                        @php
                                                            $isActive = ($selected === $pCode);
                                                            $activeClasses = match($pCode) {
                                                                'MB' => 'bg-red-600 text-white font-extrabold',
                                                                'SB' => 'bg-amber-500 text-white font-extrabold',
                                                                'BSH' => 'bg-blue-600 text-white font-extrabold',
                                                                'SAB' => 'bg-emerald-600 text-white font-extrabold',
                                                            };
                                                        @endphp
                                                        <label class="cursor-pointer">
                                                            <input type="radio"
                                                                name="assessments[{{ $student->id }}][{{ $target->id }}]"
                                                                value="{{ $pCode }}"
                                                                class="sr-only peer"
                                                                {{ $isActive ? 'checked' : '' }}>
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-gray-600 peer-checked:{{ $activeClasses }} hover:bg-gray-200 transition">
                                                                {{ $pCode }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </td>
                                        @endforeach

                                        <!-- Catatan Proses Fasilitator -->
                                        <td class="p-2 border-l border-gray-200">
                                            <textarea name="notes[{{ $student->id }}]" rows="2"
                                                placeholder="Catatan keaktifan, kemandirian, dan peran siswa dalam projek..."
                                                class="w-full text-xs rounded-lg border-gray-300 focus:border-brand-primary focus:ring focus:ring-brand-primary/20">{{ old("notes.{$student->id}", $note?->process_notes) }}</textarea>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $targets->count() + 3 }}" class="p-8 text-center text-gray-500">
                                            Tidak ada siswa yang terdaftar pada rombel ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Action Bar -->
                    <div class="p-4 bg-gray-50/70 border-t border-brand-border flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            Pastikan menekan tombol <strong>Simpan Penilaian</strong> setelah melakukan pengisian atau perubahan predikat.
                        </div>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-brand-primary hover:bg-brand-primary/90 text-white text-xs font-bold rounded-xl shadow-md transition">
                            <span class="material-symbols-outlined text-[16px]">save</span>
                            <span>Simpan Penilaian</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>

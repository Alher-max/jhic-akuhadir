@php
    $studentAssessments = isset($assessments) ? $assessments : ($assessmentsByStudent[$student->id] ?? collect());
    $studentProcessNote = $studentNote ?? (isset($studentNotes) ? ($studentNotes->get($student->id) ?? null) : null);
    $activeYear = $project->academicYear;
    $class = $project->schoolClass;
@endphp

<div class="page-sheet bg-white p-8 max-w-[210mm] w-full mx-auto shadow-lg rounded-none border border-gray-100 flex flex-col justify-between"
     style="min-height: 297mm; font-size: 11px; line-height: 1.4;">

    <div>
        <!-- Kop Satuan Pendidikan Resmi -->
        <div class="border-b-2 border-black pb-3 mb-4 text-center">
            <h1 class="text-base font-black tracking-wide uppercase text-gray-900 leading-tight">
                {{ $tenant->name ?? 'PEMERINTAH DAERAH PROVINSI' }}
            </h1>
            <h2 class="text-sm font-extrabold tracking-wide uppercase text-gray-800 leading-tight">
                LAPORAN HASIL PROJEK PENGUATAN PROFIL PELAJAR
            </h2>
            <p class="text-[10px] text-gray-600 mt-0.5">
                {{ $tenant->address ?? 'Jl. Pendidikan Nasional' }} &bull; NPSN: {{ $tenant->npsn ?? '-' }}
            </p>
        </div>

        <!-- Tabel Identitas Siswa -->
        <div class="grid grid-cols-2 gap-x-6 gap-y-1 mb-4 text-[11px] pb-3 border-b border-gray-300">
            <div class="space-y-0.5">
                <div class="flex">
                    <span class="w-28 text-gray-600">Nama Siswa</span>
                    <span class="font-extrabold text-gray-900">: {{ $student->name }}</span>
                </div>
                <div class="flex">
                    <span class="w-28 text-gray-600">NISN / NIS</span>
                    <span class="font-bold text-gray-800">: {{ $student->nisn ?? '-' }} / {{ $student->nis ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-28 text-gray-600">Kelas / Fase</span>
                    <span class="font-bold text-gray-800">: {{ $class->nama_kelas }} / Fase {{ $class->fase ?? 'E' }}</span>
                </div>
            </div>
            <div class="space-y-0.5">
                <div class="flex">
                    <span class="w-28 text-gray-600">Nama Sekolah</span>
                    <span class="font-bold text-gray-800">: {{ $tenant->name ?? '-' }}</span>
                </div>
                <div class="flex">
                    <span class="w-28 text-gray-600">Semester</span>
                    <span class="font-bold text-gray-800">: {{ $activeYear?->semester === '1' ? '1 (Ganjil)' : '2 (Genap)' }}</span>
                </div>
                <div class="flex">
                    <span class="w-28 text-gray-600">Tahun Ajaran</span>
                    <span class="font-bold text-gray-800">: {{ $activeYear?->formatted_period ?? $activeYear?->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Judul & Deskripsi Projek -->
        <div class="mb-4 bg-gray-50/80 p-3 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 bg-indigo-100 text-indigo-900 rounded">
                    Tema: {{ $project->theme }}
                </span>
                <span class="text-[10px] text-gray-500 font-semibold">
                    Fasilitator: {{ $project->coordinator?->name ?? 'Tim Fasilitator' }}
                </span>
            </div>
            <h3 class="text-xs font-black text-gray-900 mb-1 leading-snug">
                Projek: {{ $project->title }}
            </h3>
            <p class="text-[10px] text-gray-600 text-justify leading-relaxed">
                {{ $project->description }}
            </p>
        </div>

        <!-- Tabel Rubrik Capaian Perkembangan Siswa -->
        <div class="mb-4">
            <h4 class="text-[11px] font-bold text-gray-800 mb-1.5 uppercase tracking-wide">
                A. Capaian Perkembangan Karakter Siswa
            </h4>
            <table class="w-full text-left border-collapse border border-gray-400 text-[10px]">
                <thead>
                    <tr class="bg-gray-100 text-gray-800 font-bold border-b border-gray-400 text-center">
                        <th class="p-1.5 border border-gray-400 w-7">No</th>
                        <th class="p-1.5 border border-gray-400 w-36 text-left">Dimensi & Elemen</th>
                        <th class="p-1.5 border border-gray-400 text-left">Sub-elemen Target Capaian</th>
                        <th class="p-1.5 border border-gray-400 w-9 font-extrabold" title="Mulai Berkembang">MB</th>
                        <th class="p-1.5 border border-gray-400 w-9 font-extrabold" title="Sedang Berkembang">SB</th>
                        <th class="p-1.5 border border-gray-400 w-9 font-extrabold" title="Berkembang Sesuai Harapan">BSH</th>
                        <th class="p-1.5 border border-gray-400 w-9 font-extrabold" title="Sangat Berkembang">SAB</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->targets as $targetIndex => $target)
                        @php
                            $predicate = null;
                            if (is_array($studentAssessments) || $studentAssessments instanceof \ArrayAccess) {
                                $predicate = $studentAssessments[$target->id]?->predicate ?? null;
                            }
                        @endphp
                        <tr class="border-b border-gray-300">
                            <td class="p-1.5 border border-gray-300 text-center font-semibold text-gray-600">
                                {{ $targetIndex + 1 }}
                            </td>
                            <td class="p-1.5 border border-gray-300">
                                <div class="font-bold text-gray-900 leading-tight">{{ $target->dimension }}</div>
                                @if($target->element)
                                    <div class="text-[9px] text-gray-500 mt-0.5 leading-tight">{{ $target->element }}</div>
                                @endif
                            </td>
                            <td class="p-1.5 border border-gray-300">
                                <div class="font-bold text-gray-800 leading-tight">{{ $target->sub_element }}</div>
                                @if($target->target_description)
                                    <div class="text-[9px] text-gray-500 mt-0.5 leading-tight">{{ $target->target_description }}</div>
                                @endif
                            </td>
                            <td class="p-1.5 border border-gray-300 text-center font-black text-xs text-red-700">
                                {{ $predicate === 'MB' ? '✓' : '' }}
                            </td>
                            <td class="p-1.5 border border-gray-300 text-center font-black text-xs text-amber-700">
                                {{ $predicate === 'SB' ? '✓' : '' }}
                            </td>
                            <td class="p-1.5 border border-gray-300 text-center font-black text-xs text-blue-700">
                                {{ $predicate === 'BSH' ? '✓' : '' }}
                            </td>
                            <td class="p-1.5 border border-gray-300 text-center font-black text-xs text-emerald-700">
                                {{ $predicate === 'SAB' ? '✓' : '' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Legend Singkat -->
            <div class="flex items-center gap-4 text-[9px] text-gray-600 mt-1.5 px-1">
                <span><strong>MB</strong>: Mulai Berkembang</span>
                <span><strong>SB</strong>: Sedang Berkembang</span>
                <span><strong>BSH</strong>: Berkembang Sesuai Harapan</span>
                <span><strong>SAB</strong>: Sangat Berkembang</span>
            </div>
        </div>

        <!-- Catatan Proses Fasilitator -->
        <div class="mb-4">
            <h4 class="text-[11px] font-bold text-gray-800 mb-1.5 uppercase tracking-wide">
                B. Catatan Perkembangan Proses Siswa
            </h4>
            <div class="border border-gray-400 p-2.5 rounded text-[10px] text-gray-800 min-h-[55px] bg-white leading-relaxed">
                @if($studentProcessNote && !empty($studentProcessNote->process_notes))
                    {{ $studentProcessNote->process_notes }}
                @else
                    <span class="text-gray-400 italic">Siswa berpartisipasi aktif dalam kegiatan projek dan menunjukkan perkembangan sikap yang positif sesuai target profil pelajar.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Tanda Tangan & Pengesahan Dokumen -->
    <div class="mt-4 pt-2 text-[10px] text-gray-900">
        <div class="flex justify-between items-start mb-12">
            <div class="text-center w-52">
                <p>Mengetahui,</p>
                <p class="font-bold">Orang Tua / Wali Siswa</p>
                <div class="h-14"></div>
                <p class="border-b border-black w-40 mx-auto"></p>
            </div>

            <div class="text-center w-52">
                <p>{{ $tenant->city ?? 'Jakarta' }}, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold">Fasilitator / Wali Kelas</p>
                <div class="h-14"></div>
                <p class="font-black text-gray-900 underline">{{ $project->coordinator?->name ?? $class->waliKelas?->name ?? 'Guru Fasilitator' }}</p>
                <p class="text-[9px] text-gray-600">NIP: {{ $project->coordinator?->nip ?? $class->waliKelas?->nip ?? '-' }}</p>
            </div>
        </div>

        <div class="text-center w-64 mx-auto">
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala Satuan Pendidikan</p>
            <div class="h-14"></div>
            <p class="font-black text-gray-900 underline">{{ $headmaster?->name ?? 'Kepala Sekolah' }}</p>
            <p class="text-[9px] text-gray-600">NIP: {{ $headmaster?->nip ?? '-' }}</p>
        </div>
    </div>

</div>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ajukan Izin / Ketidakhadiran') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        startDate: '{{ old('start_date', date('Y-m-d')) }}',
        endDate: '{{ old('end_date', date('Y-m-d')) }}',
        selectedPreset: 1,
        setPreset(days) {
            this.selectedPreset = days;
            const today = new Date();
            const startStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
            const end = new Date(today);
            end.setDate(today.getDate() + (days - 1));
            const endStr = end.getFullYear() + '-' + String(end.getMonth() + 1).padStart(2, '0') + '-' + String(end.getDate()).padStart(2, '0');
            this.startDate = startStr;
            this.endDate = endStr;
        }
    }">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <form method="POST" action="{{ route('member.leaves.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Tipe Izin -->
                        <div class="mb-5">
                            <x-input-label for="type" value="Tipe Izin" class="font-semibold" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm" required>
                                <option value="" disabled {{ old('type') ? '' : 'selected' }}>-- Pilih Tipe Izin --</option>
                                <option value="sick" {{ old('type') == 'sick' ? 'selected' : '' }}>Sakit</option>
                                <option value="permission" {{ old('type') == 'permission' ? 'selected' : '' }}>Izin Keperluan Pribadi</option>
                                <option value="duty_trip" {{ old('type') == 'duty_trip' ? 'selected' : '' }}>Tugas Luar Kota / Dinas</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Quick Presets -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Preset Durasi Cepat</label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="setPreset(1)"
                                        :class="selectedPreset === 1 ? 'bg-red-600 text-white border-red-600 shadow-sm font-medium' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200 font-medium'"
                                        class="px-3.5 py-1.5 rounded-lg border text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-calendar-day" :class="selectedPreset === 1 ? 'text-white' : 'text-slate-500'"></i> Hari Ini (1 Hari)
                                </button>
                                <button type="button" @click="setPreset(2)"
                                        :class="selectedPreset === 2 ? 'bg-red-600 text-white border-red-600 shadow-sm font-medium' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200 font-medium'"
                                        class="px-3.5 py-1.5 rounded-lg border text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-calendar-week" :class="selectedPreset === 2 ? 'text-white' : 'text-slate-500'"></i> 2 Hari
                                </button>
                                <button type="button" @click="setPreset(3)"
                                        :class="selectedPreset === 3 ? 'bg-red-600 text-white border-red-600 shadow-sm font-medium' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200 font-medium'"
                                        class="px-3.5 py-1.5 rounded-lg border text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-calendar-days" :class="selectedPreset === 3 ? 'text-white' : 'text-slate-500'"></i> 3 Hari
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                            <!-- Start Date -->
                            <div>
                                <x-input-label for="start_date" value="Tanggal Mulai" class="font-semibold" />
                                <input id="start_date" x-model="startDate" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm" type="date" name="start_date" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>

                            <!-- End Date -->
                            <div>
                                <x-input-label for="end_date" value="Tanggal Selesai" class="font-semibold" />
                                <input id="end_date" x-model="endDate" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm" type="date" name="end_date" required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mb-5">
                            <x-input-label for="reason" value="Alasan Detail" class="font-semibold" />
                            <textarea id="reason" name="reason" rows="3" class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm" required placeholder="Jelaskan alasan ketidakhadiran secara lengkap...">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <!-- Attachment -->
                        <div class="mb-6">
                            <x-input-label for="attachment" value="Lampiran (Surat Dokter / Bukti)" class="font-semibold" />
                            <input id="attachment" type="file" name="attachment" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer" accept=".jpg,.jpeg,.png,.pdf" />
                            <p class="text-xs text-gray-400 mt-1">Opsional, format: JPG, PNG, PDF (Maks 2MB)</p>
                            <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('member.dashboard') }}" class="text-gray-600 hover:text-gray-900 mr-4 font-medium text-sm">Batal</a>
                            <x-primary-button class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800">
                                Kirim Pengajuan
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

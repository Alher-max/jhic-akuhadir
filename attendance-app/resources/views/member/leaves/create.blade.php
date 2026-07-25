<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ajukan Izin / Ketidakhadiran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('member.leaves.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Tipe Izin -->
                        <div class="mb-4">
                            <x-input-label for="type" value="Tipe Izin" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled selected>-- Pilih Tipe --</option>
                                <option value="sick" {{ old('type') == 'sick' ? 'selected' : '' }}>Sakit</option>
                                <option value="permission" {{ old('type') == 'permission' ? 'selected' : '' }}>Izin Keperluan Pribadi</option>
                                <option value="duty_trip" {{ old('type') == 'duty_trip' ? 'selected' : '' }}>Tugas Luar Kota / Dinas</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Start Date -->
                            <div>
                                <x-input-label for="start_date" value="Tanggal Mulai" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" min="{{ date('Y-m-d') }}" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>

                            <!-- End Date -->
                            <div>
                                <x-input-label for="end_date" value="Tanggal Selesai" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" min="{{ date('Y-m-d') }}" required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="mb-4">
                            <x-input-label for="reason" value="Alasan Detail" />
                            <textarea id="reason" name="reason" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required placeholder="Jelaskan alasan secara lengkap...">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <!-- Attachment -->
                        <div class="mb-6">
                            <x-input-label for="attachment" value="Lampiran (Surat Dokter / Bukti)" />
                            <input id="attachment" type="file" name="attachment" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept=".jpg,.jpeg,.png,.pdf" />
                            <p class="text-xs text-gray-400 mt-1">Opsional, format: JPG, PNG, PDF (Maks 2MB)</p>
                            <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('member.dashboard') }}" class="text-gray-600 hover:text-gray-900 mr-4 font-medium text-sm">Batal</a>
                            <x-primary-button>
                                Kirim Pengajuan
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

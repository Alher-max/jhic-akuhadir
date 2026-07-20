<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Jadwal Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('schedules.store') }}" x-data="{ type: 'routine' }">
                        @csrf

                        <!-- Nama Jadwal -->
                        <div class="mb-4">
                            <x-input-label for="name" value="Nama Jadwal" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="Contoh: Shift Pagi, Kelas Reguler" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Tipe Jadwal -->
                            <div>
                                <x-input-label for="type" value="Tipe Jadwal" />
                                <select id="type" name="type" x-model="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="routine">Rutin (Mingguan)</option>
                                    <option value="non_routine">Non-Rutin (Tanggal Spesifik)</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <!-- Opsi Hari / Tanggal -->
                            <div>
                                <template x-if="type === 'routine'">
                                    <div>
                                        <x-input-label for="day_of_week" value="Pilih Hari" />
                                        <select id="day_of_week" name="day_of_week" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="1">Senin</option>
                                            <option value="2">Selasa</option>
                                            <option value="3">Rabu</option>
                                            <option value="4">Kamis</option>
                                            <option value="5">Jumat</option>
                                            <option value="6">Sabtu</option>
                                            <option value="7">Minggu</option>
                                        </select>
                                    </div>
                                </template>
                                <template x-if="type === 'non_routine'">
                                    <div>
                                        <x-input-label for="specific_date" value="Pilih Tanggal" />
                                        <x-text-input id="specific_date" class="block mt-1 w-full" type="date" name="specific_date" />
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <!-- Jam Masuk -->
                            <div>
                                <x-input-label for="start_time" value="Jam Masuk" />
                                <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" required />
                            </div>
                            <!-- Jam Pulang -->
                            <div>
                                <x-input-label for="end_time" value="Jam Pulang" />
                                <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" required />
                            </div>
                            <!-- Toleransi -->
                            <div>
                                <x-input-label for="grace_period_minutes" value="Toleransi (Menit)" />
                                <x-text-input id="grace_period_minutes" class="block mt-1 w-full" type="number" min="0" value="0" name="grace_period_minutes" required />
                            </div>
                        </div>

                        <hr class="mb-6 border-gray-200">

                        <h3 class="text-lg font-bold text-gray-900 mb-4">Pilih Anggota (Wajib Hadir)</h3>
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md p-4 space-y-2 mb-6 bg-gray-50">
                            @forelse($users as $user)
                                <label class="flex items-center space-x-3 p-2 bg-white border border-gray-100 rounded shadow-sm hover:bg-gray-50 cursor-pointer transition">
                                    <input type="checkbox" name="users[]" value="{{ $user->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <div>
                                        <span class="block font-medium text-gray-900">{{ $user->name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $user->email }}</span>
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500 italic">Belum ada anggota yang terdaftar di institusi ini.</p>
                            @endforelse
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('schedules.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <x-primary-button>
                                Simpan Jadwal
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

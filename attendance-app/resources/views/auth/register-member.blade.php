<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Registrasi Anggota</h2>
        <p class="text-sm text-gray-600 mt-1">Bergabung dengan institusi, sekolah, atau perusahaan Anda.</p>
    </div>

    <!-- Alpine Component for Tenant Selection -->
    <div x-data="tenantSelection()" class="mb-6">
        
        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 mb-4">
            <button type="button" 
                    @click="setMode('code')" 
                    :class="mode === 'code' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="flex-1 whitespace-nowrap border-b-2 py-3 px-1 text-center text-sm font-medium transition-colors">
                Gunakan Kode
            </button>
            <button type="button" 
                    @click="setMode('name')" 
                    :class="mode === 'name' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="flex-1 whitespace-nowrap border-b-2 py-3 px-1 text-center text-sm font-medium transition-colors">
                Cari Nama
            </button>
        </div>

        <!-- Tenant Selection Area (Only show if not selected) -->
        <div x-show="!selectedTenant" x-transition>
            
            <!-- Code Mode -->
            <div x-show="mode === 'code'">
                <x-input-label for="tenant_code" value="Masukkan 6-Digit Kode Institusi" />
                <div class="flex mt-1 gap-2">
                    <x-text-input id="tenant_code" x-model="code" class="block w-full uppercase" type="text" maxlength="6" placeholder="Contoh: A1B2C3" @keyup.enter="checkCode" />
                    <button type="button" @click="checkCode" :disabled="isLoading || code.length !== 6" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50">
                        <span x-show="!isLoading">Cek</span>
                        <svg x-show="isLoading" style="display:none;" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </div>
                <p x-show="errorMsg" x-text="errorMsg" class="mt-2 text-sm text-red-600" style="display:none;"></p>
            </div>

            <!-- Name Mode (Live Search) -->
            <div x-show="mode === 'name'" style="display:none;" class="relative">
                <x-input-label for="tenant_name_search" value="Cari Nama Institusi" />
                <div class="relative mt-1">
                    <x-text-input id="tenant_name_search" x-model="query" @input.debounce.500ms="searchTenants" class="block w-full" type="text" placeholder="Ketik minimal 3 huruf..." autocomplete="off" />
                    <div x-show="isLoading" style="display:none;" class="absolute right-3 top-3">
                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
                
                <!-- Dropdown Results -->
                <div x-show="results.length > 0" @click.away="results = []" style="display:none;" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 max-h-60 overflow-y-auto">
                    <template x-for="tenant in results" :key="tenant.id">
                        <button type="button" @click="selectTenant(tenant)" class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b border-gray-100 last:border-0 transition-colors">
                            <div class="font-medium text-gray-900" x-text="tenant.name"></div>
                            <div class="text-xs text-gray-500 mt-0.5">Kode: <span x-text="tenant.code"></span></div>
                        </button>
                    </template>
                </div>
                <p x-show="errorMsg" x-text="errorMsg" class="mt-2 text-sm text-red-600" style="display:none;"></p>
            </div>
        </div>

        <!-- Selected Tenant Display -->
        <div x-show="selectedTenant" style="display:none;" x-transition class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">Institusi Terpilih</p>
                <p class="text-lg font-bold text-gray-900" x-text="selectedTenant?.name"></p>
            </div>
            <button type="button" @click="resetSelection" class="text-sm text-indigo-600 hover:text-indigo-800 underline">Ganti</button>
        </div>

        <!-- Main Registration Form -->
        <form method="POST" action="{{ route('register.member') }}" class="mt-6" x-show="selectedTenant" style="display:none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            @csrf

            <!-- Hidden Tenant ID -->
            <input type="hidden" name="tenant_id" :value="selectedTenant?.id">

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Nama Lengkap')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <x-primary-button class="w-full justify-center">
                    {{ __('Daftar Sekarang') }}
                </x-primary-button>
            </div>
        </form>

        <x-input-error :messages="$errors->get('tenant_id')" class="mt-4 text-center" />

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tenantSelection', () => ({
                mode: 'code',
                code: '',
                query: '',
                results: [],
                selectedTenant: null,
                isLoading: false,
                errorMsg: '',

                setMode(newMode) {
                    this.mode = newMode;
                    this.errorMsg = '';
                    this.results = [];
                },

                async checkCode() {
                    if (this.code.length !== 6) return;
                    
                    this.isLoading = true;
                    this.errorMsg = '';
                    
                    try {
                        const response = await fetch(`/api/tenants/check-code?code=${this.code}`);
                        const data = await response.json();
                        
                        if (data && data.id) {
                            this.selectTenant(data);
                        } else {
                            this.errorMsg = 'Kode institusi tidak ditemukan.';
                        }
                    } catch (error) {
                        this.errorMsg = 'Terjadi kesalahan jaringan.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                async searchTenants() {
                    if (this.query.length < 3) {
                        this.results = [];
                        this.errorMsg = '';
                        return;
                    }

                    this.isLoading = true;
                    this.errorMsg = '';

                    try {
                        const response = await fetch(`/api/tenants/search?q=${this.query}`);
                        const data = await response.json();
                        
                        this.results = data;
                        if (data.length === 0) {
                            this.errorMsg = 'Institusi tidak ditemukan.';
                        }
                    } catch (error) {
                        this.errorMsg = 'Terjadi kesalahan jaringan.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                selectTenant(tenant) {
                    this.selectedTenant = tenant;
                    this.results = [];
                    this.errorMsg = '';
                },

                resetSelection() {
                    this.selectedTenant = null;
                    this.code = '';
                    this.query = '';
                }
            }));
        });
    </script>
</x-guest-layout>

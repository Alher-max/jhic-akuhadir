<div class="bg-brand-surface rounded-2xl shadow-sm border border-brand-border p-6 relative overflow-hidden">
    <h3 class="font-bold text-gray-800 text-lg mb-5 flex items-center justify-center gap-2 border-b border-gray-100 pb-4">
        {{ $title }}
    </h3>
    
    @if(count($data) > 0)
        <!-- Podium Bars Area -->
        <div class="flex items-end justify-center gap-2 sm:gap-4 h-36 mt-8">
            <!-- Rank 2 -->
            <div class="w-1/3 flex flex-col items-center justify-end h-full relative group">
                @if(isset($data[1]))
                    <div class="-mb-3 relative z-20 transition-transform group-hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-full bg-gray-50 overflow-hidden border-2 border-gray-300 shadow-sm">
                            @if(isset($data[1]->master_photo) && $data[1]->master_photo)
                                <img src="{{ asset('storage/' . $data[1]->master_photo) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-500 font-bold text-sm">
                                    {{ strtoupper(substr($data[1]->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow-sm text-lg flex items-center justify-center leading-none filter drop-shadow-sm">🥈</div>
                    </div>
                    <div class="h-24 w-full bg-gradient-to-t from-gray-200 to-gray-50 rounded-t-xl border border-b-0 border-gray-300 flex flex-col items-center justify-start pt-2 shadow-inner">
                        <span class="font-black text-gray-400 text-lg">2</span>
                    </div>
                @endif
            </div>

            <!-- Rank 1 -->
            <div class="w-1/3 flex flex-col items-center justify-end h-full relative z-10 group">
                @if(isset($data[0]))
                    <div class="-mb-4 relative z-20 transition-transform group-hover:-translate-y-1">
                        <div class="w-16 h-16 rounded-full bg-yellow-50 overflow-hidden border-4 border-yellow-400 shadow-md">
                            @if(isset($data[0]->master_photo) && $data[0]->master_photo)
                                <img src="{{ asset('storage/' . $data[0]->master_photo) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-yellow-100 text-yellow-600 font-bold text-lg">
                                    {{ strtoupper(substr($data[0]->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow text-2xl flex items-center justify-center leading-none filter drop-shadow-md">🥇</div>
                    </div>
                    <div class="h-32 w-full bg-gradient-to-t from-yellow-300 to-yellow-50 rounded-t-xl border border-b-0 border-yellow-400 flex flex-col items-center justify-start pt-3 shadow-inner">
                        <span class="font-black text-yellow-600 text-xl">1</span>
                    </div>
                @endif
            </div>

            <!-- Rank 3 -->
            <div class="w-1/3 flex flex-col items-center justify-end h-full relative group">
                @if(isset($data[2]))
                    <div class="-mb-3 relative z-20 transition-transform group-hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-full bg-orange-50 overflow-hidden border-2 border-orange-300 shadow-sm">
                            @if(isset($data[2]->master_photo) && $data[2]->master_photo)
                                <img src="{{ asset('storage/' . $data[2]->master_photo) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-orange-100 text-orange-700 font-bold text-sm">
                                    {{ strtoupper(substr($data[2]->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow-sm text-lg flex items-center justify-center leading-none filter drop-shadow-sm">🥉</div>
                    </div>
                    <div class="h-20 w-full bg-gradient-to-t from-orange-200 to-orange-50 rounded-t-xl border border-b-0 border-orange-300 flex flex-col items-center justify-start pt-2 shadow-inner">
                        <span class="font-black text-orange-500 text-lg">3</span>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Text Labels Area (Fixed alignment below podiums) -->
        <div class="flex items-start justify-center gap-2 sm:gap-4 mt-3">
            <!-- Rank 2 Text -->
            <div class="w-1/3 flex flex-col items-center text-center">
                @if(isset($data[1]))
                    <div class="text-xs font-bold text-gray-800 line-clamp-2 px-1 w-full" title="{{ $data[1]->name }}">{{ $data[1]->name }}</div>
                    <div class="text-[11px] text-emerald-600 font-semibold mt-1">{{ $data[1]->total_present }} Hadir</div>
                    <div class="text-[10px] text-gray-500 mt-0.5 flex items-center gap-1 justify-center"><i class="fa-regular fa-clock"></i> {{ $data[1]->avg_clock_in }}</div>
                @endif
            </div>

            <!-- Rank 1 Text -->
            <div class="w-1/3 flex flex-col items-center text-center">
                @if(isset($data[0]))
                    <div class="text-sm font-black text-gray-900 line-clamp-2 px-1 w-full" title="{{ $data[0]->name }}">{{ $data[0]->name }}</div>
                    <div class="text-xs text-emerald-600 font-bold mt-1">{{ $data[0]->total_present }} Hadir</div>
                    <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1 justify-center"><i class="fa-regular fa-clock"></i> {{ $data[0]->avg_clock_in }}</div>
                @endif
            </div>

            <!-- Rank 3 Text -->
            <div class="w-1/3 flex flex-col items-center text-center">
                @if(isset($data[2]))
                    <div class="text-xs font-bold text-gray-800 line-clamp-2 px-1 w-full" title="{{ $data[2]->name }}">{{ $data[2]->name }}</div>
                    <div class="text-[11px] text-emerald-600 font-semibold mt-1">{{ $data[2]->total_present }} Hadir</div>
                    <div class="text-[10px] text-gray-500 mt-0.5 flex items-center gap-1 justify-center"><i class="fa-regular fa-clock"></i> {{ $data[2]->avg_clock_in }}</div>
                @endif
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs text-gray-400 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
            Peringkat diurutkan berdasarkan total kehadiran.<br class="sm:hidden"> Rata-rata jam masuk menjadi <strong>Tie-Breaker</strong>.
        </div>
    @else
        <div class="py-12 flex flex-col items-center justify-center text-center h-48">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                <i class="fa-solid fa-ranking-star text-2xl text-gray-300"></i>
            </div>
            <h4 class="text-sm font-bold text-gray-800">Belum ada data kehadiran</h4>
            <p class="text-xs text-gray-500 mt-1 max-w-xs">Peringkat akan muncul setelah presensi pertama tercatat.</p>
        </div>
    @endif
</div>

<div class="space-y-4">
    <div class="bg-indigo-600 rounded-xl p-6 text-white shadow-lg">
        <h2 class="text-sm font-medium opacity-80">Today's Status</h2>
        <div class="mt-2 text-3xl font-bold">
            @if($logs->first() && $logs->first()->punch_time->isToday())
                {{ ucfirst(str_replace('_', ' ', $logs->first()->status)) }}
            @else
                Not Checked In
            @endif
        </div>
        <p class="mt-1 text-sm opacity-90">Last punch: {{ $logs->first() ? $logs->first()->punch_time->format('H:i') : '--:--' }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="font-semibold text-gray-700">Recent Activity</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($logs as $log)
                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center 
                            {{ $log->status === 'tepat_waktu' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            @if($log->punch_type === 'in')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $log->punch_time->format('d M, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $log->device ? $log->device->location_name : 'Unknown Device' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900">{{ $log->punch_time->format('H:i:s') }}</p>
                        <p class="text-xs font-medium {{ $log->status === 'tepat_waktu' ? 'text-green-600' : 'text-red-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500 text-sm">
                    No attendance logs found.
                </div>
            @endforelse
        </div>
    </div>
</div>

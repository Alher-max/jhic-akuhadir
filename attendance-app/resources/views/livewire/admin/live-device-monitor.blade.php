<div wire:poll.5s class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($devices as $device)
        <div class="bg-white rounded-lg p-5 shadow-sm border border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $device->status === 'online' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">{{ $device->location_name }}</h3>
                    <p class="text-xs text-gray-500 font-mono">{{ $device->serial_number }}</p>
                </div>
            </div>
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $device->status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($device->status) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Live Attendance Stream</h3>
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
        </div>
        <ul class="divide-y divide-gray-200 max-h-[500px] overflow-y-auto">
            @forelse($recentLogs as $log)
                <li class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold uppercase">
                                {{ substr($log->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'Unknown User' }}</p>
                                <p class="text-xs text-gray-500">{{ $log->device->location_name ?? 'Unknown Device' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-900 font-mono">{{ $log->punch_time->format('H:i:s') }}</p>
                            <p class="text-xs font-medium {{ $log->status === 'tepat_waktu' ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                            </p>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-6 py-8 text-center text-gray-500">
                    No recent activity.
                </li>
            @endforelse
        </ul>
    </div>
</div>

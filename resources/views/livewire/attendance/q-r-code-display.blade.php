<div class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow-md" wire:poll.3s>
    <div class="mb-4 text-2xl font-bold text-gray-800">
        {{ $session->session_name }}
    </div>

    <div class="p-4 bg-white border-4 border-primary-500 rounded-xl">
        {!! $qrCode !!}
    </div>

    <div class="mt-6 text-center">
        <div class="text-sm text-gray-500 uppercase tracking-widest">Token Baru Dalam</div>
        <div class="text-4xl font-black text-primary-600 tabular-nums">
            {{ $countdown }}s
        </div>
        <div class="text-[10px] text-gray-300 mt-2">
            Last Sync: {{ now('Asia/Jakarta')->format('H:i:s') }} (WIB)
        </div>
    </div>

    <div class="mt-8 grid grid-cols-2 gap-4 w-full text-sm">
        <div class="p-3 bg-gray-50 rounded border border-gray-100">
            <span class="block text-gray-400">Lokasi</span>
            <span class="font-semibold">{{ $session->location->name }}</span>
        </div>
        <div class="p-3 bg-gray-50 rounded border border-gray-100">
            <span class="block text-gray-400">Sesi</span>
            <span class="font-semibold">{{ $session->session_date->format('d M Y') }}</span>
        </div>
    </div>
</div>

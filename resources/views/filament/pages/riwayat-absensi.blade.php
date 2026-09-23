<x-filament-panels::page>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Card Hadir -->
        <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Hadir</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $this->summaryStats['hadir'] }}</p>
            </div>
            <div class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                <x-heroicon-o-check-circle class="h-6 w-6" />
            </div>
        </div>

        <!-- Card Izin -->
        <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Izin</p>
                <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $this->summaryStats['izin'] }}</p>
            </div>
            <div class="rounded-lg bg-amber-50 p-2.5 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                <x-heroicon-o-document-text class="h-6 w-6" />
            </div>
        </div>

        <!-- Card Sakit -->
        <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Sakit</p>
                <p class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $this->summaryStats['sakit'] }}</p>
            </div>
            <div class="rounded-lg bg-rose-50 p-2.5 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                <x-heroicon-o-exclamation-circle class="h-6 w-6" />
            </div>
        </div>

        <!-- Card Alpa -->
        <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Alpa</p>
                <p class="mt-1 text-2xl font-bold text-red-700 dark:text-red-400">{{ $this->summaryStats['alpa'] }}</p>
            </div>
            <div class="rounded-lg bg-red-100 p-2.5 text-red-700 dark:bg-red-950/50 dark:text-red-400">
                <x-heroicon-o-x-circle class="h-6 w-6" />
            </div>
        </div>

        <!-- Card Persentase -->
        <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:col-span-2 lg:col-span-1">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tingkat Kehadiran</p>
                <p class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $this->summaryStats['persentase'] }}%</p>
            </div>
            <div class="rounded-lg bg-indigo-50 p-2.5 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                <x-heroicon-o-chart-bar class="h-6 w-6" />
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>

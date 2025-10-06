<x-filament-panels::page>
    @if (session('NEW_USERS') && count(session('NEW_USERS')) > 0)
        <x-filament::section class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center gap-2 text-yellow-800">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-yellow-600" />
                <span class="font-medium">لقد عثرنا على أكواد مستخدمين غير مسجلين في النظام.</span>
            </div>
        </x-filament::section>
    @endif

        <div>
            {{ $this->table }}
        </div>

</x-filament-panels::page>

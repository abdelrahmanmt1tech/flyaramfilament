<x-filament::page>
    <div class="max-w-3xl mx-auto">
        <x-filament::card>
            {{ $this->form }}

            <x-slot name="footer">
                <x-filament::button
                    type="submit"
                    form="company-settings-form"
                    icon="heroicon-o-check-circle"
                    class="w-full"
                >
                    حفظ
                </x-filament::button>
            </x-slot>
        </x-filament::card>
    </div>
</x-filament::page>

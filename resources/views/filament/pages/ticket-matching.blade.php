<x-filament::page>
    <div class="max-w-2xl mx-auto">
        {{-- Upload Card --}}
        <x-filament::card>
            {{ $this->form }}

            <x-slot name="footer">
                <x-filament::button
                    type="submit"
                    form="upload-ticket-form"
                    icon="heroicon-o-cloud-arrow-up"
                    class="w-full"
                >
                    {{ __('dashboard.save') }}
                </x-filament::button>
            </x-slot>
        </x-filament::card>
    </div>



    <div>
        {{ $this->table }}
    </div>


</x-filament::page>

<x-filament::page>
    @if($this->getTabs())
        <x-filament::tabs>
            @foreach ($this->getTabs() as $key => $tab)
                <x-filament::tabs.item 
                    :active="$activeTab === $key" 
                    wire:click="$set('activeTab', '{{ $key }}')"
                >
                    {{ $tab->getLabel() }}
                    @if($tab->getBadge())
                        <x-filament::badge color="gray" class="ms-1">{{ $tab->getBadge() }}</x-filament::badge>
                    @endif
                </x-filament::tabs.item>
            @endforeach
        </x-filament::tabs>
    @endif

    {{ $this->table }}
</x-filament::page>

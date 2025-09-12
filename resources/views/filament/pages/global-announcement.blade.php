<x-filament-panels::page>
    {{ $this->form }}

    <x-filament::button wire:click="send" class="mt-4">
        Send Announcement
    </x-filament::button>
</x-filament-panels::page>

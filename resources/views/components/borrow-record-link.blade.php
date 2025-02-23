@php
    $url = route('filament.resources.borrowings.view', ['record' => $getRecord()->borrow_id]);
@endphp

<a href="{{ $url }}" target="_blank" class="filament-button filament-button-size-md">
    View Borrow Record
</a>

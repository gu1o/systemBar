@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-base text-green-800']) }}>
        {{ $status }}
    </div>
@endif

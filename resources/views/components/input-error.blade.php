@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-base font-medium text-red-700 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif

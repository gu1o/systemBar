@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 hover:border-indigo-500 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-brand-900 transition-all duration-300']) }}>

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center min-h-11 px-6 py-3 bg-white border border-gray-300 rounded-lg cursor-pointer font-bold text-lg text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-700 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

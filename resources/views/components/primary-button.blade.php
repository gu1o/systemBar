<button {{ $attributes->merge(['type' => 'submit', 'class' => 'flex items-center justify-center min-h-11 px-6 py-3 bg-brand-700 border border-transparent rounded-lg cursor-pointer font-bold text-lg text-white hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-700 focus:ring-offset-2 transition ease-in-out duration-300']) }}>
    {{ $slot }}
</button>

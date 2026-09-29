<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center min-h-11 px-6 py-3 bg-red-700 border border-transparent rounded-lg cursor-pointer font-bold text-lg text-white hover:bg-red-800 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

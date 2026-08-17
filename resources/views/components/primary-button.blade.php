<button {{ $attributes->merge(['type' => 'submit', 'class' => 'flex items-center justify-center min-h-11 px-6 py-3 bg-gray-800 border border-transparent rounded-md cursor-pointer font-semibold text-base text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-300']) }}>
    {{ $slot }}
</button>

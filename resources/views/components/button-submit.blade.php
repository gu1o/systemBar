<button {{ $attributes->merge(['type' => 'submit', 'class' => 'bg-accent-700 hover:bg-accent-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all text-xl duration-300']) }}>
    {{ $slot}}
</button>

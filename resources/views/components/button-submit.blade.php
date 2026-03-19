<button {{ $attributes->merge(['type' => 'submit', 'class' => 'bg-[#008080] hover:bg-[#00A0A0] text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all text-xl duration-300']) }}>
    {{ $slot}}
</button>

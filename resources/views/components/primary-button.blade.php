<button {{ $attributes->merge(['type' => 'submit', 'class' => 'sim-btn-primary']) }}>
    {{ $slot }}
</button>

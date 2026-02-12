<button {{ $attributes->merge(['type' => 'submit', 'class' => 'sim-btn-danger']) }}>
    {{ $slot }}
</button>

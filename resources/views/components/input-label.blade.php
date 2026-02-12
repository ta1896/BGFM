@props(['value'])

<label {{ $attributes->merge(['class' => 'sim-label']) }}>
    {{ $value ?? $slot }}
</label>

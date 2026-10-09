@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label fw-medium small text-secondary']) }}>
    {{ $value ?? $slot }}
</label>

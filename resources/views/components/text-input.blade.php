@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 rounded-md shadow-sm']) !!}>

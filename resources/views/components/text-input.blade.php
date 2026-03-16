@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-brand-teal focus:ring-brand-teal rounded-md shadow-sm']) }}>

{{-- resources/views/components/money-input.blade.php --}}
@props(['name', 'label' => null, 'value' => '', 'required' => false, 'inline' => false])

<div {{ $inline ? 'class="relative flex-1"' : '' }}>
    @if($label && !$inline)
        <label class="block font-medium text-gray-700 mb-1 text-sm">{{ $label }}</label>
    @endif

    <div class="relative">
        <span class="absolute left-3 top-2 text-gray-500 text-xs">R$</span>
        <input
            type="text"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            class="money w-full pl-9 pr-3 py-2 border rounded-md text-sm"
            placeholder="0,00"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
    </div>
</div>
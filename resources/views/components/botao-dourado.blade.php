@props(['href' => '#', 'as' => 'a', 'texto' => null, 'disabled' => false, 'type' => 'button', 'size' => null, 'icon' => null])
@php $classes = 'btn-dourado'.($size === 'sm' ? ' btn-sm' : ''); @endphp
@if($as === 'label')
<label {{ $attributes->merge(['class' => $classes]) }} data-button-component="dourado">{{ $texto ?? $slot }}</label>
@elseif($as !== 'button')
<a @if(!$disabled) href="{{ $href }}" @else role="link" aria-disabled="true" tabindex="-1" @endif {{ $attributes->merge(['class' => $classes]) }} data-button-component="dourado">@if($icon)<span aria-hidden="true">{{ $icon }}</span>@endif{{ $texto ?? $slot }}</a>
@else
<button type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }} data-button-component="dourado">@if($icon)<span aria-hidden="true">{{ $icon }}</span>@endif{{ $texto ?? $slot }}</button>
@endif

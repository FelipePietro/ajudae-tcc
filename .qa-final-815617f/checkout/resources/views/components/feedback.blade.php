@props(['kind' => 'notice'])
<div {{ $attributes->class('my-4 rounded-xl border border-[#ddd6c9] bg-[#f8f6f1] p-4 text-[#183c2b]') }} role="{{ $kind === 'error' ? 'alert' : 'status' }}" aria-atomic="true" data-feedback-kind="{{ $kind }}">
    {{ $slot }}
</div>

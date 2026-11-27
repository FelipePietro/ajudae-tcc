@props(['nome', 'tipo' => 'text', 'placeholder' => ''])

<input 
    type="{{ $tipo }}"
    name="{{ $nome }}"
    placeholder="{{ $placeholder }}"
    class="input-padrao"
/>
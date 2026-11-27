@props(['field', 'value' => '', 'prefix' => ''])
@php
    $id = $prefix.$field['key']; $type = $field['type'] ?? 'text';
    $lengths = ['nm_pessoa'=>64,'cpf_pessoa'=>11,'rg_pessoa'=>20,'email_pessoa'=>128,'tele_pessoa'=>11,'cep_pessoa'=>8,'logradouro_pessoa'=>64,'compl_pessoa'=>64,'cidade_pessoa'=>64,'bairro_pessoa'=>64,'uf_pessoa'=>2,'login_pessoa'=>64,'login_ong'=>64,'cnpj_ong'=>14,'cpf_responsavel_ong'=>11,'cep_ong'=>8,'uf_ong'=>2,'nome_fantasia'=>255,'site_url_ong'=>255];
    $digits = ['cpf_pessoa'=>11,'tele_pessoa'=>11,'cep_pessoa'=>8,'cnpj_ong'=>14,'cpf_responsavel_ong'=>11,'cep_ong'=>8];
@endphp
<div class="account-field {{ $type === 'textarea' ? 'account-span' : '' }}">
    <label for="{{ $id }}">{{ $field['label'] }} <span>{{ $field['required'] ? '*' : '(opcional)' }}</span></label>
    @if($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $field['key'] }}" data-demo-field rows="3" {{ $field['required'] ? 'required' : '' }} @readonly($field['readonly'] ?? false) @if(isset($field['max'])) maxlength="{{ $field['max'] }}" @endif aria-describedby="{{ $id }}-hint {{ $id }}-error">{{ $value }}</textarea>
    @elseif($type === 'gender')
        <select id="{{ $id }}" name="{{ $field['key'] }}" data-demo-field required aria-describedby="{{ $id }}-hint {{ $id }}-error">
            <option value="">Selecione</option>
            @foreach(['feminino' => 'Feminino', 'masculino' => 'Masculino', 'outro' => 'Outro', 'prefiro não dizer' => 'Prefiro não dizer'] as $key => $label)<option value="{{ $key }}" @selected($value === $key)>{{ $label }}</option>@endforeach
        </select>
    @else
        <input id="{{ $id }}" name="{{ $field['key'] }}" data-demo-field type="{{ $type }}" value="{{ $value }}" {{ $field['required'] ? 'required' : '' }} autocomplete="off" @readonly($field['readonly'] ?? false) @if(isset($field['max']) || isset($lengths[$field['key']])) maxlength="{{ $field['max'] ?? $lengths[$field['key']] }}" @endif @if(isset($digits[$field['key']])) pattern="{{ '[0-9]{'.$digits[$field['key']].'}' }}" inputmode="numeric" @endif @if(in_array($field['key'], ['tel_ong','tel_responsavel_ong'])) pattern="[0-9]{10,15}" inputmode="numeric" @endif aria-describedby="{{ $id }}-hint {{ $id }}-error" @if($type === 'date') max="{{ now()->subYears(18)->toDateString() }}" @endif>
    @endif
    <small id="{{ $id }}-hint">{{ $field['hint'] ?? '' }}</small>
    <small id="{{ $id }}-error" class="account-error" data-field-error></small>
</div>

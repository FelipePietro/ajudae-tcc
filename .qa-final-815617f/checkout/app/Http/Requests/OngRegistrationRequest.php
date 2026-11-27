<?php

namespace App\Http\Requests;

use App\Rules\Cnpj;
use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;

final class OngRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        abort_unless($this->expectsJson() || $this->is('api/*'), 406);

        return ! $this->attributes->has('web_account');
    }

    protected function prepareForValidation(): void
    {
        foreach (['cnpj_ong', 'cpf_responsavel_ong'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => preg_replace('/[.\/\-]/', '', $this->input($field))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'login_ong' => ['required', 'string', 'max:64', 'unique:ong,login_ong'],
            'nome_fantasia' => ['required', 'string', 'max:100'],
            'cnpj_ong' => ['required', 'digits:14', new Cnpj, 'unique:ong,cnpj_ong'],
            'email_ong' => ['required', 'email', 'max:100', 'unique:ong,email_ong'],
            'senha_ong' => ['required', 'string', 'min:8', 'confirmed', function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_string($value) && (strlen($value) > 72 || str_contains($value, "\0"))) {
                    $fail('Informe uma senha de até 72 bytes sem caracteres nulos.');
                }
            }],
            'tel_ong' => ['required', 'digits_between:10,15'],
            'cep_ong' => ['required', 'digits:8'],
            'logradouro_ong' => ['required', 'string', 'max:100'],
            'bairro_ong' => ['required', 'string', 'max:100'],
            'cidade_ong' => ['required', 'string', 'max:100'],
            'uf_ong' => ['required', 'string', 'size:2'],
            'descricao_ong' => ['required', 'string', 'max:256'],
            'site_url_ong' => ['nullable', 'url:http,https', 'max:255'],
            'pfp_ong_link' => ['nullable', 'string', 'max:255'],
            'nome_responsavel_ong' => ['required', 'string', 'max:100'],
            'cpf_responsavel_ong' => ['required', 'digits:11', new Cpf],
            'email_responsavel_ong' => ['required', 'email', 'max:100'],
            'tel_responsavel_ong' => ['required', 'digits_between:10,15'],
            'documento_identidade' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'rg_responsavel_ong_link' => ['prohibited'],
            'aceitou_termos' => ['required', 'accepted'],
        ];
    }
}

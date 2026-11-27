<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PessoaRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nm_pessoa' => ['required', 'string', 'max:64'],
            'genero_pessoa' => ['required', Rule::in(['masculino', 'feminino', 'outro', 'prefiro não dizer'])],
            'cpf_pessoa' => ['required', 'digits:11', new Cpf, 'unique:pessoa,cpf_pessoa'],
            'dt_nasc' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'rg_pessoa' => ['required', 'string', 'max:20'],
            'email_pessoa' => ['required', 'email', 'max:128', 'unique:pessoa,email_pessoa'],
            'tele_pessoa' => ['required', 'digits:11'],
            'cep_pessoa' => ['required', 'digits:8'],
            'logradouro_pessoa' => ['required', 'string', 'max:64'],
            'compl_pessoa' => ['nullable', 'string', 'max:64'],
            'cidade_pessoa' => ['required', 'string', 'max:64'],
            'bairro_pessoa' => ['required', 'string', 'max:64'],
            'uf_pessoa' => ['required', 'string', 'size:2'],
            'login_pessoa' => ['required', 'string', 'max:64', 'unique:pessoa,login_pessoa'],
            'senha_pessoa' => ['required', 'string', 'min:8', 'confirmed'],
            'pfp_pessoa_link' => ['required', 'string', 'max:255'],
            'aceitou_termos' => ['required', 'accepted'],
            'codigo_padrinho' => ['nullable', 'string', 'regex:/\A[0-9a-f]{64}\z/i'],
        ];
    }

    public function messages(): array
    {
        return [
            'dt_nasc.before_or_equal' => 'É necessário ter pelo menos 18 anos para utilizar o Ajudae.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $cpf = $this->input('cpf_pessoa');
        if (is_string($cpf) || is_int($cpf)) {
            $this->merge(['cpf_pessoa' => preg_replace('/\D/', '', (string) $cpf)]);
        }
    }
}

<?php

namespace App\Http\Requests;

final class WebPessoaRegistrationRequest extends PessoaRegistrationRequest
{
    public function authorize(): bool
    {
        return ! $this->attributes->has('web_account');
    }

    protected function prepareForValidation(): void
    {
        abort_unless($this->expectsJson(), 406);

        parent::prepareForValidation();

        $this->merge(['pfp_pessoa_link' => '/images/avatar-pessoa.svg']);
    }
}

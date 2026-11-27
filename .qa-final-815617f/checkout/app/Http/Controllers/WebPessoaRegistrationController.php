<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebPessoaRegistrationRequest;
use App\Services\PessoaRegistrationService;
use App\Support\ApiResponse;
use App\Support\VisualOneFixtures;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

final class WebPessoaRegistrationController extends Controller
{
    public function create(Request $request)
    {
        if ($request->attributes->has('web_account')) {
            return redirect()->route('conta');
        }

        [$identification, $address, , $documents, $credentials, $review] = VisualOneFixtures::sections('pessoa');
        $credentials['intro'] = 'Escolha o login e a senha para acessar sua conta.';
        $documents['intro'] = 'A validação como voluntário exige documento de identidade e certidão de antecedentes.';

        return view('cadastro', [
            'integratedRegistration' => true,
            'sections' => [$identification, $address, $documents, $credentials, $review],
        ]);
    }

    public function store(WebPessoaRegistrationRequest $request, PessoaRegistrationService $registration)
    {
        try {
            $registration->register($request->validated(), $request->ip(), $request->userAgent());
        } catch (UniqueConstraintViolationException) {
            return ApiResponse::error('Já existe um cadastro com os dados informados.', status: 409);
        }

        return ApiResponse::success(null, 'Conta criada. A validação de voluntário está pendente.', 201);
    }
}

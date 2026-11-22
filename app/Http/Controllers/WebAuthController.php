<?php

namespace App\Http\Controllers;

use App\Services\WebApi;
use App\Services\WebSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class WebAuthController extends Controller
{
    public function login(Request $request, WebApi $api, WebSession $session)
    {
        try {
            // Account switching first revokes the old identity, even on failure.
            $session->end($request);
            $input = $request->validate([
                'tipo_login' => ['required', 'in:pessoa,ong'],
                'login' => ['required', 'string', 'max:128'],
                'senha' => ['required', 'string', 'max:1024'],
            ]);
            $type = $input['tipo_login'];
            $result = DB::transaction(function () use ($api, $session, $request, $input, $type) {
                $result = $api->call($type, 'login', [
                    'login_'.$type => $input['login'],
                    'senha_'.$type => $input['senha'],
                ]);
                if ($result['status'] >= 500) {
                    throw new RuntimeException('Authentication service unavailable.');
                }
                if ($result['status'] === 200) {
                    $session->start($request, $type, $result['body']['data']['token']);
                }

                return $result;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            // No request/exception dumps: login requests contain credentials.
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->view('login', ['authError' => 'Não foi possível entrar. Tente novamente mais tarde.'], 503);
        }
        if ($result['status'] !== 200) {
            return response()->view('login', ['authError' => match ($result['status']) {
                401, 422 => 'Login ou senha inválidos.',
                429 => 'Muitas tentativas. Aguarde um minuto.',
                default => 'Não foi possível entrar. Tente novamente mais tarde.',
            }], in_array($result['status'], [401, 403, 422, 429], true) ? $result['status'] : 503);
        }

        return redirect()->route('conta');
    }

    public function logout(Request $request, WebApi $api, WebSession $session)
    {
        $type = $request->session()->get('bff.type');
        if (in_array($type, ['pessoa', 'ong'], true) && ($bearer = $session->bearer($request))) {
            $api->call($type, 'logout', bearer: $bearer);
        }
        $session->end($request);

        return redirect()->route('login')->with('notice', 'Você saiu da sua conta.');
    }

    public function account(Request $request)
    {
        return view('conta', ['account' => $request->attributes->get('bff_account'), 'type' => $request->session()->get('bff.type')]);
    }

    public function unavailable(Request $request)
    {
        return response()->view('foundation-unavailable', [], $request->isMethod('GET') ? 200 : 503);
    }
}

<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\PersonalAccessToken;

class WebSession
{
    public function bearer(Request $request): ?string
    {
        $value = $request->session()->get('bff.credential');
        if (! is_string($value)) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }

    public function end(Request $request): void
    {
        if ($bearer = $this->bearer($request)) {
            // Delete only this session's token, including expired tokens.
            PersonalAccessToken::findToken($bearer)?->delete();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function start(Request $request, string $type, string $bearer): void
    {
        $token = PersonalAccessToken::findToken($bearer);
        abort_unless($token, 503);
        $expires = now()->addMinutes((int) config('session.lifetime'));
        $token->forceFill(['expires_at' => $expires])->save();
        $request->session()->regenerate(true);
        $request->session()->put('bff', [
            'type' => $type,
            'credential' => Crypt::encryptString($bearer),
            'expires' => $expires->getTimestamp(),
        ]);
    }
}

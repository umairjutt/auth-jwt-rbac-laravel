<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TwoFactorController extends Controller
{
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();

        $secret = Google2FA::generateSecretKey();
        $user->two_factor_secret = encrypt($secret);
        $user->two_factor_recovery_codes = encrypt(json_encode(collect(range(1, 8))->map(fn () => Str::random(10))->all()));
        $user->save();

        $otpauth = Google2FA::getQRCodeUrl(config('app.name'), $user->email, $secret);

        $renderer = new ImageRenderer(new RendererStyle(192), new SvgImageBackEnd());
        $qrSvg = (new Writer($renderer))->writeString($otpauth);

        return response()->json([
            'secret' => $secret,
            'qr_svg' => $qrSvg,
            'otpauth_url' => $otpauth,
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate(['totp' => ['required', 'string']]);
        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json(['error' => '2FA not initialized.'], 422);
        }

        $valid = Google2FA::verifyKey(decrypt($user->two_factor_secret), $data['totp']);
        if (!$valid) {
            return response()->json(['error' => 'Invalid code.'], 422);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        return response()->json([
            'recovery_codes' => json_decode(decrypt($user->two_factor_recovery_codes), true),
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json(['message' => '2FA disabled.']);
    }
}

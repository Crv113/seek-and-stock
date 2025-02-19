<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class DiscordAuthController extends Controller
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = config('custom.discord_client_id');
        $this->clientSecret = config('custom.discord_client_secret');
        $this->redirectUri = config('custom.discord_redirect_uri');
    }

    public function redirectToDiscord(Request $request)
    {
        $redirectUrl = $request->query('redirect', config('custom.front_app_url'));

        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'identify email',
            'state' => urlencode($redirectUrl)
        ]);

        return redirect("https://discord.com/api/oauth2/authorize?$query");
    }

    public function handleDiscordCallback(Request $request)
    {
        $code = $request->get('code');
        $redirectUrl = urldecode($request->get('state', config('custom.front_app_url')));

        if (!$code) {
            return response()->json(['error' => 'Authorization code not found'], 400);
        }

        // Échanger le code contre un token
        $response = Http::asForm()->post('https://discord.com/api/oauth2/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        $tokenData = $response->json();

        if (isset($tokenData['access_token'])) {
            // Récupérer les infos utilisateur
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tokenData['access_token'],
            ])->get('https://discord.com/api/users/@me')->json();

            $user = User::updateOrCreate(
                ['discord_id' => $userResponse['id']],
                [
                    'discord_username' => $userResponse['username'],
                    'discord_global_name' => $userResponse['global_name'],
                    'discord_avatar' => $userResponse['avatar'],
                    'discord_locale' => $userResponse['locale'],
                    'email' => $userResponse['email']
                ]
            );

            $token = $user->createToken('discord-login')->plainTextToken;

            return redirect("$redirectUrl?token=$token");
        }

        return response()->json(['error' => 'Failed to authenticate'], 401);
    }
}

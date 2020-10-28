<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class CallbackController extends Controller
{
    public function onPublish(Request $request, LoggerInterface $logger): Response
    {
        $this->validate($request, [
            'name' => ['required', 'string'],
        ]);

        $token = (new Parser())->parse((string) $request->input('name'));
        if (!$token->verify(new Sha256(), new Key(config('app.jwt.signingKey')))) {
            return response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $streamName = $this->fetchNameFromToken($token);
        $this->validate(new Request(['name' => $streamName]), [
            'name' => ['required', 'string'],
        ]);

        $logger->info('Accept incoming stream', ['publishingName' => $streamName, 'jwt' => $request->input('name')]);

        return response('', Response::HTTP_FOUND, ['Location' => $streamName]);
    }

    private function fetchNameFromToken(Token $token): ?string
    {
        try {
            return $token->getClaim('name');
        } catch (\OutOfBoundsException $e) {
            return null;
        }
    }
}

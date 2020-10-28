<?php

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Testing\TestResponse;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Symfony\Component\HttpFoundation\Response;

class CallbackTest extends TestCase
{
    public function onPublishDataProvider(): iterable
    {
        yield 'valid signing key' => [
            function(): string {
                $signingKey = 'mysecretkey';

                /** @var ConfigRepository $config */
                $config = $this->app->make('config');
                $config->set('app.jwt.signingKey', $signingKey);

                return (new Builder())
                    ->withClaim('name', 'foobar')
                    ->getToken(new Sha256(), new Key($signingKey));
            },
            function(TestResponse $res): void {
                $res->assertStatus(Response::HTTP_FOUND);
                $res->assertHeader('Location', 'foobar');
            },
        ];

        yield 'invalid signing key' => [
            function(): string {
                $signingKey = 'mysecretkey';

                /** @var ConfigRepository $config */
                $config = $this->app->make('config');
                $config->set('app.jwt.signingKey', $signingKey);

                return (new Builder())
                    ->withClaim('name', 'foobar')
                    ->getToken(new Sha256(), new Key('foobar'));
            },
            function(TestResponse $res): void {
                $res->assertStatus(Response::HTTP_UNAUTHORIZED);
            },
        ];

        yield 'missing name claim in token' => [
            function(): string {
                $signingKey = 'mysecretkey';

                /** @var ConfigRepository */
                $config = $this->app->make('config');
                $config->set('app.jwt.signingKey', $signingKey);

                return (new Builder())
                    ->getToken(new Sha256(), new Key($signingKey));
            },
            function(TestResponse $res): void {
                $res->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        ];
    }

    /**
     * @dataProvider onPublishDataProvider
     */
    public function testOnPublish(Closure $tokenBuilder, Closure $responseAssertion): void
    {
        $token = $tokenBuilder->call($this);

        /** @var TestResponse $response */
        $response = $this->call('POST', '/on-publish', ['name' => $token]);

        $responseAssertion->call($this, $response);
    }
}

<?php

namespace

{

    use Guzzle\Http\Client;
    use Illuminate\Support\Facades\Auth;
    use Tymon\JWTAuth\JWTAuth;

    abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
    {
        /**
         * The base URL to use while testing the application.
         *
         * @var string
         */
        protected $baseUrl = 'http://localhost';

        /**
         * Creates the application.
         *
         * @return \Illuminate\Foundation\Application
         */
        public function createApplication()
        {
            $app = require __DIR__.'/../bootstrap/app.php';

            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

            return $app;
        }

        protected function getAuthenticatedUser()
        {
            $user = $this->json(
                'POST',
                '/api/v1/app/starapi-testing/login',
                [
                    'email' => 'marko@marko.com',
                    'password' => 'marko123'
                ]
            );

            return $user;
        }

        protected function getToken()
        {
            $client = new GuzzleHttp\Client();

            $formParams = ['email' => 'marko@marko.com', 'password' => 'marko123'];

            $res = $client->request(
                'POST',
                'http://starapi.public/api/v1/app/starapi-testing/login',
                [
                'form_params' => $formParams
                ]
            );

            $headers = $res->getHeaders();
            $token = $headers['Authorization'][0];

            return $token;
        }
    }
}

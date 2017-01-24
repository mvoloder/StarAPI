<?php

namespace {

    use App\Profile;
    use Illuminate\Foundation\Testing\DatabaseMigrations;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Support\Facades\Auth;

    class AuthTest extends TestCase
    {
        use DatabaseMigrations;

        protected $token;
        protected $id;
        private $url = 'api/v1/app/starapi-testing/profiles/';

        public function getUser($id)
        {
            $this->id = $id;

            return $this->id = $this->testValidRegistration();
        }

        public function setToken($token)
        {
            $this->token = $token;

            return $this->token = $this->testValidLogin();
        }

        /**
         *
         * Test invalid login attempt
         */
        public function testEmptyRequestOnLogin()
        {
            $this->json('POST', '/api/v1/app/starapi-testing/login', [])
                ->seeJsonEquals([
                    'error' => true,
                    'errors' => ['Invalid credentials.']
                ]);
        }

        /**
         * Test invalid login attempt
         */
        public function testInvalidLogin()
        {
            $this->json('POST', '/api/v1/app/starapi-testing/login', ['name' => 'Sally'])
                ->seeJsonEquals([
                    'error' => true,
                    'errors' => ['Invalid credentials.']
                ]);
        }

        /**
         * Test invalid login attempt
         */
        public function testEmptyRequestOnRegistration()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/register',
                [
                    'name',
                    'email',
                    'password',
                    'repeat password'
                ]
            )->seeJsonEquals([
                'error' => true,
                'errors' => ['Issue with automatic sign in.']
            ]);
        }

        public function testInvalidRegistration()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/register',
                [
                    'name' => 'marko m',
                    'email' => 'marko@marko.com',
                    'password' => 'marko123',
                    'repeat password' => 'marko123'
                ]
            );

            if ($this->seeInDatabase('profiles', ['email' => 'marko@marko.com']) === true) {
                $this->seeJsonEquals([
                    'errors' => ['The email has already been taken.']
                ]);
            }
        }

        public function testValidRegistration()
        {
            $this->seed(AclCollectionSeeder::class);
            $this->seed(ValidationsSeeder::class);

            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/register',
                [
                    'name' => 'marko m',
                    'email' => 'marko@marko.com',
                    'password' => 'marko123',
                    'repeat password' => 'marko123'
                ]
            );

            $profile = Profile::where('email', '=', 'marko@marko.com')->first();

            $this->id = $profile->id;

            return $this->id;
        }

        public function testValidLogin()
        {
            $resp = $this->json(
                'POST',
                '/api/v1/app/starapi-testing/login',
                [
                    'email' => 'marko@marko.com',
                    'password' => 'marko123'
                ]
            );

            $resp->seeHeader('Authorization');

            $headers = $this->response->headers;

            $jwt = $headers->get('Authorization');

            $this->token = $jwt;

            if ($headers === null) {
                $this->seeJsonEquals([
                    'error' => true,
                    'errors' => ['Authorization header not found.']
                ]);
            } elseif ($this->token === null) {
                $this->seeJsonEquals([
                    'error' => true,
                    'errors' => ['JWT invalid']
                ]);
            }

            $this->assertResponseOk();

            return $this->token;
        }

        public function testWrongLoginPassword()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/login',
                [
                    'email' => 'pero@pero.com',
                    'password' => 'pero123'
                ]
            );

            $this->seeJsonEquals([
                'error' => true,
                'errors' => ['Invalid credentials.'],
            ]);

            $this->assertResponseStatus(401);
        }

        public function testWrongLoginEmail()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/login',
                [
                    'email' => 'peo@pero.com',
                    'password' => 'pero1234'
                ]
            );

            $this->seeJsonEquals([
                'error' => true,
                'errors' => ['Invalid credentials.'],
            ]);

            $this->assertResponseStatus(401);
        }

        public function testNameNotFullOnRegistration()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/register',
                [
                    'name' => 'mislav',
                    'email' => 'mislav@mislav.com',
                    'password' => 'miki123456',
                    'repeat password' => 'miki123456'
                ]
            );

            $this->seeJsonEquals([
                'errors' => ['Full name needed, at least 2 words.']
            ]);

            $this->assertResponseStatus(400);
        }

        public function testInvalidEmailOnRegistration()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/register',
                [
                    'name' => 'mislav m',
                    'email' => 'mislav@mislav',
                    'password' => 'miki123456',
                    'repeat password' => 'miki123456'
                ]
            );

            $this->seeJsonEquals([
                'errors' => ['The email must be a valid email address.']
            ]);

            $this->assertResponseStatus(400);
        }

        public function testPasswordTooShortOnRegistration()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/register',
                [
                    'name' => 'mislav m',
                    'email' => 'mislav@mislav.com',
                    'password' => 'miki',
                    'repeat password' => 'miki'
                ]
            );

            $this->seeJsonEquals([
                'errors' => ['The password must be at least 8 characters.']
            ]);

            $this->assertResponseStatus(400);
        }

        public function testDeleteProfileNotLoggedIn()
        {
            $this->json(
                'DELETE',
                '/api/v1/app/starapi-testing/profiles',
                [
                    '_id' => '58779e89263add372e348550'
                ]
            );

            $this->assertResponseStatus(403);
        }

        public function testUserNotFound()
        {
            $this->json(
                'GET',
                'api/v1/app/starapi-testing/profiles/2343423',
                [],
                [
                    'Authorization' => $this->setToken($this->token)
                ]
            );

            $this->seeJsonEquals([
                'error' => true,
                'errors' => ["User not found."]
            ]);

            $this->assertResponseStatus(404);
        }

        public function testChangePasswordInvalidOldPassword()
        {
            $this->json(
                'PUT',
                'api/v1/app/starapi-testing/profiles/changePassword',
                [
                    'oldPassword' => 'marko1255',
                    'newPassword' => 'marko1234',
                    'repeatNewPassword' => 'marko1234'
                ],
                [
                    'Authorization' => $this->setToken($this->token)
                ]
            );


            $this->seeJsonEquals([
                'error' => true,
                'errors' => ['Invalid old password']
            ]);
        }

        public function testChangePasswordMissMatch()
        {
            $this->json(
                'PUT',
                'api/v1/app/starapi-testing/profiles/changePassword',
                [
                    'oldPassword' => 'marko123',
                    'newPassword' => 'marko12345',
                    'repeatNewPassword' => 'marko1243'
                ],
                [
                    'Authorization' => $this->setToken($this->token)
                ]
            );

            $this->seeJsonEquals([
                'error' => true,
                'errors' => ['Passwords mismatch']
            ]);
        }

//        public function testChangePassword()
//        {
//            $this->json(
//                'PUT',
//                'api/v1/app/starapi-testing/profiles/changePassword',
//                [
//                    'oldPassword' => 'marko123',
//                    'newPassword' => 'marko1234',
//                    'repeatNewPassword' => 'marko1234'
//                ],
//                [
//                    'Authorization' => $this->setToken($this->token)
//                ]
//            );
//
//            $this->assertResponseOk();
//        }

        public function testProfileUpdate()
        {
            $this->json(
                'PUT',
                $this->url . $this->getUser($this->id),
                [
                    'slack' => 'testSlack',
                    'trello' => 'testTrello',
                    'github' => 'testGit'
                ],
                [
                    'Authorization' => $this->setToken($this->token)
                ]
            );

            $this->assertResponseOk();
        }

//        public function testDelete()
//        {
//            $profiles = Profile::all();
//
//            foreach ($profiles as $profile) {
//                if (!$profile instanceof Profile) {
//                    $this->assertResponseStatus(404);
//                    break;
//                } elseif ($profile->admin === true) {
//                    $this->json(
//                        'DELETE',
//                        $this->url . $this->getUser($this->id),
//                        [],
//                        [
//                            'Authorization' => $this->setToken($this->token)
//                        ]
//                    );
//                    $this->assertResponseStatus(200);
//                    break;
//                }
//            }
//        }
    }
}

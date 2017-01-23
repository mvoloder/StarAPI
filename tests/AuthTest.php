<?php

namespace {

    use App\Profile;
    use Illuminate\Foundation\Testing\DatabaseMigrations;
    use Illuminate\Foundation\Testing\WithoutMiddleware;

    class AuthTest extends TestCase
    {
        use DatabaseMigrations;

        /**
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
            );

            $this->assertResponseStatus(401);
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

            $this->assertResponseStatus(400);
        }

        public function testValidLogin()
        {
            $profile = factory(App\Profile::class)->make();

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

            $token = $headers->get('Authorization');

            $this->assertResponseOk();

            return $token;
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
                    'Authorization' => $this->testValidLogin()
                ]
            );

            $this->seeJsonEquals([
                'error' => true,
                'errors' => ["User not found."]
            ]);

            $this->assertResponseStatus(404);
        }

        public function testChangePasswordUserNotFound()
        {
            $profiles = Profile::all();

            foreach ($profiles as $profile) {
                if (!$profile instanceof Profile) {
                    $this->seeJsonEquals([
                        'error' => true,
                        'errors' => ['User not found.']
                    ]);
                }
            }
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
                    'Authorization' => $this->testValidLogin()
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
                    'Authorization' => $this->testValidLogin()
                ]
            );

            $this->seeJsonEquals([
                'error' => true,
                'errors' => ['Passwords mismatch']
            ]);
        }

        public function testChangePassword()
        {
//            $this->json(
//                'PUT',
//                'api/v1/app/starapi-testing/profiles/changePassword',
//                [
//                    'oldPassword' => 'marko123',
//                    'newPassword' => 'marko1234',
//                    'repeatNewPassword' => 'marko1234'
//                ],
//                [
//                    'Authorization' => $this->getToken()
//                ]
//            );
//
//            $this->assertResponseOk();
        }

        public function testProfileUpdate()
        {

            $profiles = Profile::all();

            foreach ($profiles as $profile) {
                if (!$profile instanceof Profile) {
                    $this->seeJsonEquals([
                        'error' => true,
                        'errors' => ['Model not found']
                    ]);
                    $this->assertResponseStatus(404);
                } else {
                    $this->json(
                        'PUT',
                        'api/v1/app/starapi-testing/profiles/5885d6cd263add3c9748a9c2',
                        [
                            'slack' => 'test2Slack',
                            'trello' => 'test2Trello',
                            'github' => 'test2Git'
                        ],
                        [
                            'Authorization' => $this->testValidLogin()
                        ]
                    );

                    $this->assertResponseOk();
                }
            }
        }

        public function testDelete()
        {
            $profiles = Profile::all();

            foreach ($profiles as $profile) {
                if ($profile instanceof Profile) {
                    $this->json(
                        'DELETE',
                        '/api/v1/app/starapi-testing/profiles/5885d7c4263add3e331664f3',
                        [],
                        [
                            'Authorization' => $this->testValidLogin()
                        ]
                    );
                    if (!$profile instanceof Profile) {
                        $this->assertResponseStatus(404);
                        break;
                    } elseif ($profile->admin === true) {
                        $this->assertResponseStatus(200);
                        break;
                    }
                }
            }
        }
    }
}

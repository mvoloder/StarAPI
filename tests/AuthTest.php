<?php

namespace {

    use Illuminate\Foundation\Testing\DatabaseMigrations;

    class AuthTest extends TestCase
    {
        use DatabaseMigrations;

        private $url = 'api/v1/app/starapi-testing/';

        public function setUp()
        {
            parent::setUp();

            $this->seed(AclCollectionSeeder::class);
            $this->seed(ValidationsSeeder::class);
        }

        public function tearDown()
        {
            parent::tearDown();
        }

        /**
         *
         * Test invalid login attempt
         */
        public function testEmptyRequestOnLogin()
        {
            $this->json(
                'POST',
                $this->url . 'login',
                []
            )
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
            $this->json(
                'POST',
                $this->url . 'login',
                ['name' => 'Sally']
            )
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
                $this->url . 'register',
                []
            )->seeJsonEquals([
                'error' => true,
                'errors' => ['Issue with automatic sign in.']
            ]);
        }

        public function testValidRegistration()
        {
            $resp = $this->json(
                'POST',
                $this->url . 'register',
                [
                    'name' => 'marko m',
                    'email' => 'marko@marko.com',
                    'password' => 'marko123',
                    'repeat password' => 'marko123'
                ]
            )->seeJsonContains([
                'name' => 'marko m',
                'email' => 'marko@marko.com'
            ]);

            $data = $resp->response->getContent();

            $userId = json_decode($data);

            return $userId->_id;
        }

        public function testValidLogin()
        {
            $resp = $this->json(
                'POST',
                $this->url . 'login',
                [
                    'email' => 'marko@marko.com',
                    'password' => 'marko123'
                ]
            )->seeJsonContains([
                'email' => 'marko@marko.com'
            ]);

            $resp->seeHeader('Authorization');

            $headers = $this->response->headers;

            $jwt = $headers->get('Authorization');

            return $jwt;
        }

        public function testInvalidRegistration()
        {
            $this->json(
                'POST',
                $this->url . 'register',
                [
                    'name' => 'marko',
                    'email' => 'marko@marko.com',
                    'password' => 'marko123',
                    'repeat password' => 'marko123'
                ]
            )->seeJsonEquals([
                'errors' => ['The email has already been taken.', 'Full name needed, at least 2 words.']
            ]);
        }

        public function testWrongLoginPassword()
        {
            $this->json(
                'POST',
                $this->url . 'login',
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
                $this->url . 'login',
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
                $this->url . 'register',
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
                $this->url . 'register',
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
                $this->url . 'register',
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
                $this->url . 'profiles/',
                [
                    '_id' => '58779e89263add372e348550'
                ]
            );

            $this->assertResponseStatus(403);
        }

        /**
         * @depends testValidLogin
         */
        public function testUserNotFound($token)
        {
            $this->json(
                'GET',
                $this->url . 'profiles/2343423',
                [],
                [
                    'authorization' => $token
                ]
            );

            $this->seeJsonEquals([
                'error' => true,
                'errors' => ["User not found."]
            ]);

            $this->assertResponseStatus(404);
        }

        /**
         * @depends testValidLogin
         */
        public function testChangePasswordInvalidOldPassword($token)
        {
            $this->json(
                'PUT',
                $this->url . 'profiles/changePassword',
                [
                    'oldPassword' => 'marko1255',
                    'newPassword' => 'marko1234',
                    'repeatNewPassword' => 'marko1234'
                ],
                [
                    'Authorization' => $token
                ]
            );


            $this->seeJsonEquals([
                'error' => true,
                'errors' => ['Invalid old password']
            ]);
        }

        /**
         * @depends testValidLogin
         */
        public function testChangePasswordMissmatch($token)
        {
            $this->json(
                'PUT',
                $this->url . 'profiles/changePassword',
                [
                    'oldPassword' => 'marko123',
                    'newPassword' => 'marko12345',
                    'repeatNewPassword' => 'marko1243'
                ],
                [
                    'Authorization' => $token
                ]
            );

            $this->seeJsonEquals([
                'error' => true,
                'errors' => ['Passwords mismatch']
            ]);
        }

        /**
         * @depends testValidLogin
         * @depends testValidRegistration
         */
        public function testChangePassword($token, $id)
        {
            $this->json(
                'PUT',
                $this->url . 'profiles/changePassword',
                [
                    'oldPassword' => 'marko123',
                    'newPassword' => 'marko1234',
                    'repeatNewPassword' => 'marko1234'
                ],
                [
                    'Authorization' => $token
                ]
            )->seeJsonContains([
                '_id' => $id
            ]);
        }

        /**
         * @depends testValidLogin
         * @depends testValidRegistration
         */
        public function testProfileUpdate($token, $id)
        {
            $this->json(
                'PUT',
                $this->url . 'profiles/' . $id,
                [
                    'slack' => 'testSlack',
                    'trello' => 'testTrello',
                    'github' => 'testGit'
                ],
                [
                    'Authorization' => $token
                ]
            )->seeJsonContains([
                'slack' => 'testSlack',
                'trello' => 'testTrello',
                'github' => 'testGit'
            ]);
        }

        /**
         * @depends testValidLogin
         */
        public function testDeleteUserNotAdmin($token)
        {
            $this->json(
                'DELETE',
                $this->url . 'profiles/' . '58887134263add26f70bd9ce',
                [],
                [
                    'Authorization' => $token
                ]
            )->seeJsonEquals([
                'error' => true,
                'errors' => ['Insufficient permissions.']
            ]);
        }

        /**
         * @depends testValidLogin
         * @depends testValidRegistration
         */
        public function testDelete($token, $id)
        {
            $this->json(
                'DELETE',
                $this->url . 'profiles/' . $id,
                [],
                [
                    'Authorization' => $token
                ]
            )->seeJsonContains([
                'id' => [$id]
            ]);
        }
    }
}

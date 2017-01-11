<?php

namespace

{

    use Illuminate\Foundation\Testing\DatabaseMigrations;
    use Illuminate\Foundation\Testing\WithoutMiddleware;

    class AuthTest extends TestCase
    {
        use DatabaseMigrations;
        use WithoutMiddleware;
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
            $this->markTestIncomplete('Registration test not implemented yet.');

            $this->json('POST', '/api/v1/app/starapi-testing/register', [])
                ->see('oblah');
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

            $this->seeJsonEquals([
                'errors' => ['The email has already been taken.']
            ]);
        }

        public function testValidLogin()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/login',
                [
                   'email' => 'marko@marko.com',
                    'password' => 'marko123'
                ]
            );

            $this->assertResponseStatus(200);
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

        public function testDeleteProfileNotAdmin()
        {
            $this->json(
                'DELETE',
                'api/v1/app/starapi-testing/profiles',
                [
                    '_id' => '587636ca263add33d5675c3e'
                ]
            );
//            $this->seeJsonEquals([
//                'error' => true,
//                'errors' => 'Not enough permissions.'
//            ]);

            $this->assertResponseStatus(403);
        }

        public function testShowProfiles()
        {
            $this->json('GET', 'api/v1/app/starapi-testing/profiles');
        }

//        public function testUserNotFound()
//        {
//            $this->json('GET', 'api/v1/app/starapi-testing/profiles', [
//                'id' => '32452345'
//            ]);
//
//            $this->seeJsonEquals([
//                'error' => true,
//                'errors' => ['User not found.']
//            ]);
//
//            $this->assertResponseStatus(404);
//
//        }
    }
}

<?php

namespace {

    use App\GenericModel;

    class GenericTest extends TestCase
    {
        public function testStoreIntoGenericModel()
        {
            $this->json(
                'POST',
                '/api/v1/app/starapi-testing/projects',
                [

                ],
                []
            );
        }
    }
}

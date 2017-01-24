<?php

namespace {

    use App\GenericModel;
    use App\Profile;
    use Illuminate\Support\Facades\App;

    class GenericTest extends AuthTest
    {

        protected $url = '/api/v1/app/starapi-testing/';
        protected $collection = 'fruit/';
        protected $model = 'citrus';
        protected $modelName = 'lemon';

        public function testInvalidGenericModelStore()
        {
            $profiles = Profile::all();

            foreach ($profiles as $profile) {
                if ($profile->admin === false) {
                    $this->json(
                        'POST',
                        $this->url . $this->collection,
                        [
                            $this->model => $this->modelName
                        ],
                        [
                            'Authorization' => $this->setToken($this->token)
                        ]
                    )->seeJsonEquals([
                        'error' => true,
                        'errors' => ['Insufficient permissions.']
                    ]);
                }
            }
        }

        public function testStoreIntoGenericModel()
        {
            $this->json(
                'POST',
                $this->url . $this->collection,
                [
                    $this->model => $this->modelName,
                ],
                [
                    'Authorization' => $this->setToken($this->token)
                ]
            );

            $this->assertResponseOk();
        }

        public function testGetGenericModel()
        {
            $this->json(
                'GET',
                $this->url . $this->collection,
                [],
                [
                    'Authorization' => $this->setToken($this->token)
                ]
            );

            $this->assertResponseOk();
        }

        public function testShowGenericModel()
        {
            $models = GenericModel::all();

            foreach ($models as $model) {
                $this->json(
                    'GET',
                    $this->url . $this->collection . $model->_id,
                    [],
                    [
                        'Authorization' => $this->setToken($this->token)
                    ]
                );

                $this->seeInDatabase($this->collection, [$this->model => $model->_id]);
            }
        }

//        public function testSearchByField()
//        {
//            $models = GenericModel::all();
//
//            foreach ($models as $model){
//                $this->json(
//                    'GET',
//                    $this->url . $this->collection,
//                    [
//                        'searchField' => 'cars'
//                    ],
//                    [
//                        'Authorization' => $this->setToken($this->token)
//                    ]
//                );
//                $this->assertResponseOk();
//            }
//        }

//        public function testHasOrderBy()
//        {
//            $models = GenericModel::all();
//
//            foreach ($models as $model){
//                $this->json(
//                    'GET',
//                    $this->url . $this->collection,
//                    [
//                        'orderBy' => '_id'
//                    ],
//                    [
//                        'Authorization' => $this->setToken($this->token)
//                    ]
//                );
//            }
//        }
    }
}

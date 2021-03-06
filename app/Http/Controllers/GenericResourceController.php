<?php

namespace App\Http\Controllers;

use App\Events\GenericModelCreate;
use App\Events\GenericModelUpdate;
use App\GenericModel;
use Illuminate\Http\Request;

/**
 * Class GenericResourceController
 * @package App\Http\Controllers
 */
class GenericResourceController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Http\JsonResponse|static[]
     */
    public function index(Request $request)
    {
        $query = GenericModel::query();

        //default query params values
        $orderBy = '_id';
        $orderDirection = 'desc';
        $offset = 0;
        $limit = 100;

        $errors = [];

        // Validate query params based on request params
        if ($request->has('searchField') && $request->has('searchQuery')) {
            $searchField = $request->get('searchField');
            $searchQuery = '%' . $request->get('searchQuery') . '%';
            if ($request->input('searchExact', null)) {
                $query->where($searchField, '=', $request->get('searchQuery'));
            } else {
                $query->where($searchField, 'LIKE', $searchQuery);
            }
        }

        if ($request->has('orderBy')) {
            $orderBy = $request->get('orderBy');
        }

        if ($request->has('orderDirection')) {
            if (strtolower(substr($request->get('orderDirection'), 0, 3)) === 'asc' ||
                strtolower(substr($request->get('orderDirection'), 0, 4)) === 'desc'
            ) {
                $orderDirection = $request->get('orderDirection');
            } else {
                $errors[] = 'Invalid orderDirection input.';
            }
        }

        if ($request->has('offset')) {
            if (ctype_digit($request->get('offset')) && $request->get('offset') >= 0) {
                $offset = (int)$request->get('offset');
            } else {
                $errors[] = 'Invalid offset input.';
            }
        }

        if ($request->has('limit')) {
            if (ctype_digit($request->get('limit')) && $request->get('limit') >= 0) {
                $limit = (int)$request->get('limit');
            } else {
                $errors[] = 'Invalid limit input.';
            }
        }

        if (count($errors) > 0) {
            return $this->jsonError($errors, 400);
        }

        $query->orderBy($orderBy, $orderDirection)->offset($offset)->limit($limit);
        $models = $query->get();

        return $models;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $model = GenericModel::find($request->route('id'));

        if (!$model instanceof GenericModel) {
            return $this->jsonError(['Model not found.'], 404);
        }

        $fields = $request->all();
        if ($this->validateInputsForResource($fields, $request->route('resource'), $model) === false) {
            return $this->jsonError(['Insufficient permissions.'], 403);
        }

        return $model;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|static
     */
    public function store(Request $request)
    {
        $fields = $request->all();
        if ($this->validateInputsForResource($fields, $request->route('resource')) === false) {
            return $this->jsonError(['Insufficient permissions.'], 403);
        }

        $model = GenericModel::create($fields);

        event(new GenericModelCreate($model));

        if ($model->save()) {
            return $model;
        }
        return $this->jsonError('Issue with saving resource.');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $model = GenericModel::find($request->route('id'));

        if (!$model instanceof GenericModel) {
            return $this->jsonError(['Model not found.'], 404);
        }

        $updateFields = $request->all();

        if ($this->validateInputsForResource($updateFields, $request->route('resource'), $model) === false) {
            return $this->jsonError(['Insufficient permissions.'], 403);
        }

        $model->fill($updateFields);

        event(new GenericModelUpdate($model));

        if ($model->save()) {
            return $model;
        }

        return $this->jsonError('Issue with updating resource.');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $model = GenericModel::find($request->route('id'));

        if (!$model instanceof GenericModel) {
            return $this->jsonError(['Model not found.'], 404);
        }

        $fields = $request->all();
        if ($this->validateInputsForResource($fields, $request->route('resource'), $model) === false) {
            return $this->jsonError(['Insufficient permissions.'], 403);
        }

        if ($model->delete()) {
            return $model;
        }
        return $this->jsonError('Issue with deleting resource.');
    }
}

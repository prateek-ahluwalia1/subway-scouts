<?php

namespace App\Repositories;

class BaseRepository {

    /**
     * The Model name.
     *
     * @var \Illuminate\Database\Eloquent\Model;
     */
    protected $model;
    const DEFULT_PAGES = 10;

    public function __construct() {}

        /**
     * Paginate the given query.
     *
     * @param The number of models to return for pagination $n integer
     *
     * @return mixed
     */
    public function getPaginate($n) {
        return $this->model->paginate($n);
    }

    /**
     * @param array $data
     */
    public function create(array $data) {
        try {
            return $this->model->create($data);
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * @param array $data
     */
    public function insert(array $data) {
        try {
            return $this->model->insert($data);
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param array $data
     */
    public function save(array $data) {
        try {
            if (!empty($data)) {
                foreach ($data as $key => $input) {
                    $this->model->{$key} = $input;
                }
                $this->model->save();
                return $this->model;
            }
            return false;
        } catch (QueryException $e) {
            dd($e->getMessage());
            return false;
        }
    }

    /**
     * @param int $id
     */
    public function getById(int $id) {
        try {
            return $this->model->findOrFail($id);
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * @param int $slug
     */
    public function getBySlug(string $slug) {
        try {
            return $this->model->where(['slug' => $slug])->first();
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * Update the model in the database.
     *
     * @param int $id
     * @param array $inputs
     */
    public function update(int $id, array $inputs) {
        try {
            $model = $this->getById($id);
            $model->update($inputs);
            return $model;
        } catch (QueryException $e) {
            dd($e->getMessage());
            return false;
        }
    }

    public function updateByKeys(int $id, array $inputs) {
        try {
            $model = $this->getById($id);
            if (!empty($inputs)) {
                foreach ($inputs as $key => $input) {
                    $model->{$key} = $input;
                }
                $model->update();
                return $model;
            }
            return false;
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * Delete the model from the database.
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function delete(int $id) {
        try {
            return $this->getById($id)->delete();
        } catch (QueryException $e) {
            return false;
        }
    }

}

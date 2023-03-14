<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public $model;

    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Retrieve all data of repository
     * @return Collection|Model[] `
     */
    public function all(): array|Collection
    {
        return $this->model->all();
    }

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return
     */
    public function paginate($limit = null, array $columns = ['*'])
    {
        $limit = is_null($limit) ? config('repository.pagination.limit', 10) : $limit;

        return $this->model->paginate($limit, $columns);
    }

    /**
     * Find data by id
     * @param $id
     * @param array $columns
     * @return
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function findOrFail($id, array $columns = ['*']): mixed
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function findOrFailWithTrashed($id, array $columns = ['*']): mixed
    {
        return $this->model->withTrashed()->findOrFail($id);
    }

    /**
     * Save a new entity in repository
     * @param array $input
     * @return
     */
    public function create(array $input)
    {
        return $this->model->create($input);
    }

    /**
     * Update a entity in repository by id
     * @param array $input
     * @param $id
     * @return BaseRepository
     */
    public function update(array $input, $id): self
    {
        $model = $this->model->findOrFail($id);
        $model->fill($input);
        $model->save();

        return $model;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function destroy($id): int
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id): mixed
    {
        $model = $this->model->findOrFail($id);
        $model?->delete();

        return $model;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function multipleDelete(array $ids): mixed
    {
        return $this->model->destroy(array_values($ids));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function latest($id): mixed
    {
        return $this->model->latest('id');
    }

    abstract public function model();

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function makeModel()
    {
        $this->model = app()->make($this->model());
    }

    /**
     * @param array $arrayFind
     * @param $arrayCreate
     * @return mixed
     */
    public function updateOrCreate(array $arrayFind, array $arrayCreate = ['*']):mixed
    {
        return $this->model->updateOrCreate($arrayFind, $arrayCreate);
    }

    /**
     * @param $data
     * @return null
     */
    public function insertMany($dataCreates)
    {
        return count($dataCreates) > 0 ? $this->model->insert($dataCreates) : null;
    }
}

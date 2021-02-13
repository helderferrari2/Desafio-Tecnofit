<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\AbstractRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class AbstractRepository implements AbstractRepositoryInterface
{
    /**
     * Instance that extends Illuminate\Database\Eloquent\Model
     *
     * @var Model
     */
    protected $model;

    /**
     *  A limit for paginate
     *
     * @var $limit
     */
    protected $limit = 15;

    /**
     * Constructor
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get Model instance
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get class name
     *
     * @param Model $model
     */
    protected function getClassName()
    {
        return class_basename($this->model);
    }

    /**
     * Get all from resource
     *
     * @return Collection
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Find a resource by id
     *
     * @param $id
     * @return Model|null
     */
    public function find(int $id)
    {
        $response =  $this->model->find($id);
        if (empty($response)) {
            return;
        }

        return $response;
    }

    /**
     * Find a resource by criteria
     *
     * @param array $criteria
     * @return Model|null
     */
    public function findOneBy(array $criteria)
    {
        return $this->model->where($criteria)->first();
    }

    /**
     * Search All resources by any values of a key
     *
     * @param string $key
     * @param array $values
     * @return Collection
     */
    public function findIn(string $key, array $values)
    {
        return $this->model->whereIn($key, $values)->get();
    }

    /**
     * Search All resources by criteria
     *
     * @param array $searchCriteria
     * @return Collection
     */
    public function findBy(array $searchCriteria = [])
    {
        $limit = !empty($searchCriteria['per_page']) ? (int)$searchCriteria['per_page'] : 15; // it's needed for pagination
        $queryBuilder = $this->model->where(function ($query) use ($searchCriteria) {
            $this->applySearchCriteriaInQueryBuilder($query, $searchCriteria);
        });

        return $queryBuilder->paginate($limit);
    }

    /**
     * Apply condition on query builder based on search criteria
     *
     * @param Object $queryBuilder
     * @param array $searchCriteria
     * @return mixed
     */
    protected function applySearchCriteriaInQueryBuilder($queryBuilder, array $searchCriteria = [])
    {
        foreach ($searchCriteria as $key => $value) {
            //skip pagination related query params
            if (in_array($key, ['page', 'per_page'])) {
                continue;
            }
            //we can pass multiple params for a filter with commas
            $allValues = explode(',', $value);
            if (count($allValues) > 1) {
                $queryBuilder->whereIn($key, $allValues);
            } else {
                $operator = '=';
                $queryBuilder->where($key, $operator, $value);
            }
        }

        return $queryBuilder;
    }

    /**
     * Save a resource
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data)
    {
        $filledProperties = $this->model->getFillable();
        $keys = array_keys($data);
        foreach ($keys as $key) {
            if (!in_array($key, $filledProperties)) {
                unset($data[$key]);
            }
        }

        return $this->model->create($data);
    }

    /**
     * Insert array in resource
     *
     * @param array $data
     * @return Model
     */
    public function insert(array $data)
    {
        foreach($data as $item){
            $filledProperties = $this->model->getFillable();
            foreach (array_keys($item) as $key) {
                if (!in_array($key, $filledProperties)) {
                    unset($item[$key]);
                    $data[] = $item;
                }
            }
        }
        return $this->model->insert($data);
    }

    /**
     * Update a resource
     *
     * @param integer $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data)
    {
        $model = $this->find($id);
        $filledProperties = $model->getFillable();
        $keys = array_keys($data);
        foreach ($keys as $key) {
            if (in_array($key, $filledProperties)) {
                $model->$key = $data[$key];
            }
        }
        $model->save($data);
        return $model;
    }

    /**
     * Delete a resource
     *
     * @param Model $model
     * @return boolean
     */
    public function delete($model)
    {
        return $model->delete();
    }

    /**
     * Get a list of customers
     *
     * @return Collection
     */
    public function getAllCustomers()
    {
        return User::where('role', 'customer')->get();
    }
}

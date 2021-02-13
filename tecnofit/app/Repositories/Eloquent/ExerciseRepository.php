<?php

namespace App\Repositories\Eloquent;

use App\Models\Exercise;
use App\Repositories\Contracts\ExerciseRepositoryInterface;

class ExerciseRepository extends AbstractRepository implements ExerciseRepositoryInterface
{
    protected $model;

    public function __construct(Exercise $exercise)
    {
        $this->model = $exercise;
    }

    public function deleteAllExercisesByTrainingId(int $id)
    {
        return $this->model->where('training_id', $id)->delete();
    }
}

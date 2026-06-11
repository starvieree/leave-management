<?php

namespace App\Repositories\Contracts;

interface LeaveRepositoryInterface
{
    public function create(array $data);

    public function find(int $id);

    public function employeeLeaves(
        int $employeeId,
        array $filters = []
    );

    public function all(
        array $filters = []
    );
}
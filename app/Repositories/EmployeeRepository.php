<?php
namespace App\Repositories;
use App\Models\Employee;

class EmployeeRepository extends BaseRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Employee;
    }

    public function getDiningCarID($cartNumber)
    {
        $dining_car_id = $this->model->select('dining_car_id')->where('supplier_id',$cartNumber)->first();
        return $dining_car_id;
    }

}

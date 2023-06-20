<?php

namespace App\Services;

use App\Repositories\SystemVariable\SystemVariableRepositoryInterface;
use App\Services\BaseService;
use App\Models\SystemVariable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemVariableService extends BaseService
{
    protected $repository;

    public function __construct(SystemVariableRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function setModel()
    {
        return new SystemVariable();
    }

    public function getVariables()
    {
        $variables = $this->model->all();

        $result = [];
        foreach ($variables as $variable) {
            if (isset($result[$variable->function])) {
                $result[$variable->function][] = $variable->toArray();
            } else {
                $result[$variable->function] = [
                    $variable->toArray()
                ];
            }
        }

        return $result;
    }

    public function update($variables)
    {
        try {
            DB::beginTransaction();

            foreach ($variables as $variableId => $data)
            {
                $this->repository->update($variableId, $data);
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error(__METHOD__ . ' error: ' . $exception->getMessage());
            Log::error($exception);

            return false;
        }
    }
}

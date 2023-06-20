<?php

namespace App\Services;

use App\Models\Rank;
use App\Repositories\Rank\RankRepositoryInterface;
use App\Repositories\RevenuePeriod\RevenuePeriodRepositoryInterface;

class RankService extends BaseService
{
    protected $repository;
    protected $revenuePeriodRepository;

    public function __construct(
        RankRepositoryInterface   $repository,
        RevenuePeriodRepositoryInterface $revenuePeriodRepository
    )
    {
        parent::__construct();

        $this->repository        = $repository;
        $this->revenuePeriodRepository = $revenuePeriodRepository;
    }

    public function setModel()
    {
        return new Rank();
    }

    public function update($id, $attributes)
    {
        $rank = $this->repository->find($id);

        if (!$rank) {
            return [
                'result' => false,
                'message' => "Not found rank with id: $id."
            ];
        }

        if (isset($attributes['status'])
            && $attributes['status'] == Rank::STATUS_INACTIVE
            && $attributes['status'] != $rank->status
        ) {
            $revenuePeriods = $this->revenuePeriodRepository->getByRank($id, now()->format('Y-m-d'));

            if (count($revenuePeriods)) {
                return [
                    'result' => false,
                    'message' => 'Hạng hiện tại có hợp đồng Key, không thể chuyển qua "Ngừng kích hoạt".'
                ];
            }
        }

        $this->repository->update($id, $attributes);

        return [
            'result' => true,
            'message' => 'Hạng đã được update thành công.'
        ];
    }
}

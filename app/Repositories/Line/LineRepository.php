<?php

namespace App\Repositories\Line;

use App\Helpers\Helper;
use App\Models\Line;
use App\Models\LineStore;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LineRepository extends BaseRepository implements LineRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Line();
    }

    public function getByRequest(
        $with = [],
        $requestParams = [],
        $showOption = []
    )
    {
//        DB::enableQueryLog();
        $query = $this->model
            ->with($with)
            ->with(['stores' => function ($query) use ($requestParams) {
                $query->when(isset($requestParams['year']) && isset($requestParams['month']), function ($query1) use ($requestParams) {
                    $fromDate = Carbon::createFromDate($requestParams['year'], $requestParams['month'], 1)->toDateString();
                    $toDate   = Carbon::createFromDate($requestParams['year'], $requestParams['month'])->endOfMonth()->toDateString();
                    return $query1
                        ->where(function ($query2) use ($toDate) {
                            $query2->where('to', '>=', $toDate)
                                ->orWhere('to', null);
                        })
                        ->where('from', '<=', $fromDate)
                        ->where('line_stores.status', LineStore::STATUS_ACTIVE);
                });
            }])
            ->with(['productGroup' => function ($query) use ($requestParams) {
                $query->when(isset($requestParams['weekday']), function ($query2) use ($requestParams) {
                    return $query2->whereIn('day_of_week', $requestParams['weekday']);
                });
            }])
            ->with(['organizations.users' => function ($query) use ($requestParams) {
                $query->when(isset($requestParams['user_id']), function ($query2) use ($requestParams) {
                    return $query2->where('id', $requestParams['user_id']);
                });
            }])->when($requestParams['name'] ?? '', function ($query) use ($requestParams) {
                return $query->where('name', 'LIKE', '%' . $requestParams['name'] . '%');
            })
            ->when($requestParams['locality_ids'] ?? '', function ($query) use ($requestParams) {
                return $query->whereIn('organization_id', $requestParams['locality_ids']);
            })
            ->when($requestParams['status'] ?? '', function ($query) use ($requestParams) {
                return $query->where('status', $requestParams['status']);
            });
//            ->get();
//        dd(DB::getQueryLog());

        return $this->showOption($query, $showOption);
    }

    /**
     * @param $locality_id
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getByLocality($locality_id = null)
    {
        return $this->model->query()
            ->where('organization_id', $locality_id)
            ->where('status', Line::STATUS_ACTIVE)
            ->get();
    }

    public function getForListScreen(
        $requestParams = [],
        $showOption = []
    )
    {
        $fromDateRange = $requestParams['dateRange']['from'];
        $toDateRange = $requestParams['dateRange']['to'];

        $query = $this->model
            ->with('lineGroup', 'lineStores')
            ->selectRaw("`lines`.*,
                line_stores.store_id,
                line_stores.number_visit,
                line_stores.from,
                line_stores.to,
                organizations.name as organization_name,
                stores.name as store_name,
                users.id as user_id,
                users.name as user_name
            ")
            ->leftJoin('line_groups', 'line_groups.line_id', '=', 'lines.id')
            ->leftJoin('organizations', 'organizations.id', '=', 'lines.organization_id')
            ->leftJoin('line_stores', 'line_stores.line_id', '=', 'lines.id')
            ->leftJoin('stores', 'line_stores.store_id', '=', 'stores.id')
            ->leftJoin('user_organization', 'user_organization.organization_id', '=', 'lines.organization_id')
            ->leftJoin('users', 'user_organization.user_id', '=', 'users.id')
            ->when($requestParams['name'] ?? '', function ($query) use ($requestParams) {
                return $query->where('lines.name', 'LIKE', '%' . $requestParams['name'] . '%');
            })
            ->when($requestParams['locality_ids'] ?? '', function ($query) use ($requestParams) {
                return $query->whereIn('lines.organization_id', $requestParams['locality_ids']);
            })
            ->when($requestParams['user_id'] ?? '', function ($query) use ($requestParams) {
                return $query->where('users.id', $requestParams['user_id']);
            })
            ->when($requestParams['status'] ?? '', function ($query) use ($requestParams) {
                return $query->where('lines.status', $requestParams['status']);
            });

        if (!isset($requestParams['emptyLine'])) {
            $query->when($requestParams['weekday'] ?? '', function ($query) use ($requestParams) {
                return $query->whereIn('line_groups.day_of_week', $requestParams['weekday']);
            })
                ->where(function ($q) use ($fromDateRange, $toDateRange) {
                    return $q->where(function ($q1) {
                        $q1->whereNull('line_stores.from')
                            ->whereNull('line_stores.to');
                    })->orWhere(function ($q1) use ($fromDateRange, $toDateRange) {
                        $q1->where('line_stores.from', '>=', $fromDateRange)
                            ->where('line_stores.from', '<=', $toDateRange);
                    });
                })
                ->whereIn('line_stores.reference_type', [
                    LineStore::REFERENCE_TYPE_LINE, LineStore::DEFAULT_REFERENCE_TYPE
                ]);
        } else {
            $query->whereNull('line_stores.id');
        }

        return $query->groupBy('organization_id', 'id', 'user_id', 'store_id')->get();
    }
}

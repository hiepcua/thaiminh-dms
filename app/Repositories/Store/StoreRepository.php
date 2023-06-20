<?php

namespace App\Repositories\Store;

use App\Helpers\Helper;
use App\Models\Line;
use App\Models\LineStore;
use App\Models\Organization;
use App\Models\Store;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function Clue\StreamFilter\fun;
use Illuminate\Database\Eloquent\Builder;

class StoreRepository extends BaseRepository implements StoreRepositoryInterface
{

    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new Store();
    }

    public function find($id, $with = [], $status = false)
    {
        return $this->model->with($with)
            ->when(!isset($status), function ($query) use ($status) {
                $query->where('status', Store::STATUS_ACTIVE);
            })
            ->find($id);
    }

    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model
            ->with($with)
            ->when($args['name'] ?? '', function ($query) use ($args) {
                $query->where('name', 'LIKE', '%' . $args['name'] . '%')
                    ->orWhere('code', $args['name']);
            })
            ->when($args['diaban'] ?? '', function ($query) use ($args) {
                $query->where('organization_id', $args['diaban']);
            })
            ->when($args['status'] ?? '', function ($query) use ($args) {
                $status = $args['status'] == 'yes' ? '1' : '0';
                $query->where('status', '=', $status);
            })
            ->orderBy('created_at', 'desc')
            ->orderByRaw('IF(parent_id, CONCAT(parent_id, LPAD(id, 3, 0)), CONCAT(id, LPAD(id, 3, 0)))')
            ->paginate($limit);
    }

    public function getList($province = null, $district = null, $name = null)
    {
        return $this->model
            ->when($province ?? '', function ($query) use ($province) {
                $query->where('stores.province_id', $province);
            })
            ->when($district ?? '', function ($query) use ($district) {
                $query->where('district_id', $district);
            })
            ->when($name ?? '', function ($query) use ($name) {
                return $query->where(function ($q) use ($name) {
                    $q->where('name', 'LIKE', '%' . $name . '%')
                        ->orWhere('code', $name);
                });
            })
            ->join('districts', 'districts.id', '=', 'stores.district_id')
            ->select('district_name', 'stores.*')
            ->limit(50)
            ->get();
    }

    public function getParentStore($id)
    {
        return $this->model->find($id);
    }

    public function getByRequest($with = [], $requestParams = [], $showOption = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $organizationOfCurrentUser = Helper::getUserOrganization();

        $query = $this->getQueryByRequest($with, $requestParams)
            ->with($with)
            ->with(['storeOrders' => function ($q) use ($requestParams) {
                $q->when(isset($requestParams['dateRange']), function ($q1) use ($requestParams) {
                    $q1->where('booking_at', '>=', $requestParams['dateRange']['from'])
                        ->where('booking_at', '<=', $requestParams['dateRange']['to']);
                });
            }])
            ->when(isset($organizationOfCurrentUser[Organization::TYPE_KHU_VUC]), function ($q) use ($organizationOfCurrentUser) {
                return $q->whereHas('organization', function ($query2) use ($organizationOfCurrentUser) {
                    return $query2->whereIn('parent_id', $organizationOfCurrentUser[Organization::TYPE_KHU_VUC]);
                });
            });

        return $this->showOption($query, $showOption);
    }

    public function getByRequestTDV($with = [], $requestParams = [], $showOption = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $currentUser     = Helper::currentUser();
        $userId          = $currentUser->id;
        $currentDate     = now()->format('Y-m-d');
        $arrStoreVisited = $arrStoreMustVisit = [];

        if (isset($requestParams['dateRange']['from']) && isset($requestParams['dateRange']['to'])) {
            // List nha thuoc da den tham trong khoang (from => to) + so lan den tham
            $visited = DB::table('checkin')
                ->selectRaw('store_id, COUNT(store_id) as solancheckin')
                ->where('created_by', $userId)
                ->where('forget', 0)
                ->where('checkin_at', '>=', $requestParams['dateRange']['from'])
                ->where('checkin_at', '<=', $requestParams['dateRange']['to'])
                ->groupBy('store_id')
                ->get();

            foreach ($visited as $checkin) {
                $arrStoreVisited[$checkin->store_id] = $checkin->solancheckin;
            }
        }

        // Lits nha thuoc can phai di + so lan di tham toi thieu: line_store
        $mustVisit = DB::table('line_stores')
            ->where('status', LineStore::STATUS_ACTIVE)
            ->orWhere(function ($query) use ($currentDate) {
                $query->where('from', '<=', $currentDate)
                    ->where('to', '>=', $currentDate);
            })
            ->orWhere(function ($query) use ($currentDate) {
                $query->where('from', '<=', $currentDate)
                    ->where('to', null);
            })
            ->selectRaw('DISTINCT store_id, number_visit')
            ->get();

        foreach ($mustVisit as $lineStore) {
            $arrStoreMustVisit[$lineStore->store_id] = $lineStore->number_visit;
        }

        $storeVisited        = array_keys($arrStoreVisited); // Nhà thuốc đã ghé thăm
        $storeMustVisited    = array_keys($arrStoreMustVisit); // Nhà thuốc phải ghé thăm
        $storeNotVisited     = array_diff($storeMustVisited, $storeVisited); // Nhà thuốc chưa ghé thăm => nghé thăm chưa đủ
        $storeNotEnoughVisit = []; // Nhà thuốc ghé thăm mà chưa đủ

        foreach ($arrStoreVisited as $key => $item) {
            if (isset($arrStoreMustVisit[$key]) && $arrStoreMustVisit[$key] > $item) {
                $storeNotEnoughVisit[] = $key;
            }
        }
        $storeNotEnoughVisit = array_merge($storeNotEnoughVisit, $storeNotVisited);

        // Danh sách nhà thuốc cần phải đi thăm
//        DB::enableQueryLog();
        $query = $this->getQueryByRequest($with, $requestParams)
            ->select('stores.*')
            ->with(['reportRevenueStores' => function ($q) use ($requestParams) {
                $q->when(isset($requestParams['dateRange']), function ($q1) use ($requestParams) {
                    $q1->where('day', '>=', $requestParams['dateRange']['from'])
                        ->where('day', '<=', $requestParams['dateRange']['to'])
                        ->groupBy('report_revenue_store.store_id');
                });
            }])
            ->when($requestParams['weekday'] ?? '', function ($query) use ($requestParams) {
                return $query->whereHas('line', function ($q) use ($requestParams) {
                    return $q->whereHas('productGroup', function ($q1) use ($requestParams) {
                        $weekday = $requestParams['weekday'];
                        if ($weekday !== Line::ALL_DAY) {
                            return $q1->where('day_of_week', $requestParams['weekday']);
                        } else {
                            return $q1->whereIn('day_of_week', array_keys(Line::WEEKDAYS));
                        }
                    });
                });
            })
            ->when($requestParams['number_day_not_order'] ?? '', function ($query) use ($requestParams) {
                $minutesDate = Carbon::now()->subDays($requestParams['number_day_not_order'])->format('Y-m-d');
                $query->where('last_booking', '<=', $minutesDate);
            })
            ->when($requestParams['not_enough_visit'] ?? '', function ($query) use ($storeNotEnoughVisit) {
                $query->whereIn('stores.id', $storeNotEnoughVisit);
            })
//            ;
            ->where('id', 28447);
//        ->get();
//        dd(DB::getQueryLog());

        return $this->showOption($query, $showOption);
    }

    /**
     * @param $with
     * @param array $attributes
     * @return array|Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function checkStoreExist($with = [], array $attributes = [])
    {
        return $this->model->query()
            ->with($with)
            ->when($attributes['excludeId'] ?? '', function ($query) use ($attributes) {
                $query->where('id', '<>', $attributes['excludeId']);
            })
            ->where(function ($query) use ($attributes) {
                if (isset($attributes['vat_number']) && $attributes['vat_number'] !== '') {
                    $query->orWhere('vat_number', $attributes['vat_number']);
                }
                if (isset($attributes['address']) && $attributes['address'] !== '') {
                    $query->orWhere('address', $attributes['address']);
                }
                if (isset($attributes['code']) && $attributes['code'] !== '') {
                    $query->orWhere('code', $attributes['code']);
                }
                if (isset($attributes['phone_owner']) && $attributes['phone_owner'] !== '') {
                    $query->orWhere('phone_owner', $attributes['phone_owner']);
                }
                if (isset($attributes['name']) && $attributes['name'] !== '' && isset($attributes['wardId']) && $attributes['wardId'] !== '') {
                    return $query->orWhere(function ($q) use ($attributes) {
                        return $q->where('name', $attributes['name'])
                            ->where('ward_id', $attributes['wardId']);
                    });
                }
            })
            ->limit(20)
            ->get();

    }

    public function getByCode($code = null)
    {
        return $this->model->where('code', $code)->get();
    }

    public function organizationExists(array $organization_ids): bool
    {
        return $this->model::query()->whereIn('organization_id', $organization_ids)->exists();
    }

    public function getQueryByRequest($with = [], $requestParams = [])
    {
        return $this->model->with($with)
            ->when($requestParams['status'] ?? '', function (Builder $query) use ($requestParams) {
                $status = $requestParams['status'] == Store::STATUS_ACTIVE_TEXT ? 1 : 0;
                $query->where('stores.status', $status);
            })
            ->when($requestParams['created_by'] ?? '', function (Builder $query) use ($requestParams) {
                $query->where('stores.created_by', (int)$requestParams['created_by']);
            })
            ->when($requestParams['locality_ids'] ?? '', function ($query) use ($requestParams) {
                return $query->whereIn('stores.organization_id', $requestParams['locality_ids']);
            })
            ->when($requestParams['name'] ?? '', function ($query) use ($requestParams) {
                return $query->where(function ($q) use ($requestParams) {
                    return $q->where('stores.name', 'LIKE', '%' . $requestParams['name'] . '%')
                        ->orWhere('stores.code', $requestParams['name']);
                });
            });
    }

    /**
     * @param $locality_id
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     * Lay danh sach store dang hoat dong ma chua duoc set vao tuyen nao hoac da set vao tuyen nhung da bi inactive theo list dia ban
     */
    public function getByLocality($locality_id = null)
    {
        return $this->model->query()
            ->when(is_array($locality_id), function ($q1) use ($locality_id) {
                return $q1->whereIn('organization_id', $locality_id);
            })
            ->when(!is_array($locality_id), function ($q1) use ($locality_id) {
                return $q1->where('organization_id', $locality_id);
            })
            ->whereDoesntHave('line', function ($query) {
                $query->where("line_stores.to", null);
            })
            ->where('status', Store::STATUS_ACTIVE)
            ->get();
    }

    public function getByTdv($tdvId)
    {
        return $this->model->whereHas('organization', function ($q) use ($tdvId) {
            return $q->whereHas('users', function ($q1) use ($tdvId) {
                return $q1->where('id', $tdvId);
            });
        })
            ->whereNotNull('lng')
            ->whereNotNull('lat')
            ->get();
    }
}

<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\TableHelper;
use App\Models\ForgetCheckin;
use App\Models\SystemVariable;
use App\Repositories\Checkin\CheckinRepositoryInterface;
use App\Repositories\ForgetCheckin\ForgetCheckinRepositoryInterface;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\SystemVariable\SystemVariableRepositoryInterface;
use App\Services\BaseService;
use App\Models\Checkin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckinService extends BaseService
{
    protected $repository;
    protected $storeRepository;
    protected $systemVariableRepository;
    protected $forgetCheckinRepository;

    public function __construct(
        CheckinRepositoryInterface        $repository,
        StoreRepositoryInterface          $storeRepository,
        SystemVariableRepositoryInterface $systemVariableRepository,
        ForgetCheckinRepositoryInterface  $forgetCheckinRepository
    )
    {
        parent::__construct();

        $this->repository               = $repository;
        $this->storeRepository          = $storeRepository;
        $this->systemVariableRepository = $systemVariableRepository;
        $this->forgetCheckinRepository  = $forgetCheckinRepository;
    }

    public function setModel()
    {
        return new Checkin();
    }

    public function getFormOptions($model)
    {
        $options = parent::formOptions($model);

        return $options;
    }

    public function formOptions($model = null): array
    {
        return $this->getFormOptions($model);
    }

    public function getTable($requestParams = [], $showOption = [])
    {
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "created_at",
                "type"   => "DESC"
            ]
        ], $showOption);

        $requestParams['tdv_name']        = $requestParams['tdv_name'] ?? null;
        $requestParams['store_name']      = $requestParams['store_name'] ?? null;
        $requestParams['checkin_at_date'] = $requestParams['checkin_at_date'] ?? null;
        $requestParams['checkin_at']      = $this->handleDateRangeData($requestParams['checkin_at'] ?? '');

        $results  = $this->repository->getByRequest(['user', 'store'], $requestParams, $showOption);
        $cur_page = $results->currentPage();
        $per_page = $results->perPage();

        $results->getCollection()->transform(function ($item, $loopIndex) use ($cur_page, $per_page) {
            $item->stt         = ($loopIndex + 1) + ($cur_page - 1) * ($per_page);
            $item->store_name  = $item->store->name;
            $item->user_name   = $item->user->name;
            $item->checkout_at = isset($item->forget)
                ? '<div class="d-flex justify-content-center">
                        <span class="badge bg-danger rounded-3" id="refresh-store-list" style="padding: 5px 10px; cursor: pointer">Quên Checkout</span>
                    </div>'
                : $item->checkout_at;

            return $item;
        });

        $nameTable = 'checkin-history-list';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
        );
    }

    public function getTableRequestTab($requestParams = [], $showOption = [], $nameTable = 'request-checkout-list')
    {
        $showOption        = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "created_at",
                "type"   => "DESC"
            ]
        ], $showOption);
        $textStatusApprove = ForgetCheckin::STATUS_TEXTS[ForgetCheckin::STATUS_APPROVE];
        $textStatusReject  = ForgetCheckin::STATUS_TEXTS[ForgetCheckin::STATUS_REJECT];
        $textStatusReview  = ForgetCheckin::STATUS_TEXTS[ForgetCheckin::STATUS_REVIEW];

        $requestParams['tdv_name']   = $requestParams['request_tdv_name'] ?? null;
        $requestParams['store_name'] = $requestParams['request_store_name'] ?? null;
        $requestParams['created_at'] = $this->handleDateRangeData($requestParams['request_created_at'] ?? '');
        $requestParams['status']     = $requestParams['request_status'] ?? null;

        $results  = $this->forgetCheckinRepository->getByRequest(['creator', 'reviewer', 'checkin', 'checkin.store'], $requestParams, $showOption);
        $cur_page = $results->currentPage();
        $per_page = $results->perPage();
        $currentPermissions = Helper::getCurrentPermissions();

        $results->getCollection()->transform(function ($item, $loopIndex)
        use ($cur_page, $per_page, $textStatusApprove, $textStatusReject, $textStatusReview, $currentPermissions) {
            $item->stt               = ($loopIndex + 1) + ($cur_page - 1) * ($per_page);
            $item->store_name        = $item->checkin->store->name;
            $item->creator_name      = $item->creator?->name;
            $item->reviewer_name     = $item->reviewer?->name;
            $item->checkin_at        = $item->checkin->checkin_at;
            $item->note              = "
                <div>
                    <b>Người tạo: </b> $item->creator_note <br>
                    <b>Người duyệt: </b> $item->reviewer_note
                </div>
            ";
            $requestForgetCheckoutId = $item->id;

            if ($item->status == ForgetCheckin::STATUS_REVIEW
                && in_array('duyet_de_xuat_quen_checkout', $currentPermissions)
            ) {
                $item->action = "
                    <div class='d-flex justify-content-center align-items-center'>
                        <span class='badge bg-success rounded-3 btn-approve-request'
                            forget-checkout-id='$requestForgetCheckoutId'
                            style='padding: 5px 10px; cursor: pointer'>" . $textStatusApprove . "</span>
                        <span class='badge ms-1 bg-danger rounded-3 btn-reject-request'
                            forget-checkout-id='$requestForgetCheckoutId'
                            style='padding: 5px 10px; cursor: pointer'>" . $textStatusReject . "</span>
                    </div>
                ";
            }

            $statusText = "<div class='d-flex justify-content-center align-items-center'>";
            switch ($item->status) {
                case ForgetCheckin::STATUS_REVIEW:
                    $item->status = $statusText . '<span class="badge bg-warning rounded-3" style="padding: 5px 10px">';
                    $item->status .= $textStatusReview . '</span></div>';
                    break;
                case ForgetCheckin::STATUS_APPROVE:
                    $item->status = $statusText . '<span class="badge bg-success rounded-3" style="padding: 5px 10px">';
                    $item->status .= $textStatusApprove . '</span></div>';
                    break;
                case ForgetCheckin::STATUS_REJECT:
                    $item->status = $statusText . '<span class="badge bg-danger rounded-3" style="padding: 5px 10px">';
                    $item->status .= $textStatusReject . '</span></div>';
                    break;
                default:
                    $item->status = "";
                    break;
            }

            return $item;
        });

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
        );
    }

    public function getTableTDV($requestParams = [], $showOption = [])
    {
        $currentId  = Helper::currentUser()->id;
        $showOption = array_merge([
            "perPage" => config("table.default_paginate"),
            "orderBy" => [
                "column" => "created_at",
                "type"   => "DESC"
            ]
        ], $showOption);

        $requestParams['tdv_id']          = $currentId;
        $requestParams['checkin_at_date'] = $requestParams['checkin_at_date'] ?? Carbon::now()->format('Y-m-d');
        $requestParams['checkin_at']      = $this->handleDateRangeData($requestParams['checkin_at'] ?? '');

        $results        = $this->repository->getByRequest(['user', 'store'], $requestParams, $showOption);
        $cur_page       = $results->currentPage();
        $per_page       = $results->perPage();
        $forgetCheckins = $this->forgetCheckinRepository->getByCreator(
            $currentId,
            Carbon::now()->startOfMonth()->format("Y-m-d 00:00:00"),
            Carbon::now()->endOfMonth()->format("Y-m-d 23:59:59"),
            [
                ForgetCheckin::STATUS_REVIEW,
                ForgetCheckin::STATUS_APPROVE,
            ]
        );

        $limitForgetCheckin = $this->systemVariableRepository->findByName(SystemVariable::LIMIT_FORGET_CHECKIN_A_MONTH)?->value;

        $cantCreateForgetCheckin = $forgetCheckins->count() < $limitForgetCheckin;

        $results->getCollection()->transform(function ($item, $loopIndex)
        use ($cur_page, $per_page, $cantCreateForgetCheckin, $forgetCheckins) {
            $buttonRequestCheckin = $cantCreateForgetCheckin
                ? "<span class='badge bg-warning rounded-3 btn-request-checkin' checkin-id = '$item->id'
                    style='padding: 5px 10px; cursor: pointer'>Tạo đề xuất</span>"
                : "";

            $forgetCheckin = $forgetCheckins->where('checkin_id', $item->id)->first();

            $statusRequestCheckin = "";

            if ($forgetCheckin) {
                $forgetCheckinStatus = $forgetCheckin->status;

                switch ($forgetCheckinStatus) {
                    case ForgetCheckin::STATUS_REVIEW:
                        $statusText           = ForgetCheckin::STATUS_TEXTS[ForgetCheckin::STATUS_REVIEW];
                        $statusRequestCheckin = "<span class='badge bg-secondary rounded-3' style='padding: 5px 10px'>$statusText</span>";
                        break;
                    case ForgetCheckin::STATUS_APPROVE:
                        $statusText           = ForgetCheckin::STATUS_TEXTS[ForgetCheckin::STATUS_APPROVE];
                        $statusRequestCheckin = "<span class='badge bg-success rounded-3' style='padding: 5px 10px'>$statusText</span>";
                        break;
                    case ForgetCheckin::STATUS_REJECT:
                        $statusText           = ForgetCheckin::STATUS_TEXTS[ForgetCheckin::STATUS_REJECT];
                        $statusRequestCheckin = "<span class='badge bg-danger rounded-3' style='padding: 5px 10px'>$statusText</span>";
                        break;
                    default:
                        break;
                }
            }
            $actionCheckout = $forgetCheckin ? $statusRequestCheckin : $buttonRequestCheckin;

            $item->stt        = ($loopIndex + 1) + ($cur_page - 1) * ($per_page);
            $storeName        = $item->store->name;
            $item->store_info = "<div class='w-100 d-flex'><div><b>Tên</b></div><div class='ms-auto'>$storeName</div></div>
                <div class='w-100 d-flex'><div><b>Checkin</b></div><div class='ms-auto'>$item->checkin_at</div></div>
            ";

            $item->store_info .= $item->checkout_at
                ? "<div class='w-100 d-flex'><div><b>Checkout</b></div><div class='ms-auto'>$item->checkout_at</div></div>"
                : "<div class='w-100 d-flex'><div><b>Checkout</b></div><div class='ms-auto'>$actionCheckout</div></div>";
            $item->store_name = $item->store->name;
            $item->user_name  = $item->user->name;

            return $item;
        });

        $nameTable = 'checkin-history-tdv-list';

        return new TableHelper(
            collections: $results,
            nameTable: $nameTable,
        );
    }

    public function getStoresChecked($tdvId, $from = null, $to = null)
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        if (!isset($from) && !isset($to)) {
            $from = "$currentDate 00:00:00";
            $to   = "$currentDate 23:59:59";
        }

        return $this->repository
            ->getStoresChecked($tdvId, $from, $to);
    }

    public function getStoresCheckin($lat, $long, $tdvId)
    {
        try {
            $result        = [];
            $storesOfTdv   = $this->storeRepository->getByTdv($tdvId)->toArray();
            $limitDistance = $this->systemVariableRepository->findByName(SystemVariable::DISTANCE_ALLOW_CHECKIN)?->value;

            $currentDate         = Carbon::now()->format('Y-m-d');
            $storesHasChecked    = $this->getStoresChecked($tdvId);
            $storesHasCheckedIds = $storesHasChecked->pluck('store_id')->toArray();

            foreach ($storesOfTdv as $store) {
                $data = [];
                if (!isset($store['lat']) || !isset($store['lng']) || !$store['lat'] || !$store['lng']) {
                    continue;
                }
                $distance = Helper::getDistanceBetweenTwoPoints($lat, $long, $store['lat'], $store['lng']);

                if (in_array($store['id'], $storesHasCheckedIds)) {
                    $storeHasChecked = $storesHasChecked->where('store_id', $store['id'])->first();
                    if ($storeHasChecked?->checkout_at) {
                        $action          = '<span class="badge bg-success rounded-3" style="padding: 5px 10px; cursor: pointer">Đã checkout</span>';
                        $data['checked'] = 2;
                    } else {
                        $action          = '<span class="badge bg-danger rounded-3 checkout-btn" store-id="' . $store['id'] . '" style="padding: 5px 10px; cursor: pointer">checkout</span>';
                        $data['checked'] = 1;
                    }

                } else {
                    $action          = '<span class="badge bg-warning rounded-3 checkin-btn" store-id="' . $store['id'] . '" style="padding: 5px 10px; cursor: pointer">checkin</span>';
                    $data['checked'] = 0;
                }

                if ($distance <= $limitDistance) {
                    $data     += [
                        'name'     => $store['name'],
                        'distance' => $distance,
                        'action'   => $action
                    ];
                    $result[] = $data;
                }
            }

            if (count($result) > 1) {
                $checkedColumns  = array_column($result, 'checked');
                $distanceColumns = array_column($result, 'distance');
                array_multisort($checkedColumns, SORT_ASC, $distanceColumns, SORT_ASC, $result);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' error: ' . $e->getMessage());
            Log::error($e);

            return [];
        }
    }

    public function checkin($lat, $long, $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId ?? 0);

            if ($store) {
                if (!$store->lat || !$store->lng) {
                    return [
                        'message' => 'Nhà thuốc chưa được setup tọa độ',
                        'result'  => false
                    ];
                }

                $limitDistance = $this->systemVariableRepository->findByName(SystemVariable::DISTANCE_ALLOW_CHECKIN)?->value;
                $distance      = Helper::getDistanceBetweenTwoPoints($lat, $long, $store->lat, $store->lng);

                if ($distance > $limitDistance) {
                    return [
                        'message' => 'Nhà thuốc nằm ngoài phạm vi checkin.',
                        'result'  => false
                    ];
                }

                $checkinAt = Carbon::now()->format('Y-m-d H:i:s');

                $this->repository->create([
                    'store_id'   => $storeId,
                    'lng'        => $long,
                    'lat'        => $lat,
                    'checkin_at' => $checkinAt,
                    'created_by' => Helper::currentUser()->id,
                ]);

                return [
                    'message' => "Checkin thành công lúc: $checkinAt",
                    'result'  => true
                ];
            } else {
                return [
                    'message' => 'Không tìm thấy nhà thuốc',
                    'result'  => false
                ];
            }
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' error: ' . $e->getMessage());
            Log::error($e);

            return [
                'message' => 'Có lỗi xảy ra.',
                'result'  => false
            ];
        }
    }

    public function checkout($lat, $long, $storeId)
    {
        try {
            $store         = $this->storeRepository->find($storeId ?? 0);
            $currentUserId = Helper::currentUser()->id;

            if ($store) {
                if (!$store->lat || !$store->lng) {
                    return [
                        'message' => 'Nhà thuốc chưa được setup tọa độ',
                        'result'  => false
                    ];
                }

                $limitDistance = $this->systemVariableRepository->findByName(SystemVariable::DISTANCE_ALLOW_CHECKIN)?->value;
                $distance      = Helper::getDistanceBetweenTwoPoints($lat, $long, $store->lat, $store->lng);

                if ($distance > $limitDistance) {
                    return [
                        'message' => 'Nhà thuốc nằm ngoài phạm vi checkout.',
                        'result'  => false
                    ];
                }

                $checkinResource = $this->repository->getToDayCheckin($storeId, $currentUserId);

                if (!$checkinResource) {
                    return [
                        'message' => "Không tìm thấy dữ liệu checkin",
                        'result'  => false
                    ];
                }

                $checkinResource->checkout_at = Carbon::now()->format('Y-m-d H:i:s');
                $checkinResource->updated_by  = $currentUserId;
                $checkinResource->save();

                return [
                    'message' => "Checkout thành công",
                    'result'  => true
                ];
            } else {
                return [
                    'message' => 'Không tìm thấy nhà thuốc',
                    'result'  => false
                ];
            }
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ' error: ' . $e->getMessage());
            Log::error($e);

            return [
                'message' => 'Có lỗi xảy ra.',
                'result'  => false
            ];
        }
    }
}

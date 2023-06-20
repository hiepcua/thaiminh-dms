<?php

namespace App\Repositories;

use Illuminate\Support\ServiceProvider;

class RepositoriesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            \App\Repositories\User\UserRepositoryInterface::class,
            \App\Repositories\User\UserRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Role\RoleRepositoryInterface::class,
            \App\Repositories\Role\RoleRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Permission\PermissionRepositoryInterface::class,
            \App\Repositories\Permission\PermissionRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Product\ProductRepositoryInterface::class,
            \App\Repositories\Product\ProductRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Organization\OrganizationRepositoryInterface::class,
            \App\Repositories\Organization\OrganizationRepository::class
        );

        $this->app->singleton(
            \App\Repositories\ProductGroup\ProductGroupRepositoryInterface::class,
            \App\Repositories\ProductGroup\ProductGroupRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Store\StoreRepositoryInterface::class,
            \App\Repositories\Store\StoreRepository::class
        );

        $this->app->singleton(
            \App\Repositories\ProductGroupPriority\ProductGroupPriorityRepositoryInterface::class,
            \App\Repositories\ProductGroupPriority\ProductGroupPriorityRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Rank\RankRepositoryInterface::class,
            \App\Repositories\Rank\RankRepository::class
        );

        $this->app->singleton(
            \App\Repositories\RevenuePeriod\RevenuePeriodRepositoryInterface::class,
            \App\Repositories\RevenuePeriod\RevenuePeriodRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Agency\AgencyRepositoryInterface::class,
            \App\Repositories\Agency\AgencyRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Province\ProvinceRepositoryInterface::class,
            \App\Repositories\Province\ProvinceRepository::class
        );

        $this->app->singleton(
            \App\Repositories\District\DistrictRepositoryInterface::class,
            \App\Repositories\District\DistrictRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Ward\WardRepositoryInterface::class,
            \App\Repositories\Ward\WardRepository::class
        );

        $this->app->singleton(
            \App\Repositories\StoreChangeController\StoreChangeControllerRepositoryInterface::class,
            \App\Repositories\StoreChangeController\StoreChangeControllerRepository::class
        );

        $this->app->singleton(
            \App\Repositories\NewStore\NewStoreRepositoryInterface::class,
            \App\Repositories\NewStore\NewStoreRepository::class
        );

        $this->app->singleton(
            \App\Repositories\StoreChange\StoreChangeRepositoryInterface::class,
            \App\Repositories\StoreChange\StoreChangeRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Gift\GiftRepositoryInterface::class,
            \App\Repositories\Gift\GiftRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Promotion\PromotionRepositoryInterface::class,
            \App\Repositories\Promotion\PromotionRepository::class
        );

        $this->app->singleton(
            \App\Repositories\PromotionCondition\PromotionConditionRepositoryInterface::class,
            \App\Repositories\PromotionCondition\PromotionConditionRepository::class
        );

        $this->app->singleton(
            \App\Repositories\StoreOrder\StoreOrderRepositoryInterface::class,
            \App\Repositories\StoreOrder\StoreOrderRepository::class
        );

        $this->app->singleton(
            \App\Repositories\AgencyOrder\AgencyOrderRepositoryInterface::class,
            \App\Repositories\AgencyOrder\AgencyOrderRepository::class
        );

        $this->app->singleton(
            \App\Repositories\AgencyOrderTDV\AgencyOrderTDVRepositoryInterface::class,
            \App\Repositories\AgencyOrderTDV\AgencyOrderTDVRepository::class
        );

        $this->app->singleton(
            \App\Repositories\AgencyOrderItemRepository\AgencyOrderItemRepositoryRepositoryInterface::class,
            \App\Repositories\AgencyOrderItemRepository\AgencyOrderItemRepositoryRepository::class
        );

        $this->app->singleton(
            \App\Repositories\AgencyOrderItem\AgencyOrderItemRepositoryInterface::class,
            \App\Repositories\AgencyOrderItem\AgencyOrderItemRepository::class
        );

        $this->app->singleton(
            \App\Repositories\ReportRevenueOrder\ReportRevenueOrderRepositoryInterface::class,
            \App\Repositories\ReportRevenueOrder\ReportRevenueOrderRepository::class
        );

        $this->app->singleton(
            \App\Repositories\ProductLog\ProductLogRepositoryInterface::class,
            \App\Repositories\ProductLog\ProductLogRepository::class
        );

        $this->app->singleton(
            \App\Repositories\File\FileRepositoryInterface::class,
            \App\Repositories\File\FileRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Line\LineRepositoryInterface::class,
            \App\Repositories\Line\LineRepository::class
        );

        $this->app->singleton(
            \App\Repositories\LineGroup\LineGroupRepositoryInterface::class,
            \App\Repositories\LineGroup\LineGroupRepository::class
        );

        $this->app->singleton(
            \App\Repositories\StoreRank\StoreRankRepositoryInterface::class,
            \App\Repositories\StoreRank\StoreRankRepository::class
        );

        $this->app->singleton(
            \App\Repositories\AgencyStoreFileRepository\AgencyStoreFileRepositoryRepositoryInterface::class,
            \App\Repositories\AgencyStoreFileRepository\AgencyStoreFileRepositoryRepository::class
        );

        $this->app->singleton(
            \App\Repositories\AgencyOrderFile\AgencyOrderFileRepositoryInterface::class,
            \App\Repositories\AgencyOrderFile\AgencyOrderFileRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Poster\PosterRepositoryInterface::class,
            \App\Repositories\Poster\PosterRepository::class
        );

        $this->app->singleton(
            \App\Repositories\PosterAcceptanceDate\PosterAcceptanceDateRepositoryInterface::class,
            \App\Repositories\PosterAcceptanceDate\PosterAcceptanceDateRepository::class
        );

        $this->app->singleton(
            \App\Repositories\PosterStoreRegister\PosterStoreRegisterRepositoryInterface::class,
            \App\Repositories\PosterStoreRegister\PosterStoreRegisterRepository::class
        );

        $this->app->singleton(
            \App\Repositories\Checkin\CheckinRepositoryInterface::class,
            \App\Repositories\Checkin\CheckinRepository::class
        );

        $this->app->singleton(
            \App\Repositories\LineStore\LineStoreRepositoryInterface::class,
            \App\Repositories\LineStore\LineStoreRepository::class
        );

        $this->app->singleton(
            \App\Repositories\SystemVariable\SystemVariableRepositoryInterface::class,
            \App\Repositories\SystemVariable\SystemVariableRepository::class
        );

        $this->app->singleton(
            \App\Repositories\ForgetCheckin\ForgetCheckinRepositoryInterface::class,
            \App\Repositories\ForgetCheckin\ForgetCheckinRepository::class
        );


        $this->app->singleton(
            \App\Repositories\ReportRevenuePharmacy\ReportRevenuePharmacyRepositoryInterface::class,
            \App\Repositories\ReportRevenuePharmacy\ReportRevenuePharmacyRepository::class
        );

        $this->app->singleton(
            \App\Repositories\ReportAgencyInventory\ReportAgencyInventoryRepositoryInterface::class,
            \App\Repositories\ReportAgencyInventory\ReportAgencyInventoryRepository::class
        );

        $this->app->singleton(
            \App\Repositories\KeyRewardOrder\KeyRewardOrderRepositoryInterface::class,
            \App\Repositories\KeyRewardOrder\KeyRewardOrderRepository::class
        );
        //#replace#

    }
}

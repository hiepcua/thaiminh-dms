<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ForgetCheckinController;
use App\Http\Controllers\KeyRewardOrderController;
use App\Http\Controllers\LineStoreController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PosterPharmacyController;
use App\Http\Controllers\PosterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\ReportAgencyInventoryController;
use App\Http\Controllers\ReportRevenueStoreController;
use App\Http\Controllers\ReportRevenueTDVController;
use App\Http\Controllers\RevenuePeriodController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductGroupPriorityController;
use App\Http\Controllers\StoreOrderController;
use App\Http\Controllers\SystemVariableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RankController;
use App\Http\Controllers\DistrictController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\NewStoreController;
use App\Http\Controllers\StoreChangeController;
use App\Http\Controllers\AgencyOrderController;
use App\Http\Controllers\AgencyTDVOrderController;
use \App\Http\Controllers\ReportAgencyOrderController;
use App\Http\Controllers\LineController;
use \App\Http\Controllers\AgencyOrderFileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Auth::routes();
Route::as('admin.')->group(function () {
    Route::get('/auth/{provider}', [UserController::class, 'redirectToProvider'])->name('auth.provider');
    Route::get('/auth/{provide}/callback', [UserController::class, 'handleProviderCallback']);

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login1');
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::as('password.')->group(function () {
            Route::get('reset-password', [ResetPasswordController::class, 'showResetForm'])->name('reset.index');
            Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('reset.update');
        });

        Route::middleware(['redirect_change_pass'])->group(function () {

            Route::get('demo-get-current-location', function () {
                return view('pages.dashboard.current_location');
            });

            Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

            Route::resource('roles', RoleController::class)->except('show');
            Route::resource('permissions', PermissionController::class)->except('show', 'create');
            //
            Route::resource('users', UserController::class)->except('show');
            Route::post('users/switch/change', [UserController::class, 'switchUserChange'])->name('users.switch.change');
            Route::get('users/switch/back/{user}', [UserController::class, 'switchUserBack'])->name('users.switch.back');
            Route::get('users/switch/{user}', [UserController::class, 'switchUser'])->name('users.switch');
            //
            Route::resource('organizations', OrganizationController::class)->except('show');
            Route::resource('product-groups', ProductGroupController::class)->except('show');
            Route::resource('stores', StoreController::class);

            Route::post('product-has-grouped', [ProductController::class, 'getProductGrouped'])->name('get-product-grouped');
            Route::resource('products', ProductController::class)->except('show');
            Route::resource('ranks', RankController::class)->except('show');

            Route::get('products/priority', [ProductController::class, 'productByPriority'])->name('products.priority');

            Route::resource('product-group-priorities', ProductGroupPriorityController::class)->except('show');
            Route::get('product-group-priorities/history/product/{id}', [ProductGroupPriorityController::class, 'indexProduct'])
                ->name('product-group-priorities.history');
            Route::get('product-group-priorities/create/product/{id}', [ProductGroupPriorityController::class, 'createProduct'])
                ->name('product-group-priorities.create_product');


            Route::get('revenue-periods/products', [RevenuePeriodController::class, 'findProductByDate'])->name('period.products');
            Route::resource('revenue-periods', RevenuePeriodController::class)->except('show');

            Route::post('stores/list-store', [StoreController::class, 'listStore'])->name('stores.list-store');
            Route::resource('districts', DistrictController::class)->except('show');

            Route::post('organizations/get-option-locality', [OrganizationController::class, 'getOptionLocality'])
                ->name('organizations.get_option_locality');

            Route::post('organizations/get-user-by-locality', [OrganizationController::class, 'getUserByLocality'])
                ->name('organizations.get_user_by_locality');

            Route::post('organizations/get-locality-by-organization', [OrganizationController::class, 'getOptionLocalityByOrganization'])
                ->name('organizations.get_locality_by_organization');

            Route::post('agency/get-by-locality', [AgencyController::class, 'getByLocality'])->name('get-agency-by-locality');
            Route::resource('agency', AgencyController::class);

            Route::resource('gift', GiftController::class);

            Route::resource('new-stores', NewStoreController::class);
            Route::get('new-stores/{id}/approve', [NewStoreController::class, 'approve'])->name('new-stores.approve');
            Route::put('new-stores/update-approve/{id}', [NewStoreController::class, 'updateApprove'])->name('new-stores.update-approve');

            Route::resource('store_changes', StoreChangeController::class);
            Route::get('store_changes/{id}/approve', [StoreChangeController::class, 'approve'])->name('store_changes.approve');
            Route::put('store_changes/update-approve/{id}', [StoreChangeController::class, 'updateApprove'])->name('store_changes.update-approve');

            Route::resource('promotion', PromotionController::class);

            Route::post('agency-order/export', [AgencyOrderController::class, 'export'])->name('agency-order.export');
            Route::post('agency-order/check-allow-delete-order',
                [AgencyOrderController::class, 'checkOrderAllowDelete']
            )->name('agency-order.check-order-allow-delete');
            Route::post('agency-order/remove-order',
                [AgencyOrderController::class, 'removeOrder']
            )->name('agency-order.remove-order');

            Route::get('agency-order/show-order-tdv/{id}',
                [AgencyOrderController::class, 'showOrderTdv']
            )->name('agency-order.show-order-tdv');

            Route::resource('agency-order', AgencyOrderController::class);

            Route::post('agency-order-tdv/check-allow-create-order',
                [AgencyTDVOrderController::class, 'checkOrderAllowCreate']
            )->name('agency-order-tdv.check-order-allow-create');
            Route::post('agency-order-tdv/export', [AgencyTDVOrderController::class, 'export'])->name('agency-order-tdv.export');
            Route::post('agency-order-tdv/validate-before-create', [
                AgencyTDVOrderController::class, 'validateBeforeCreate'
            ])->name('agency-order-tdv.validate-before-create');
            Route::post('agency-order-tdv/create-agency-order', [
                AgencyTDVOrderController::class, 'storeAgencyOrder'
            ])->name('agency-order-tdv.create-agency-order');
            Route::get('agency-order-tdv/validate-before-create', [
                AgencyTDVOrderController::class, 'getValidateBeforeCreate'
            ]);
            Route::resource('agency-order-tdv', AgencyTDVOrderController::class);

            // STORE ORDERS
            Route::get('store-orders/get-data-by-locality', [StoreOrderController::class, 'getDataByLocality'])->name('store-orders.get-data-by-locality');
            Route::post('store-orders/get-promotion-items', [StoreOrderController::class, 'getPromotionItems'])->name('store-orders.get-promotion-items');
            Route::post('store-orders/action/{type}', [StoreOrderController::class, 'order_action'])->name('store-orders.action');
            Route::resource('store-orders', StoreOrderController::class);
            // STORE ORDERS

            // REPORT
            Route::as('report.')->prefix('reports')->group(function () {
                Route::get('revenue-tdv/summary', [ReportRevenueTDVController::class, 'index'])->name('revenue.tdv');
                Route::post('revenue-tdv/summary/export', [ReportRevenueTDVController::class, 'summaryExport'])->name('revenue.tdv.export');
                Route::get('revenue-tdv/detail', [ReportRevenueTDVController::class, 'detail'])->name('revenue.tdv.detail');
                Route::get('revenue-tdv/export/asm-detail', [ReportRevenueTDVController::class, 'exportAsmDetail'])->name('revenue.export.asm_detail');

                Route::get('revenue-store/summary', [ReportRevenueStoreController::class, 'index'])->name('revenue.store.key_qc');
                Route::post('revenue-store/summary/export', [ReportRevenueStoreController::class, 'exportRevenue'])->name('revenue.store.key_qc.export');
            });
            // REPORT

            // LINE
            Route::resource('lines', LineController::class);
            Route::resource('line-store-change', LineStoreController::class);
            // LINE

            //AJAX
            Route::get('get-province/{type}', [ProvinceController::class, 'getByType'])->name('get-province-type');
            Route::post('get-locality', [OrganizationController::class, 'getLocality'])->name('get-locality');
            Route::post('get-agency', [OrganizationController::class, 'getAgency'])->name('get-agency');
            Route::post('get-locality-province', [OrganizationController::class, 'getLocalityByProvince'])->name('get-locality-province');
            Route::post('get-locality-user', [OrganizationController::class, 'getLocalityUser'])->name('get-locality-user');
            Route::post('get-user-by-locality', [OrganizationController::class, 'getUserByLocality'])->name('get-user-by-locality');
            Route::post('get-store-by-id', [StoreController::class, 'getStoreById'])->name('get-store-by-id');
            Route::post('get-store-duplicate', [StoreController::class, 'getStoreDuplicate'])->name('get-store-duplicate');
            Route::get('get-agency-code', [AgencyController::class, 'getAgencyCode'])->name('get-agency-code');
            Route::post('generation-store-code', [StoreController::class, 'generationCode'])->name('generation-store-code');
            Route::get('get-province', [AgencyController::class, 'getProvinces'])->name('get-province');
            Route::post('get-line-by-locality', [LineController::class, 'getByLocality'])->name('get-line-by-locality');
            Route::post('not-approve-store-changes', [StoreChangeController::class, 'notApprove'])->name('not-approve-store-changes');
            Route::post('not-approve-new-stores', [NewStoreController::class, 'notApprove'])->name('not-approve-new-stores');

            Route::prefix('report')->as('report.')->group(function () {
                Route::get('agency-orders', [ReportAgencyOrderController::class, 'index'])->name('agency-orders');
                Route::post('export-agency-orders', [ReportAgencyOrderController::class, 'export'])->name('agency-orders.export');
                Route::get('print-pxk', [AgencyOrderFileController::class, 'index'])->name('print_pxk');

                Route::get('pharmacy-revenue', [ReportRevenueStoreController::class, 'pharmacyRevenue'])->name('pharmacy-revenue');
                Route::get('pharmacy-revenue/detail/{id?}', [ReportRevenueStoreController::class, 'detailRevenue'])->name('pharmacy-revenue.detail');
                Route::post('export-pharmacy-revenue', [ReportRevenueStoreController::class, 'export'])->name('export-pharmacy-revenue');
            });

            Route::prefix('pdf')->as('pdf.')->group(function () {
                Route::get('open', [FileController::class, 'open'])->name('open');
            });

            // FILE
            Route::prefix('files')->as('file.')->group(function () {
                Route::get('{type}', [FileController::class, 'handle'])->name('action');
            });
            Route::get('get-store-locality', [StoreController::class, 'getByLocality'])->name('get-store-locality');

            //Posters
            Route::resource('posters', PosterController::class)->except('show');
            Route::resource('posters-agency', PosterPharmacyController::class)->except('show');
            Route::post('ajax-tdv-division', [OrganizationController::class, 'getUserByDivision'])->name('ajax-tdv-division');

            Route::post('posters-pharmacy/export', [PosterPharmacyController::class, 'exportPosterPharmacy'])->name('poster-pharmacy.export');
            //End Posters

            Route::get('system-variable', [SystemVariableController::class, 'setting'])->name('system-variable.setting');
            Route::post('system-variable', [SystemVariableController::class, 'update'])->name('system-variable.update');

            Route::get('/checkin/histories', [CheckinController::class, 'history'])->name('checkin.histories');
            Route::post('/checkin/review-forget-checkout-request', [ForgetCheckinController::class, 'updateStatus'])->name('checkin.review-forget-checkout');
            Route::get('/checkin/forget-checkout', [ForgetCheckinController::class, 'create'])->name('checkin.create-forget-checkout');
            Route::post('/checkin/forget-checkout/store', [ForgetCheckinController::class, 'store'])->name('checkin.forget-checkout.store');
            Route::get('/checkin/forget-checkout/{id}', [ForgetCheckinController::class, 'show'])->name('checkin.show-forget-checkout');
            Route::put('/checkin/forget-checkout/{id}', [ForgetCheckinController::class, 'update'])->name('checkin.forget-checkout.update');

            //Agency inventory
            Route::get('/agency-inventory', [ReportAgencyInventoryController::class, 'index'])->name('agency-inventory.index');
            Route::post('/agency-inventory/save', [ReportAgencyInventoryController::class, 'saveInventory'])->name('agency-inventory.save-inventory');
            Route::post('/agency-inventory/export', [ReportAgencyInventoryController::class, 'export'])->name('agency-inventory.export');
            //end agency inventory
            Route::resource('key-reward-order', KeyRewardOrderController::class);
            Route::get('key-reward-order/detail/{id}', [KeyRewardOrderController::class, 'orderDetail'])->name('key-reward-order.detail');
            Route::post('key-reward-order/change-status', [KeyRewardOrderController::class, 'change_status'])->name('key-reward-order.change-status');
            Route::post('key-reward-order/export', [KeyRewardOrderController::class, 'exportKeyRewardOrder'])->name('key-reward-order.export');

        });

    });
});

<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Models\District;
use App\Models\Organization;
use App\Models\ProductGroup;
use App\Models\ProductGroupPriority;
use App\Models\Province;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class Helper
{
    public static function successMessage(string $message)
    {
        self::setMessages([
            'type'    => 'success',
            'content' => $message
        ]);
    }

    public static function errorMessage(string $message)
    {
        self::setMessages([
            'type'    => 'error',
            'content' => $message
        ]);
    }

    public static function setMessages(array $params)
    {
        session()->flash('messages', $params);
    }

    public static function userRoleName(): string
    {
        $user = self::currentUser();
        return $user?->roles?->first()?->name ?: '';
    }

    public static function currentUser()
    {
        static $user;
        if (empty($user)) {
            $user = auth()->user();
            $user?->load('other_user');
        }
        return $user;
    }

    public static function userCan($perm): bool
    {
        $user = self::currentUser();
        return $user->can($perm);
    }

    public static function isTDV()
    {
        $roleName = self::userRoleName();
        return $roleName == 'TDV';
    }

    public static function correctPhone($phone)
    {
        // thay the cac ky tu khong phai so ve rong
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (Str::startsWith($phone, '84')) {
            return $phone;
        }
        if (Str::startsWith($phone, '0')) {
            return Str::replaceFirst('0', '84', $phone);
        }
        return $phone;
    }

    public static function menuData(): array
    {
        $user     = self::currentUser();
        $roleName = self::userRoleName();

        $user_tdv_items = [];
        if ($roleName == "TDV") {
            $user_tdv_items = config('menu_tdv');
        } else {
            $menu_items = config('menu.default');
        }

        $user_items = [
            'name'  => 'Tài khoản: ' . $user->name ?? '',
            'group' => true,
            'child' => [
                [
                    'name'    => $user->email ?? '',
                    'href'    => '#',
                    'icon'    => '<i data-feather="mail"></i>',
                    'display' => (bool)($user->email ?? '')
                ],
                [
                    'name'    => $user->username ?? '',
                    'href'    => '#',
                    'icon'    => '<i data-feather="user"></i>',
                    'display' => (bool)($user->username ?? '')
                ],
                [
                    'name'  => 'Đổi mật khẩu',
                    'route' => 'admin.password.reset.index',
                    'icon'  => '<i data-feather="key"></i>',
                ],
                [
                    'name'  => 'Đăng xuất',
                    'route' => 'admin.logout',
                    'icon'  => '<i data-feather="log-out"></i>',
                ],
            ]
        ];
        if ($user->other_user) {
            $user_items['child'][] = [
                'name' => 'Đổi TK: ' . $user->other_user->username,
                'href' => route('admin.users.switch', $user->other_user->id),
                'icon' => '<i data-feather="log-in"></i>',
            ];
        }

        $menu_items[] = $user_tdv_items;
        $menu_items[] = $user_items;
        foreach ($menu_items as &$item) {
            $item = self::validMenu($item, $user);
        }
        unset($item);
        return array_filter($menu_items);
    }

    static function validMenu($item, $user)
    {
        if (isset($item["display"]) && $item["display"] === false) {
            return false;
        }
        if (!empty($item['perm'])) {
            if (!$user) {
                return false;
            }
            $perms = (array)$item['perm'];
            if (!$user->hasAnyPermission($perms)) {
                return false;
            }
        }

        if (isset($item["display"]) && $item["display"] === false) {
            return false;
        }
        $item['active'] = false;
        if (!empty($item['route'])) {
            $item['href'] = route($item['route']);
            if (request()->route()->getName() == $item['route']) {
                $item['active'] = true;
            }
        }
        if (!empty($item['href']) && !$item['active'] && !empty($item['pattern'])) {
            foreach ($item['pattern'] as $pattern) {
                $active = self::checkPattern($pattern, $item['href']);
                if ($active) {
                    $item['active'] = true;
                    break;
                }
            }
        }
        if (!empty($item['child'])) {
            foreach ($item['child'] as &$child_item) {
                $child_item = self::validMenu($child_item, $user);
            }
            $item['child'] = array_filter($item['child']);
            if ($item['child'] && collect($item['child'])->where('active', true)->count()) {
                $item['class'] = 'open';
            }
        }
        if (isset($item['group']) && empty($item['child'])) {
            return false;
        } elseif (empty($item['child']) && empty($item['href'])) {
            return false;
        }
        return $item;
    }

    static function checkPattern(string $pattern, string $path): bool
    {
        $pattern = preg_replace('@^https?://@', '*', URL::to($pattern));
        $path    = preg_replace('@^https?://@', '', request()->fullUrl());

        return Str::is(trim($pattern), trim($path));
    }

    public static function nullPaginator(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
    }


    public static function getPeriodByProductType($productTypes)
    {
        $productTypePeriods = [];
        foreach ($productTypes as $_id => $_value) {
            $periodOfYear             = $_value['period_of_year'];
            $productTypePeriods[$_id] = self::periodOptions(null, $periodOfYear);
        }
        return $productTypePeriods;
    }

    static function getPeriodName($time, $periodOfYearInput = 6)
    {
        if ($time == null) {
            return 'Mãi mãi';
        }
        $timeConvert = Carbon::parse($time);
        $year        = Carbon::parse($time)->format('Y');

        $start_of_year = Carbon::parse($year . '-01-01');

        for ($i = 1; $i <= $periodOfYearInput; $i += 1) {
            $periodName = $i;

            $_started_at = $i == 1 ? $start_of_year->startOfMonth() : $start_of_year->addDay()->startOfMonth();

            if ($periodOfYearInput % 12 == 0) {
                $_ended_at = $start_of_year->endOfMonth();
            } else {
                $_ended_at = $start_of_year->addMonth()->endOfMonth();
            }

            if ($_started_at->getTimestamp() >= $timeConvert->getTimestamp() && $timeConvert->getTimestamp() <= $_ended_at->getTimestamp()) {
                return sprintf('Chu kỳ %s / %s', str_pad($periodName, 2, '0', 0), $start_of_year->year);
            }
        }

        return null;

        //dd($periods);
        //die();
        //return $periods;
    }

    static function periodOptions($year = null, $periodOfYearInput = 6)
    {
        $totalYear    = 3;
        $periodOfYear = $periodOfYearInput * $totalYear;
        if (!$year) {
            $year = now()->subYear()->format('Y');
        }
        $periods       = [];
        $start_of_year = Carbon::parse($year . '-01-01');

        $y = 1;
        for ($i = 1; $i <= $periodOfYear; $i += 1) {

            if (is_int(($i - 1) / $periodOfYearInput)) {
                $y = 1;
            }

            $periodName = $y;

            $_started_at                    = $i == 1 ? $start_of_year->startOfMonth() : $start_of_year->addDay()->startOfMonth();
            $period                         = [
                'name'       => sprintf('Chu kỳ %s / %s', str_pad($periodName, 2, '0', 0), $start_of_year->year),
                'year'       => $start_of_year->year,
                'started_at' => '',
                'ended_at'   => '',
            ];
            $period['started_at']           = $_started_at->format('Y-m-d');
            $period['started_at_timestamp'] = $_started_at->getTimestamp();

            if ($periodOfYearInput % 12 == 0) {
                $_ended_at = $start_of_year->endOfMonth();
            } else {
                $_ended_at = $start_of_year->addMonth()->endOfMonth();
            }

            $period['ended_at']           = $_ended_at->format('Y-m-d');
            $period['ended_at_timestamp'] = $_ended_at->getTimestamp();
            $period['period']             = $i;
            $period['periodYear']         = $i . '-' . $year;

            $periods[$i] = $period;
            $y++;
        }

        //die();
        return $periods;
    }

    static function periodOptionMultipleYears($minYear = null, $maxYear = null, $minCurrent = false): array
    {
        $arrPeriods   = [];
        $minYear      = $minYear ?? now()->format('Y');
        $maxYear      = $maxYear ?? $minYear;
        $minTimestamp = 0;
        if ($minCurrent) {
            $monthPeriods = ProductGroupPriority::MONTH_PERIOD;
            $numPeriod    = $monthPeriods[now()->format('n')];
            $firstMonth   = collect($monthPeriods)->filter(function ($value) use ($numPeriod) {
                return $value == $numPeriod;
            })->keys()->min();
            $minTimestamp = now()->setMonth($firstMonth)->setDay(1)->setHour(0)->setMinute(0)->setSecond(0)->timestamp;
        }

        for ($i = $minYear; $i <= $maxYear; $i += 1) {
            $periods = self::periodOptions($i);
            if ($minTimestamp) {
                $periods = array_filter($periods, function ($item) use ($minTimestamp) {
                    return $item['started_at_timestamp'] >= $minTimestamp;
                });
            }

            foreach ($periods as $period) {
                $arrPeriods[$period['period'] . '-' . $period['year']] = $period;
            }
        }
        return $arrPeriods;
    }

    static public function getPeriodByDate($date_string, $periodOfYear = 6)
    {
        //echo "$date_string <brn>";
        $date           = Carbon::parse($date_string);
        $start_of_month = $date->startOfMonth()->format('Y-m-d');
        $end_of_month   = $date->endOfMonth()->format('Y-m-d');
        $periods        = self::periodOptions($date->format('Y'), $periodOfYear);

        foreach ($periods as $period) {
            if ($start_of_month == $period['started_at'] || $end_of_month == $period['ended_at']) {
                return $period['name'];
            }
        }

        return '-';
    }

    static public function getPeriodByDateAndProductType($date_string, $productType = null)
    {
        $date              = Carbon::parse($date_string);
        $start_of_month    = $date->startOfMonth()->format('Y-m-d');
        $end_of_month      = $date->endOfMonth()->format('Y-m-d');
        $countPeriodOfYear = ProductGroup::PRODUCT_TYPES[$productType]['period_of_year'] ?? null;
        $periods           = self::periodOptions($date->format('Y'), $countPeriodOfYear);

        foreach ($periods as $period) {
            if ($start_of_month == $period['started_at'] || $end_of_month == $period['ended_at']) {
                return $period;
            }
        }

        return '-';
    }

    static public function getPeriodByMonth(int $month)
    {
        $month_period = ProductGroupPriority::MONTH_PERIOD;
        foreach ($month_period as $key => $value) {
            if ($month == $key) return $value;
        }
        return null;
    }

    static function getUserOrganization($user = null): array
    {
        static $cache;
        if (!$user) {
            $user = self::currentUser();
        }
        if (!empty($cache[$user->id])) {
            return $cache[$user->id];
        }
        if (!$user->organizations) {
            $user->load('organizations');
        }
        $output           = [];
        $role_name        = $user?->roles[0]?->name;
        $all_organization = Organization::allActive();
        foreach ($user->organizations as $item) {
            $output[$item->type][$item->id] = $item->id;
            if ($item->type == Organization::TYPE_KHU_VUC && $role_name != 'TDV') {
                foreach ($all_organization->where('parent_id', $item->id) as $child) {
                    $output[$child->type][$child->id] = $child->id;
                }
            }
            self::_getParents($output, $item->parent_id, $all_organization);
        }
        $cache[$user->id] = $output;
        return $output;
    }

    static function _getParents(&$output, $id, $lists): void
    {
        $item = $lists->where('id', $id)->first();
        if ($item) {
            $output[$item->type][$item->id] = $item->id;
            if ($item->parent_id) {
                self::_getParents($output, $item->parent_id, $lists);
            }
        }
    }

    static function makeTreeOrganizationData(&$results, &$organizations, $parentId, $idDataAllows = [], $haveRelationship = true): array
    {
        foreach ($organizations as $key => $organization) {
            if (is_array($idDataAllows)) {
                if (in_array($organization['id'] ?? null, $idDataAllows)) {
                    $children = [];
                    if (!$haveRelationship) {
                        $results[] = $organization;
                    } else if ($organization['parent_id'] == $parentId) {
                        unset($organizations[$key]);
                        $organization['children'] = self::makeTreeOrganizationData(
                            $children,
                            $organizations,
                            $organization['id'],
                            $idDataAllows,
                            $haveRelationship
                        );
                        $results[]                = $organization;
                    }
                }
            }
        }

        return $results ?? [];
    }

    static function makeOptionsTreeOrganization(
        &$html,
        $organizationData,
        $activeType,
        $level = 0,
        $selected = null,
        $userOrganization = [],
        $excludeTypes = [],
    )
    {
        $isActive = false;
        if (in_array($organizationData['type'] ?? null, $activeType)) {
            $isActive = true;
        }

        $isSelected = false;

        if (is_array($selected)) {
            $isSelected = in_array($organizationData['id'], $selected);
        } else {
            $isSelected = $selected == $organizationData['id'];
        }

        $check_type = !in_array($organizationData['type'] ?? null, $excludeTypes);
        $check_user = true;
        if ($userOrganization && isset($userOrganization[$organizationData['type']]) && !in_array($organizationData['id'], $userOrganization[$organizationData['type']])) {
            $check_user = false;
        }

        if ($check_type && $check_user) {
            $newOption = '<option '
                . ($isActive ? '' : 'disabled') . ' '
                . ($isSelected ? ' selected="selected"' : '') . ' '
                . ' data-level="' . $level . '" data-type="' . ($organizationData['type'] ?? '') . '" value="' . ($organizationData['id'] ?? '') . '">' . ' '
                . ($organizationData['name'] ?? '') . ' '
                . '</option>';

            $html .= $newOption;
        }

        if (isset($organizationData['children']) && count($organizationData['children'])) {
            if (isset($setup['prefix'])) {
                $setup['prefix'] .= $setup['prefix'];
            }

            $level++;
            foreach ($organizationData['children'] as $child) {
                self::makeOptionsTreeOrganization($html, $child, $activeType, $level, $selected, $userOrganization, $excludeTypes);
            }

        }
    }

    // active type is children type of
    //    const TYPE_TONG_CONG_TY = 1;
    //    const TYPE_CONG_TY      = 2;
    //    const TYPE_MIEN         = 3;
    //    const TYPE_KHU_VUC      = 4;
    //    const TYPE_DIA_BAN      = 5;
    //    const TYPE_KHAC         = 6;
    static public function getTreeOrganization(
        $currentUser = null,
        $activeTypes = [],
        $excludeTypes = [],
        $hasRelationship = false,
        $setup = [
            'multiple'    => false,
            'name'        => '',
            'class'       => '',
            'id'          => '',
            'attributes'  => '',
            'selected'    => null,
            'placeholder' => null
        ],
        $defaultOptionText = null,
    )
    {
        $organizations    = Organization::allActive()->toArray();
        $userOrganization = [];
        if ($currentUser) {
            $user             = self::currentUser();
            $userOrganization = self::getUserOrganization($user);
        }

        $isMultipleSelect = $setup['multiple'] ?? false;
        $nameSelect       = $setup['name'] ?? '';
        $idSelect         = $setup['id'] ?? '';
        $classSelect      = $setup['class'] ?? '';
        $attributes       = $setup['attributes'] ?? '';
        $selected         = $setup['selected'] ?? null;
        $placeholder      = $setup['placeholder'] ?? null;

        $idDataAllows = array_column($organizations, 'id');
        //TODO get id Organization can view

        $organizationData = self::makeTreeOrganizationData(
            $results,
            $organizations,
            0,
            $idDataAllows,
            $hasRelationship,
        );

        $html    = "<select id='$idSelect'
                    class='form-control has-select2 form-select $classSelect'
                    " . ($isMultipleSelect ? 'multiple' : '') . "
                    name='$nameSelect'
                    data-select2-id='$nameSelect'
                    placeholder='" . $placeholder . "'
                    $attributes
                    >";
        $html    .= "<option value=''  >- " . ($defaultOptionText ?? "Khu vực") . " -</option>";
        $options = "";

        foreach ($organizationData as $data) {
            self::makeOptionsTreeOrganization($options, $data, $activeTypes, 0, $selected, $userOrganization, $excludeTypes);
        }
        $html .= $options;

        $html .= '</select>';

        return $html;
    }

    public static function getCurrentPermissions()
    {
        return request()->user()->getAllPermissions()->pluck('name')->toArray();
    }

    public static function formatPrice($price, $unit = ''): ?string
    {
        if ($unit) {
            $unit = "<sup>$unit</sup>";
        }
        if (!$price) {
            return null;
        }

        $result = null;
        if (!empty($price)) $result = number_format($price, 0, ',', '.') . $unit;

        return $result;
    }

    public static function settingView($name, $default = 20, $style = '', $items = [10, 20, 50, 100, 500])
    {
        $item_default = self::getPerPage($name, $default);
        return view('snippets.per-page', compact('items', 'item_default', 'name', 'style'))->render();
    }

    public static function getPerPage($name, $default = 20)
    {
        return Cookie::get($name, $default);
    }

    public static function getMonthPrevious($year, $month)
    {
        $previousMonth = null;
        $previousYear  = null;

        if ($month == 1) {
            $previousYear  = $year - 1;
            $previousMonth = 12;
        } else {
            $previousYear  = $year;
            $previousMonth = $month - 1;
        }

        return [
            'year'  => $previousYear,
            'month' => $previousMonth
        ];
    }

    /**
     * @param $month
     * @param $year
     * @return string[]
     */
    public static function getPreviousRevenue($date): array
    {
        $date  = Carbon::create($date);
        $month = $date->month;
        $year  = $date->year;

        if ($month == 1 || $month == 2) {
            $previousYear = $year - 1;
            return [
                'from' => "$previousYear-11-01",
                'to'   => "$previousYear-12-31",
            ];
        } else {
            $previousMonthFrom = $month % 2 == 0 ? $month - 3 : $month - 2;
            $previousMonthTo   = $previousMonthFrom + 1;

            $previousMonthFrom = str_pad($previousMonthFrom, 2, "0", STR_PAD_LEFT);
            $previousMonthTo   = str_pad($previousMonthTo, 2, "0", STR_PAD_LEFT);

            return [
                'from' => Carbon::create($year, $previousMonthFrom)->format('Y-m-d'),
                'to'   => Carbon::create($year, $previousMonthTo)->endOfMonth()->format('Y-m-d'),
            ];
        }
    }

    public static function getCurrentRevenue($date): array
    {
        $date        = Carbon::create($date);
        $monthAgo    = (clone $date)->subMonths();
        $monthFuture = (clone $date)->addMonths();

        $currentMonth = $date->month;

        return [
            'from' => $currentMonth % 2 == 0 ? $monthAgo->format('Y-m-d') : $date->format('Y-m-d'),
            'to'   => $currentMonth % 2 == 0 ? $date->endOfMonth()->format('Y-m-d') : $monthFuture->endOfMonth()->format('Y-m-d'),
        ];
    }

    public static function calculatePercent($number1, $number2)
    {
        if (!$number2) {
            return null;
        }

        return number_format(($number1 / $number2) * 100, 2, ',') . '%';
    }

    public static function getPrefixCode($provinceId, $districtId): string
    {
        static $cache;
        $key = $provinceId . '_' . $districtId;
        if (!empty($cache[$key])) {
            return $cache[$key];
        }
        $district    = District::query()->find($districtId);
        $province    = Province::query()->find($provinceId);
        $cache[$key] = $district?->district_code ?: $province->province_code;

        return $cache[$key];
    }

    public static function getImagePath($imagePath): string
    {
        return Storage::exists('public/' . $imagePath) ? asset('storage/' . $imagePath) : asset('images/default_image.jpg');
    }

    public static function defaultMonthFromToDate(): array
    {
        return [
            'from' => now()->setDay(1)->format('Y-m-d'),
            'to'   => now()->format('Y-m-d')
        ];
    }

    public static function arrayToAttribute(array $attributes): string
    {
        $output = '';
        foreach ($attributes as $key => $value) {
            $output .= $key . '="' . $value . '" ';
        }
        return $output;
    }

    public static function getExcelNameFromNumber($num)
    {
        $numeric = $num % 26;
        $letter  = chr(65 + $numeric);
        $num2    = intval($num / 26);
        if ($num2 > 0) {
            return self::getExcelNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    public static function getWeekdayNumber($date = null)
    {
        $date = $date ?? now();
        return Carbon::parse($date)->dayOfWeek;
    }

    public static function getRangeYear($startYear = null, $endYear = null)
    {
        $startYear = $startYear ?? now()->format('Y');
        $endYear   = $endYear ?? now()->format('Y');
        $newArray  = [];
        $arrYear   = range($startYear, $endYear);
        foreach ($arrYear as $year) {
            $newArray[$year] = $year;
        }

        return $newArray;
    }

    public static function getSql($query): string
    {
        $bindings = collect($query->getBindings())->map(function ($item) {
            if (!is_numeric($item) && is_string($item)) {
                $item = "'" . $item . "'";
            }
            return $item;
        })->toArray();
        return Str::replaceArray('?', $bindings, $query->toSql());
    }

    static function convertSpecialCharInput($requestInput)
    {
        if (is_string($requestInput)) {
            //!, @, #,$,%, ^, &, *
            $requestInput = trim($requestInput, '!');
            $requestInput = trim($requestInput, '@');
            $requestInput = trim($requestInput, '#');
            $requestInput = trim($requestInput, '$');
            $requestInput = trim($requestInput, '%');
            $requestInput = trim($requestInput, '^');
            $requestInput = trim($requestInput, '&');
            $requestInput = trim($requestInput, '*)');
        }

        return $requestInput;
    }

    public static function getDistanceBetweenTwoPoints($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'm')
    {
        $theta    = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)))
            + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;

        switch ($unit) {
            case 'miles':
                break;
            case 'km':
                $distance = $distance * 1.609344;
            case 'm':
                $distance = $distance * 1.609344 * 1000;
        }

        return round($distance, 2);
    }
}

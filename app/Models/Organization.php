<?php

namespace App\Models;

class Organization extends BaseModel
{
    protected $fillable = [
        'name',
        'parent_id',
        'type',
        'province_id',
        'status',
        'created_by',
        'updated_by',
    ];

    const TYPE_TONG_CONG_TY = 1;
    const TYPE_CONG_TY      = 2;
    const TYPE_MIEN         = 3;
    const TYPE_KHU_VUC      = 4;
    const TYPE_DIA_BAN      = 5;
    const TYPE_KHAC         = 6;

    const ORDER_OF_ORGANIZATION = [
        0 => self::TYPE_TONG_CONG_TY,
        1 => self::TYPE_CONG_TY,
        2 => self::TYPE_MIEN,
        3 => self::TYPE_KHU_VUC,
        4 => self::TYPE_DIA_BAN,
    ];

    const TYPE_TEXTS = [
        self::TYPE_TONG_CONG_TY => 'Tổng công ty',
        self::TYPE_CONG_TY      => 'Công ty',
        self::TYPE_MIEN         => 'Miền',
        self::TYPE_KHU_VUC      => 'Khu vực',
        self::TYPE_DIA_BAN      => 'Địa bàn',
        self::TYPE_KHAC         => 'Khác',
    ];

    const CACHE_KEY_ALL_ACTIVE = 'org_all_active';

    function province()
    {
        return $this->belongsTo(Province::class);
    }

    function districts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(District::class, 'organization_districts')->withPivot('organization_id', 'district_id');
    }

    function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_organization')->where('status', 1);
    }

    function getTypeNameAttribute()
    {
        return self::TYPE_TEXTS[$this->type] ?? '';
    }

    function getProvinceNameAttribute()
    {
        return $this->province->province_name_with_type ?? '';
    }

    function getDistrictNamesAttribute()
    {
        if ($this->districts->isNotEmpty()) {
            return $this->districts->pluck('district_name_with_type')->toArray();
        }
        return [];
    }

    static function sortLists(int $id = 0, int $level = 0, \Illuminate\Support\Collection $lists = null): \Illuminate\Support\Collection
    {
        if (is_null($lists)) {
            $lists = self::query()
                ->with(['users', 'districts', 'province'])
                ->get();
        }
        $results = collect([]);
        $items   = $lists->where('parent_id', $id)->sortBy('name');
        foreach ($items as $_item) {
            $_item->level = $level;
            $results->add($_item);
            $children = self::sortLists($_item->id, $level + 1, $lists);
            if ($children->isNotEmpty()) {
                $results = $results->merge($children);
            }
        }

        return $results;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeLocality($query)
    {
        return $query->where('organizations.type', self::TYPE_DIA_BAN);
    }

    public function scopeDivision($query)
    {
        return $query->where('organizations.type', self::TYPE_KHU_VUC);
    }

    public function agency(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Agency::class, 'agency_organizations')
            ->where('status', Agency::STATUS_ACTIVE);
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'organization_id', 'id')
            ->where('status', Agency::STATUS_ACTIVE);
    }

    // Lay ra danh sach nhung dia ban theo cay so do
    static function getLocalityByOrganization(int $id, $i = 0): \Illuminate\Support\Collection
    {
        $results = collect([]);
        if ($i == 0) {
            $tmp = self::find($id);
            if ($tmp->type == self::TYPE_DIA_BAN) {
                $results->add($tmp);
                return $results;
            }
        }
        $i++;

        $lists = self::sortLists($id);
        foreach ($lists as $item) {
            if ($item->type == self::TYPE_DIA_BAN) $results->add($item);
        }
        return $results;
    }

    // Lay danh sach dia ban theo tinh/ thanh pho
    static function getLocalityByProvince($province_id)
    {
        $province_id = $province_id ?? 0;
        $result      = self::query()->where('province_id', $province_id)->where('type', Organization::TYPE_KHU_VUC)->get();
    }

    // Lay danh sach user theo khu vuc
    static function getUserByLocality($organization_id)
    {
        $org = self::find($organization_id);
        return $org->users()->get();
    }

    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

    public function childs()
    {
        return $this->hasOne(self::class, 'parent_id', 'id');
    }

    static function allActive()
    {
        return cache()->remember(self::CACHE_KEY_ALL_ACTIVE, now()->addHour(), function () {
            return self::active()->get();
        });
    }

    static function getChildren($id): \Illuminate\Support\Collection
    {
        $lists = self::allActive();
        $ids   = (array)$id;
        $items = collect([]);
        foreach ($ids as $_id) {
            $items = $items->merge(self::_getChildren($_id, $lists));
        }
        return $items->unique();
    }

    static function _getChildren($id, $lists): \Illuminate\Support\Collection
    {
        $items = collect([]);
        $item  = $lists->where('id', $id)->first();
        if (!$item) {
            return $items;
        }
        if ($item->type == self::TYPE_DIA_BAN) {
            $items->add($item);
        } else {
            foreach ($lists->where('parent_id', $id) as $child) {
                if ($child->type == self::TYPE_DIA_BAN) {
                    $items->add($child);
                } else {
                    $items = $items->merge(self::_getChildren($child->id, $lists));
                }
            }
        }
        return $items;
    }
}

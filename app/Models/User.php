<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Organization;

class User extends Authenticatable
{
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'username',
        'password',
        'phone',
        'dob',
        'position',
        'agency_id',
        'status',
        'last_login_ip',
        'last_login_at',
        'change_pass',
        'created_by',
        'updated_by',
        'parent_id',
        'image',
        'base_code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'dob' => 'date',
    ];
    protected $with = ['organizations', 'product_groups'];

    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_TEXT     = [
        self::STATUS_ACTIVE   => "Hoạt động",
        self::STATUS_INACTIVE => "Ngừng HĐ",
    ];

    const ROLE_Admin      = 'Admin';
    const ROLE_BOD        = 'BOD';
    const ROLE_SALE_ADMIN = 'SaleAdmin';
    const ROLE_BM         = 'BM';
    const ROLE_ASM        = 'ASM';
    const ROLE_TDV        = 'TDV';
    const ROLE_MARKETING  = 'Marketing';
    const ROLE_WEB        = 'web';
    const ROLE_TEST       = 'Test';
    const TDV_NULL        = 'Chưa có TDV'; // Dung o danh sach tuyen

    function other_user()
    {
        return $this->hasOne(User::class, 'id', 'parent_id');
    }

    function organizations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'user_organization');
    }

    function product_groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductGroup::class, 'user_product_group');
    }

    public function checkin(): HasMany
    {
        return $this->hasMany(Checkin::class, 'created_by', 'id');
    }

    public function agency()
    {
        return $this->hasOne(Agency::class, 'id', 'agency_id');
    }

    function avatar(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(File::class, 'id', 'image');
    }
}

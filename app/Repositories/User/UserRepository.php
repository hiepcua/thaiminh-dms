<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @return mixed|\Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return new User();
    }

    public function paginate(int $limit, array $with = [], array $args = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $with[] = 'roles';
        return $this->model::query()
            ->with($with)
            ->when($args['keyword'] ?? '', function ($query) use ($args) {
                $query->where(function ($q1) use ($args) {
                    return $q1->where('email', 'like', '%' . $args['keyword'] . '%')
                        ->orWhere('username', 'like', '%' . $args['keyword'] . '%')
                        ->orWhere('name', 'like', '%' . $args['keyword'] . '%');
                });
            })
            ->when(($args['status'] ?? null) !== null, function ($query) use ($args) {
                $query->where('status', '=', $args['status']);
            })
            ->when($args['role_id'] ?? '', function ($query) use ($args) {
                $query->whereRelation('roles', 'id', '=', $args['role_id']);
            })
            ->orderByDesc('updated_at')
            ->paginate(20);
    }

    function organizationExists(array $organization_ids): bool
    {
        return $this->model::query()->whereHas('organizations', function ($query) use ($organization_ids) {
            $query->whereIn('id', $organization_ids);
        })->exists();
    }

    function getByOrganization(array $organization_ids)
    {
        return $this->model::query()->whereHas('organizations', function ($query) use ($organization_ids) {
            $query->whereIn('id', $organization_ids);
        })->get();
    }

    function getByRole($roleName)
    {
        return $this->model::role($roleName)->where('status', 1)->orderByDesc('created_at')->get();
    }

    function getUserById($id)
    {
        return $this->model::find($id);
    }

    public function getByOrganizations(array $organization_ids, string $roleName = '', $with = []): \Illuminate\Database\Eloquent\Collection|array
    {
        $query = $this->model::with($with);
        if ($roleName) {
            $query->role($roleName);
        }
        $query->whereHas('organizations', function ($query) use ($organization_ids) {
            $query->whereIn('id', $organization_ids);
        });

        return $query->get();
    }


    public function changeAgency($agencyId, $userId = 0)
    {
        $this->model::query()
            ->where('agency_id', $agencyId)
            ->when($userId, function ($query) use ($userId) {
                $query->where('id', '!=', $userId);
            })
            ->update(['agency_id' => 0]);
        if ($userId) {
            $this->model::query()->where('id', $userId)->update(['agency_id' => $agencyId]);
        }
    }
}

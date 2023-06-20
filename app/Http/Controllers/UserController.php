<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\CreateEditUser;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected $repository;
    protected $service;

    public function __construct(
        UserRepositoryInterface $repository,
        UserService             $service
    )
    {
        $this->repository = $repository;
        $this->service    = $service;
        $this->middleware('can:xem_danh_sach_nguoi_dung')->only('index');
        $this->middleware('can:them_nguoi_dung')->only('store');
        $this->middleware('can:sua_nguoi_dung')->only('edit', 'update');
        $this->middleware('can:switch_user')->only('switchUserChange');
    }

    public function index(Request $request)
    {
        $search        = $request->get('search', []);
        $user_inactive = User::query()->where('status', 0)->count();
        $roles         = Role::all();
        $users         = $this->repository->paginate(20, ['avatar'], $search);
        return view('pages.users.index', compact('users', 'roles', 'user_inactive'));
    }

    public function create(Request $request)
    {
        $user                  = $this->repository->getModel();
        $user_id               = 0;
        $formOptions           = $this->service->formOptions($user);
        $formOptions['action'] = route('admin.users.store');
        $default_values        = $formOptions['default_values'];

        $view_data = compact('formOptions', 'default_values', 'user_id');

        return view('pages.users.add-edit', $view_data);
    }

    public function store(CreateEditUser $request)
    {
        $inputs             = $request->all();
        $inputs['password'] = Hash::make($inputs['password']);

        $this->service->create($inputs);

        return redirect(route('admin.users.index'))
            ->with('successMessage', 'Thêm mới thành công.');
    }

    public function edit(Request $request, int $user_id)
    {
        $user                  = $this->repository->find($user_id, ['roles', 'product_groups']);
        $formOptions           = $this->service->formOptions($user);
        $formOptions['action'] = route('admin.users.update', $user_id);
        $default_values        = $formOptions['default_values'];

        $view_data = compact('formOptions', 'default_values', 'user_id');

        return view('pages.users.add-edit', $view_data);
    }

    public function update(CreateEditUser $request, int $user_id)
    {
        $this->service->update($user_id, $request->all());

        return redirect(route('admin.users.index'))
            ->with('successMessage', 'Thay đổi thành công.');
    }

    public function redirectToProvider($provider)
    {
        $url_previous = URL::previous();
        $url_login    = URL::to(route('admin.login'));
        $url_index    = URL::to('/');

        if ($url_previous != $url_login) {
            session()->put('pre_url', $url_previous);
        } else {
            session()->put('pre_url', $url_index);
        }
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        $authUser = $this->findOrCreateUser($user, $provider);
        if (!$authUser) {
            return redirect(route('admin.login'))->withErrors(['email' => 'Người dùng không tồn tại trong hệ thống, bạn liên hệ với quản trị viên để được hỗ trợ.']);
        }

        Auth::guard()->login($authUser, true);
        return redirect(Session::get('pre_url'));
    }

    public function findOrCreateUser($user, $provider)
    {
        $user = $this->repository->getModel()::where('email', $user->email)
            ->where('status', 1)
            ->first();
        if (!$user) {
            return false;
        }

        return $user;
    }

    public function switchUser(Request $request, int $user_id)
    {
        $user = Helper::currentUser();
        if ($user && $user->other_user && $user->other_user->id == $user_id) {
            Auth::login($user->other_user, true);

            if ($user->other_user->hasRole(User::ROLE_TDV)) {
                return redirect()->route('admin.tdv.dashboard');
            }

            return redirect()->route('admin.dashboard.index')
                ->with('successMessage', 'Đổi tài khoản thành công.');
        }

        abort(403, 'Bạn không có quyền thực hiện chức năng này.');
    }

    public function switchUserChange(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string']
        ]);
        $email       = $request->input('email');
        $currentUser = Helper::currentUser();
        if ($email == $currentUser->email) {
            return back()->with('errorMessage', 'Bạn đang ở tài khoản này.');
        }
        $authUser = User::where(function ($query) use ($email) {
            if (str_contains($email, '@')) {
                $query->where('email', $email);
            } else {
                $query->where('username', $email);
            }
        })->where('status', User::STATUS_ACTIVE)->first();
        if ($authUser) {
            Auth::login($authUser, true);
            session()->put('switch_back', $currentUser->id);

            return back()->with('successMessage', 'Đổi tài khoản thành công.');
        } else {
            return back()->with('errorMessage', 'Không tìm thấy tài khoản này.');
        }
    }

    public function switchUserBack(Request $request, int $userId)
    {
        $switchBack = session()->get('switch_back');
        if (!$switchBack || $switchBack != $userId) {
            abort(403);
        }
        $authUser = User::where('id', $userId)->where('status', User::STATUS_ACTIVE)->first();
        if ($authUser) {
            session()->forget('switch_back');
            Auth::login($authUser, true);
            return back()->with('successMessage', 'Đổi tài khoản thành công.');
        }
        return back()->with('errorMessage', 'Không tìm thấy tài khoản này.');
    }
}

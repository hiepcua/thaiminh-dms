<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request)
    {
        return view('auth.reset-password');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'string', 'min:8',
                function ($attribute, $value, $fail) {
                    $value = (string)$value;
                    if (!preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất một chữ hoa và một chữ thường.');
                    } elseif (!preg_match('/\pN/u', $value)) {
                        $fail('Mật khẩu phải chứa ít nhất một số.');
                    }
//                    elseif (!Container::getInstance()->make(UncompromisedVerifier::class)->verify([
//                        'value'     => $value,
//                        'threshold' => 0,
//                    ])) {
//                        $fail('Mật khẩu bạn cung cấp đã bị rò rỉ dữ liệu. Vui lòng chọn một mật khẩu khác.');
//                    }
                }],
        ]);

        $password          = $request->input('password');
        $user              = Helper::currentUser();
        $user->password    = Hash::make($password);
        $user->change_pass = 0;
        $user->setRememberToken(Str::random(60));
        $user->update();

        Auth::guard()->login($user);

        return redirect(route('admin.dashboard.index'));
    }
}

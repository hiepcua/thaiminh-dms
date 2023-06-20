<?php

namespace App\Http\Requests;

use App\Repositories\PosterStoreRegister\PosterStoreRegisterRepository;
use Illuminate\Foundation\Http\FormRequest;

class StorePosterStoreRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    function __construct(
        PosterStoreRegisterRepository $posterStoreRegisterRepository,
    )
    {
        $this->posterStoreRegisterRepository = $posterStoreRegisterRepository;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $reg_id = $this->route()->parameter('store_poster_id');
        $route  = $this->route()->getName();
        $check  = $this->posterStoreRegisterRepository->getAllImages($reg_id, 'image_acceptance');

        $rules = [
            'title' => ['required'],
        ];
        if ($route == 'admin.tdv.image-acceptance-store.post') {
            if (count($check) == 0) {
                $rules['image_acceptance'] = ['required'];
            }
        } else {
            $rules['image_poster'] = ['required'];
        }
        return $rules;
    }

    public function attributes()
    {
        return [
            'title'            => 'Tiêu đề',
            'image_poster'     => 'Ảnh poster',
            'image_acceptance' => 'Ảnh NT',
        ];
    }
}

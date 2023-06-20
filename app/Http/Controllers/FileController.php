<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function handle(Request $request, $type)
    {
        try {
            if (!method_exists($this, $type)) {
                abort(404);
            }

            $nameFile = $request->get('nameFile', null);
            $folder   = $request->get('folder', null);
            if (Helper::userCan($folder)) {
                if (File::exists(Storage::path("$folder/$nameFile"))) {
                    return $this->$type("$folder/$nameFile");
                } else {
                    return redirect()->back()->with('errorMessage', 'File không tồn tại');
                }
            } else {
                return redirect()->back()->with('errorMessage', 'Bạn không có quyên xem file.');
            }
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " error: " . $e->getMessage());
            Log::error($e);

            return redirect()->back()->with('errorMessage', 'Đã có lỗi xảy ra. Vui lòng thử lại sau.');
        }
    }

    public function download($url)
    {
        return Storage::download($url);
    }

    public function open($url)
    {
        $url      = Storage::path($url);
        $contents = File::get($url);
        $response = response($contents);
        $response->header('Content-Type', mime_content_type($url));

        return $response;
    }
}

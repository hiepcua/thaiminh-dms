<?php

namespace App\Services;

use App\Repositories\File\FileRepositoryInterface;
use App\Models\File;
use Illuminate\Http\UploadedFile;

class FileService extends BaseService
{
    protected $repository;

    public function __construct(FileRepositoryInterface $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    public function setModel()
    {
        return new File();
    }

    public function saveFile(UploadedFile $file, $folder = 'images', $public = true)
    {
        $options = [];
        if ($public) {
            $options['disk'] = 'public';
        }
        $path = $file->store($folder, $options);

        $file = $this->repository->create([
            'mime_type' => $file->getMimeType(),
            'name'      => $file->getClientOriginalName(),
            'source'    => $path
        ]);
        return $file->id;
    }
}

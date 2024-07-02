<?php

namespace App\DomainUtils\BaseBLL;

use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\Filesystem;

trait BaseBLLFileUtils
{
    public function prepareFileFields(&$data, $fileFields, $model = null)
    {

        $fileFieldsToDelete = [];

        foreach ($fileFields as $fileField) {
            if (isset($data[$fileField['field']]) && $data[$fileField['field']]) {
                $field = $fileField['field'];
                $path = $fileField['path'];

                if ($model && $model->$field) {
                    $fileFieldsToDelete[] = $field;
                }

                $data[$field] = $this->storeFile($data[$field], $path);
            }
        }

        if ($model && $fileFieldsToDelete) {
            $this->deleteFilesIfExist($model, $fileFieldsToDelete);
        }
    }

    public function storeFile(UploadedFile $file, $fileDir)
    {
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
            '.' . $file->getClientOriginalExtension();

        /** @var Filesystem $filesystem */
        $filesystem = app(Filesystem::class);

        if ($filesystem->isDirectory(public_path($fileDir)) === false) {
            $filesystem->makeDirectory(public_path($fileDir), 0755, true);
        }

        $filePath = $fileDir . $fileName;

        $file->move(public_path($fileDir), $fileName);

        return $filePath;
    }

    public function deleteFilesIfExist($model, $fileFields)
    {
        foreach ($fileFields as $fileField) {
            if ($model->$fileField) {
                $this->deleteFile($model->$fileField);
            }
        }
    }

    public function deleteFile($filePath)
    {
       if (is_file(public_path($filePath))) {
           unlink(public_path($filePath));
       }
    }
}

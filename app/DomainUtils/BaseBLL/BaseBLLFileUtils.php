<?php

namespace App\DomainUtils\BaseBLL;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

trait BaseBLLFileUtils
{
    public function prepareFileFields(&$data, $fileFields, $model = null): void
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

    public function storeResourceFile(UploadedFile $file, $fileDir): string
    {
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).
            '.'.$file->getClientOriginalExtension();

        /** @var Filesystem $filesystem */
        $filesystem = app(Filesystem::class);

        if ($filesystem->isDirectory(public_path($fileDir)) === false) {
            $filesystem->makeDirectory(public_path($fileDir), 0755, true);
        }

        $filePath = $fileDir.$fileName;

        $file->move(public_path($fileDir), $fileName);

        return $filePath;
    }

    public function copyFile($originFullPath, $targetPath, $targetName): string
    {
        /** @var Filesystem $filesystem */
        $filesystem = app(Filesystem::class);

        if ($filesystem->isDirectory(public_path($targetPath)) === false) {
            $filesystem->makeDirectory(public_path($targetPath), 0755, true);
        }

        $targetFullPath = $targetPath.$targetName;

        $filesystem->copy(public_path($originFullPath), public_path($targetFullPath));

        return $targetFullPath;
    }

    public function deleteFilesIfExist($model, $fileFields): void
    {
        foreach ($fileFields as $fileField) {
            if ($model->$fileField) {
                $this->deleteFile($model->$fileField);
            }
        }
    }

    public function deleteFile($filePath): void
    {
        if (is_file(public_path($filePath))) {
            unlink(public_path($filePath));
        }
    }
}

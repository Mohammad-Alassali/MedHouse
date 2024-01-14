<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageController extends Controller
{
    public function getImage(): BinaryFileResponse
    {
        return Response::download(public_path('Uploads/' . request()->query('name')));
    }

    /**
     * Store a photo
     *
     * @param $file
     * @param $groupName
     * @return string
     */
    public static function store($file, $groupName): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = $groupName . "/" . time() . '.' . $extension;
        $file->move("Uploads/$groupName/", $filename);
        return $filename;
    }

    /**
     * @param $file
     * @return void
     */
    public static function destroy($file): void
    {
        $path = public_path("Uploads/$file");
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    /**
     * @param $file
     * @param $oldFile
     * @param $groupName
     * @return string
     */
    public static function update($file, $oldFile, $groupName): string
    {
        $path = public_path("Uploads/$oldFile");
        if (File::exists($path)) {
            self::destroy($oldFile);
        }
        $extension = $file->getClientOriginalExtension();
        $filename = $groupName . "/" . time() . '.' . $extension;
        $file->move("Uploads/$groupName/", $filename);
        return $filename;
    }
}

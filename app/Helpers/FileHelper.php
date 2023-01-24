<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use PHPUnit\Exception;
use Illuminate\Support\Facades\File;
class FileHelper
{
    public static function saveBase64($base64File, $dir = "files")
    {
//        $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64File));
//
//        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
//        file_put_contents($tmpFilePath, $fileData);
//
//        $tmpFile = new File($tmpFilePath);
//
//        $file = new UploadedFile(
//            $tmpFile->getPathname(),
//            $tmpFile->getFilename(),
//            $tmpFile->getMimeType(),
//            0,
//            true
//        );
//        $filename = 'images/' . $dir . '/' . date('YmdHi') . '.' . $file->extension();
//        if ($file->move(public_path('images/' . $dir), $filename)) {
//
//            return $filename;
//        }
        return null;
    }

    public static function fileUpload($data)
    {

        if (isset($data["file"])) {
            $file = $data["file"];
            if($data['type'] == 'resource'){
                $validator = Validator::make($data->all(), [
                    'file' => ['mimes:pdf,doc,docx,xlsx,pptx,xml|size:2048']
                ]);
            }else{
                $validator = Validator::make($data->all(), [
                    'file' => ['mimes:jpeg,png,jpg,gif,svg,mp4,webm,mov,wmv,avi|size:2048']
                ]);
            }
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    "errors" => $validator->errors()
                ])->header('Status-Code', 200);
            }
            $loc = $data["type"];
            $location = $loc ? $loc . "/" : "";
            try {
                $dir = "files";
                $mime = explode("/", $file->getClientMimeType());
                if ($mime[0] == "image") {
                    $dir = "images";
                } elseif ($mime[0] == "video") {
                    $dir = "videos";
                }
                $dirPath = $dir . '/' . $location;
                $filename = $dirPath . date('mdYHis') . "_" . $loc . '.' . $file->getClientOriginalExtension();
                if($dir == 'images' && $data["type"] == "user"){
                    File::ensureDirectoryExists(public_path('images/user'));
                    $img = Image::make($file->getRealPath());
                    $img->resize(200, 200, function ($const) {
                        $const->aspectRatio();
                    })->save(public_path($filename));
                }else if($dir == 'images' && $data["type"] == "course"){
                    File::ensureDirectoryExists(public_path('images/course'));
                    $img = Image::make($file->getRealPath());
                    $img->resize(400, 250, function ($const) {
                        $const->aspectRatio();
                    })->save(public_path($filename));
                }else {
                    $file->move(public_path($dirPath), $filename);
                }
                if ($data["type"] == "user" && !empty(auth()->id())) {
                    $user = User::query()->find(auth()->id());
                    File::delete(public_path( $user->avatar));
                    $user->avatar = $filename;
                    $user->save();
                }
                return $filename;
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    "errors" => $validator->errors()
                ])->header('Status-Code', 200);
            }

        }
    }
}

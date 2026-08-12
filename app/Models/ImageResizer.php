<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
* Image Resizer
*
* @mixin Builder
*/

class ImageResizer extends Model
{
    use HasFactory;

    /**
    * For storing image
    *
    * @return path
    */
    static function store($request,$path,$fileName){
        $request->move(public_path($path), $fileName);
        return $path.'/'.$fileName;
    }

    /**
    * For deleting files
    *
    * 
    */
    static function deleteFile($path){
        if (File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}

<?php

/*
 * Class for generalized media helper functions.
 * @author Arslan Arif <AArif.KFZ@emaar.ae>
 */

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Model;

class MediaHelper extends Model {

    private $fields;
    private $request;
    private $medias;
    private $paths = [];

    const PLACEHOLDER_IMG = 'images/camera_thumb.png';
    const PLACEHOLDER_IMG_NOT_FOUND = 'images/not_found.jpg';

    public function __construct(Request $request, array $fields = ["image"]) {
        $this->fields = $fields;
        $this->request = $request;
    }

    public function doUploadStoreMedia($path) {
        $this->path = self::BASE_UPLOAD_DIR . $path;
        $this->doUpload();
    }

    public function getUploadedMedias() {
        $uploads = [];
        if (!empty($this->paths)) {
            foreach ($this->paths as $key => $paths) {
                if (is_array($paths) && !empty($paths)) {
                    $uploads[$key] = ['isMultiple' => true, 'uploads' => $paths];
                } else {
                    $uploads[$key] = ['isMultiple' => false, 'uploads' => $paths];
                }
            }
        }
        return $uploads;
    }

    public function getUploadedMediaByKey($key) {
        if (!empty($this->paths) && isset($this->paths[$key])) {
            if (is_array($this->paths[$key]) && !empty($this->paths[$key])) {
                return ['isMultiple' => true, 'uploads' => $this->paths[$key]];
            }
            return ['isMultiple' => false, 'uploads' => $this->paths[$key]];
        }
        return [];
    }

    private function doUpload() {
        if (!empty($this->fields)) {
            foreach ($this->fields as $field) {
                if ($this->request->hasFile($field)) {
                    $files = $this->request->file($field);
                    if (is_array($files) && !empty($files)) {
                        $medias = [];
                        foreach ($files as $file) {
                            $media = $this->uploader($file);
                            if ($media != null) {
                                array_push($medias, $media);
                            }
                        }
                        if (!empty($media)) {
                            $this->paths[$field] = $medias;
                        }
                    } else {
                        $media = $this->uploader($files);
                        $this->paths[$field] = $media;
                    }
                }
            }
        }
    }

    public function unlink($path, $files) { //Delete media physicaly from drive
        $fileList = []; 
        $this->path = self::BASE_UPLOAD_DIR . $path . '/';

        if (!empty($files)) {
            foreach ($files as $file) {
                $path = public_path($this->path . $file);

                if (file_exists($path)) {
                    array_push($fileList, $path);
                }
            }

            File::delete($fileList);
        }
    }

    private function uploader($file) {
        try {
             $destinationPath = $this->path;
             $fileName = str_random(25) . '.' . $file->getClientOriginalExtension();
             $file->move($destinationPath, $fileName);
             return $fileName;
        } catch (Exception $e) {
             dd($e->getMessage());
        }
       
    }

    //    private function doUpload() {
//        if ($this->request->hasFile($this->field)) {
//            if ($this->request->file($this->field)->isValid()) {
//                $file = $this->request->file($this->field);
//                $destinationPath = $this->path;
//                $fileName = str_random(25) . '.' . $file->getClientOriginalExtension();
//                $file->move($destinationPath, $fileName);
//                $this->paths[$this->field] = $fileName;
//            }
//        }
//    }
}

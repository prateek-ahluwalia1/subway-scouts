<?php

/*
 * Class for generalized media helper functions.
 * @author Arslan Arif <3103arsl@gmail.com>
 */

namespace App\Http\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Helpers\SettingHelper;
use App\Http\Helpers\Thumbnail;
use Illuminate\Support\Str;

class Uploader {

    private $fields;
    private $request;
    private $medias;
    private $paths = [];
    private $imageUploadDir;
    private $thumbnailGenerator;
    private $type;
    private $isThumbGenerate = true;
    private $isUnlink = false;

    public function __construct($type = 'post', Request $request = null, array $fields = ["image"]) {
        $this->fields = $fields;
        $this->request = $request;
        $this->type = $type;
        $this->imageUploadDir = SettingHelper::publicUploadAssetPath();
        $this->thumbnailGenerator = new Thumbnail($type);
    }

    public function setIsThumbCreate($isCreate) {
        $this->isThumbGenerate = $isCreate;
        return $this;
    }

    public function doUploadStoreMedia() {
        $this->path = $this->imageUploadDir;
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

    public function getUploadedMediaByKey($key, $isList = true) {
        if (!empty($this->paths) && isset($this->paths[$key])) {
            if (is_array($this->paths[$key]) && !empty($this->paths[$key])) {
                return ['isMultiple' => true, 'uploads' => $this->paths[$key]];
            }
            if ($isList) {
                return ['isMultiple' => false, 'uploads' => $this->paths[$key]];
            }
            return $this->paths[$key];
        }
        return ($isList) ? [] : null;
    }

    public function getSingleUploadedMediaByKey($key) {
        return $this->getUploadedMediaByKey($key, false);
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

    public function unlink($files) { //Delete media physicaly from drive
        $fileList = [];
        $this->path = $this->imageUploadDir;
        if (!empty($files)) {
            foreach ($files as $file) {
                $path = $this->path . $file;
                if (file_exists($path)) {
                    array_push($fileList, $path);
                    $this->thumbnailGenerator->remove($file, $this->type);
                }
            }
            File::delete($fileList);
        }
    }

    private function uploader($file) {
        try {
            $destinationPath = $this->path;
            $newName = Str::random(25);
            $fileName = $newName . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            if ($this->isImage($destinationPath . $fileName)) {
                $this->thumbnailGenerator->create($newName, $file->getClientOriginalExtension(), $this->type);
            }
            return $fileName;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function isImage($path) {
        $a = getimagesize($path);
        $image_type = $a[2];
        if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
            return true;
        }
        return false;
    }

    private function isSetUnlink($unlink) {
        $this->isUnlink = $unlink;
        return $this;
    }

}

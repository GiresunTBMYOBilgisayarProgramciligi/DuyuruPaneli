<?php

namespace App;

class Helper
{
    /**
     * todo move to helper Class
     * @param $uploadMaxSize
     * @return float|int
     */
    public static function getUploadMaxSizeToBytes() {
        $number = (int)ini_get('upload_max_filesize');
        $unit = strtoupper(substr(ini_get('upload_max_filesize'), -1));

        switch ($unit) {
            case 'G':
                return $number * 1024 * 1024 * 1024;
            case 'M':
                return $number * 1024 * 1024;
            case 'K':
                return $number * 1024;
            default:
                return $number;
        }
    }
}
<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Storage;
use App\Support\VideoConverter;

trait ConvertVideos
{
    /**
     * Convert videos from an Eloquent query collection.
     *
     * @param \Illuminate\Database\Eloquent\Collection $videos
     * @param string $storageColumn The column containing the video storage path
     * @return void
     */
    public function convertVideos($videos, $storageColumn)
    {
        foreach ($videos as $video) {
            $this->convertSingleVideo($video->$storageColumn);
        }
    }

    /**
     * Convert a single video.
     *
     * @param string $storagePath The storage path of the video
     * @return void
     */
    private function convertSingleVideo($storagePath)
    {
        try {
            $converter = new VideoConverter();
            if (Storage::exists($storagePath)) {
                $input = Storage::path($storagePath);
                $fileNameWithoutExt = pathinfo($input, PATHINFO_FILENAME);
                $directory = pathinfo($input, PATHINFO_DIRNAME);
                $directoryName = basename($directory);
                $outputDir = Storage::path($directoryName);
                $newFileName = $outputDir . "/" . $fileNameWithoutExt . '.mp4';
                $result = $converter->convertMovToMp4($input, $newFileName);
                if (!!$result['success']) {
                    Storage::delete($storagePath);
                    return $directoryName . '/' . $fileNameWithoutExt . '.mp4';
                }
            }
        } catch (Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }
}

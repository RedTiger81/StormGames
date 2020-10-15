<?php

declare(strict_types=1);

namespace Eren5960\SkyBlock\utils;

use Eren5960\SkyBlock\island\island\IslandBase;
use Eren5960\SkyBlock\SkyBlock;
use pocketmine\Server;
use ZipArchive;
use function opendir;
use function readdir;
use function str_replace;
use function is_file;
use function is_dir;
use function substr;
use function closedir;
use function pathinfo;
use function strlen;
use function basename;
use function rmdir;
use function mkdir;
use function copy;
use function unlink;

class Compression{
    /**
     * Add files and sub-directories in a folder to zip file.
     * @param string $folder
     * @param ZipArchive $zipFile
     * @param int $exclusiveLength Number of text to be exclusived from the file path.
     * @param $name
     * @param $base
     */
    private static function folderToZip($folder, &$zipFile, $exclusiveLength, $name, $base) {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip.
                $localPath = str_replace($base, $name, substr($filePath, $exclusiveLength));
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength, $name, $base);
                }
            }
        }
        closedir($handle);
    }

    /**
     * Zip a folder (include itself).
     * Usage:
     *   Compression::zipDir('/path/to/sourceDir', '/path/to/out.zip');
     *
     * @param string $island
     * @param string $name
     */
    public static function cloneIsland(string $island, string $name){
        $sourcePath = SkyBlock::getBackupFolder() . $island;
        $outZipPath = SkyBlock::getIslandFolder() . $name . '-' . $island . '.zip';
        $pathInfo = pathinfo($sourcePath);
        $parentPath = $pathInfo['dirname'];

        $z = new ZipArchive();
        $z->open($outZipPath, ZIPARCHIVE::CREATE);
        $z->addEmptyDir($name . '-' . $island);
        self::folderToZip($sourcePath, $z, strlen("$parentPath/"), $name. '-' . $island, basename($sourcePath));
        $z->setArchiveComment(yaml_emit(self::getCustomData("Benim Adam")));
        $z->close();
    }

    public static function saveIsland(IslandBase $island){
        $index = $island->owner . '-' . $island->getName();
        $sourcePath = Server::getInstance()->getDataPath() . 'worlds' . DS . $index;
        $outZipPath = SkyBlock::getIslandFolder() . $index . '.zip';
        $pathInfo = pathinfo($sourcePath);
        $parentPath = $pathInfo['dirname'];

        $z = new ZipArchive();
        $z->open($outZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $z->addEmptyDir($index);
        self::folderToZip($sourcePath, $z, strlen("$parentPath/"), $index, basename($sourcePath));
        $z->close();
    }

    public static function getCustomData(string $name): array{
        return [
            "level" => 0,
            "xp" => 0,
            "need_xp" => 50,
            "name" => $name,
            "members" => [],
            "locked" => false,
            "banneds" => []
        ];
    }

    // utils

    public static function remove($dirname): void{
        $dir_handle = null;
        if(is_dir($dirname)){
            $dir_handle = opendir($dirname);
        }
        if(!$dir_handle) return;
        while($file = readdir($dir_handle)){
            if($file != "." && $file != ".."){
                if(!is_dir($dirname . DIRECTORY_SEPARATOR . $file)){
                    unlink($dirname . DIRECTORY_SEPARATOR . $file);
                }else{
                    self::remove($dirname . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
    }

    public static function copy($from, $to){
        @mkdir($to);
	    @mkdir($to . "db");
        copy($from . "level.dat", $to . "level.dat");
        copy($from . "config.yml", $to . "config.yml");
	    foreach(glob($from . "db/*") as $mca){
		    copy($mca, $to . "db/" . basename($mca));
	    }
    }
}
<?php
namespace TheCodingMachine\Funky\Utils;

use TheCodingMachine\Funky\IoException;

class FileSystem
{
    public static function mkdir(string $dir, int $mode = 0777): void
    {
        if (is_dir($dir)) {
            return;
        }

        if (true !== @mkdir($dir, $mode, true)) {
            $error = error_get_last();
            if (!is_dir($dir)) {
                // The directory was not created by a concurrent process.
                // Let's throw an exception with a developer friendly error message if we have one
                if ($error !== null) {
                    throw IoException::cannotCreateDirectory($dir, $error['message']);
                }
                throw IoException::cannotCreateDirectory($dir, 'unknown error');
            }
        }
    }

    public static function rmdir(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== "." && $object !== "..") {
                    if (is_dir($dir. '/' .$object)) {
                        self::rmdir($dir. '/' .$object);
                    } else {
                        unlink($dir. '/' .$object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}

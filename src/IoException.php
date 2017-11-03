<?php


namespace TheCodingMachine\Funky;

class IoException extends \RuntimeException
{
    public static function cannotWriteFile(string $fileName): self
    {
        return new self(sprintf(
            'Failed to write file %s',
            $fileName
        ));
    }

    public static function cannotCreateDirectory(string $dirName, string $message): self
    {
        return new self(sprintf(
            'Failed to create directory %s: %s',
            $dirName,
            $message
        ));
    }
}

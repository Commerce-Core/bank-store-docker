<?php

namespace CommerceCore\EssentialPluginsInit;

include_once(ABSPATH . 'wp-admin/includes/file.php');

class FileSystemOperationsHelper
{
    public function createDirectory(string $directoryPath, bool $forceOverWrite = false): void
    {
        if (file_exists($directoryPath) && !$forceOverWrite) {
            return;
        }

        if ($forceOverWrite) {
            $this->removeDirectory($directoryPath);
            $this->removeFile($directoryPath);
        }

        mkdir($directoryPath, 0755, true);
    }

    public function removeDirectory(string $directoryPath): void
    {
        if (!is_dir($directoryPath)) {
            return;
        }

        foreach (scandir($directoryPath) as $object) {
            if ($object !== "." && $object !== "..") {
                if (filetype($directoryPath . "/" . $object) === "dir") {
                    $this->removeDirectory($directoryPath . "/" . $object);
                } else {
                    unlink($directoryPath . "/" . $object);
                }
            }
        }

        rmdir($directoryPath);
    }

    /**
     * @throws \Exception
     */
    function renameDirectory(
        string $oldDirectoryPath,
        string $newDirectoryPath,
        bool $replace = false
    ): void
    {
        // Check if the new directory already exists
        if (is_dir($newDirectoryPath)) {
            $replace
                ? $this->removeDirectory($newDirectoryPath)
                : throw new \Exception('Target directory exists!');
        }

        // Rename the old directory to the new name
        rename($oldDirectoryPath, $newDirectoryPath);
    }

    public function removeSymlink(string $symlink): void
    {
        if (!file_exists($symlink) || !is_link($symlink)) {
            return;
        }

        unlink($symlink);
    }

    public function removeFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
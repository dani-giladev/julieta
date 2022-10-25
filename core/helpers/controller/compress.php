<?php

namespace core\helpers\controller;

/**
 * Bunch of global useful compression files functions
 *
 * @author Dani Gilabert
 * 
 */
class compress
{
    
    public static function zip($srcFolderToZip, $dstFilenameZip)
    {
        // Initialize archive object
        $zip = new \ZipArchive;
        $zip->open($dstFilenameZip, \ZipArchive::CREATE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcFolderToZip),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($srcFolderToZip) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();         
        
    }    
    
}
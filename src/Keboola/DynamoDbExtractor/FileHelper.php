<?php

namespace Keboola\DynamoDbExtractor;

class FileHelper
{
    /**
     * Appends content to file
     * @param string $filename
     * @param string $content
     * @throws Exception
     */
    public static function appendContentToFile(string $filename, string $content)
    {
        if (@file_put_contents($filename, $content, FILE_APPEND | LOCK_EX) === false) {
            $message = 'Failed write to file "' . $filename . '". Free space '
                . disk_free_space(dirname($filename)) . '.';
            throw new Exception($message, 0, null);
        }
    }
}

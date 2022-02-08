<?php

namespace Gifmaker\Validator;

use GifMaker\Exception\VideoException;

class Mp4FileValidator
{
    private const MP4 = 'mp4';

    /**
     * @param string $pathToFile
     * @return void
     * @throws VideoException
     */
    public function validate(string $pathToFile): void
    {
        if (!file_exists($pathToFile)) {
            throw new VideoException("File '$pathToFile' does not exist.");
        }

        $extension = pathinfo($pathToFile)['extension'];
        if ($extension['extension'] !== self::MP4) {
            throw new VideoException("File extension must be mp4");
        }
    }
}

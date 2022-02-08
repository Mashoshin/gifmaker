<?php

namespace GifMaker\Validator;

use GifMaker\Exception\InvalidExtensionException;
use GifMaker\Exception\NotFoundException;

class Mp4FileValidator
{
    private const MP4 = 'mp4';

    /**
     * @param string $pathToFile
     * @return void
     * @throws InvalidExtensionException
     * @throws NotFoundException
     */
    public function validate(string $pathToFile): void
    {
        if (!file_exists($pathToFile)) {
            throw new NotFoundException("File '$pathToFile' does not exist.");
        }

        $extension = pathinfo($pathToFile)['extension'];
        if ($extension !== self::MP4) {
            throw new InvalidExtensionException("File extension must be mp4.");
        }
    }
}

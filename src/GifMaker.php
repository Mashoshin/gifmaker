<?php

namespace GifMaker;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFProbe;
use FFMpeg\FFMpeg;
use GifMaker\Exception\InvalidExtensionException;
use GifMaker\Exception\NotFoundException;
use GifMaker\Exception\VideoException;
use GifMaker\Validator\Mp4FileValidator;
use Imagick;
use ImagickException;
use Throwable;

class GifMaker
{
    private const MIN_SECONDS_VALUE = 0;

    private Mp4FileValidator $validator;

    public function __construct(
        private string $savingDir,
        private string $runtimeDir = '/runtime',
        private int $start = 0,
        private int $end = 10,
        private int $framesCount = 10
    ) {
        $this->validator = new Mp4FileValidator();
        $this->initRuntimeDir();
    }

    /**
     * @param string $pathToVideo
     * @return void
     * @throws InvalidExtensionException
     * @throws NotFoundException
     * @throws ImagickException
     * @throws VideoException
     */
    public function create(string $pathToVideo): void
    {
        $this->validator->validate($pathToVideo);

        $duration = $this->getVideoDuration($pathToVideo);
        $this->prepareGifProperties($duration);

        $delay = $this->calculateDelay();

        $ffmpeg = FFMpeg::create();
        $ffmpegVideo = $ffmpeg->open($pathToVideo);

        $gif = new Imagick();
        $gif->setFormat('gif');

        $position = $this->start;
        for ($i = 0; $i < $this->framesCount; ++$i) {
            $position += $delay;
            $pathToFrame = sprintf($this->runtimeDir . '/frame%03d.jpg', $i);
            $ffmpegVideo->frame(TimeCode::fromSeconds($position))->save($pathToFrame);

            $frame = new Imagick();
            $frame->readImage($pathToFrame);
            $frame->setImageDelay(15);
            $gif->addImage($frame);
        }

        $gif->writeImages($this->getSavingPath($pathToVideo), true);

        $gif->destroy();
        $this->removeRuntimeDir();
    }

    /**
     * @param string $pathToVideo
     * @return int
     * @throws VideoException
     */
    private function getVideoDuration(string $pathToVideo): int
    {
        try {
            $ffprobe = FFProbe::create();
            return (int) $ffprobe->format($pathToVideo)->get('duration');
        } catch (Throwable) {
            throw new VideoException('Unable to determine video duration.');
        }
    }

    private function prepareGifProperties(int $duration): void
    {
        $this->start = max(self::MIN_SECONDS_VALUE, $this->start);
        $this->end = min($duration, $this->end);
    }

    private function initRuntimeDir(): void
    {
        if (!file_exists($this->runtimeDir)) {
            mkdir(directory: $this->runtimeDir, recursive: true);
        }
    }

    private function removeRuntimeDir(): void
    {
        $files = glob($this->runtimeDir . '/*');
        foreach ($files as $file) {
            unlink($file);
        }

        rmdir($this->runtimeDir);
    }

    private function calculateDelay(): float
    {
        return (float) (($this->end - $this->start) / ($this->framesCount + 1));
    }

    private function getSavingPath(string $originFilePath): string
    {
        $filename = pathinfo($originFilePath)['filename'];
        $trimmedSavingDir = rtrim($this->savingDir, '/');

        return "$trimmedSavingDir/$filename.gif";
    }
}

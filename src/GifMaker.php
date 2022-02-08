<?php

namespace GifMaker;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFProbe;
use FFMpeg\FFMpeg;
use GifMaker\Exception\InvalidExtensionException;
use GifMaker\Exception\NotFoundException;
use GifMaker\Exception\VideoException;
use GifMaker\Validator\Mp4FileValidator;
use GifMaker\ValueObject\GifSettings;
use Imagick;
use ImagickException;
use Throwable;

class GifMaker
{
    private GifSettings $settings;
    private Mp4FileValidator $validator;

    public function __construct(
        private string $savingPath,
        private string $runtimeDir = '/runtime',
    ) {
        $this->settings = new GifSettings();
        $this->validator = new Mp4FileValidator();
        $this->initRuntimeDir();
    }

    public function setSettings(GifSettings $settings): void
    {
        $this->settings = $settings;
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
        $this->settings->calculateStart();
        $this->settings->calculateEnd($duration);
        $delay = $this->settings->calculateFramesCutDelay();

        $ffmpeg = FFMpeg::create();
        $ffmpegVideo = $ffmpeg->open($pathToVideo);

        $gif = new Imagick();
        $gif->setFormat('gif');

        $position = $this->settings->getStart();
        for ($i = 0; $i < $this->settings->getFramesCount(); ++$i) {
            $position += $delay;
            $pathToFrame = sprintf($this->runtimeDir . '/frame%03d.jpg', $i);
            $ffmpegVideo->frame(TimeCode::fromSeconds($position))->save($pathToFrame);

            $frame = new Imagick();
            $frame->readImage($pathToFrame);
            $frame->setImageDelay($this->settings->getImageDelay());
            $gif->addImage($frame);
        }

        $gif->writeImages($this->savingPath, true);

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
}

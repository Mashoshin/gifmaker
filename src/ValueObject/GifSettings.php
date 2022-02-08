<?php

namespace GifMaker\ValueObject;

class GifSettings
{
    private const MIN_SECONDS_VALUE = 0;

    /**
     * @param int $start
     * Moment of the first frame of the video
     * @param int $end
     * Moment of the last frame of the video
     * @param int $framesCount
     * Count of frames
     * @param int $imageDelay
     * The amount of time expressed in 'ticks' that the image should be
     * displayed for. For animated GIFs there are 100 ticks per second, so a
     * value of 20 would be 20/100 of a second aka 1/5th of a second.
     */
    public function __construct(
        private int $start = 0,
        private int $end = 10,
        private int $framesCount = 10,
        private int $imageDelay = 10,
    ) {}

    public function getStart(): int
    {
        return $this->start;
    }

    public function calculateStart(): void
    {
        $this->start = max(self::MIN_SECONDS_VALUE, $this->start);
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function calculateEnd(int $duration): void
    {
        $this->end = min($duration, $this->end);
    }

    public function getFramesCount(): int
    {
        return $this->framesCount;
    }

    public function getImageDelay(): int
    {
        return $this->imageDelay;
    }

    public function calculateFramesCutDelay(): float
    {
        return (float) (($this->end - $this->start) / ($this->framesCount + 1));
    }
}

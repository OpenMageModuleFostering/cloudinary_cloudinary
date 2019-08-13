<?php

namespace CloudinaryExtension\Image;

use CloudinaryExtension\Image\Transformation\Dimensions;
use CloudinaryExtension\Image\Transformation\Dpr;
use CloudinaryExtension\Image\Transformation\FetchFormat;
use CloudinaryExtension\Image\Transformation\Format;
use CloudinaryExtension\Image\Transformation\Gravity;
use CloudinaryExtension\Image\Transformation\Quality;
use CloudinaryExtension\Image\Transformation\Crop;

class Transformation
{
    private $gravity;
    private $dimensions;
    private $crop;
    private $fetchFormat;
    private $quality;
    private $format;
    private $dpr;
    private $validFormats;
    private $flags;

    public function __construct()
    {
        $this->fetchFormat = FetchFormat::fromString(Format::FETCH_FORMAT_AUTO);
        $this->crop = 'pad';
        $this->format = Format::fromExtension('jpg');
        $this->validFormats = array('gif', 'jpg', 'png', 'svg', 'webp');
        $this->flags = [];
    }

    public function withGravity(Gravity $gravity)
    {
        $this->gravity = $gravity;
        $this->crop = ((string)$gravity) ? 'crop' : 'pad';
        return $this;
    }

    public function withDimensions(Dimensions $dimensions)
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function withCrop(Crop $crop)
    {
        $this->crop = $crop;
        return $this;
    }

    public function withFetchFormat(FetchFormat $fetchFormat)
    {
        $this->fetchFormat = $fetchFormat;
        return $this;
    }

    public function withFormat(Format $format)
    {
        if (in_array((string)$format, $this->validFormats)) {
            $this->format = $format;
        }

        return $this;
    }

    public function withoutFormat()
    {
        $this->format = null;
        return $this;
    }

    public function withQuality(Quality $quality)
    {
        $this->quality = $quality;
        return $this;
    }

    public function withDpr(Dpr $dpr)
    {
        $this->dpr = $dpr;
        return $this;
    }

    public function withOptimisationDisabled()
    {
        return $this->withFetchFormat(FetchFormat::fromString(''));
    }

    public function addFlags(array $flags = [])
    {
        $this->flags += $flags;
        return $this;
    }

    public static function builder()
    {
        return new Transformation();
    }

    public function build()
    {
        return array(
            'fetch_format' => (string)$this->fetchFormat,
            'quality' => (string)$this->quality,
            'crop' => (string)$this->crop,
            'gravity' => (string)$this->gravity ?: null,
            'width' => $this->dimensions ? $this->dimensions->getWidth() : null,
            'height' => $this->dimensions ? $this->dimensions->getHeight() : null,
            'format' => (string)$this->format,
            'dpr' => (string)$this->dpr,
            'flags' => $this->flags
        );
    }
}


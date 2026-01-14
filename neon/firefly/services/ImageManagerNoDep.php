<?php 
namespace neon\firefly\services;

use Imagick;
use \neon\core\helpers\Arr;
use \neon\core\helpers\Str;
use neon\core\helpers\Hash;
use yii\web\HttpException;
/**
 * 
 * NoDependency on Intervention Image.
 * This class uses image magick directly rather than wrap.
 */
class ImageManagerNoDep
{
    /**
     * Generate placeholder images
     * @return string
     */
    public function placeholder($params)
    {
        $placeholderImg = dirname(__DIR__) . '/assets/placeholder.jpg';
        if (isset($params['type'])) {
            if ($params['type'] === 'profile') {
                $placeholderImg = dirname(__DIR__) . '/assets/placeholder/profile-1.jpg';
            }
        }
        return $this->process($placeholderImg, $params);
    }

    /**
     * @param string $source - Source to create an image from. Can be one of the following:
     * string - A firefly uuid
     * string - Path of the image in filesystem.
     * string - URL of an image (allow_url_fopen must be enabled).
     * string - Binary image data.
     * string - Data-URL encoded image data.
     * string - Base64 encoded image data.
     * resource - PHP resource of type gd. (when using GD driver)
     * object - Imagick instance (when using Imagick driver)
     * object - Intervention\Image\Image instance
     * object - SplFileInfo instance (To handle file uploads via Symfony\Component\HttpFoundation\File\UploadedFile)
     * @throws HttpException if $image is a firefly uuid and is not found
     * @return string - image data
     */
    public function process($source, $params): string
    {
        // get firefly image if the source is a firefly id
        if (Hash::isUuid64($source)) {
            if (!neon()->firefly->fileManager->exists($source))
                throw new HttpException(404);
            $meta = neon()->firefly->fileManager->getMeta($source);
            // can be 'image/svg' or 'image/svg+xml'
            if (Str::startsWith($meta['mime_type'], 'image/svg'))
                return neon()->firefly->fileManager->read($source);
            $source = neon()->firefly->fileManager->readStream($source);
        }
        
        $image = new Imagick();
        $image->readImageFile($source);

        $processed = $this->processImage($image, $params);

        $image->setImageFormat($image->getImageFormat());
        $image->setImageCompressionQuality(Arr::get($params, 'q', 90));

        return $processed->getImageBlob();
    }

    /**
     * Process the image based on passed in params
     * @param Imagick $image
     * @param array $params - url params passed (array format)
     */
    public function processImage(Imagick $image, $params): Imagick
    {
        $this->imageResize($image, $params);
        $this->imageRotate($image, $params);
        $this->imageFlip($image, $params);
        $this->imageFilter($image, $params);
        $this->imageCrop($image, $params);
        return $image;
    }

    /**
     * Process the image resize based on passed $params (w,h,fit | width,height)
     * @param Imagick $image - The Imagick image object
     * @param array $params - url params passed (array format)
     */
    public function imageResize(Imagick $image, $params)
    {
        $width = Arr::get($params, 'w', Arr::get($params, 'width', null));
        $height = Arr::get($params, 'h', Arr::get($params, 'height', null));
        list($width, $height) = $this->resolveMissingDimensions($image, $width, $height);
        $pd = (double) Arr::get($params, 'pd', 1);
        if (Arr::get($params, 'fit', false)) {
            $image->cropThumbnailImage($width, $height);
        } elseif ($width || $height) {
            $width = $width * $pd;
            $height = $height * $pd;
            $image->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
        }
    }

    /**
     * Perform stretch resize image manipulation.
     *
     * @param Imagick $image  The source image.
     * @param int   $width  The width.
     * @param int   $height The height.
     *
     * @return Imagick The manipulated image.
     */
    public function runStretchResize(Imagick $image, $width, $height): Imagick
    {
        $image->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, false);
        return $image;
    }

    /**
     * Rotate the image if params contains rotate
     * @param Imagick $image - The Imagick image object
     * @param array $params - url params passed (array format)
     */
    public function imageRotate(Imagick $image, $params)
    {
        $rotate = Arr::get($params, 'rotate', false);
        if ($rotate) {
            $image->rotateImage(new ImagickPixel('none'), $rotate);
        }
    }

    /**
     * Flip the image if params contains flip info (flip=v)
     * @param Imagick $image - The Imagick image object
     * @param array $params - url params passed (array format)
     */
    public function imageFlip(Imagick $image, $params)
    {
        $flip = Arr::get($params, 'flip', false);
        if ($flip !== false) {
            if ($flip === 'v') {
                $image->flipImage();
            } else {
                $image->flopImage();
            }
        }
    }

    /**
     * Apply filters to the image if params contains filter info (flip=v)
     * @param Imagick $image - The Imagick image object
     * @param array $params - url params passed (array format)
     */
    public function imageFilter(Imagick $image, $params)
    {
        $filter = Arr::get($params, 'filter', false);
        if ($filter) {
            $filters = explode(',', $filter);
            if (in_array('greyscale', $filters, true)) {
                $image->setImageType(Imagick::IMGTYPE_GRAYSCALE);
            }
            if (in_array('sepia', $filters, true)) {
                $this->runSepiaFilter($image);
            }
            if (in_array('invert', $filters, true)) {
                $image->negateImage(false);
            }
        }
    }

    /**
     * Apply crop to the image if params contains crop params info
     * @param Imagick $image - The Imagick image object
     * @param array $params - url params passed (array format)
     */
    public function imageCrop(Imagick $image, $params)
    {
        $crop = Arr::get($params, 'crop', false);
        if ($crop !== false) {
            if (is_string($crop)) {
                $bits = explode(',', $crop);
                $w = Arr::get($bits, 0, null);
                $h = Arr::get($bits, 1, null);
                $x = Arr::get($bits, 2, null);
                $y = Arr::get($bits, 3, null);
            }
            if (is_array($crop)) {
                $w = Arr::get($crop, 'width', null);
                $h = Arr::get($crop, 'height', null);
                $x = Arr::get($crop, 'x', null);
                $y = Arr::get($crop, 'y', null);
            }
            if ($w == 0 && $h == 0 && $x == 0 && $y == 0) {
                return $image;
            }
            $image->cropImage($w, $h, $x, $y);
        }
    }

    /**
     * Resolve missing image dimensions.
     * @param  Imagick        $image  The source image.
     * @param  integer|null $width  The image width.
     * @param  integer|null $height The image height.
     * @return integer[]    The resolved width and height.
     */
    public function resolveMissingDimensions(Imagick $image, $width, $height)
    {
        if (is_null($width) && is_null($height)) {
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();
        }

        if (is_null($width)) {
            $width = $height * ($image->getImageWidth() / $image->getImageHeight());
        }

        if (is_null($height)) {
            $height = $width / ($image->getImageWidth() / $image->getImageHeight());
        }

        return [
            (int) $width,
            (int) $height,
        ];
    }

    /**
     * Perform sepia manipulation.
     * @param  Imagick $image The source image.
     * @return Imagick The manipulated image.
     */
    public function runSepiaFilter(Imagick $image)
    {
        $image->modulateImage(100, 0, 100);
        $image->colorizeImage(new ImagickPixel('rgb(112, 66, 20)'), 1);
        return $image;
    }

    /**
     * @return string
     */
    public function getPlaceholderImageContents()
    {
        return file_get_contents(dirname(__DIR__).'/assets/placeholder.jpg');
    }

    public function getNotReadableImageContents()
    {
        return file_get_contents(dirname(__DIR__).'/assets/placeholder-broken.png');
    }
}

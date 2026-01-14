<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 06/05/2017
 * @package neon
 */

namespace neon\firefly\services;

use \Intervention\Image\ImageManager as InterventionImageManager;
use \Intervention\Image\Exception\NotReadableException;
use \Intervention\Image\Image as Image;
use \neon\core\helpers\Arr;
use \neon\core\helpers\Str;
use neon\core\helpers\Hash;
use yii\web\HttpException;

class ImageManager
{
	/**
	 * Generate placeholder images
	 * @return Intervention\Image\Image::response 
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
	 *
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
		$manager = $this->getImageDriver();
		$image = $manager->make($source);
		$image = $this->processImage($image, $params);
		return $image->getEncoded();
	}

	/**
	 * @param mixed $source string - A firefly uuid
	 * string - Path of the image in filesystem.
	 * @throws \yii\web\HttpException
	 * @return \Intervention\Image\Image
	 */
	public function getImage($source): Image
	{
		$manager = $this->getImageDriver();
		return $manager->make($source);
	}

	/**
	 * Get the image processing driver (implements Imagick or GB)
	 * @return InterventionImageManager
	 */
	public function getImageDriver()
	{
		$manager = new InterventionImageManager();
		if (extension_loaded('imagick')) {
			$manager->configure(['driver' => 'imagick']);
		}
		return $manager;
	}

	/**
	 * Process the image based on passed in params
	 * @param Image $image
	 * @param array $params - url params passed (array format)
	 */
	public function processImage(Image &$image, $params): Image
	{
		$image->interlace();
		$this->imageResize($image, $params);
		$this->imageRotate($image, $params);
		$this->imageFlip($image, $params);
		$this->imageFilter($image, $params);
		$this->imageCrop($image, $params);
		// get desired format to convert an image to
		$format = Arr::get($params, 'f', $image->mime);
		// get desired quality (default to 90%)
		$quality = Arr::get($params, 'q', 90);
		$image->encode($format, $quality);
		return $image;
	}

	/**
	 * Process the image resize based on passed $params (w,h,fit | width,height)
	 * @param Image $image - The Intervention image object
	 * @param array $params - url params passed (array format)
	 */
	public function imageResize(Image &$image, $params)
	{
		// Get url params
		$width = Arr::get($params, 'w', Arr::get($params, 'width', null));
		$height = Arr::get($params, 'h', Arr::get($params, 'height', null));
		list($width, $height) = $this->resolveMissingDimensions($image, $width, $height);
		$pd = (float) Arr::get($params, 'pd', 1);
		if (Arr::get($params, 'fit', false)) {
			// add callback functionality to retain maximal original image size
			$options = ['top-left', 'top', 'top-right', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right'];
			$option = in_array($params['fit'], $options) ? $params['fit'] : 'center';
			$image->fit($width, $height, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			}, $option);

			// if fit is not specified resizes the image to fit inside the container and maintains aspect ratio
		} elseif ($width || $height) {
			$width = $width * $pd;
			$height = $height * $pd;
			$image->resize($width, $height, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			});
		}
	}

	/**
	 * Perform stretch resize image manipulation.
	 *
	 * @param Image $image  The source image.
	 * @param int   $width  The width.
	 * @param int   $height The height.
	 *
	 * @return Image The manipulated image.
	 */
	public function runStretchResize(Image $image, $width, $height)
	{
		return $image->resize($width, $height);
	}

	/**
	 * Rotate the image if params contains rotate
	 * @param Image $image - The Intervention image object
	 * @param array $params - url params passed (array format)
	 */
	public function imageRotate(Image &$image, $params)
	{
		$rotate = Arr::get($params, 'rotate', false);
		if ($rotate) $image->rotate($rotate);
	}

	/**
	 * Flip the image if params contains flip info (flip=v)
	 * @param Image $image - The Intervention image object
	 * @param array $params - url params passed (array format)
	 */
	public function imageFlip(Image &$image, $params)
	{
		$flip = Arr::get($params, 'flip', false);
		if ($flip !== false) {
			// flip horizontal
			if ($flip === 'v') {
				$image->flip('v');
			} else {
				// by default it will flip horizontally
				$image->flip('h');
			}
		}
	}

	/**
	 * Apply filters to the image if params contains filter info (flip=v)
	 * @param Image $image - The Intervention image object
	 * @param array $params - url params passed (array format)
	 */
	public function imageFilter(Image &$image, $params)
	{
		$filter = Arr::get($params, 'filter', false);
		if ($filter) {
			$filters = explode(',', $filter);
			if (in_array('greyscale', $filters, true)) {
				$image->greyscale();
			}
			if (in_array('sepia', $filters, true)) {
				$this->runSepiaFilter($image);
			}
			if (in_array('invert', $filters, true)) {
				$image->invert();
			}
		}
	}

	/**
	 * Apply crop to the image if params contains crop params info
	 * @param Image $image - The Intervention image object
	 * @param array $params - url params passed (array format)
	 */
	public function imageCrop(Image &$image, $params)
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
			$image->crop($w, $h, $x, $y);
			// create an image with a transparent background
			$bg = $this->getImageDriver()->canvas($w, $h, array(255, 255, 255, 0));
			// Fill up the blank spaces with transparent color
			$image = $bg->insert($image, 'center');
			return $image;
		}
	}

	/**
	 * Resolve missing image dimensions.
	 * @param  Image        $image  The source image.
	 * @param  integer|null $width  The image width.
	 * @param  integer|null $height The image height.
	 * @return integer[]    The resolved width and height.
	 */
	public function resolveMissingDimensions(Image &$image, $width, $height)
	{
		if (is_null($width) and is_null($height)) {
			$width = $image->width();
			$height = $image->height();
		}

		if (is_null($width)) {
			$width = $height * ($image->width() / $image->height());
		}

		if (is_null($height)) {
			$height = $width / ($image->width() / $image->height());
		}

		return [
			(int) $width,
			(int) $height,
		];
	}

	/**
	 * Perform sepia manipulation.
	 * @param  Image $image The source image.
	 * @return Image The manipulated image.
	 */
	public function runSepiaFilter(Image &$image)
	{
		$image->greyscale();
		$image->brightness(-10);
		$image->contrast(10);
		$image->colorize(38, 27, 12);
		$image->brightness(-10);
		$image->contrast(10);
		return $image;
	}

	/**
	 * @return string
	 */
	public function getPlaceholderImageContents()
	{
		// should be configurable by settings somewhere
		return file_get_contents(dirname(__DIR__) . '/assets/placeholder.jpg');
	}

	public function getNotReadableImageContents()
	{
		// should be configurable by settings somewhere
		return file_get_contents(dirname(__DIR__) . '/assets/placeholder-broken.png');
	}
}

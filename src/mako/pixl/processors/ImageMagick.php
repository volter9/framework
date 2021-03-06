<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pixl\processors;

use \Imagick;
use \ImagickPixel;
use \InvalidArgumentException;
use \RuntimeException;

use \mako\pixl\Image;
use \mako\pixl\processors\ProcessorInterface;

/**
 * ImageMagick processor.
 *
 * @author  Frederic G. Østby
 */

class ImageMagick implements ProcessorInterface
{
	/**
	 * Imagick instance.
	 * 
	 * @var \Imagick
	 */

	protected $image;

	/**
	 * Imagick instance.
	 * 
	 * @var \Imagick
	 */

	protected $snapshot;

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		if($this->image instanceof Imagick)
		{
			$this->image->destroy();
		}

		if($this->snapshot instanceof Imagick)
		{
			$this->snapshot->destroy();
		}
	}

	/**
	 * Add the hash character (#) if its missing.
	 * 
	 * @access  public
	 * @param   string  $hex  HEX value
	 * @return  string
	 */

	public function normalizeHex($hex)
	{
		if(preg_match('/^(#?[a-f0-9]{3}){1,2}$/i', $hex) === 0)
		{
			throw new InvalidArgumentException(vsprintf("%s(): Invalid HEX value [ %s ].", [__METHOD__, $hex]));
		}

		return (strpos($hex, '#') !== 0) ? '#' . $hex : $hex;
	}

	/**
	 * {@inheritdoc}
	 */

	public function open($image)
	{
		$this->image = new Imagick($image);
	}

	/**
	 * {@inheritdoc}
	 */

	public function snapshot()
	{
		$this->snapshot = clone $this->image;
	}

	/**
	 * {@inheritdoc}
	 */

	public function restore()
	{
		if(!($this->snapshot instanceof Imagick))
		{
			throw new RuntimeException(vsprintf("%s(): No snapshot to restore.", [__METHOD__]));
		}

		$this->image = $this->snapshot;

		$this->snapshot = null;
	}

	/**
	 * {@inheritdoc}
	 */

	public function rotate($degrees)
	{
		$this->image->rotateImage(new ImagickPixel('none'), (360 - $degrees));
	}

	/**
	 * {@inheritdoc}
	 */

	public function resize($width, $height = null, $aspectRatio = Image::RESIZE_IGNORE)
	{
		$w = $this->image->getImageWidth();
		$h = $this->image->getImageHeight();

		if($height === null)
		{				
			$newWidth  = round($w * ($width / 100));
			$newHeight = round($h * ($width / 100));
		}
		else
		{
			if($aspectRatio === Image::RESIZE_AUTO)
			{
				// Calculate smallest size based on given height and width while maintaining aspect ratio

				$percentage = min(($width / $w), ($height / $h));

				$newWidth  = round($w * $percentage);
				$newHeight = round($h * $percentage);
			}
			elseif($aspectRatio === Image::RESIZE_WIDTH)
			{
				// Base new size on given width while maintaining aspect ratio

				$newWidth  = $width;
				$newHeight = round($h * ($width / $w));
			}
			elseif($aspectRatio === Image::RESIZE_HEIGHT)
			{
				// Base new size on given height while maintaining aspect ratio

				$newWidth  = round($w * ($height / $h));
				$newHeight = $height;
			}
			else
			{
				// Ignone aspect ratio
				
				$newWidth  = $width;
				$newHeight = $height;
			}					
		}
		
		$this->image->scaleImage($newWidth, $newHeight);
	}

	/**
	 * {@inheritdoc}
	 */

	public function crop($width, $height, $x, $y)
	{			
		$this->image->cropImage($width, $height, $x, $y);
	}

	/**
	 * {@inheritdoc}
	 */

	public function flip($direction = Image::FLIP_HORIZONTAL)
	{
		if($direction ===  Image::FLIP_VERTICAL)
		{
			// Flips the image in the vertical direction

			$this->image->flipImage();
		}
		else
		{
			// Flips the image in the horizontal direction

			$this->image->flopImage();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	
	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100)
	{
		$watermark = new Imagick($file);
		
		$watermarkW = $watermark->getImageWidth();
		$watermarkH = $watermark->getImageHeight();
		
		if($opacity < 100)
		{				
			$watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, ($opacity / 100), Imagick::CHANNEL_ALPHA);
		}
		
		// Position the watermark.
		
		switch($position)
		{
			case Image::WATERMARK_TOP_RIGHT:
				$x = $this->image->getImageWidth() - $watermarkW;
				$y = 0;
				break;
			case Image::WATERMARK_BOTTOM_LEFT:
				$x = 0;
				$y = $this->image->getImageHeight() - $watermarkH;
				break;
			case Image::WATERMARK_BOTTOM_RIGHT:
				$x = $this->image->getImageWidth() - $watermarkW;
				$y = $this->image->getImageHeight() - $watermarkH;
				break;
			case Image::WATERMARK_CENTER:
				$x = ($this->image->getImageWidth() / 2) - ($watermarkW / 2);
				$y = ($this->image->getImageHeight() / 2) - ($watermarkH / 2);
				break;
			default:
				$x = 0;
				$y = 0;
		}
		
		$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);
		
		$watermark->destroy();
	}

	/**
	 * {@inheritdoc}
	 */

	public function brightness($level = 50)
	{
		$this->image->modulateImage(100 + $level, 100, 100);
	}

	/**
	 * {@inheritdoc}
	 */
	
	public function greyscale()
	{
		$this->image->setImageType(Imagick::IMGTYPE_GRAYSCALE);
	}

	/**
	 * {@inheritdoc}
	 */
	
	public function sepia()
	{
		$this->image->sepiaToneImage(80);
	}

	/**
	 * {@inheritdoc}
	 */

	public function colorize($color)
	{		
		$this->image->colorizeImage($this->normalizeHEX($color), 1.0);
	}

	/**
	 * {@inheritdoc}
	 */

	public function sharpen()
	{
		$this->image->sharpenImage(0, 1);
	}

	/**
	 * {@inheritdoc}
	 */

	public function pixelate($pixelSize = 10)
	{
		$width = $this->image->getImageWidth();

		$height = $this->image->getImageHeight();

		$this->image->scaleImage((int) ($width / $pixelSize), (int) ($height / $pixelSize));

		$this->image->scaleImage($width, $height);
	}

	/**
	 * {@inheritdoc}
	 */

	public function negate()
	{
		$this->image->negateImage(false);
	}

	/**
	 * {@inheritdoc}
	 */
	
	public function border($color = '#000', $thickness = 5)
	{
		$this->image->shaveImage($thickness, $thickness);
		
		$this->image->borderImage($this->normalizeHEX($color), $thickness, $thickness);
	}

	/**
	 * {@inheritdoc}
	 */

	public function getImageBlob($type = null, $quality = 95)
	{
		if($type !== null)
		{
			if(!$this->image->setImageFormat($type))
			{
				throw new RuntimeException(vsprintf("%s(): Unsupported image type [ %s ].", [__METHOD__, $type]));
			}
		}

		// Set image quality

		$this->image->setImageCompressionQuality($quality);

		// Return image blob

		return $this->image->getImageBlob();
	}

	/**
	 * {@inheritdoc}
	 */

	public function save($file, $quality = 95)
	{	
		// Set image quality
		
		$this->image->setImageCompressionQuality($quality);

		// Save image
		
		$this->image->writeImage($file);
	}
}
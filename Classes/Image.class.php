<?php
/**
 * Image Class
 * Resize and crop images
 * @author Ardalan Samimi
*/

class Image {

	const IM_SIZE_EXACT = 1;
	const IM_SIZE_WIDTH = 2;
	const IM_SIZE_HEIGHT = 3;
	const IM_SIZE_CROP = 4;

	/**
	 * @access private
	 * @var resource
	 */
	private $sourceImage;

	/**
	 * @access private
	 * @var integer
	 */
	private $sourceWidth;

	/**
	 * @access private
	 * @var integer
	 */
	private $sourceHeight;

	/**
	 * @access private
	 * @var resource
	 */
	private $targetImage;

	/**
	 * @access private
	 * @var integer
	 */
	private $targetWidth;

	/**
	 * @access private
	 * @var integer
	 */
	private $targetHeight;

	/**
	 * @access private
	 * @var integer
	 */
	private $fileExtension;

	/**
	 * @access private
	 * @var string
	 */
	private $errorMessage;

	/**
	 * Initialize the Image Class.
	 * Variable $file is the string path
	 * to the image to be manipulated.
	 *
	 * @param string $file
	 * @param integer $width (optional)
	 * @param integer $height (optional)
	 * @param string $option (optional)
	 */
	public function __construct ($file = FALSE, $width = NULL, $height = NULL, $option = NULL) {
		if ($file === FALSE)
			return NULL;
		$this->setSourceImage ($file);
		if ($width !== NULL && $height !== NULL)
			$this->resize ($width, $height, $option);
	}

	/**
	 * Free memory associated
	 * with the created images.
	 *
	 */
	public function cleanUp() {
		imagedestroy($this->targetImage);
		imagedestroy($this->sourceImage);
	}

	private function throwError ($message) {
		$this->errorMessage = $message;
	}

	public function getErrorMessage () {
		return $this->errorMessage;
	}

	/**
	 * Resizes the image to the
	 * requested dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param integer $option (optional)
	 */
	public function resize ($width, $height, $option = 0) {
		// Check for resize option
		if ($option === self::IM_SIZE_EXACT)
			$this->setSizeExact ($width, $height);
		else if ($option === self::IM_SIZE_WIDTH)
			$this->setSizeWithFixedWidth ($width);
		else if ($option === self::IM_SIZE_HEIGHT)
			$this->setSizeWithFixedHeight ($height);
		else if ($option === self::IM_SIZE_CROP)
			$this->setSizeWithCrop ($width, $height);
		else
			$this->setSize ($width, $height);
		// Create the resource for the resized image
		$this->setTargetImage();
		if ($option === self::IM_SIZE_CROP)
			$this->crop ($width, $height);
	}

	/**
	 * Save the resized image.
	 *
	 * @param $string $filePath
	 * @return bool|mixed
	 */
	public function save ($filePath = NULL) {
		if ($filePath === NULL)
			return $this->throwError("Could not save file. No path given");
		if ($this->fileExtension === IMAGETYPE_JPEG)
			if (imagetypes() && IMG_JPG)
				imagejpeg($this->targetImage, $filePath, "90");
		else if ($this->fileExtension === IMAGETYPE_PNG)
			if (imagetypes() && IMG_PNG)
				imagepng($this->targetImage, $filePath, 9);
		else if ($this->fileExtension === IMAGETYPE_GIF)
			if (imagetypes() && IMG_GIF)
				imagegif($this->targetImage, $filePath);

		return TRUE;
	}

	/**
	 * Creates the original (source) image
	 * out of the inputed image file.
	 *
	 * @param string $file
	 */
	private function setSourceImage ($file) {
		// Retrieve the width, height and type of
		// of the selected image with getimagesize().
		if (list($this->sourceWidth, $this->sourceHeight, $this->fileExtension) = @getimagesize($file)) {
			// Create the source image from the file,
			// based on its extension.
			if ($this->fileExtension === IMAGETYPE_JPEG)
				$this->sourceImage = $this->imageCreateFromJpeg($file);
			else if ($this->fileExtension === IMAGETYPE_PNG)
				$this->sourceImage = $this->imageCreateFromPng($file);
			else if ($this->fileExtension === IMAGETYPE_GIF)
				$this->sourceImage = $this->imageCreateFromGif($file);
			else
				throw new Exception ("File is not an image");
		} else {
			throw new Exception ("File is not an image");
		}
	}

	/**
	 * Create the new (target) image with
	 * the user requested dimensions.
	 */
	private function setTargetImage () {
		// Create the canvas and resample image
		$this->targetImage = imagecreatetruecolor($this->targetWidth, $this->targetHeight);
		imagecopyresampled($this->targetImage, $this->sourceImage, 0, 0, 0, 0, $this->targetWidth, $this->targetHeight, $this->sourceWidth, $this->sourceHeight);
	}

	/**
	 * Set the new size with the,
	 * the exact dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 */
	private function setSizeExact ($width, $height) {
		$this->targetWidth = $width;
		$this->targetHeight = $height;
	}

	/**
	 * Set the new size with an exact width,
	 * but adjusted height. If the new width
	 * is larger, then it should use the old
	 * width, instead of embiggen the image.
	 *
	 * @param integer $width
	 */
	private function setSizeWithFixedWidth ($width) {
		if ($this->sourceWidth > $width)
			$this->targetWidth = $width;
		else
			$this->targetWidth = $this->sourceWidth;
		$this->targetHeight = $this->getHeightByWidth ($this->targetWidth);
	}

	/**
	 * Set the new size with an exact height,
	 * but adjusted width. If the new height
	 * is larger, then it should use the old
	 * height, instead of embiggen the image.
	 *
	 * @param integer $height
	 */
	private function setSizeWithFixedHeight ($height) {
		if ($this->sourceHeight > $height)
			$this->targetHeight = $height;
		else
			$this->targetHeight = $this->sourceHeight;
		$this->targetWidth = $this->getWidthByHeight ($this->targetHeight);

	}

	/**
	 * Determine the optimal size for
	 * the new image to be cropped.
	 *
	 * @param integer $width
	 * @param integer $height
	 */
	private function setSizeWithCrop ($width, $height) {
		// Calculate the scaling factor
		$ratio = array(
			"width" 	=> $this->sourceWidth / $width,
			"height" 	=> $this->sourceHeight / $height
		);
		// Determine which aspect ratio to use based
		// on if its a landscape or portrait image.
		if ($ratio["width"] > $ratio["height"]) {
			$this->targetWidth = $this->sourceWidth / $ratio["height"];
			$this->targetHeight = $this->sourceHeight / $ratio["height"];
		} else {
			$this->targetWidth = $this->sourceWidth / $ratio["width"];
			$this->targetHeight = $this->sourceHeight / $ratio["width"];
		}
	}

	/**
	 * Set the new size automatically
	 * based on the image dimensions.
	 *
	 * @param integer $width
	 * @param integer $height
	 */
	private function setSize ($width, $height) {
		if ($this->sourceWidth > $this->sourceHeight)
			$this->setSizeWithFixedWidth ($width);
		else if ($this->sourceWidth < $this->sourceHeight)
			$this->setSizeWithFixedHeight ($height);
		else
			if ($width > $height)
				$this->setSizeWithFixedWidth ($width);
			else if ($width < $height)
				$this->setSizeWithFixedHeight ($height);
			else
				$this->setSizeExact ($width, $height);
	}

	/**
	 * Crop the target image with its
	 * aspect ratio uncompromised.
	 *
	 * @param integer $width
	 * @param integer $height
	 */
	private function crop ($width, $height) {
		// Calculate the X, Y coordinates
		// to use for cropping the images.
		$cropX = ($this->targetWidth - $width) / 2;
		$cropY = ($this->targetHeight - $height) / 2;
		// Create new canvas with the proper
		// dimensions and the xy-coordinates.
		// This will crop the image to the
		// with the image (mostly) centered.
		$cropImage = $this->targetImage;
	    $this->targetImage = imagecreatetruecolor($width , $height);
	    imagecopyresampled($this->targetImage, $cropImage, 0, 0, $cropX, $cropY, $width, $height , $width, $height);
	}

	/**
	 * Calculate height adjusted
	 * to the fixed width.
	 *
 	 * @param integer $width
	 */
	private function getHeightByWidth ($width) {
		return floor(($this->sourceHeight / $this->sourceWidth) * $width);
	}

	/**
	 * Calculate width adjusted
	 * to the fixed height.
	 *
 	 * @param integer $height
	 */
	private function getWidthByHeight ($height) {
		return floor(($this->sourceWidth / $this->sourceHeight) * $height);
	}

	/**
	 * Calls imagecreatefromjpeg function, but
	 * not before it checks if it exists.
	 * Implemented to make sure the php build
	 * has JPEG/PNG/GIF support enabled.
	 *
	 * @param string $file
	 * @return resource
	 */
	private function imageCreateFromJpeg ($file) {
		if (function_exists('imagecreatefromjpeg')
			&& is_callable('imagecreatefromjpeg')) {
			return imagecreatefromjpeg($file);
		} else {
			throw new Exception ("No JPEG support");
		}
	}

	/**
	 * Calls imageCreateFromPng function, but
	 * not before it checks if it exists.
	 * Implemented to make sure the php build
	 * has JPEG/PNG/GIF support enabled.
	 *
	 * @param string $file
	 * @return resource
	 */
	private function imageCreateFromPng ($file) {
		if (function_exists('imagecreatefrompng')
			&& is_callable('imagecreatefrompng')) {
			return imagecreatefrompng($file);
		} else {
			throw new Exception ("No PNG support");
		}
	}

	/**
	 * Calls imageCreateFromGif function, but
	 * not before it checks if it exists.
	 * Implemented to make sure the php build
	 * has JPEG/PNG/GIF support enabled.
	 *
	 * @param string $file
	 * @return resource
	 */
	private function imageCreateFromGif ($file) {
		if (function_exists('imagecreatefromgif')
			&& is_callable('imagecreatefromgif')) {
			return imagecreatefromgif($file);
		} else {
			throw new Exception ("No GIF support");
		}
	}

}

?>

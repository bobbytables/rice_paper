<?php

/**
 * Rice Paper CakePHP Component for easy image munipulation
 *
 * @package default
 * @author Robert Ross
 */
class RicePaperComponent extends Object {
	//called before Controller::beforeFilter()
	function initialize(&$controller, $settings = array()) {
		// saving the controller reference for later use
		$this->controller =& $controller;
	}

	//called after Controller::beforeFilter()
	function startup(&$controller) {
		
	}
	
	/**
	 * Define the image type to save as
	 *
	 * @return void
	 * @author Robert Ross
	 */
	function saveAs($ext = 'jpg'){
		if($ext == 'jpg'){
			$this->saveAs = 'imagejpeg';
			$this->ext    = 'jpg';
		} elseif($ext == 'png'){
			$this->saveAs = 'imagepng';
			$this->ext    = 'png';
		} elseif($ext = 'gif'){
			$this->saveAs = 'imagegif';
			$this->ext    = 'gif';
		}
		else {
			$this->saveAs = 'imagejpeg';
			$this->ext    = 'jpg';
		}
	}
	
	/**
	 * Save the image to the path with the saveAs extension
	 *
	 * @param string $path 
	 * @return void
	 * @author Robert Ross
	 */
	function save($image, $path){
		return call_user_func($this->saveAs, $image, sprintf('%s.%s', $path, $this->ext));
	}
	
	/**
	 * Load image into a resource
	 *
	 * @param string $imagePath 
	 * @return void
	 * @author Robert Ross
	 */
	function loadImage($imagePath){
		// Free up some resources if needed
		if(isset($this->image) && is_resource($this->image)){
			imagedestroy($this->image);
		}
		
		if(is_array($imagePath) && isset($imagePath['tmp_name'])){
			$imagePath = $imagePath['tmp_name'];
		} elseif(is_array($imagePath)){
			throw new Exception('Could not find image path');
		}
		
		$image = @imagecreatefromjpeg($imagePath);
		
		if(!$image){
			$image = @imagecreatefrompng($imagePath);
		}
		if(!$image){
			$image = @imagecreatefromgif($imagePath);
		}
		
		if(!$image){
			throw new Exception('Could not create image resource for '.$imagePath);
		}
		
		$this->image = $image;
	}
	
	/**
	 * Resize the image in the resource
	 *
	 * @param string $width 
	 * @param string $height 
	 * @param string $savePath 
	 * @param string $maintainAspect Force width and height or retain the ration as best as possible
	 * @return void
	 * @author Robert Ross
	 */
	function resize($width, $height, $savePath, $maintainAspect = true){
		$oldWidth  = $this->getWidth();
		$oldHeight = $this->getHeight();
		
		if($maintainAspect){
			$oldRatio = $oldWidth / $oldHeight;
			$newRatio = $width / $height;
			
			if($oldWidth <= $width && $oldHeight <= $height){
				$width = $oldWidth;
				$height = $oldHeight;
			} else if($newRatio > $oldRatio){
				$width = (int) $height * $oldRatio;
			} else if ($newRatio < $oldRatio){
				$height = (int) $width / $oldRatio;
			}
		}
		
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);
		
		return $this->save($new_image, $savePath);
	}
	
	/**
	 * Get width
	 *
	 * @return void
	 * @author Robert Ross
	 */
	function getWidth(){
		return imagesx($this->image);
	}
	
	/**
	 * Get height
	 *
	 * @return void
	 * @author Robert Ross
	 */
	function getHeight(){
		return imagesy($this->image);
	}
}
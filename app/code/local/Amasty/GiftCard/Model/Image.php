<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */
class Amasty_GiftCard_Model_Image extends Amasty_GiftCard_Model_Abstract
{
	const STATUS_ACTIVE		= 1;
	const STATUS_INACTIVE	= 0;

	const IMAGE_FIELD		= 'image_path';

	protected $imagePath 	= 'amasty_giftcard/image_templates';



	protected function _construct()
	{
		$this->_init('amgiftcard/image');
	}

	/**
	 * @param Amasty_GiftCard_Model_Uploader_Abstract $image
	 *
	 * @return $this
	 */
	public function setImage($image)
	{
		if ($image instanceof Varien_File_Uploader) {
			$this->unsImage();  // Delete old image
			$newImageName = uniqid()."_".$image->getRealCorrectFileName();
			$fileNameInfo = pathinfo($newImageName);
			$thumbName = $fileNameInfo['filename']."_thumb.".$fileNameInfo['extension'];
			$image->save($this->getImageDirPath(), $newImageName);
			$this->image_resize($this->getImageDirPath().$newImageName, $this->getImageDirPath().$thumbName, 256,256);
			$image = $image->getUploadedFileName();
		}
		$this->setData(self::IMAGE_FIELD, $image);
		return $this;
	}

	/**
	 * Path to directory image
	 * @return string
	 */
	public function getImageDirPath()
	{
		return Mage::getBaseDir('media') . DS . $this->imagePath . DS;
	}

	/**
	 * Path to image
	 * @return string
	 */
	public function getImagePath()
	{
		if ($image = $this->getData(self::IMAGE_FIELD)) {
			return Mage::getBaseDir('media') . DS . $this->imagePath . DS . $image;
		} else {
			return '';
		}
	}

	public function getImage()
	{
		return $this->getData(self::IMAGE_FIELD);
	}

	/**
	 * Web url to image
	 * @return string
	 */
	public function getImageUrl()
	{
		if ($image = $this->getData(self::IMAGE_FIELD)) {
			return Mage::getBaseUrl('media') . $this->imagePath . DS . $image;
		} else {
			return '';
		}
	}

	public function getThumbName()
	{
		if ($image = $this->getData(self::IMAGE_FIELD)) {
			$fileNameInfo = pathinfo($image);
			$thumbName = $fileNameInfo['filename']."_thumb.".$fileNameInfo['extension'];
			return $thumbName;
		} else {
			return '';
		}
	}

	public function getThumbUrl()
	{
		if ($image = $this->getThumbName()) {
			return Mage::getBaseUrl('media') . $this->imagePath . DS . $image;
		} else {
			return '';
		}
	}

	public function getThumbPath()
	{
		if ($image = $this->getThumbName()) {
			return Mage::getBaseDir('media') . $this->imagePath . DS . $image;
		} else {
			return '';
		}
	}

	/**
	 * Delete and unset image
	 * @return $this
	 */
	public function unsImage()
	{
		$image = $this->getOrigData(self::IMAGE_FIELD);
		if(!$image) {
			return $this;
		}
		$image = $this->getImageDirPath() . $image;
		if (file_exists($image) && is_file($image)) {
			unlink($image);
		}
		if(file_exists($this->getThumbPath()) && is_file($this->getThumbPath())) {
			unlink($this->getThumbPath());
		}
		$this->setData(self::IMAGE_FIELD, '');
		return $this;
	}


	/**
	 *
	 * TODO: refactor this with using Varien_Image class
	 * @param $sourse
	 * @param $new_image
	 * @param $width
	 * @param $height
	 */
	public function image_resize($sourse,$new_image,$width,$height)
	{
		$size = GetImageSize($sourse);
		$new_height = $height;
		$new_width = $width;

		if ($size[0] < $size[1])
			$new_width=($size[0]/$size[1])*$height;
		else
			$new_height=($size[1]/$size[0])*$width;
		$new_width=($new_width > $width)?$width:$new_width;
		$new_height=($new_height > $height)?$height:$new_height;
		$image_p = @imagecreatetruecolor($new_width, $new_height);
		if ($size[2]==IMAGETYPE_JPEG)
		{
			$image_cr = imagecreatefromjpeg($sourse);
		}
		else if ($size[2]==IMAGETYPE_PNG)
		{
			$image_cr = imagecreatefrompng($sourse);
		}
		else if ($size[2]==IMAGETYPE_GIF)
		{
			$image_cr = imagecreatefromgif($sourse);
		}
		imagecopyresampled($image_p, $image_cr, 0, 0, 0, 0, $new_width, $new_height, $size[0], $size[1]);
		if ($size[2]==IMAGETYPE_JPEG)
		{
			imagejpeg($image_p, $new_image, 75);
		}
		else if ($size[2]==IMAGETYPE_GIF)
		{
			imagegif($image_p, $new_image);
		}
		else if ($size[2]==IMAGETYPE_PNG)
		{
			imagepng($image_p, $new_image);
		}
	}

	protected function _beforeSave()
	{
		try {
			if(!empty($_FILES['image']['tmp_name'])) {
				$uploader = new Amasty_GiftCard_Model_Uploader_Image('image');
				$uploader->setAllowRenameFiles(true);
				$this->setImage($uploader);
			}
		} catch (Exception $e) {
			Mage::throwException($e);
		}
		return parent::_beforeSave();
	}


	/**
	 * @return $this
	 */
	protected function _afterDeleteCommit()
	{
		$this->unsImage();
		parent::_afterDeleteCommit();
		return $this;
	}





}
<?php

/**
 * PHP version 5
 * @copyright  InfinitySoft 2011 - 2013
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    QRCode
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


// load qr library
require_once(TL_ROOT . '/system/modules/qrcode/vendor/phpqrcode/qrlib.php');


/**
 * Class QRCode 
 *
 * @copyright  InfinitySoft 2011 - 2013
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    QRCode
 */
class QRCodeGenerator extends Controller
{
	/**
	 * Singleton
	 * 
	 * @var QRCodeGenerator
	 */
	protected static $objInstance = null;
	
	
	/**
	 * Get singleton instance.
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null)
		{
			self::$objInstance = new QRCodeGenerator();
		}
		return self::$objInstance;
	}
	
	
	/**
	 * Singleton
	 */
	protected function __construct() {}
	
	
	/**
	 * Generate a QR Code and return the filename. 
	 * 
	 * @param string $strContent
	 * @param int $intEclevel
	 * @param int $intSize
	 * @param int $intMargin
	 */
	public static function generate($strContent, $intEclevel = QR_ECLEVEL_L, $intSize = 3, $intMargin = 4)
	{
		return self::getInstance()->_generate($strContent, $intEclevel, $intSize, $intMargin);
	}
	
	
	/**
	 * Singleton method.
	 * 
	 * @param string $strContent
	 * @param int $intEclevel
	 * @param int $intSize
	 * @param int $intMargin
	 */
	protected function _generate($strContent, $intEclevel = QR_ECLEVEL_L, $intSize = 3, $intMargin = 4)
	{
		if (is_string($intEclevel))
		{
			switch (strtoupper($intEclevel))
			{
			case 'H': $intEclevel = QR_ECLEVEL_H; break;
			case 'Q': $intEclevel = QR_ECLEVEL_Q; break;
			case 'M': $intEclevel = QR_ECLEVEL_M; break;
			default:  $intEclevel = QR_ECLEVEL_L; break;
			}
		}
		
		$strFile = 'assets/images/qrcode-' . substr(md5($intEclevel.'-'.$intSize.'-'.$intMargin.'-'.$strContent), 0, 8) . '.png';
		
		if (!file_exists(TL_ROOT . '/' . $strFile))
		{
			QRcode::png($strContent, TL_ROOT . '/' . $strFile, $intEclevel, $intSize, $intMargin);
		}
		
		return $strFile;
	}
	
	
	/**
	 * Add qrcode to template.
	 * 
	 * @param [Module|ContentElement] $objData
	 * @param FrontendTemplate $objData
	 */
	public static function addQRCodeToTemplate($objData, $objTemplate)
	{
		self::getInstance()->_addQRCodeToTemplate($objData, $objTemplate);
	}
	
	
	/**
	* Sigleton method.
	*/
	public function _addQRCodeToTemplate($objData, $objTemplate)
	{
		if ($objData instanceof Module)
		{
			$size = deserialize($objData->imgSize);
		}
		else
		{
			if (in_array($objData->floating, array('left', 'right')))
			{
				$objTemplate->floatClass = ' float_' . $objData->floating;
				$objTemplate->float = 'float:' . $objData->floating . ';';
			}
			
			$size = deserialize($objData->size);
		}
		
		$objTemplate->alt = specialchars($objData->alt);
		$objTemplate->fullsize = $objData->fullsize ? true : false;
		$objTemplate->margin = $this->generateMargin(deserialize($objData->imagemargin), 'margin');
		$objTemplate->qrcode = QRCodeGenerator::generate($objData->replaceInsertTags($objData->qrcode), $objData->qrcode_ecclevel, $objData->qrcode_size, $objData->qrcode_margin);
		
		// Image link
		if (strlen($objData->imageUrl) && TL_MODE == 'FE')
		{
			$objTemplate->href = $objData->imageUrl;
			$objTemplate->attributes = $objData->fullsize ? LINK_NEW_WINDOW : '';
		}
		
		// Fullsize view
		elseif ($objData->fullsize && TL_MODE == 'FE')
		{
			$objTemplate->href = $objTemplate->qrcode;
			$objTemplate->attributes = ' rel="lightbox"';
		}
		
		if ($size[0] > 0 || $size[1] > 0)
		{
			$objTemplate->src    = $this->getImage($objTemplate->qrcode, $size[0], $size[1], $size[2]);
			$objTemplate->width  = $size[0];
			$objTemplate->height = $size[1];
		}
		else
		{
			$objTemplate->src   = $objTemplate->qrcode;
		}
		
		// Image dimensions
		if (($imgSize = @getimagesize(TL_ROOT .'/'. $objTemplate->src)) !== false)
		{
			$objTemplate->width  = $imgSize[0];
			$objTemplate->height = $imgSize[1];
		}
	}
}

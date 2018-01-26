<?php
/**
 * PHP Portable Code Kernel
 * Kernel library file to provide an object interface to the GD library.
 *
 * $Id: libGD.php 1623 2011-01-23 14:19:56Z tompsonn $
 *
 * @author		$Author: tompsonn $
 * @version		$Revision: 1623 $
 * @copyright	(c) 2009 Futurion Power Interactive
 * @license 	http://opensource.org/licenses/gpl-2.0.php
 * @package		coreKernel
 *
 **/

#if ( !defined( 'KERNEL_INIT' ) )
#	die( 'Permission denied. You cannot access this file directly.' );

/* Constant fix ups for E_NOTICE compatibility */
if ( PHP_VERSION < 5.3 )
{
	if ( ! defined( 'IMG_FILTER_PIXELATE' ) )
	{
		/**
		 * Special GD filter used by the imagefilter() function. 
		 * 
		 * Added in PHP 5.3.0, defined here to fix E_NOTICE with "undefined" constant
		 * in previous versions.
		 */
		define( 'IMG_FILTER_PIXELATE', 11 );
	}

	if ( ! defined( 'IMAGETYPE_ICO' ) )
	{
		/**
		 * Image type constant used by the image_type_to_mime_type() and image_type_to_extension() functions. 
		 * 
		 * Added in PHP 5.3.0, defined here to fix E_NOTICE with "undefined" constant
		 * in previous versions.
		 */
		define( 'IMAGETYPE_ICO', 17 );
	}
}

/**
 * GD Library: Static, global methods - callable without an image resource
 * instance.
 *
 * @abstract
 * @package		coreKernel::Lib
 */
abstract class GD
{
	/* 
	 * Re-defining the GD library constants is probably un-necessary
	 * but seeing as this is a wrapper, I would like to be complete.
	 */
	
	/**#@+
	 * Image Types 
	 */
	const IMG_GIF			= IMG_GIF;
	const IMG_JPG			= IMG_JPG;
	const IMG_JPEG			= IMG_JPG;
	const IMG_PNG			= IMG_PNG;
	const IMG_WBMP			= IMG_WBMP;
	const IMG_XPM			= IMG_XPM;
	
	/* Below are used for image_type_to_* functions */
	const IMAGETYPE_GIF		= IMAGETYPE_GIF;
	const IMAGETYPE_JPEG		= IMAGETYPE_JPEG;
	const IMAGETYPE_JPEG2000	= IMAGETYPE_JPEG2000;
	const IMAGETYPE_PNG		= IMAGETYPE_PNG;
	const IMAGETYPE_SWF		= IMAGETYPE_SWF;
	const IMAGETYPE_PSD		= IMAGETYPE_PSD;
	const IMAGETYPE_BMP		= IMAGETYPE_BMP;
	const IMAGETYPE_WBMP		= IMAGETYPE_WBMP;
	const IMAGETYPE_XBM		= IMAGETYPE_XBM;
	const IMAGETYPE_TIFF_II		= IMAGETYPE_TIFF_II;
	const IMAGETYPE_TIFF_MM		= IMAGETYPE_TIFF_MM;
	const IMAGETYPE_IFF		= IMAGETYPE_IFF;
	const IMAGETYPE_JB2		= IMAGETYPE_JB2;
	const IMAGETYPE_JPC		= IMAGETYPE_JPC;
	const IMAGETYPE_JP2		= IMAGETYPE_JP2;
	const IMAGETYPE_JPX		= IMAGETYPE_JPX;
	const IMAGETYPE_SWC		= IMAGETYPE_SWC;
	const IMAGETYPE_ICO		= IMAGETYPE_ICO;
	/**#@-*/
		
	/**#@+
	 * Image Colors 
	 */
	const IMG_COLOR_TILED			= IMG_COLOR_TILED;
	const IMG_COLOR_STYLED			= IMG_COLOR_STYLED;
	const IMG_COLOR_BRUSHED			= IMG_COLOR_BRUSHED;
	const IMG_COLOR_STYLEDBRUSHED		= IMG_COLOR_STYLEDBRUSHED;
	const IMG_COLOR_TRANSPARENT		= IMG_COLOR_TRANSPARENT;
	/**#@-*/
	
	/**#@+
	 * Arc Styles 
	 */
	const IMG_ARC_PIE		= IMG_ARC_PIE;
	const IMG_ARC_CHORD	= IMG_ARC_CHORD;
	const IMG_ARC_NOFILL	= IMG_ARC_NOFILL;
	const IMG_ARC_EDGED	= IMG_ARC_EDGED;
	/**#@-*/
	
	/**#@+ 
	 * GD2 
	 */
	const IMG_GD2_RAW			= IMG_GD2_RAW;
	const IMG_GD2_COMPRESSED	= IMG_GD2_COMPRESSED;
	/**#@-*/
	
	/**#@+ 
	 * Alpha Blending Effects
	 */
	const IMG_EFECT_REPLACE		= IMG_EFFECT_REPLACE;
	const IMG_EFFECT_ALPHABLEND	= IMG_EFFECT_ALPHABLEND;
	const IMG_EFFECT_NORMAL		= IMG_EFFECT_NORMAL;
	/**#@-*/
	
	/**#@+ 
	 * GD Filters 
	 */
	const IMG_FILTER_NEGATE			= IMG_FILTER_NEGATE;
	const IMG_FILTER_GRAYSCALE		= IMG_FILTER_GRAYSCALE;
	const IMG_FILTER_BRIGHTNESS		= IMG_FILTER_BRIGHTNESS;
	const IMG_FILTER_CONTRAST		= IMG_FILTER_CONTRAST;
	const IMG_FILTER_COLORIZE		= IMG_FILTER_COLORIZE;
	const IMG_FILTER_EDGEDETECT		= IMG_FILTER_EDGEDETECT;
	const IMG_FILTER_GAUSSIAN_BLUR	= IMG_FILTER_GAUSSIAN_BLUR;
	const IMG_FILTER_SELECTIVE_BLUR	= IMG_FILTER_SELECTIVE_BLUR;
	const IMG_FILTER_EMBOSS			= IMG_FILTER_EMBOSS;
	const IMG_FILTER_MEAN_REMOVAL		= IMG_FILTER_MEAN_REMOVAL;
	const IMG_FILTER_SMOOTH			= IMG_FILTER_SMOOTH;
	const IMG_FILTER_PIXELATE		= IMG_FILTER_PIXELATE;
	/**#@-*/
	
	/**#@+
	 * PNG Filters
	 */
	const PNG_NO_FILTER	= PNG_NO_FILTER;
	const PNG_FILTER_NONE	= PNG_FILTER_NONE;
	const PNG_FILTER_SUB	= PNG_FILTER_SUB;
	const PNG_FILTER_UP	= PNG_FILTER_UP;
	const PNG_FILTER_AVG	= PNG_FILTER_AVG;
	const PNG_FILTER_PAETH	= PNG_FILTER_PAETH;
	const PNG_ALL_FILTERS	= PNG_ALL_FILTERS;
	/**#@-*/
	
	/**
	 * Initialized Flag
	 * 
	 * @static
	 * @access	protected
	 * @var	boolean
	 */
	protected static $initted		= false;
	
	/**
	 * Initialized Status
	 * 
	 * @static	
	 * @access	protected
	 * @var	boolean
	 */
	protected static $inittedStatus	= null;
	
	/**
	 * Initialize
	 * 
	 * Sets up the GD library and makes sure that we support GD 2.0.
	 * 
	 * @access	public
	 * @return	void
	 */
	public static function init()
	{
		/* INIT */
		if ( ! self::$initted )
		{
			/* Redirect to availability method and find out if we support GD */
			self::$inittedStatus = self::getGdAvailability();
			self::$initted = true;
			
			/* Check for some extra support */
			/**
			 * GD Supports PostScript
			 * 
			 * Flag indicating whether or not this PHP build supports PostScript
			 * functions in GD
			 */
			define( 'GD_SUPPORTS_PSCRIPT', ( self::$inittedStatus && function_exists( 'imagepsloadfont' ) ) ? true : false );
				
			/**
			 * GD Supports Alpha
			 * 
			 * Flag indicating whether or not this PHP build supports alpha blending
			 * functions in GD
			 */
			define( 'GD_SUPPORTS_ALPHA', ( self::$inittedStatus && function_exists( 'imagealphablending' ) ) ? true : false );
			
			/**
			 * GD Supports FreeType
			 * 
			 * Flag indicating whether or not this PHP build supports FreeType
			 * functions in GD
			 */
			define( 'GD_SUPPORTS_FTYPE', ( self::$inittedStatus && 
					( ( $_i = self::gdInfo() ) && $_i['FreeType Support'] == true ) ) ? true : false );
		}
		
		return self::$inittedStatus;
	}
	
	/**
	 * Get GD Availability
	 * 
	 * Checks to make sure we have the GD extension loaded and that it is version 2.0
	 * or higher.
	 * 
	 * @access	protected
	 * @return	boolean
	 */
	protected static function getGdAvailability()
	{
		/* Get GD version */
		static $gdIsAvailable = null;
		
		if ( $gdIsAvailable == null )
		{
			$gdInfo = null;
			$requiredVersion = 2.0;
						
			if ( ! extension_loaded( 'gd' ) || ( ! ( $gdInfo = self::gdInfo() ) || ! is_array( $gdInfo ) ) )
			{
				/* Don't even have the extension loaded, bail now */
				return ( $gdIsAvailable = false );
			}
	
			$gdInfo['GD Version'] = str_replace( "bundled (",	"", $gdInfo['GD Version'] );
			$gdInfo['GD Version'] = str_replace( " compatible",	"", $gdInfo['GD Version'] );
			
			list ( $major, $minor ) = explode( ".", $gdInfo['GD Version'] );
			$gdIsAvailable = ( ( ( float ) $major . '.' . $minor ) >= $requiredVersion ) ? true : false;
		}
		
		return $gdIsAvailable;
	}
	
	/* Static Interface: Core */
	/**
	 * GD Info
	 * 
	 * Retrieve information about the currently installed GD library.
	 * 
	 * @access	public
	 * @return	array
	 */
	public static function gdInfo()
	{
		static $gdInfo;
		
		if ( ! $gdInfo )
		{
			$gdInfo = gd_info();
			
			/* PHP 5.3 changed the JPG attribute to "JPEG" */
			if ( isset( $gdInfo['JPEG Support'] ) )
			{
				$gdInfo['JPG Support'] =& $gdInfo['JPEG Support'];
			}
		}
		
		return $gdInfo;
	}
	
	/**
	 * Get Image Size
	 * 
	 * Get the size of an image.
	 * 
	 * @access	public
	 * @param	string	Filename	This parameter specifies the file you wish to retrieve information about. 
	 *						It can reference a local file or (configuration permitting) a remote file using one of the supported streams. 
	 * @param	array		Image Info	This optional parameter allows you to extract some extended information from the image file. 
	 *						Currently, this will return the different JPG APP markers as an associative array. Some programs use these APP markers to embed text information in images. 
	 *						A very common one is to embed IPTC information in the APP13 marker. 
	 *						You can use the iptcparse() function to parse the binary APP13 marker into something readable. 
	 * @return	array
	 */
	public static function getImageSize( $fileName, array &$imageInfo = array() )
	{
		return getimagesize( $fileName, $imageInfo );
	}
	
	/**
	 * Get Image Types
	 * 
	 * Return the image types supported by this PHP build.
	 * 
	 * @access	public
	 * @return	array
	 */
	public static function getImageTypes()
	{
		static $imageTypes;
		return ( $imageTypes ) ? $imageTypes : ( $imageTypes = imagetypes() );
	}
	
	
	/**
	 * Image Type To Extension
	 * 
	 * Get file extension for image type.
	 * 
	 * @access	public
	 * @param	integer	Image type		One of the IMAGETYPE_XXX constant.
	 * @param	boolean	Include dot		Whether to prepend a dot to the extension or not. Default to TRUE. 
	 * @return	string
	 */
	public static function imageTypeToExtension( $imageType, $includeDot = true )
	{
		static $typeMap;
		$_d = ( bool ) $includeDot;

		return ( isset( $typeMap[ $imageType . $_d ] ) ) 
			? $typeMap[ $imageType . $_d ] 
			: ( $typeMap[ $imageType . $_d ] = image_type_to_extension( $imageType, $includeDot ) );
	}
	
	/**
	 * Image Type To Mime Type
	 * 
	 * Determine the Mime-Type for an IMAGETYPE constant. 
	 * 
	 * @access	public
	 * @param	integer	Image type	One of the IMAGETYPE_XXX constants.
	 * @return	string
	 */
	public static function imageTypeToMimeType( $imageType )
	{
		static $typeMap;
		return ( isset( $typeMap[ $imageType ] ) ) 
			? $typeMap[ $imageType ] 
			: ( $typeMap[ $imageType ] = image_type_to_mime_type( $imageType ) );
	}
	
	/**
	 * Image Copy
	 * 
	 * Copy part of an image.
	 * 
	 * @access	public
	 * @param	object	Destination image.
	 * @param	object	Source image.
	 * @param	integer	x-coordinate of destination point. 
	 * @param	integer	y-coordinate of destination point. 
	 * @param	integer	x-coordinate of source point. 
	 * @param	integer	y-coordinate of source point. 
	 * @param	integer	Source width. 
	 * @param	integer	Source height.
	 * @return	boolean
	 */
	public static function imageCopy( GDImage $dst, GDImage $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH )
	{
		return ( $dst && $src ) 
			? imagecopy( $dst->resource, $src->resource, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH ) 
			: false;
	}
	
	/**
	 * Image Copy Merge
	 * 
	 * Copy and merge part of an image.
	 * 
	 * @access	public
	 * @param	object	Destination image.
	 * @param	object	Source image.
	 * @param	integer	x-coordinate of destination point. 
	 * @param	integer	y-coordinate of destination point. 
	 * @param	integer	x-coordinate of source point. 
	 * @param	integer	y-coordinate of source point. 
	 * @param	integer	Source width. 
	 * @param	integer	Source height.
	 * @param	integer	Percent				The two images will be merged according to pct which can range from 0 to 100. 
	 *									When pct = 0, no action is taken, when 100 this function behaves identically to imagecopy() for pallete images, 
	 *									while it implements alpha transparency for true colour images.
	 * @return	boolean
	 */
	public static function imageCopyMerge( GDImage $dst, GDImage $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $pct )
	{
		return ( $dst && $src ) 
			? imagecopymerge( $dst->resource, $src->resource, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $pct ) 
			: false;
	}
	
	/**
	 * Image Copy Merge Gray
	 * 
	 * Copy and merge part of an image with gray scale.
	 * 
	 * @access	public
	 * @param	object	Destination image.
	 * @param	object	Source image.
	 * @param	integer	x-coordinate of destination point. 
	 * @param	integer	y-coordinate of destination point. 
	 * @param	integer	x-coordinate of source point. 
	 * @param	integer	y-coordinate of source point. 
	 * @param	integer	Source width. 
	 * @param	integer	Source height.
	 * @param	integer	Percent				The two images will be merged according to pct which can range from 0 to 100. 
	 *									When pct = 0, no action is taken, when 100 this function behaves identically to imagecopy() for pallete images, 
	 *									while it implements alpha transparency for true colour images.
	 * @return	boolean
	 */
	public static function imageCopyMergeGray( GDImage $dst, GDImage $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $pct )
	{
		return ( $dst && $src ) 
			? imagecopymergegray( $dst->resource, $src->resource, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $pct ) 
			: false;
	}
	
	/**
	 * Image Copy Resampled
	 * 
	 * Copy and resize part of an image with resampling.
	 * 
	 * @access	public
	 * @param	object	Destination image.
	 * @param	object	Source image.
	 * @param	integer	x-coordinate of destination point. 
	 * @param	integer	y-coordinate of destination point. 
	 * @param	integer	x-coordinate of source point. 
	 * @param	integer	y-coordinate of source point. 
	 * @param	integer	Destination width. 
	 * @param	integer	Destination height.
	 * @param	integer	Source width. 
	 * @param	integer	Source height.
	 * @return	boolean
	 */
	public static function imageCopyResampled( GDImage $dst, GDImage $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH )
	{
		return ( $dst && $src ) 
			? imagecopyresampled( $dst->resource, $src->resource, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH ) 
			: false;
	}
	
	/**
	 * Image Copy Resized
	 * 
	 * Copy and resize part of an image.
	 * 
	 * @access	public
	 * @param	object	Destination image.
	 * @param	object	Source image.
	 * @param	integer	x-coordinate of destination point. 
	 * @param	integer	y-coordinate of destination point. 
	 * @param	integer	x-coordinate of source point. 
	 * @param	integer	y-coordinate of source point. 
	 * @param	integer	Destination width. 
	 * @param	integer	Destination height.
	 * @param	integer	Source width. 
	 * @param	integer	Source height.
	 * @return	boolean
	 */
	public static function imageCopyResized( GDImage $dst, GDImage $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH )
	{
		return ( $dst && $src ) 
			? imagecopyresized( $dst->resource, $src->resource, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH ) 
			: false;
	}
	
	/**
	 * Image Destroy
	 * 
	 * Destroy an image.
	 * 
	 * @access	public
	 * @param	object	Image to destroy.
	 * @return	boolean
	 */
	public static function imageDestroy( GDImage $image )
	{
		return ( $image && $image->resource ) ? imagedestroy( $image->resource ) : false;
	}
	
	/**
	 * Image Palette Copy
	 * 
	 * Copy the palette from one image to another.
	 * 
	 * @access	public
	 * @param	object	Destination image.
	 * @param	object	Source image.
	 * @return	void
	 */
	public static function imagePaletteCopy( GDImage $dst, GDImage $src )
	{
		return ( $dst && $src ) ? imagepalettecopy( $dst->resource, $src->resource ) : false;
	}
	
	/* Static Interface: Create Functions */
	/**
	 * Image Create
	 * 
	 * Create a new palette based image.
	 * 
	 * @access	public
	 * @param	integer	Width		The image width. 
	 * @param	integer	Height	The image height.
	 * @return	object
	 */
	public static function imageCreate( $width, $height )
	{
		return ( $_o = imagecreate( $width, $height ) ) ? GDImage::create( $_o ) : false;
	}
	
	/**
	 * Image Create Truecolor
	 * 
	 * Create a new true color image.
	 * 
	 * @access	public
	 * @param	integer	Width		The image width. 
	 * @param	integer	Height	The image height.
	 * @return	object
	 */
	public static function imageCreateTrueColor( $width, $height )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return ( $_o = imagecreatetruecolor( $width, $height ) ) ? GDImage::create( $_o ) : false;
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Image Create From GD2
	 * 
	 * Create a new image from GD2 file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the GD2 image. 
	 * @return	object
	 */
	public static function imageCreateFromGd2( $fileName )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return ( $_o = imagecreatefromgd2( $fileName ) ) ? GDImage::create( $_o ) : false;
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Image Create From GD2 (Part)
	 * 
	 * Create a new image from a given part of GD2 file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the GD2 image.
	 * @param	integer	X-coordinate of source point.
	 * @param	integer	Y-coordinate of source point.
	 * @param	integer	Source width.
	 * @param	integer	Source height.
	 * @return	object
	 */
	public static function imageCreateFromGd2Part( $fileName, $srcX, $srcY, $width, $height )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return ( $_o = imagecreatefromgd2part( $fileName, $srcX, $srcY, $width, $height ) ) 
					? GDImage::create( $_o ) : false;
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Image Create From GD
	 * 
	 * Create a new image from GD file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the GD image. 
	 * @return	object
	 */
	public static function imageCreateFromGd( $fileName )
	{
		return ( $_o = imagecreatefromgd( $fileName ) ) ? GDImage::create( $_o ) : false;
	}
	
	/**
	 * Image Create From GIF
	 * 
	 * Create a new image from GIF file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the GIF image. 
	 * @return	object
	 */
	public static function imageCreateFromGif( $fileName )
	{
		/* 
		 * GIF support was removed from the GD library in Version 1.6, and added back in Version 2.0.28. 
		 * This function is not available between these versions.
		 *
		 * Check support.
		 */
		if ( self::getImageTypes() & self::IMG_GIF )
		{
			return ( $_o = imagecreatefromgif( $fileName ) ) ? GDImage::create( $_o ) : false;
		}
		
		throw new Exception( "GIF support is not available with this version of the GD library" );
	}
	
	/**
	 * Image Create From JPEG
	 * 
	 * Create a new image from JPEG file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the JPEG image. 
	 * @return	object
	 */
	public static function imageCreateFromJpeg( $fileName )
	{
		return ( $_o = imagecreatefromjpeg( $fileName ) ) ? GDImage::create( $_o ) : false;
	}
	
	/**
	 * Image Create From PNG
	 * 
	 * Create a new image from PNG file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the PNG image. 
	 * @return	object
	 */
	public static function imageCreateFromPng( $fileName )
	{
		return ( $_o = imagecreatefrompng( $fileName ) ) ? GDImage::create( $_o ) : false;
	}
	
	/**
	 * Image Create From String
	 * 
	 * Create a new image from the image stream in the string.
	 * 
	 * @access	public
	 * @param	string	Data		A string containing the image data. 
	 * @return	object
	 */
	public static function imageCreateFromString( $data )
	{
		return ( $_o = imagecreatefromstring( $data ) ) ? GDImage::create( $_o ) : false;
	}
	
	/**
	 * Image Create From WBMP
	 * 
	 * Create a new image from WBMP file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the WBMP image. 
	 * @return	object
	 */
	public static function imageCreateFromWbmp( $fileName )
	{
		return ( $_o = imagecreatefromwbmp( $fileName ) ) ? GDImage::create( $_o ) : false;
	}
	
	/**
	 * Image Create From XBM
	 * 
	 * Create a new image from XBM file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the XBM image. 
	 * @return	object
	 */
	public static function imageCreateFromXbm( $fileName )
	{
		return ( $_o = imagecreatefromxbm( $fileName ) ) ? GDImage::create( $_o ) : false;
	}
	
	/**
	 * Image Create From XPM
	 * 
	 * Create a new image from XBM file or URL.
	 * 
	 * @access	public
	 * @param	string	Filename	Path to the XPM image. 
	 * @return	object
	 */
	public static function imageCreateFromXpm( $fileName )
	{
		/* No Windows implementation */
		if ( KERNEL_PHP_OS == 'WIN' )
		{
			throw new Exception( "This function '" .__METHOD__. "' is not implemented on Windows" );
		}
		else
		{
			/* Check support */
			if ( GD_BUNDLED )
			{
				return ( $_o = imagecreatefromxpm( $fileName ) ) ? GDImage::create( $_o ) : false;
			}
		}
		
		throw new Exception( "This function '" .__METHOD__. "' requires that PHP is compiled with the bundled version of the GD library." );
	}
	
	/* Static Interface: Color */
	/**
	 * Color Match
	 * 
	 * Makes the colors of the palette version of an image more closely match the true color version.
	 * 
	 * @access	public
	 * @param	object	A truecolor image link resource.
	 * @param	object	A palette image link resource pointing to an image that has the same size as image1. 
	 * @return	boolean
	 */
	public static function colorMatch( GDImage $image1, GDImage $image2 )
	{
		/* Check support */
		if ( GD_BUNDLED && GD_SUPPORTS_ALPHA )
		{
			return ( $image1 && $image2 ) ? imagecolormatch( $image1->resource, $image2->resource ) : false;
		}
		
		throw new Exception( "This function '" .__METHOD__. "' requires that PHP is compiled with the bundled version 
			of the GD library version 2.0.1 or later" );
	}
	
	/* Static Interface: IPTC */
	/**
	 * Embed IPTC Data
	 * 
	 * Embeds binary IPTC data into a JPEG image.
	 * 
	 * @access	public
	 * @param	string	Data		The data to be written.
	 * @param	string	JPEG File	Path to the JPEG image.
	 * @param	string	Spool		Spool flag. If the spool flag is over 2 then the JPEG will be returned as a string.
	 * @return	mixed
	 */
	public static function empedIptcData( $data, $jpegFileName, $spool = 0 )
	{
		return iptcembed( $data, $jpegFileName, $spool );
	}
	
	/**
	 * Parse IPTC Data
	 * 
	 * Parse a binary IPTC block into single tags.
	 * 
	 * @access	public
	 * @param	string	Data		A binary IPTC block.
	 * @return	array
	 */
	public static function parseIptcData( $data )
	{
		return iptcparse( $data );
	}
	
	/* Static Interface: Convert */
	/**
	 * Convert JPEG To WBMP
	 * 
	 * Convert JPEG image file to WBMP image file.
	 * 
	 * @access	public
	 * @param	string	JPEG Name		Path to JPEG file
	 * @param	string	WBMP Name		Path to destination WBMP file.
	 * @param	integer	Height		Destination image height.
	 * @param	integer	Width			Destination image width.
	 * @param	integer	Threshold value between 0 and 8 (inclusive)
	 * @return	boolean
	 */
	public static function convertJpegToWbmp( $jpegName, $wbmpName, $destHeight, $destWidth, $threshold )
	{
		return jpeg2wbmp( $jpegName, $wbmpName, $destHeight, $destWidth, $threshold );
	}
	
	/**
	 * Convert PNG To WBMP
	 *
	 * Convert PNG image file to WBMP image file.
	 * 
	 * @access	public
	 * @param	string	PNG Name		Path to PNG file
	 * @param	string	WBMP Name		Path to destination PNG file.
	 * @param	integer	Height		Destination image height.
	 * @param	integer	Width			Destination image width.
	 * @param	integer	Threshold value between 0 and 8 (inclusive)
	 * @return	boolean
	 */
	public static function convertPngToWbmp( $jpegName, $wbmpName, $destHeight, $destWidth, $threshold )
	{
		return png2wbmp( $jpegName, $wbmpName, $destHeight, $destWidth, $threshold );
	}
}

/**
 * GD Library: GD Image object wrapper.
 *
 * @abstract
 * @package		coreKernel::Lib
 */
class GDImage extends GD
{
	/**
	 * Resource Handle
	 * 
	 * @access	protected
	 * @var	resource
	 */
	protected $resource;
	
	/**
	 * Auto-Headers Flag
	 * 
	 * @access	private
	 * @var	boolean
	 */
	private $_headers;
	
	/**
	 * Constructor
	 * 
	 * Setup and load component to run.
	 * 
	 * @access	private
	 * @param	resource	Handle to the image resource.
	 * @return	void
	 */
	private function __construct( &$resource )
	{
		/* INIT */
		if ( $resource )
		{
			$this->resource =& $resource;
		}
	}
	
	/**
	 * Create Image
	 * 
	 * Takes a valid image resource and constructs a new GD Image wrapper object.
	 * 
	 * @access	protected
	 * @param	resource	Handle to the image resource.
	 * @return	object
	 */
	protected static function create( &$imageResource )
	{
		/* INIT */
		if ( $imageResource )
		{
			return new self( $imageResource );
		}
		
		return null;
	}
	
	/**
	 * Destructor
	 * 
	 * Destroys this image when the object is collected.
	 * 
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
		GD::imageDestroy( $this );
		$this->resource = null;
	}
	
	/**
	 * Destroy Image
	 * 
	 * Frees any memory associated with image, this just calls our destructor so we
	 * avoid free()'ing twice.
	 * 
	 * @access	public
	 * @return	boolean
	 */
	public function destroy()
	{
		if ( $this->resource )
		{
			$this->__destruct();
		}
	}
	
	/**
	 * Set Headers
	 * 
	 * Helper function to specify whether to automatically print headers when using the
	 * output functions.
	 * 
	 * @access	public
	 * @param	boolean	Flag true for on, false for off.
	 * @return	void
	 */
	public function setHeaders( $on = true )
	{
		$this->_headers = $on;
	}
	
	/* Object Interface: Core */	
	/**
	 * Is True Color
	 * 
	 * Finds whether an image is a truecolor image.
	 * 
	 * @access	public
	 * @return	boolean
	 */
	public function isTrueColor()
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imageistruecolor( $this->resource );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Convert True Color To Palette
	 * 
	 * Convert a true color image to a palette image.
	 * 
	 * @access	public
	 * @param	boolean	Dither	Indicates if the image should be dithered - if it is TRUE then dithering will 
	 *						be used which will result in a more speckled image but with better color approximation. 
	 * @param	integer	Sets the maximum number of colors that should be retained in the palette. 
	 * @return	boolean
	 */
	public function convertTrueColorToPalette( $dither, $nColors )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagetruecolortopalette( $this->resource, $dither, $nColors );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Get Width
	 * 
	 * Returns the width of the given image resource. 
	 * 
	 * @access	public
	 * @return	integer
	 */
	public function getWidth()
	{
		return imagesx( $this->resource );
	}
	
	/**
	 * Get Height
	 * 
	 * Returns the height of the given image resource. 
	 * 
	 * @access	public
	 * @return	integer
	 */
	public function getHeight()
	{
		return imagesy( $this->resource );
	}
	
	/**
	 * Set Alpha Blending
	 * 
	 * Set the blending mode for an image.
	 * 
	 * @access	public
	 * @param	boolean	Blend Mode		Whether to enable the blending mode or not. On true color images the default
	 *							value is TRUE otherwise the default value is FALSE.
	 * @return	boolean
	 */
	public function setAlphaBlending( $blendMode )
	{
		/* Check support (docs state this only available with 2.0.1+ */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagealphablending( $this->resource, $blendMode );
		}
		
		throw new Exception( "Alpha blending functions only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Set Antialias
	 * 
	 * Should antialias functions be used or not.
	 * 
	 * @access	public
	 * @param	boolean	Whether to enable antialiasing or not. 
	 * @return	boolean
	 */
	public function setAntiAlias( $enabled )
	{
		/* Check support */
		if ( GD_BUNDLED )
		{
			return imageantialias( $this->resource, $enabled );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' requires that PHP is compiled with the bundled version of the GD library." );
	}
	
	/**
	 * Set Interface
	 * 
	 * Enable or disable interlace.
	 * 
	 * @access	public
	 * @param	integer	If non-zero, the image will be interlaced, else the interlace bit is turned off. 
	 *				If null the method returns the current interlace state.
	 * @return	integer
	 */
	public function setInterlace( $interlace = null )
	{
		return ( $interlace === null ) ? imageinterlace( $this->resource ) : imageinterlace( $this->resource, $interlace );
	}
	
	/**
	 * Set Layer Effect
	 * 
	 * Set the alpha blending flag to use the bundled libgd layering effects.
	 * 
	 * @access	public
	 * @param	integer	Effect	One of the following constants: 
	 *							IMG_EFFECT_REPLACE	- Use pixel replacement (equivalent of passing TRUE to imagealphablending()) 
	 *							IMG_EFFECT_ALPHABLEND	- Use normal pixel blending (equivalent of passing FALSE to imagealphablending()) 
	 *							IMG_EFFECT_NORMAL		- Same as IMG_EFFECT_ALPHABLEND. 
	 *							IMG_EFFECT_OVERLAY	- Overlay has the effect that black background pixels will remain black, white background pixels will remain white, 
	 *											  but grey background pixels will take the colour of the foreground pixel. 
	 * @return	boolean
	 */
	public function setLayerEffect( $effect )
	{
		/* Check support */
		if ( GD_BUNDLED && GD_SUPPORTS_ALPHA )
		{
			return imagelayereffect( $this->resource, $effect );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' requires that PHP is compiled with the bundled version 
			of the GD library version 2.0.1 or later" );
	}
	
	/**
	 * Set Save Alpha
	 * 
	 * Set the flag to save full alpha channel information (as opposed to single-color transparency) 
	 * when saving PNG images.
	 * 
	 * @access	public
	 * @param	boolean	Whether to save the alpha channel or not. Default to FALSE. 
	 * @return	boolean
	 */
	public function setSaveAlpha( $saveFlag )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagesavealpha( $this->resource, $saveFlag );
		}
		
		throw new Exception( "Alpha blending functions only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Set Brush
	 * 
	 * Set the brush image for line drawing.
	 * 
	 * @access	public
	 * @param	object	Brush image resource.
	 * @return	booolean
	 */
	public function setBrush( GDImage $brush )
	{
		return ( $brush ) ? imagesetbrush( $this->resource, $brush->resource ) : false;
	}
	
	/**
	 * Set Pixel
	 * 
	 * Draws a pixel at the specified coordinate. 
	 * 
	 * @access	public
	 * @param	integer	x-coordinate.
	 * @param	integer	y-coordinate.
	 * @param	integer	A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function setPixel( $x, $y, $color )
	{
		return imagesetpixel( $this->resource, $x, $y, $color );
	}
	
	/**
	 * Set Style
	 * 
	 * Set the style for line drawing.
	 * 
	 * @access	public
	 * @param	array		An array of pixel colors. You can use the IMG_COLOR_TRANSPARENT constant to add a transparent pixel. 
	 * @return	boolean
	 */
	public function setStyle( array $style )
	{
		return imagesetstyle( $this->resource, $style );
	}
	
	/**
	 * Set Thickness
	 * 
	 * Set the thickness for line drawing.
	 * 
	 * @access	public
	 * @param	integer	Thickness, in pixels.
	 * @return	boolean
	 */
	public function setThickness( $thickness )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagesetthickness( $this->resource, $thickness );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Set Tile
	 * 
	 * Set the tile image for filling. 
	 * 
	 * NOTE: You need not take special action when you are finished with a tile, 
	 * but if you destroy the tile image, you must not use the IMG_COLOR_TILED color until you have set a new tile image! 
	 * 
	 * @access	public
	 * @param	object	Tile image resource.
	 * @return	boolean
	 */
	public function setTile( GDImage $tile )
	{
		return ( $tile ) ? imagesettile( $this->resource, $tile->resource ) : false;
	}
	
	/* Object Interface: Color */	
	/**
	 * Color Allocate
	 * 
	 * Returns a color identifier representing the color composed of the given RGB components. 
	 * 
	 * NOTE: The first call to imagecolorallocate() fills the background color in palette-based images -
	 * images created using imagecreate(). 
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @return	integer
	 */
	public function colorAllocate( $red, $green, $blue )
	{
		$_r = imagecolorallocate( $this->resource, $red, $green, $blue );
		return ( $_r != -1 && $_r !== false ) ? $_r : false; /* Prior to PHP 5.1.3 we get -1 if failed,
											* so make it standard (false).
											*/
	}
	
	/**
	 * Color Allocate Alpha
	 * 
	 * Returns a color identifier representing the color composed of the given RGB components,
	 * with the addition of the transparency parameter alpha. 
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @param	integer	A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent. 
	 * @return	integer
	 */
	public function colorAllocateAlpha( $red, $green, $blue, $alpha )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			$_r = imagecolorallocatealpha( $this->resource, $red, $green, $blue, $alpha );
			return ( $_r != -1 && $_r !== false ) ? $_r : false; /* Prior to PHP 5.1.3 we get -1 if failed,
												* so make it standard (false).
												*/
		}
		
		throw new Exception( "Alpha blending functions only available with GD 2.0.1 or later" );
	}

	/**
	 * Color De-Allocate
	 * 
	 * De-allocates a color previously allocated with imagecolorallocate() or imagecolorallocatealpha().
	 * 
	 * @access	public
	 * @param	integer	Color identifier.
	 * @return	boolean
	 */
	public function colorDeAllocate( $color )
	{
		return imagecolordeallocate( $this->resource, $color );
	}
	
	/**
	 * Get Color At
	 * 
	 * Get the index of the color of a pixel.
	 * 
	 * @access	public
	 * @param	integer	x-coordinate of the point. 
	 * @param	integer	y-coordinate of the point. 
	 * @return	integer
	 */
	public function getColorAt( $x, $y )
	{
		return imagecolorat( $this->resource, $x, $y );
	}
	
	/**
	 * Get Color Cloest
	 * 
	 * Get the index of the closest color to the specified color.
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @return	integer
	 */
	public function getColorClosest( $red, $green, $blue )
	{
		return imagecolorclosest( $this->resource, $red, $green, $blue );
	}
	
	/**
	 * Get Color Closest Alpha
	 * 
	 * Get the index of the closest color to the specified color + alpha.
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @param	integer	A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent. 
	 * @return	integer
	 */
	public function getColorClosestAlpha( $red, $green, $blue, $alpha )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagecolorcloestalpha( $this->resource, $red, $green, $blue, $alpha );
		}
		
		throw new Exception( "Alpha blending functions only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Get Color Cloest (Hue, White, Blackness)
	 * 
	 * Get the index of the color which has the hue, white and blackness.
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @return	integer
	 */
	public function getColorClosestHwb( $red, $green, $blue )
	{
		/* Only became available on Windows as of PHP 5.3 */
		if ( KERNEL_PHP_OS == 'WIN' && ! function_exists( 'imagecolorclosesthwb' ) )
		{
			throw new Exception( "This function '" .__METHOD__. "' is not available on Windows for your PHP build" );
		}
		
		return imagecolorclosesthwb( $this->resource, $red, $green, $blue );
	}
	
	/**
	 * Get Color Exact
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @return	integer
	 */
	public function getColorExact( $red, $green, $blue )
	{
		return imagecolorexact( $this->resource, $red, $green, $blue );
	}
	
	/**
	 * Get Color Exact Alpha
	 * 
	 * Get the index of the specified color + alpha.
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @param	integer	A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent. 
	 * @return	integer
	 */
	public function getColorExactAlpha( $red, $green, $blue, $alpha )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagecolorexactalpha( $this->resource, $red, $green, $blue, $alpha );
		}
		
		throw new Exception( "Alpha blending functions only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Color Resolve
	 * 
	 * Get the index of the specified color or its closest possible alternative.
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @return	integer
	 */
	public function colorResolve( $red, $green, $blue )
	{
		return imagecolorresolve( $this->resource, $red, $green, $blue );
	}
	
	/**
	 * Color Resolve Alpha
	 * 
	 * Get the index of the specified color + alpha or its closest possible alternative.
	 * 
	 * @access	public
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @param	integer	A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent. 
	 * @return	integer
	 */
	public function colorResolveAlpha( $red, $green, $blue, $alpha )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagecolorresolvealpha( $this->resource, $red, $green, $blue, $alpha );
		}
		
		throw new Exception( "Alpha blending functions only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Set Index Color
	 * 
	 * Set the color for the specified palette index.
	 * 
	 * @access	public
	 * @param	integer	An index in the palette. 
	 * @param	integer	Value of red component.
	 * @param	integer	Value of blue component.
	 * @param	integer	Value of green component.
	 * @param	integer	A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent. 
	 * @return	void
	 */
	public function setIndexColor( $index, $red, $green, $blue, $alpha = 0 )
	{
		imagecolorset( $this->resource, $index, $red, $green, $blue, $alpha );
	}
	
	/**
	 * Get Index Color
	 * 
	 * Gets the color for a specified index. 
	 * 
	 * @access	public
	 * @param	integer	The color index.
	 * @return	array
	 */
	public function getIndexColor( $index )
	{
		return imagecolorsforindex( $this->resource, $index );
	}
	
	/**
	 * Get Total Colors
	 * 
	 * Find out the number of colors in an image's palette
	 * 
	 * @access	public
	 * @return	integer
	 */
	public function getTotalColors()
	{
		return imagecolorstotal( $this->resource );
	}
	
	/**
	 * Set Transparent Color
	 * 
	 * Define a color as transparent.
	 * 
	 * @access	public
	 * @param	integer	A color identifier created with imagecolorallocate(). If NULL, the function
	 *				returns the current transparent color ID.
	 * @return	integer
	 */
	public function setTransparentColor( $color = null )
	{
		return ( $color === null ) ? imagecolortransparent( $this->resource ) : imagecolortransparent( $this->resource, $color );
	}
	
	/* Object Interface: Drawing */
	/**
	 * Apply Convolution
	 * 
	 * Applies a convolution matrix on the image, using the given coefficient and offset
	 * 
	 * @access	public
	 * @param	array		A 3x3 matrix: an array of three arrays of three floats. 
	 * @param	float		The divisor of the result of the convolution, used for normalization. 
	 * @param	float		Color offset. 
	 * @return	boolean
	 */
	public function applyConvolution( array $matrix, $div, $offset )
	{
		/* Check version */
		if ( GD_BUNDLED )
		{
			return imageconvolution( $this->resource, $matrix, $div, $offset );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' requires that PHP is compiled with the bundled version of the GD library." );
	}
	
	/**
	 * Apply Filter
	 * 
	 * Applies a filter to an image.
	 * 
	 * @access	public
	 * @param	integer	Filtertype	Can be one of the following: 
	 *					IMG_FILTER_NEGATE: Reverses all colors of the image.
	 *					IMG_FILTER_GRAYSCALE: Converts the image into grayscale.
	 *					IMG_FILTER_BRIGHTNESS: Changes the brightness of the image. Use arg1 to set the level of brightness.
	 *					IMG_FILTER_CONTRAST: Changes the contrast of the image. Use arg1 to set the level of contrast.
	 *					IMG_FILTER_COLORIZE: Like IMG_FILTER_GRAYSCALE, except you can specify the color. Use arg1, arg2 and arg3 in the 
	 *								   form of red, blue, green and arg4 for the alpha channel. The range for each color is 0 to 255.
	 *					IMG_FILTER_EDGEDETECT: Uses edge detection to highlight the edges in the image.
	 *					IMG_FILTER_EMBOSS: Embosses the image.
	 *					IMG_FILTER_GAUSSIAN_BLUR: Blurs the image using the Gaussian method.
	 *					IMG_FILTER_SELECTIVE_BLUR: Blurs the image.
	 *					IMG_FILTER_MEAN_REMOVAL: Uses mean removal to achieve a "sketchy" effect.
	 *					IMG_FILTER_SMOOTH: Makes the image smoother. Use arg1 to set the level of smoothness.
	 *					IMG_FILTER_PIXELATE: Applies pixelation effect to the image, use arg1 to set the block size and arg2 to set the pixelation effect mode.
	 * 
	 * @param	integer	IMG_FILTER_BRIGHTNESS: Brightness level.
	 *				IMG_FILTER_CONTRAST: Contrast level.
	 *				IMG_FILTER_COLORIZE: Value of red component.
	 *				IMG_FILTER_SMOOTH: Smoothness level.
	 *				IMG_FILTER_PIXELATE: Block size in pixels.
	 * 
	 * @param	integer	IMG_FILTER_COLORIZE: Value of green component.
	 *				IMG_FILTER_PIXELATE: Whether to use advanced pixelation effect or not (defaults to FALSE).
	 * 
	 * @param	integer	IMG_FILTER_COLORIZE: Value of blue component. 
	 * @param	integer	IMG_FILTER_COLORIZE: Alpha channel, A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent. 
	 * @return	boolean
	 */
	public function applyFilter( $filterType, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null )
	{
		/* Check support */
		if ( GD_BUNDLED )
		{
			return imagefilter( $this->resource, $filterType, $arg1, $arg2, $arg3, $arg4 );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' requires that PHP is compiled with the bundled version of the GD library." );
	}
	
	/**
	 * Apply Gamma Correct
	 * 
	 * Apply a gamma correction to a GD image.
	 * 
	 * @access	public
	 * @param	float		The input gamma.
	 * @param	float		The output gamma.
	 * @return	boolean
	 */
	public function applyGammaCorrect( $inputGamma, $outputGamma )
	{
		return imagegammacorrect( $this->resource, $inputGamma, $outputGamma );
	}
	
	/**
	 * Draw Arc
	 * 
	 * Draws an arc of circle centered at the given coordinates. 
	 * 
	 * @access	public
	 * @param	integer	x-coordinate of the center. 
	 * @param	integer	y-coordinate of the center. 
	 * @param	integer	The arc width. 
	 * @param	integer	The arc height. 
	 * @param	integer	The arc start angle, in degrees. 
	 * @param	integer	The arc end angle, in degrees. 0 deg is located at the three-o'clock position, and the arc is drawn clockwise. 
	 * @param	integer	A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function drawArc( $cx, $cy, $width, $height, $start, $end, $color )
	{
		return imagearc( $this->resource, $cx, $cy, $width, $height, $start, $end, $color );
	}
	
	/**
	 * Draw Character
	 * 
	 * Draw a character horizontally.
	 * 
	 * @access	public
	 * @param	object	GD Font object
	 * @param	integer	x-coordinate of the start. 
	 * @param	integer	y-coordinate of the start. 
	 * @param	string	The character to draw. 
	 * @param	integer	A color identifier created with imagecolorallocate().
	 * @return	boolean
	 */
	public function drawChar( GDFont $font, $x, $y, $c, $color )
	{
		return ( $font ) ? imagechar( $this->resource, $font->identifier, $x, $y, $c, $color ) : false;
	}
	
	/**
	 * Draw Character Up
	 * 
	 * Draw a character vertically.
	 * 
	 * @access	public
	 * @param	object	GD Font object
	 * @param	integer	x-coordinate of the start. 
	 * @param	integer	y-coordinate of the start. 
	 * @param	string	The character to draw. 
	 * @param	integer	A color identifier created with imagecolorallocate().
	 * @return	boolean
	 */
	public function drawCharUp( GDFont $font, $x, $y, $c, $color )
	{
		return ( $font ) ? imagecharup( $this->resource, $font->identifier, $x, $y, $c, $color ) : false;
	}
	
	/**
	 * Draw Ellipse
	 * 
	 * Draws an ellipse centered at the specified coordinates. 
	 * 
	 * @access	public
	 * @param	integer	x-coordinate of the center. 
	 * @param	integer	y-coordinate of the center. 
	 * @param	integer	The ellipse width. 
	 * @param	integer	The ellipse height. 
	 * @param	integer	A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function drawEllipse( $cx, $cy, $width, $height, $color )
	{
		/* Check support */
		if ( function_exists( 'imageellipse' ) )
		{
			return imageellipse( $this->resource, $cx, $cy, $width, $height, $color );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.2 or later" );
	}
	
	/**
	 * Draw Filled Arc
	 * 
	 * Draw a partial arc and fill it.
	 * 
	 * @access	public
	 * @param	integer	x-coordinate of the center. 
	 * @param	integer	y-coordinate of the center. 
	 * @param	integer	The arc width. 
	 * @param	integer	The arc height. 
	 * @param	integer	The arc start angle, in degrees. 
	 * @param	integer	The arc end angle, in degrees. 0 deg is located at the three-o'clock position, and the arc is drawn clockwise. 
	 * @param	integer	A color identifier created with imagecolorallocate(). 
	 * @param	integer	A bitwise OR of the following possibilities:
	 *					  1. IMG_ARC_PIE
	 *					  2. IMG_ARC_CHORD
	 *					  3. IMG_ARC_NOFILL
	 *					  4. IMG_ARC_EDGED
	 *				IMG_ARC_PIE and IMG_ARC_CHORD are mutually exclusive; IMG_ARC_CHORD just connects the starting and 
	 *				ending angles with a straight line, while IMG_ARC_PIE produces a rounded edge. IMG_ARC_NOFILL indicates
	 *				that the arc or chord should be outlined, not filled. IMG_ARC_EDGED, used together with IMG_ARC_NOFILL, 
	 *				indicates that the beginning and ending angles should be connected to the center - this is a good 
	 *				way to outline (rather than fill) a 'pie slice'. 
	 *
	 * @return	boolean
	 */
	public function drawFilledArc( $cx, $cy, $width, $height, $start, $end, $color, $style )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagefilledarc( $this->resource, $cx, $cy, $width, $height, $start, $end, $color, $style );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Draw Filled Ellipse
	 * 
	 * Draw a filled ellipse.
	 * 
	 * @access	public
	 * @param	integer	x-coordinate of the center. 
	 * @param	integer	y-coordinate of the center. 
	 * @param	integer	The ellipse width. 
	 * @param	integer	The ellipse height. 
	 * @param	integer	A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function drawFilledEllipse( $cx, $cy, $width, $height, $color )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagefilledellipse( $this->resource, $cx, $cy, $width, $height, $color );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Draw Filled Polygon
	 * 
	 * Draw a filled polygon.
	 * 
	 * @access	public
	 * @param	array		An array containing the x and y coordinates of the polygons vertices consecutively. 
	 * @param	integer	Total number of vertices, which must be at least 3. 
	 * @param	integer	A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function drawFilledPolygon( array $points, $numPoints, $color )
	{
		return imagefilledpolygon( $this->resource, $points, $numPoints, $color );
	}
	
	/**
	 * Draw Filled Rectangle
	 * 
	 * Draw a filled rectangle.
	 * 
	 * @access	public
	 * @param	integer	x-coordinate for point 1. 
	 * @param	integer	y-coordinate for point 1. 
	 * @param	integer	x-coordinate for point 2. 
	 * @param	integer	y-coordinate for point 2. 
	 * @param	integer	The fill color. A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function drawFilledRectangle( $x1, $y1, $x2, $y2, $color )
	{
		return imagefilledrectangle( $this->resource, $x1, $y1, $x2, $y2, $color );
	}
	
	/**
	 * Draw Line
	 * 
	 * Draws a line between the two given points. 
	 * 
	 * @access	public
	 * @param	integer	x-coordinate for first point. 
	 * @param	integer	y-coordinate for first point. 
	 * @param	integer	x-coordinate for second point. 
	 * @param	integer	y-coordinate for second point. 
	 * @param	integer	The line color. A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function drawLine( $x1, $y1, $x2, $y2, $color )
	{
		return imageline( $this->resource, $x1, $y1, $x2, $y2, $color );
	}
	
	/**
	 * Draw Polygon
	 * 
	 * Draws a polygon.
	 * 
	 * @access	public
	 * @param	array		An array containing the x and y coordinates of the polygons vertices consecutively. 
	 * @param	integer	Total number of vertices, which must be at least 3. 
	 * @param	integer	A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function drawPolygon( array $points, $numPoints, $color )
	{
		return imagepolygon( $this->resource, $points, $numPoints, $color );
	}
	
	/**
	 * Draw Rectangle
	 * 
	 * Draws a rectangle.
	 * 
	 * @access	public
	 * @param	integer	x-coordinate for point 1. 
	 * @param	integer	y-coordinate for point 1. 
	 * @param	integer	x-coordinate for point 2. 
	 * @param	integer	y-coordinate for point 2. 
	 * @param	integer	The fill color. A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function drawRectangle( $x1, $y1, $x2, $y2, $color )
	{
		return imagerectangle( $this->resource, $x1, $y1, $x2, $y2, $color );
	}
	
	/**
	 * Draw String
	 * 
	 * Draw a string horizontally.
	 * 
	 * @access	public
	 * @param	object	GD Font object
	 * @param	integer	x-coordinate of the upper left corner. 
	 * @param	integer	y-coordinate of the upper left corner. 
	 * @param	string	The string to be written. 
	 * @param	integer	A color identifier created with imagecolorallocate().
	 * @return	boolean
	 */
	public function drawString( GDFont $font, $x, $y, $string, $color )
	{
		return ( $font ) ? imagestring( $this->resource, $font->identifier, $x, $y, $string, $color ) : false;
	}
	
	/**
	 * Draw String Up
	 * 
	 * Draw a string vertically.
	 * 
	 * @access	public
	 * @param	object	GD Font object
	 * @param	integer	x-coordinate of the bottom left corner. 
	 * @param	integer	y-coordinate of the bottom left corner. 
	 * @param	string	The string to be written. 
	 * @param	integer	A color identifier created with imagecolorallocate().
	 * @return	boolean
	 */
	public function drawStringUp( GDFont $font, $x, $y, $string, $color )
	{
		return ( $font ) ? imagestringup( $this->resource, $font->identifier, $x, $y, $string, $color ) : false;
	}
	
	/**
	 * Image Fill
	 * 
	 * Performs a flood fill starting at the given coordinate (top left is 0, 0) with the given color in the image. 
	 * 
	 * @access	public
	 * @param	integer	x-coordinate of start point.
	 * @param	integer	y-coordinate of start point.
	 * @param	integer	The fill color. A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function imageFill( $x, $y, $color )
	{
		return imagefill( $this->resource, $x, $y, $color );
	}
	
	/**
	 * Image Fill To Border
	 * 
	 * Flood fill to specific color.
	 * 
	 * @access	public
	 * @param	integer	x-coordinate of start.
	 * @param	integer	y-coordinate of start.
	 * @param	integer	The border color. A color identifier created with imagecolorallocate(). 
	 * @param	integer	The fill color. A color identifier created with imagecolorallocate(). 
	 * @return	boolean
	 */
	public function imageFillToBorder( $x, $y, $border, $color )
	{
		return imagefilltoborder( $this->resource, $x, $y, $border, $color );
	}
	
	/**
	 * Image Rotate
	 * 
	 * Roate an image with a given angle.
	 * 
	 * @access	public
	 * @param	integer	Rotation angle, in degrees.
	 * @param	integer	Specifies the color of the uncovered zone after the rotation.
	 * @param	integer	If set and non-zero, transparent colors are ignored (otherwise kept).
	 * @return	object
	 */
	public function imageRotate( $angle, $bgdColor, $ignoreTransparent = 0 )
	{
		/* Check support */
		if ( GD_BUNDLED )
		{
			return ( $_o = imagerotate( $this->resource, $angle, $bgdColor, $ignoreTransparent ) ) ? self::create( $_o ) : false;
		}
		
		throw new Exception( "This function '" .__METHOD__. "' requires that PHP is compiled with the bundled version of the GD library." );
	}
	
	/* Object Interface: Font */
	/**
	 * Draw FreeType Text
	 * 
	 * Write text to the image using fonts using FreeType 2
	 * 
	 * @access	public
	 * @param	integer	The font size to use in points.
	 * @param	integer	The angle in degrees, with 0 degrees being left-to-right reading text. 
	 *				Higher values represent a counter-clockwise rotation. For example, a value of 90 would result in bottom-to-top reading text. 
	 * @param	integer	The coordinates given by x and y will define the basepoint of the first character 
	 *				(roughly the lower-left corner of the character). This is different from the imagestring(), 
	 *				where x and y define the upper-left corner of the first character. For example, "top left" is 0, 0. 
	 * @param	integer	The y-ordinate. This sets the position of the fonts baseline, not the very bottom of the character. 
	 * @param	integer	The index of the desired color for the text, see imagecolorexact(). 
	 * @param	string	The path to the TrueType font you wish to use. 
	 * @param	string	Text to be inserted into image.
	 * @param	array		Possible array indexes for extrainfo:
	 *					linespacing 	float 	Defines drawing linespacing.
	 * @return array
	 */
	public function drawFreeTypeText( $size, $angle, $x, $y, $color, $fontFile, $text, array $extraInfo = array() )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA && GD_SUPPORTS_FTYPE )
		{
			return imagefttext( $this->resource, $size, $angle, $x, $y, $color, $fontFile, $text, $extraInfo );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is not available." );
	}
	
	/**
	 * Draw PostScript Text
	 * 
	 * Draws a text over an image using PostScript Type1 fonts.
	 * 
	 * @access	public
	 * @param	object	Font			GD PostScript Font object.
	 * @param	string	Text			The text to be written.
	 * @param	integer	Size			Size is expressed in pixels.
	 * @param	integer	Foreground		The color in which the text will be painted. 
	 * @param	integer	Background		The color to which the text will try to fade in with antialiasing. 
	 *							No pixels with the color background are actually painted, so the background image does not need to be of solid color. 
	 * @param	integer	x-coordinate for the lower-left corner of the first character. 
	 * @param	integer	y-coordinate for the lower-left corner of the first character. 
	 * @param	integer	Space			Allows you to change the default value of a space in a font. This amount is added to the 
	 *							normal value and can also be negative. Expressed in character space units, where 1 unit is 1/1000th of an em-square. 
	 * @param	float		Angle			Angle is in degrees.
	 * @param	integer	Antialias Steps	Allows you to control the number of colours used for antialiasing text. Allowed values are 4 and 16. 
	 *							The higher value is recommended for text sizes lower than 20, where the effect in text quality is quite visible. 
	 *							With bigger sizes, use 4. It's less computationally intensive. 
	 * @return	array
	 */
	public function drawPostScriptText( GDFont_PostScript $font, $text, $size, $foreground, $background, $x, $y, $space = 0, $tightness = 0, $angle = 0.0, $antialiasSteps = 4 )
	{
		/* Check support */
		if ( GD_SUPPORTS_PSCRIPT )
		{
			return ( $font ) ? imagepstext( $this->resource, $text, $font->index, $size, $foreground, $background, $x, $y, $space, $tightness, $angle, $antialiasSteps ) 
				: false;
		}
		
		throw new Exception( "PostScript fonts are not available with this PHP build" );
	}

	/**
	 * Draw TrueType Text
	 * 
	 * Write text to the image using TrueType fonts
	 * 
	 * @access	public
	 * @param	integer	The font size to use in points.
	 * @param	integer	The angle in degrees, with 0 degrees being left-to-right reading text. 
	 *				Higher values represent a counter-clockwise rotation. For example, a value of 90 would result in bottom-to-top reading text. 
	 * @param	integer	The coordinates given by x and y will define the basepoint of the first character 
	 *				(roughly the lower-left corner of the character). This is different from the imagestring(), 
	 *				where x and y define the upper-left corner of the first character. For example, "top left" is 0, 0. 
	 * @param	integer	The y-ordinate. This sets the position of the fonts baseline, not the very bottom of the character. 
	 * @param	integer	The color index. Using the negative of a color index has the effect of turning off antialiasing. See imagecolorallocate(). 
	 * @param	string	The path to the TrueType font you wish to use. 
	 * @param	string	The text string in UTF-8 encoding. 
	 * @return	array
	 */
	public function drawTrueTypeText( $size, $angle, $x, $y, $color, $fontFile, $text )
	{
		/* Check support */
		if ( GD_SUPPORTS_FTYPE )
		{
			return imagettftext( $this->resource, $size, $angle, $x, $y, $color, $fontFile, $text );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is not available." );
	}

	/* Object Interface: Output */
	/**
	 * Output GD2
	 * 
	 * Output GD2 image to browser or file.
	 * 
	 * @access	public
	 * @param	string	The path to save the file to. If not set or NULL, the raw image stream will be outputted directly. 
	 * @param	integer	Chunk size. 
	 * @param	integer	Either IMG_GD2_RAW or IMG_GD2_COMPRESSED. Default is IMG_GD2_RAW. 
	 * @return	boolean
	 */	
	public function outputGd2( $fileName = null, $chunkSize = null, $type = null )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA )
		{
			return imagegd2( $this->resource, $fileName, $chunkSize, $type );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is only available with GD 2.0.1 or later" );
	}
	
	/**
	 * Output GD
	 * 
	 * Output GD image to browser or file.
	 * 
	 * @access	public
	 * @param	string	The path to save the file to. If not set or NULL, the raw image stream will be outputted directly. 
	 * @return	boolean
	 */
	public function outputGd( $fileName = null )
	{
		return imagegd( $this->resource, $fileName );
	}
	
	/**
	 * Output GIF
	 * 
	 * Output GIF image to browser or file.
	 * 
	 * @access	public
	 * @param	string	The path to save the file to. If not set or NULL, the raw image stream will be outputted directly. 
	 * @return	boolean
	 */
	public function outputGif( $fileName = null )
	{
		/* 
		 * GIF support was removed from the GD library in Version 1.6, and added back in Version 2.0.28. 
		 * This function is not available between these versions.
		 *
		 * Check support.
		 */
		if ( parent::getImageTypes() & parent::IMG_GIF )
		{
			return imagegif( $this->resource, $fileName );
		}
		
		throw new Exception( "GIF support is not available with this version of the GD library" );
	}
	
	/**
	 * Output JPEG
	 * 
	 * Output JPEG image to browser or file.
	 * 
	 * @access	public
	 * @param	string	The path to save the file to. If not set or NULL, the raw image stream will be outputted directly. 
	 * @param	integer	Quality is optional, and ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file). 
	 *				The default is the default IJG quality value (about 75). 
	 * @return	boolean
	 */
	public function outputJpeg( $fileName = null, $quality = null )
	{
		return imagejpeg( $this->resource, $fileName, $quality );
	}
	
	/**
	 * Output PNG
	 * 
	 * Output PNG image to browser or file.
	 * 
	 * @access	public
	 * @param	string	The path to save the file to. If not set or NULL, the raw image stream will be outputted directly. 
	 * @param	integer	Compression level: from 0 (no compression) to 9. 
	 * @param	integer	Allows reducing the PNG file size. It is a bitmask field which may be set to any combination of the PNG_FILTER_XXX constants. 
	 *				PNG_NO_FILTER or PNG_ALL_FILTERS may also be used to respectively disable or activate all filters. 
	 * @return	boolean
	 */
	public function outputPng( $fileName = null, $quality = 0, $filters = parent::PNG_NO_FILTER )
	{
		/* NULL for file name is invalid if the quality and filters arguments are not used. */
		/*if ( $fileName === null && $quality === null && $filters === null )
		{
			return imagepng( $this->resource );
		}*/

		return imagepng( $this->resource, $fileName, $quality, $filters );
	}
	
	/**
	 * Output WBMP2
	 * 
	 * Output WBMP2 image to browser or file.
	 * 
	 * @access	public
	 * @param	string	The path to save the file to. If not set or NULL, the raw image stream will be outputted directly.
	 * @param	integer	Threshold value, between 0 and 255 (inclusive). 
	 * @return	boolean
	 */
	public function outputWbmp2( $fileName = null, $threshold = 0 )
	{
		return image2wbmp( $this->resource, $fileName, $threshold );
	}
	
	/**
	 * Output WBMP
	 * 
	 * Output WBMP image to browser or file.
	 * 
	 * @access	public
	 * @param	string	The path to save the file to. If not set or NULL, the raw image stream will be outputted directly.
	 * @param	integer	You can set the foreground color with this parameter by setting an identifier
	 *				obtained from imagecolorallocate(). The default foreground color is black. 
	 * @return	boolean
	 */
	public function outputWbmp( $fileName = null, $foreground = null )
	{
		return imagewbmp( $this->resource, $fileName, $foreground );
	}
	
	/**
	 * Output XBM
	 * 
	 * Output XBM image to browser or file.
	 * 
	 * @access	public
	 * @param	string	The path to save the file to. If not set or NULL, the raw image stream will be outputted directly.
	 * @param	integer	You can set the foreground color with this parameter by setting an identifier
	 *				obtained from imagecolorallocate(). The default foreground color is black. 
	 * @return	boolean
	 */
	public function outputXbm( $fileName = null, $foreground = null )
	{
		/* Check support */
		if ( GD_BUNDLED )
		{
			return imagexbm( $this->resource, $fileName, $foreground );
		}
		
		throw new Exception( "XBM functions require that PHP is compiled with the bundled version of the GD library." );
	}
}

/**
 * GD Library: GD Font (FreeType and TrueType) object wrapper.
 *
 * @abstract
 * @package		coreKernel::Lib
 */
class GDFont extends GDImage
{
	/**#@+
	 * Font Types
	 */
	const FONT_TYPE_TTFT	= 0x1;
	const FONT_TYPE_PS	= 0x2;
	/**#@-*/
	
	/**#@+
	 * Stock Fonts
	 */
	const STOCK_FONT_1	= 1;
	const STOCK_FONT_2	= 2;
	const STOCK_FONT_3	= 3;
	const STOCK_FONT_4	= 4;
	const STOCK_FONT_5	= 5;
	/**#@-*/
	
	/**
	 * Font Identifier
	 * 
	 * @access	protected
	 * @var	integer
	 */
	protected $identifier	= 0;

	/**
	 * Cached Font Height
	 * 
	 * @access	protected
	 * @var	integer
	 */
	protected $height;
	
	/**
	 * Cached Font Width
	 * 
	 * @access	protected
	 * @var	integer
	 */	
	protected $width;

	/**
	 * Constructor
	 * 
	 * Setup and load component to run.
	 * 
	 * @access	private
	 * @param	integer	Font identifier.
	 * @return	void
	 */
	private function __construct( $identifier )
	{
		$this->identifier = $identifier;
		$this->height = 0;
		$this->width  = 0;
	}
	
	/**
	 * Create Font
	 * 
	 * Creates a new font specified by the type (PostScript or True/FreeType)
	 * 
	 * @access	public
	 * @param	integer	Font type to create
	 * @param	string	File name of font.
	 * @return	object
	 */
	final public static function create( $type = self::FONT_TYPE_TTFT, $fileName )
	{
		/* INIT */
		switch ( $type )
		{
			case self::FONT_TYPE_PS:
				/* Only available if PHP is compiled using --with-t1lib[=DIR] */
				if ( ! GD_SUPPORTS_PSCRIPT )
				{
					throw new Exception( "PostScript fonts are not available with this PHP build" );
				}
				
				return GDFont_PostScript::loadFont( $fileName );
				break;
			case self::FONT_TYPE_TTFT:
			default:
				return self::loadFont( $fileName );
				break;
		}	
	}
	
	/**
	 * Get Stock Font
	 * 
	 * Returns a new object for one of the built in PHP fonts (1-5)
	 * 
	 * @access	public
	 * @param	integer	Stock font identifier
	 * @return	object
	 */
	final public static function getStockFont( $identifier )
	{
		static $stockFonts = array();
		
		/* Check cache */
		if ( ! isset( $stockFonts[ $identifier ] ) )
		{
			$stockFonts[ $identifier ] = new self( $identifier );
		}
		
		return $stockFonts[ $identifier ];
	}
	
	/* Static Interface */
	/**
	 * Load Font
	 * 
	 * Load a new font.
	 * 
	 * @access	protected
	 * @param	string	File
	 * @return	object
	 */
	protected static function loadFont( $file )
	{
		return ( $file ) ? new self( imageloadfont( $file ) ) : false;
	}
	
	/**
	 * FreeType Bounding Box
	 * 
	 * Give the bounding box of a text using fonts via freetype2.
	 * 
	 * @access	public
	 * @param	float		Size		The font size. Depending on your version of GD, this should be specified as the 
	 *						pixel size (GD1) or point size (GD2).
	 * @param	float		Angle		Angle in degrees in which text will be measured.
	 * @param	string	Font File	The name of the TrueType font file (can be a URL). Depending on which version of the GD library that PHP 
	 *						is using, it may attempt to search for files that do not begin with a leading '/' by appending '.ttf' to 
	 *						the filename and searching along a library-defined font path. 
	 * @param	string	Text		The string to be measured. 
	 * @param	array		Extra Info
	 * @return	array
	 */
	public static function freeTypeBbox( $size, $angle, $fontFile, $text, array $extraInfo = array() )
	{
		/* Check support */
		if ( GD_SUPPORTS_ALPHA && GD_SUPPORTS_FTYPE )
		{
			return imageftbbox( $size, $angle, $fontFile, $text, $extraInfo );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is not available." );
	}
	
	/**
	 * TrueType Bounding Box
	 * 
	 * Give the bounding box of a text using fonts via TrueType.
	 * 
	 * @access	public
	 * @param	float		Size		The font size. Depending on your version of GD, this should be specified as the 
	 *						pixel size (GD1) or point size (GD2).
	 * @param	float		Angle		Angle in degrees in which text will be measured.
	 * @param	string	Font File	The name of the TrueType font file (can be a URL). Depending on which version of the GD library that PHP 
	 *						is using, it may attempt to search for files that do not begin with a leading '/' by appending '.ttf' to 
	 *						the filename and searching along a library-defined font path. 
	 * @param	string	Text		The string to be measured. 
	 * @return	array
	 */
	public static function trueTypeBbox( $size, $angle, $fontFile, $text )
	{
		/* Check support */
		if ( GD_SUPPORTS_FTYPE )
		{
			return imagettfbbox( $size, $angle, $fontFile, $text, $extraInfo );
		}
		
		throw new Exception( "This function '" .__METHOD__. "' is not available." );
	}
	
	/* Object Interface */
	/**
	 * Get Font Height
	 * 
	 * Returns the pixel height of a character in the this font.
	 * 
	 * @access	public
	 * @return	integer
	 */
	public function getFontHeight()
	{
		/* Not PostScript! */
		if ( $this->identifier == 0 || $this->identifier == self::FONT_TYPE_PS )
		{
			throw new Exception( "getFontHeight() cannot be called on PostScript object" );
		}
		
		return ( ! $this->height ) ? ( $this->height = imagefontheight( $this->identifier ) ) : $this->height;
	}
	
	/**
	 * Get Font Width
	 * 
	 * Returns the pixel width of a character in the this font.
	 * 
	 * @access	public
	 * @return	integer
	 */
	public static function getFontWidth()
	{
		/* Not PostScript! */
		if ( $this->identifier == 0 || $this->identifier == self::FONT_TYPE_PS )
		{
			throw new Exception( "getFontHeight() cannot be called on PostScript object" );
		}
		
		return ( ! $this->width ) ? ( $this->width = imagefontwidth( $this->identifier ) ) : $this->width;
	}
}

/* PostScript only available if PHP is compiled using --with-t1lib[=DIR] */
if ( function_exists( 'imagepsloadfont' ) )
{
	/**
	 * GD Library: GD PostScript Font object wrapper.
	 *
	 * @abstract
	 * @package		coreKernel::Lib
	*/
	class GDFont_PostScript extends GDFont
	{
		/**
		 * PostScript Font Index
		 * 
		 * @access	protected
		 * @var	resource
		 */
		protected $index;
		
		/**
		 * Constructor
		 * 
		 * Setup and load component to run.
		 * 
		 * @access	private
		 * @param	resource	PostScript font index.
		 * @return	void
		 */
		private function __construct( $index )
		{
			parent::__construct( parent::FONT_TYPE_PS );
			$this->index	 = $index;
		}
		
		/**
		 * Destructor
		 * 
		 * Destroys this font resource when the object is collected.
		 * 
		 * @access	public
		 * @return	void
		 */
		public function __destruct()
		{
			imagepsfreefont( $this->index );
		}
		
		/* Static Interface */
		/**
		 * Load Font
		 * 
		 * Load a PostScript Type 1 font from file.
		 * 
		 * @access	protected
		 * @param	string	Path to the Postscript font file. 
		 * @return	object
		 */
		protected static function loadFont( $fileName )
		{
			return ( $fileName ) ? new self( imagepsloadfont( $fileName ) ) : false;
		}
		
		/* Object Interface */
		/**
		 * PostScript Bounding Box
		 * 
		 * Give the bounding box of a text rectangle using PostScript Type1 fonts.
		 * 
		 * @access	public
		 * @param	string	Text		The text to be written. 
		 * @param	integer	Size		Size is expressed in pixels. 
		 * @param	integer	Space		Allows you to change the default value of a space in a font. This amount is added to the normal value 
		 *						and can also be negative. Expressed in character space units, where 1 unit is 1/1000th of an em-square. 
		 * @param	integer	Tightness	Allows you to control the amount of white space between characters. This amount is added to the normal 
		 *						character width and can also be negative. Expressed in character space units, where 1 unit is 1/1000th of an em-square. 
		 * @param	integer	Angle		Angle is in degrees. 
		 * @return	array
		 */
		public function postScriptBbox( $text, $size, $space = null, $tightness = null, $angle = null )
		{
			/* Two versions of the method... */
			if ( $space !== null && $tightness !== null && $angle !== null )
			{
				return ( $this->index ) ? imagepsbbox( $text, $this->index, $size, $space, $tightness, $angle ) : false;
			}
			
			return ( $this->index ) ? imagepsbbox( $text, $size ) : false;
		}
		
		/**
		 * PostScript Encode Font
		 * 
		 * Change the character encoding vector of a font.
		 * 
		 * @access	public
		 * @param	string	The exact format of this file is described in T1libs documentation. 
		 *				T1lib comes with two ready-to-use files, IsoLatin1.enc and IsoLatin2.enc.
		 * @return	boolean 
		 */
		public function postScriptEncode( $encodingFile )
		{
			return ( $this->index ) ? imagepsencodefont( $this->index, $encodingFile ) : false;
		}
		
		/**
		 * PostScript Extend Font
		 * 
		 * Extend or condense a font.
		 * 
		 * @access	public
		 * @param	float		Extension value, must be greater than 0. 
		 * @return	boolean
		 */
		public function postScriptExtend( $extend )
		{
			return ( $this->index ) ? imagepsextendfont( $this->index, $extend ) : false;
		}
		
		/**
		 * PostScript Free Font
		 * 
		 * Free memory used by a PostScript Type 1 font, this calls our destructor so we
		 * don't double free.
		 * 
		 * @access	public
		 * @return	boolean
		 */
		public function postScriptFree()
		{
			unset( $this );
		}
		
		/**
		 * PostScript Slant Font
		 * 
		 * Slant a font.
		 * 
		 * @access	public
		 * @param	float		Slant level.
		 * @return	boolean
		 */
		public function postScriptSlant( $slant )
		{
			return ( $this->index ) ? imagepsslantfont( $this->index, $slant ) : false;
		}
	}
}
else
{
	/**
	 * Define blank class so we dont mess up with the PS functions that take
	 * a GDFont_PostScript.
	 * 
	 * @ignore
	 */
	abstract class GDFont_PostScript /*extends GDFont*/ {}
}

/**
 * INIT
 */
GD::init();
?>
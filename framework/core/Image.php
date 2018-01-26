<?php
/**
 * Class Image
 * This class makes image manipulation in PHP as simple as possible.
 * @package clqimage
 *
 */
class Image{

    /**
     * @var int Default output image quality
     *
     */
    public $quality = 80;

    protected $image, $filename, $original_info, $width, $height, $imagestring;

    /**
     * Create instance and load an image, or create an image from scratch
     *
     * @param null|string   $filename   Path to image file (may be omitted to create image from scratch)
     * @param int           $width      Image width (is used for creating image from scratch)
     * @param int|null      $height     If omitted - assumed equal to $width (is used for creating image from scratch)
     * @param null|string   $color      Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                                  Where red, green, blue - integers 0-255, alpha - integer 0-127<br>
     *                                  (is used for creating image from scratch)
     *
     * @return clqimage
     * @throws Exception
     *
     */
    function __construct($filename = null, $width = null, $height = null, $color = null) {
        if ($filename) {
            $this->load($filename);
        } elseif ($width) {
            $this->create($width, $height, $color);
        }
        return $this;
    }

    /**
     * Destroy image resource
     *
     */
    function __destruct() {
        
        if($this->image) {
            if(get_resource_type($this->image) === 'gd' ) {
                imagedestroy($this->image);
            }            
        }
    }

    /**
     * Adaptive resize
     *
     * This function has been deprecated and will be removed in an upcoming release. Please
     * update your code to use the `thumbnail()` method instead. The arguments for both
     * methods are exactly the same.
     *
     * @param int           $width
     * @param int|null      $height If omitted - assumed equal to $width
     *
     * @return clqimage
     *
     */
    function adaptive_resize($width, $height = null) {

        return $this->thumbnail($width, $height);

    }

    /**
     * Rotates and/or flips an image automatically so the orientation will be correct (based on exif 'Orientation')
     *
     * @return clqimage
     *
     */
    function auto_orient() {

        switch ($this->original_info['exif']['Orientation']) {
            case 1:
                // Do nothing
                break;
            case 2:
                // Flip horizontal
                $this->flip('x');
                break;
            case 3:
                // Rotate 180 counterclockwise
                $this->rotate(-180);
                break;
            case 4:
                // vertical flip
                $this->flip('y');
                break;
            case 5:
                // Rotate 90 clockwise and flip vertically
                $this->flip('y');
                $this->rotate(90);
                break;
            case 6:
                // Rotate 90 clockwise
                $this->rotate(90);
                break;
            case 7:
                // Rotate 90 clockwise and flip horizontally
                $this->flip('x');
                $this->rotate(90);
                break;
            case 8:
                // Rotate 90 counterclockwise
                $this->rotate(-90);
                break;
        }

        return $this;

    }

    /**
     * Best fit (proportionally resize to fit in specified width/height)
     *
     * Shrink the image proportionally to fit inside a $width x $height box
     *
     * @param int           $max_width
     * @param int           $max_height
     *
     * @return  clqimage
     *
     */
    function best_fit($max_width, $max_height) {

        // If it already fits, there's nothing to do
        if ($this->width <= $max_width && $this->height <= $max_height) {
            return $this;
        }

        // Determine aspect ratio
        $aspect_ratio = $this->height / $this->width;

        // Make width fit into new dimensions
        if ($this->width > $max_width) {
            $width = $max_width;
            $height = $width * $aspect_ratio;
        } else {
            $width = $this->width;
            $height = $this->height;
        }

        // Make height fit into new dimensions
        if ($height > $max_height) {
            $height = $max_height;
            $width = $height / $aspect_ratio;
        }

        return $this->resize($width, $height);

    }

    /**
     * Blur
     *
     * @param string        $type   selective|gaussian
     * @param int           $passes Number of times to apply the filter
     *
     * @return clqimage
     *
     */
    function blur($type = 'selective', $passes = 1) {
        switch (strtolower($type)) {
            case 'gaussian':
                $type = IMG_FILTER_GAUSSIAN_BLUR;
                break;
            default:
                $type = IMG_FILTER_SELECTIVE_BLUR;
                break;
        }
        for ($i = 0; $i < $passes; $i++) {
            imagefilter($this->image, $type);
        }
        return $this;
    }

    /**
     * Brightness
     *
     * @param int           $level  Darkest = -255, lightest = 255
     *
     * @return clqimage
     *
     */
    function brightness($level) {
        imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $this->keep_within($level, -255, 255));
        return $this;
    }

    /**
     * Contrast
     *
     * @param int           $level  Min = -100, max = 100
     *
     * @return clqimage
     *
     *
     */
    function contrast($level) {
        imagefilter($this->image, IMG_FILTER_CONTRAST, $this->keep_within($level, -100, 100));
        return $this;
    }

    /**
     * Colorize
     *
     * @param string        $color      Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                                  Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @param float|int     $opacity    0-1
     *
     * @return clqimage
     *
     */
    function colorize($color, $opacity) {
        $rgba = $this->normalize_color($color);
        $alpha = $this->keep_within(127 - (127 * $opacity), 0, 127);
        imagefilter($this->image, IMG_FILTER_COLORIZE, $this->keep_within($rgba['r'], 0, 255), $this->keep_within($rgba['g'], 0, 255), $this->keep_within($rgba['b'], 0, 255), $alpha);
        return $this;
    }

    /**
     * Create an image from scratch
     *
     * @param int           $width  Image width
     * @param int|null      $height If omitted - assumed equal to $width
     * @param null|string   $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     *
     * @return clqimage
     *
     */
    function create($width, $height = null, $color = null) {

        $height = $height ?: $width;
        $this->width = $width;
        $this->height = $height;
        $this->image = imagecreatetruecolor($width, $height);
        $this->original_info = array(
            'width' => $width,
            'height' => $height,
            'orientation' => $this->get_orientation(),
            'exif' => null,
            'format' => 'png',
            'mime' => 'image/png'
        );

        if ($color) {
            $this->fill($color);
        }

        return $this;

    }

    /**
     * Crop an image
     *
     * @param int           $x1 Left
     * @param int           $y1 Top
     * @param int           $x2 Right
     * @param int           $y2 Bottom
     *
     * @return clqimage
     *
     */
    function crop($x1, $y1, $x2, $y2) {

        // Determine crop size
        if ($x2 < $x1) {
            list($x1, $x2) = array($x2, $x1);
        }
        if ($y2 < $y1) {
            list($y1, $y2) = array($y2, $y1);
        }
        $crop_width = $x2 - $x1;
        $crop_height = $y2 - $y1;

        // Perform crop
        $new = imagecreatetruecolor($crop_width, $crop_height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopyresampled($new, $this->image, 0, 0, $x1, $y1, $crop_width, $crop_height, $crop_width, $crop_height);

        // Update meta data
        $this->width = $crop_width;
        $this->height = $crop_height;
        $this->image = $new;

        return $this;

    }

    /**
     * Desaturate
     *
     * @param int           $percentage Level of desaturization.
     *
     * @return clqimage
     *
     */
    function desaturate($percentage = 100) {

        // Determine percentage
        $percentage = $this->keep_within($percentage, 0, 100);

        if( $percentage === 100 ) {
            imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        } else {
            // Make a desaturated copy of the image
            $new = imagecreatetruecolor($this->width, $this->height);
            imagealphablending($new, false);
            imagesavealpha($new, true);
            imagecopy($new, $this->image, 0, 0, 0, 0, $this->width, $this->height);
            imagefilter($new, IMG_FILTER_GRAYSCALE);

            // Merge with specified percentage
            $this->imagecopymerge_alpha($this->image, $new, 0, 0, 0, 0, $this->width, $this->height, $percentage);
            imagedestroy($new);

        }

        return $this;
    }

    /**
     * Edge Detect
     *
     * @return clqimage
     *
     */
    function edges() {
        imagefilter($this->image, IMG_FILTER_EDGEDETECT);
        return $this;
    }

    /**
     * Emboss
     *
     * @return clqimage
     *
     */
    function emboss() {
        imagefilter($this->image, IMG_FILTER_EMBOSS);
        return $this;
    }

    /**
     * Fill image with color
     *
     * @param string        $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     *
     * @return clqimage
     *
     */
    function fill($color = '#000000') {

        $rgba = $this->normalize_color($color);
        $fill_color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $fill_color);

        return $this;

    }

    /**
     * Fit to height (proportionally resize to specified height)
     *
     * @param int           $height
     *
     * @return clqimage
     *
     */
    function fit_to_height($height) {

        $aspect_ratio = $this->height / $this->width;
        $width = $height / $aspect_ratio;

        return $this->resize($width, $height);

    }

    /**
     * Fit to width (proportionally resize to specified width)
     *
     * @param int           $width
     *
     * @return clqimage
     *
     */
    function fit_to_width($width) {

        $aspect_ratio = $this->height / $this->width;
        $height = $width * $aspect_ratio;

        return $this->resize($width, $height);

    }

    /**
     * Flip an image horizontally or vertically
     *
     * @param string        $direction  x|y
     *
     * @return clqimage
     *
     */
    function flip($direction) {

        $new = imagecreatetruecolor($this->width, $this->height);
        imagealphablending($new, false);
        imagesavealpha($new, true);

        switch (strtolower($direction)) {
            case 'y':
                for ($y = 0; $y < $this->height; $y++) {
                    imagecopy($new, $this->image, 0, $y, 0, $this->height - $y - 1, $this->width, 1);
                }
                break;
            default:
                for ($x = 0; $x < $this->width; $x++) {
                    imagecopy($new, $this->image, $x, 0, $this->width - $x - 1, 0, 1, $this->height);
                }
                break;
        }

        $this->image = $new;

        return $this;

    }

    /**
     * Get the current height
     *
     * @return int
     *
     */
    function get_height() {
        return $this->height;
    }

    /**
     * Get the current orientation
     *
     * @return string   portrait|landscape|square
     *
     */
    function get_orientation() {

        if (imagesx($this->image) > imagesy($this->image)) {
            return 'landscape';
        }

        if (imagesx($this->image) < imagesy($this->image)) {
            return 'portrait';
        }

        return 'square';

    }

    /**
     * Get info about the original image
     *
     * @return array <pre> array(
     *  width        => 320,
     *  height       => 200,
     *  orientation  => ['portrait', 'landscape', 'square'],
     *  exif         => array(...),
     *  mime         => ['image/jpeg', 'image/gif', 'image/png'],
     *  format       => ['jpeg', 'gif', 'png']
     * )</pre>
     *
     */
    function get_original_info() {
        return $this->original_info;
    }

    /**
     * Get the current width
     *
     * @return int
     *
     */
    function get_width() {
        return $this->width;
    }

    /**
     * Invert
     *
     * @return clqimage
     *
     */
    function invert() {
        imagefilter($this->image, IMG_FILTER_NEGATE);
        return $this;
    }

    /**
     * Load an image
     *
     * @param string        $filename   Path to image file
     *
     * @return clqimage
     * @throws Exception
     *
     */
    function load($filename) {

        // Require GD library
        if (!extension_loaded('gd')) {
            throw new Exception('Required extension GD is not loaded.');
        }
        $this->filename = $filename;
        return $this->get_meta_data();
    }

    /**
     * Load a base64 string as image
     *
     * @param string        $filename   base64 string
     *
     * @return clqimage
     *
     */
    function load_base64($base64string) {
        if (!extension_loaded('gd')) {
            throw new Exception('Required extension GD is not loaded.');
        }
        //remove data URI scheme and spaces from base64 string then decode it
        $this->imagestring = base64_decode(str_replace(' ', '+',preg_replace('#^data:image/[^;]+;base64,#', '', $base64string)));
        $this->image = imagecreatefromstring($this->imagestring);
        return $this->get_meta_data();
    }

    /**
     * Mean Remove
     *
     * @return clqimage
     *
     */
    function mean_remove() {
        imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * Changes the opacity level of the image
     *
     * @param float|int     $opacity    0-1
     *
     * @throws Exception
     *
     */
    function opacity($opacity) {

        // Determine opacity
        $opacity = $this->keep_within($opacity, 0, 1) * 100;

        // Make a copy of the image
        $copy = imagecreatetruecolor($this->width, $this->height);
        imagealphablending($copy, false);
        imagesavealpha($copy, true);
        imagecopy($copy, $this->image, 0, 0, 0, 0, $this->width, $this->height);

        // Create transparent layer
        $this->create($this->width, $this->height, array(0, 0, 0, 127));

        // Merge with specified opacity
        $this->imagecopymerge_alpha($this->image, $copy, 0, 0, 0, 0, $this->width, $this->height, $opacity);
        imagedestroy($copy);

        return $this;

    }

    /**
     * Outputs image without saving
     *
     * @param null|string   $format     If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param int|null      $quality    Output image quality in percents 0-100
     *
     * @throws Exception
     *
     */
    function output($format = null, $quality = null) {

        // Determine quality
        $quality = $quality ?: $this->quality;

        // Determine mimetype
        switch (strtolower($format)) {
            case 'gif':
                $mimetype = 'image/gif';
                break;
            case 'jpeg':
            case 'jpg':
                imageinterlace($this->image, true);
                $mimetype = 'image/jpeg';
                break;
            case 'png':
                $mimetype = 'image/png';
                break;
            default:
                $info = (empty($this->imagestring)) ? getimagesize($this->filename) : getimagesizefromstring($this->imagestring);
                $mimetype = $info['mime'];
                unset($info);
                break;
        }

        // Output the image
        header('Content-Type: '.$mimetype);
        switch ($mimetype) {
            case 'image/gif':
                imagegif($this->image);
                break;
            case 'image/jpeg':
                imagejpeg($this->image, null, round($quality));
                break;
            case 'image/png':
                imagepng($this->image, null, round(9 * $quality / 100));
                break;
            default:
                throw new Exception('Unsupported image format: '.$this->filename);
                break;
        }
    }

    /**
     * Outputs image as data base64 to use as img src
     *
     * @param null|string   $format     If omitted or null - format of original file will be used, may be gif|jpg|png
     * @param int|null      $quality    Output image quality in percents 0-100
     *
     * @return string
     * @throws Exception
     *
     */
    function output_base64($format = null, $quality = null) {

        // Determine quality
        $quality = $quality ?: $this->quality;

        // Determine mimetype
        switch (strtolower($format)) {
            case 'gif':
                $mimetype = 'image/gif';
                break;
            case 'jpeg':
            case 'jpg':
                imageinterlace($this->image, true);
                $mimetype = 'image/jpeg';
                break;
            case 'png':
                $mimetype = 'image/png';
                break;
            default:
                $info = getimagesize($this->filename);
                $mimetype = $info['mime'];
                unset($info);
                break;
        }

        // Output the image
        ob_start();
        switch ($mimetype) {
            case 'image/gif':
                imagegif($this->image);
                break;
            case 'image/jpeg':
                imagejpeg($this->image, null, round($quality));
                break;
            case 'image/png':
                imagepng($this->image, null, round(9 * $quality / 100));
                break;
            default:
                throw new Exception('Unsupported image format: '.$this->filename);
                break;
        }
        $image_data = ob_get_contents();
        ob_end_clean();

        // Returns formatted string for img src
        return 'data:'.$mimetype.';base64,'.base64_encode($image_data);

    }

    /**
     * Overlay
     *
     * Overlay an image on top of another, works with 24-bit PNG alpha-transparency
     *
     * @param string        $overlay        An image filename or a clqimage object
     * @param string        $position       center|top|left|bottom|right|top left|top right|bottom left|bottom right
     * @param float|int     $opacity        Overlay opacity 0-1
     * @param int           $x_offset       Horizontal offset in pixels
     * @param int           $y_offset       Vertical offset in pixels
     *
     * @return clqimage
     *
     */
    function overlay($overlay, $position = 'center', $opacity = 1, $x_offset = 0, $y_offset = 0) {

        // Load overlay image
        if( !($overlay instanceof clqimage) ) {
            $overlay = new clqimage($overlay);
        }

        // Convert opacity
        $opacity = $opacity * 100;

        // Determine position
        switch (strtolower($position)) {
            case 'top left':
                $x = 0 + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'top right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'top':
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = 0 + $y_offset;
                break;
            case 'bottom left':
                $x = 0 + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'bottom right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'bottom':
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = $this->height - $overlay->height + $y_offset;
                break;
            case 'left':
                $x = 0 + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
            case 'right':
                $x = $this->width - $overlay->width + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
            case 'center':
            default:
                $x = ($this->width / 2) - ($overlay->width / 2) + $x_offset;
                $y = ($this->height / 2) - ($overlay->height / 2) + $y_offset;
                break;
        }

        // Perform the overlay
        $this->imagecopymerge_alpha($this->image, $overlay->image, $x, $y, 0, 0, $overlay->width, $overlay->height, $opacity);

        return $this;

    }

    /**
     * Pixelate
     *
     * @param int           $block_size Size in pixels of each resulting block
     *
     * @return clqimage
     *
     */
    function pixelate($block_size = 10) {
        imagefilter($this->image, IMG_FILTER_PIXELATE, $block_size, true);
        return $this;
    }

    /**
     * Resize an image to the specified dimensions
     *
     * @param int   $width
     * @param int   $height
     *
     * @return clqimage
     *
     */
    function resize($width, $height) {

        // Generate new GD image
        $new = imagecreatetruecolor($width, $height);

        if( $this->original_info['format'] === 'gif' ) {
            // Preserve transparency in GIFs
            $transparent_index = imagecolortransparent($this->image);
            $palletsize = imagecolorstotal($this->image);
            if ($transparent_index >= 0 && $transparent_index < $palletsize) {
                $transparent_color = imagecolorsforindex($this->image, $transparent_index);
                $transparent_index = imagecolorallocate($new, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($new, 0, 0, $transparent_index);
                imagecolortransparent($new, $transparent_index);
            }
        } else {
            // Preserve transparency in PNGs (benign for JPEGs)
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }

        // Resize
        imagecopyresampled($new, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        // Update meta data
        $this->width = $width;
        $this->height = $height;
        $this->image = $new;

        return $this;

    }

    /**
     * Rotate an image
     *
     * @param int           $angle      0-360
     * @param string        $bg_color   Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                                  Where red, green, blue - integers 0-255, alpha - integer 0-127
     *
     * @return clqimage
     *
     */
    function rotate($angle, $bg_color = '#000000') {

        // Perform the rotation
        $rgba = $this->normalize_color($bg_color);
        $bg_color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
        $new = imagerotate($this->image, -($this->keep_within($angle, -360, 360)), $bg_color);
        imagesavealpha($new, true);
        imagealphablending($new, true);

        // Update meta data
        $this->width = imagesx($new);
        $this->height = imagesy($new);
        $this->image = $new;

        return $this;

    }

    /**
     * Save an image
     *
     * The resulting format will be determined by the file extension.
     *
     * @param null|string   $filename   If omitted - original file will be overwritten
     * @param null|int      $quality    Output image quality in percents 0-100
     * @param null|string   $format     The format to use; determined by file extension if null
     *
     * @return clqimage
     * @throws Exception
     *
     */
    function save($filename = null, $quality = null, $format = null) {

        // Determine quality, filename, and format
        $quality = $quality ?: $this->quality;
        $filename = $filename ?: $this->filename;
        if( !$format ) {
            $format = $this->file_ext($filename) ?: $this->original_info['format'];
        }

        // Create the image
        switch (strtolower($format)) {
            case 'gif':
                $result = imagegif($this->image, $filename);
                break;
            case 'jpg':
            case 'jpeg':
                imageinterlace($this->image, true);
                $result = imagejpeg($this->image, $filename, round($quality));
                break;
            case 'png':
                $result = imagepng($this->image, $filename, round(9 * $quality / 100));
                break;
            default:
                throw new Exception('Unsupported format');
        }

        if (!$result) {
            throw new Exception('Unable to save image: ' . $filename);
        }

        return $this;

    }

    /**
     * Sepia
     *
     * @return clqimage
     *
     */
    function sepia() {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        imagefilter($this->image, IMG_FILTER_COLORIZE, 100, 50, 0);
        return $this;
    }

    /**
     * Sketch
     *
     * @return clqimage
     *
     */
    function sketch() {
        imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * Smooth
     *
     * @param int           $level  Min = -10, max = 10
     *
     * @return clqimage
     *
     */
    function smooth($level) {
        imagefilter($this->image, IMG_FILTER_SMOOTH, $this->keep_within($level, -10, 10));
        return $this;
    }

    /**
     * Add text to an image
     *
     * @param string        $text
     * @param string        $font_file
     * @param float|int     $font_size
     * @param string        $color
     * @param string        $position
     * @param int           $x_offset
     * @param int           $y_offset
     *
     * @return clqimage
     * @throws Exception
     *
     */
    function text($text, $font_file, $font_size = 12, $color = '#000000', $position = 'center', $x_offset = 0, $y_offset = 0, $stroke_color = null, $stroke_size = null) {

        // todo - this method could be improved to support the text angle
        $angle = 0;

        // Determine text color
        $rgba = $this->normalize_color($color);
        $color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);

        // Determine textbox size
        $box = imagettfbbox($font_size, $angle, $font_file, $text);
        if (!$box) {
            throw new Exception('Unable to load font: '.$font_file);
        }
        $box_width = abs($box[6] - $box[2]);
        $box_height = abs($box[7] - $box[1]);

        // Determine position
        switch (strtolower($position)) {
            case 'top left':
                $x = 0 + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'top right':
                $x = $this->width - $box_width + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'top':
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = 0 + $y_offset + $box_height;
                break;
            case 'bottom left':
                $x = 0 + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'bottom right':
                $x = $this->width - $box_width + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'bottom':
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = $this->height - $box_height + $y_offset + $box_height;
                break;
            case 'left':
                $x = 0 + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
            case 'right';
                $x = $this->width - $box_width + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
            case 'center':
            default:
                $x = ($this->width / 2) - ($box_width / 2) + $x_offset;
                $y = ($this->height / 2) - (($box_height / 2) - $box_height) + $y_offset;
                break;
        }

        // Add the text
        imagesavealpha($this->image, true);
        imagealphablending($this->image, true);
        if( isset($stroke_color) && isset($stroke_size) ) {
            // Text with stroke
            $rgba = $this->normalize_color($color);
            $stroke_color = imagecolorallocatealpha($this->image, $rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
            $this->imagettfstroketext($this->image, $font_size, $angle, $x, $y, $color, $stroke_color, $stroke_size, $font_file, $text);
        } else {
            // Text without stroke
            imagettftext($this->image, $font_size, $angle, $x, $y, $color, $font_file, $text);
        }

        return $this;

    }

    /**
     * Thumbnail
     *
     * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
     * remaining overflow (from the center) to get the image to be the size specified. Useful for generating thumbnails.
     *
     * @param int           $width
     * @param int|null      $height If omitted - assumed equal to $width
     *
     * @return clqimage
     *
     */
    function thumbnail($width, $height = null) {

        // Determine height
        $height = $height ?: $width;

        // Determine aspect ratios
        $current_aspect_ratio = $this->height / $this->width;
        $new_aspect_ratio = $height / $width;

        // Fit to height/width
        if ($new_aspect_ratio > $current_aspect_ratio) {
            $this->fit_to_height($height);
        } else {
            $this->fit_to_width($width);
        }
        $left = floor(($this->width / 2) - ($width / 2));
        $top = floor(($this->height / 2) - ($height / 2));

        // Return trimmed image
        return $this->crop($left, $top, $width + $left, $height + $top);

    }

    /**
     * Returns the file extension of the specified file
     *
     * @param string    $filename
     *
     * @return string
     *
     */
    protected function file_ext($filename) {

        if (!preg_match('/\./', $filename)) {
            return '';
        }

        return preg_replace('/^.*\./', '', $filename);

    }

    /**
     * Get meta data of image or base64 string
     *
     * @param string|null       $imagestring    If omitted treat as a normal image
     *
     * @return clqimage
     * @throws Exception
     *
     */
    protected function get_meta_data() {
        //gather meta data
        if(empty($this->imagestring)) {
            $info = getimagesize($this->filename);

            switch ($info['mime']) {
                case 'image/gif':
                    $this->image = imagecreatefromgif($this->filename);
                    break;
                case 'image/jpeg':
                    $this->image = imagecreatefromjpeg($this->filename);
                    break;
                case 'image/png':
                    $this->image = imagecreatefrompng($this->filename);
                    break;
                default:
                    throw new Exception('Invalid image: '.$this->filename);
                    break;
            }
        } elseif (function_exists('getimagesizefromstring')) {
            $info = getimagesizefromstring($this->imagestring);
        } else {
            throw new Exception('PHP 5.4 is required to use method getimagesizefromstring');
        }

        $this->original_info = array(
            'width' => $info[0],
            'height' => $info[1],
            'orientation' => $this->get_orientation(),
            'exif' => function_exists('exif_read_data') && $info['mime'] === 'image/jpeg' && $this->imagestring === null ? $this->exif = @exif_read_data($this->filename) : null,
            'format' => preg_replace('/^image\//', '', $info['mime']),
            'mime' => $info['mime']
        );
        $this->width = $info[0];
        $this->height = $info[1];

        imagesavealpha($this->image, true);
        imagealphablending($this->image, true);

        return $this;

    }

    /**
     * Same as PHP's imagecopymerge() function, except preserves alpha-transparency in 24-bit PNGs
     *
     * @param $dst_im
     * @param $src_im
     * @param $dst_x
     * @param $dst_y
     * @param $src_x
     * @param $src_y
     * @param $src_w
     * @param $src_h
     * @param $pct
     *
     * @link http://www.php.net/manual/en/function.imagecopymerge.php#88456
     *
     */
    protected function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {

        // Get image width and height and percentage
        $pct /= 100;
        $w = imagesx($src_im);
        $h = imagesy($src_im);

        // Turn alpha blending off
        imagealphablending($src_im, false);

        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        }

        // Loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                // Get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($src_im, $x, $y);
                $alpha = ($colorxy >> 24) & 0xFF;
                // Calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }
                // Get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
                // Set pixel with the new color + opacity
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return;
                }
            }
        }

        // Copy it
        imagesavealpha($dst_im, true);
        imagealphablending($dst_im, true);
        imagesavealpha($src_im, true);
        imagealphablending($src_im, true);
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);

    }

    /**
     *  Same as imagettftext(), but allows for a stroke color and size
     *
     * @param  object &$image       A GD image object
     * @param  float $size          The font size
     * @param  float $angle         The angle in degrees
     * @param  int $x               X-coordinate of the starting position
     * @param  int $y               Y-coordinate of the starting position
     * @param  int &$textcolor      The color index of the text
     * @param  int &$stroke_color   The color index of the stroke
     * @param  int $stroke_size     The stroke size in pixels
     * @param  string $fontfile     The path to the font to use
     * @param  string $text         The text to output
     *
     * @return array                This method has the same return values as imagettftext()
     *
     */
    protected function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $stroke_size, $fontfile, $text) {
        for( $c1 = ($x - abs($stroke_size)); $c1 <= ($x + abs($stroke_size)); $c1++ ) {
            for($c2 = ($y - abs($stroke_size)); $c2 <= ($y + abs($stroke_size)); $c2++) {
                $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
            }
        }
        return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
    }

    /**
     * Ensures $value is always within $min and $max range.
     *
     * If lower, $min is returned. If higher, $max is returned.
     *
     * @param int|float     $value
     * @param int|float     $min
     * @param int|float     $max
     *
     * @return int|float
     *
     */
    protected function keep_within($value, $min, $max) {

        if ($value < $min) {
            return $min;
        }

        if ($value > $max) {
            return $max;
        }

        return $value;

    }

    /**
     * Converts a hex color value to its RGB equivalent
     *
     * @param string        $color  Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                              Where red, green, blue - integers 0-255, alpha - integer 0-127
     *
     * @return array|bool
     *
     */
    protected function normalize_color($color) {

        if (is_string($color)) {

            $color = trim($color, '#');

            if (strlen($color) == 6) {
                list($r, $g, $b) = array(
                    $color[0].$color[1],
                    $color[2].$color[3],
                    $color[4].$color[5]
                );
            } elseif (strlen($color) == 3) {
                list($r, $g, $b) = array(
                    $color[0].$color[0],
                    $color[1].$color[1],
                    $color[2].$color[2]
                );
            } else {
                return false;
            }
            return array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b),
                'a' => 0
            );

        } elseif (is_array($color) && (count($color) == 3 || count($color) == 4)) {

            if (isset($color['r'], $color['g'], $color['b'])) {
                return array(
                    'r' => $this->keep_within($color['r'], 0, 255),
                    'g' => $this->keep_within($color['g'], 0, 255),
                    'b' => $this->keep_within($color['b'], 0, 255),
                    'a' => $this->keep_within(isset($color['a']) ? $color['a'] : 0, 0, 127)
                );
            } elseif (isset($color[0], $color[1], $color[2])) {
                return array(
                    'r' => $this->keep_within($color[0], 0, 255),
                    'g' => $this->keep_within($color[1], 0, 255),
                    'b' => $this->keep_within($color[2], 0, 255),
                    'a' => $this->keep_within(isset($color[3]) ? $color[3] : 0, 0, 127)
                );
            }

        }
        return false;
    }
}

/**
 * Class ColorPalette
 *
 * @see discource [https://github.com/discourse/discourse]
 * palette of optimally distinct colors
 * cf. http://tools.medialab.sciences-po.fr/iwanthue/index.php
 * parameters used:
 *   - H: 0 - 360
 *   - C: 0 - 2
 *   - L: 0.75 - 1.5
 */
final class ColorPalette {
    /**
     * Colors
     *
     * @var array
     */
    private static $colors = [
        [198, 125, 40],
        [61, 155, 243],
        [74, 243, 75],
        [238, 89, 166],
        [52, 240, 224],
        [177, 156, 155],
        [240, 120, 145],
        [111, 154, 78],
        [237, 179, 245],
        [237, 101, 95],
        [89, 239, 155],
        [43, 254, 70],
        [163, 212, 245],
        [65, 152, 142],
        [165, 135, 246],
        [181, 166, 38],
        [187, 229, 206],
        [77, 164, 25],
        [179, 246, 101],
        [234, 93, 37],
        [225, 155, 115],
        [142, 140, 188],
        [223, 120, 140],
        [249, 174, 27],
        [244, 117, 225],
        [137, 141, 102],
        [75, 191, 146],
        [188, 239, 142],
        [164, 199, 145],
        [173, 120, 149],
        [59, 195, 89],
        [222, 198, 220],
        [68, 145, 187],
        [236, 204, 179],
        [159, 195, 72],
        [188, 121, 189],
        [166, 160, 85],
        [181, 233, 37],
        [236, 177, 85],
        [121, 147, 160],
        [234, 218, 110],
        [241, 157, 191],
        [62, 200, 234],
        [133, 243, 34],
        [88, 149, 110],
        [59, 228, 248],
        [183, 119, 118],
        [251, 195, 45],
        [113, 196, 122],
        [197, 115, 70],
        [80, 175, 187],
        [103, 231, 238],
        [240, 72, 133],
        [228, 149, 241],
        [180, 188, 159],
        [172, 132, 85],
        [180, 135, 251],
        [236, 194, 58],
        [217, 176, 109],
        [88, 244, 199],
        [186, 157, 239],
        [113, 230, 96],
        [206, 115, 165],
        [244, 178, 163],
        [230, 139, 26],
        [241, 125, 89],
        [83, 160, 66],
        [107, 190, 166],
        [197, 161, 210],
        [198, 203, 245],
        [238, 117, 19],
        [228, 119, 116],
        [131, 156, 41],
        [145, 178, 168],
        [139, 170, 220],
        [233, 95, 125],
        [87, 178, 230],
        [157, 200, 119],
        [237, 140, 76],
        [229, 185, 186],
        [144, 206, 212],
        [236, 209, 158],
        [185, 189, 79],
        [34, 208, 66],
        [84, 238, 129],
        [133, 140, 134],
        [67, 157, 94],
        [168, 179, 25],
        [140, 145, 240],
        [151, 241, 125],
        [67, 162, 107],
        [200, 156, 21],
        [169, 173, 189],
        [226, 116, 189],
        [133, 231, 191],
        [194, 161, 63],
        [241, 77, 99],
        [241, 217, 53],
        [123, 204, 105],
        [210, 201, 119],
        [229, 108, 155],
        [240, 91, 72],
        [187, 115, 210],
        [240, 163, 100],
        [178, 217, 57],
        [179, 135, 116],
        [204, 211, 24],
        [186, 135, 57],
        [223, 176, 135],
        [204, 148, 151],
        [116, 223, 50],
        [95, 195, 46],
        [123, 160, 236],
        [181, 172, 131],
        [142, 220, 202],
        [240, 140, 112],
        [172, 145, 164],
        [228, 124, 45],
        [135, 151, 243],
        [42, 205, 125],
        [192, 233, 116],
        [119, 170, 114],
        [158, 138, 26],
        [73, 190, 183],
        [185, 229, 243],
        [227, 107, 55],
        [196, 205, 202],
        [132, 143, 60],
        [233, 192, 237],
        [62, 150, 220],
        [205, 201, 141],
        [106, 140, 190],
        [161, 131, 205],
        [135, 134, 158],
        [198, 139, 81],
        [115, 171, 32],
        [101, 181, 67],
        [149, 137, 119],
        [37, 142, 183],
        [183, 130, 175],
        [168, 125, 133],
        [124, 142, 87],
        [236, 156, 171],
        [232, 194, 91],
        [219, 200, 69],
        [144, 219, 34],
        [219, 95, 187],
        [145, 154, 217],
        [165, 185, 100],
        [127, 238, 163],
        [224, 178, 198],
        [119, 153, 120],
        [124, 212, 92],
        [172, 161, 105],
        [231, 155, 135],
        [157, 132, 101],
        [122, 185, 146],
        [53, 166, 51],
        [70, 163, 90],
        [150, 190, 213],
        [210, 107, 60],
        [166, 152, 185],
        [159, 194, 159],
        [39, 141, 222],
        [202, 176, 161],
        [95, 140, 229],
        [168, 142, 87],
        [93, 170, 203],
        [159, 142, 54],
        [14, 168, 39],
        [94, 150, 149],
        [187, 206, 136],
        [157, 224, 166],
        [235, 158, 208],
        [109, 232, 216],
        [141, 201, 87],
        [208, 124, 118],
        [142, 125, 214],
        [19, 237, 174],
        [72, 219, 41],
        [234, 102, 111],
        [168, 142, 79],
        [188, 135, 35],
        [95, 155, 143],
        [148, 173, 116],
        [223, 112, 95],
        [228, 128, 236],
        [206, 114, 54],
        [195, 119, 88],
        [235, 140, 94],
        [235, 202, 125],
        [233, 155, 153],
        [214, 214, 238],
        [246, 200, 35],
        [151, 125, 171],
        [132, 145, 172],
        [131, 142, 118],
        [199, 126, 150],
        [61, 162, 123],
        [58, 176, 151],
        [215, 141, 69],
        [225, 154, 220],
        [220, 77, 167],
        [233, 161, 64],
        [130, 221, 137],
        [81, 191, 129],
        [169, 162, 140],
        [174, 177, 222],
        [236, 174, 47],
        [233, 188, 180],
        [69, 222, 172],
        [71, 232, 93],
        [118, 211, 238],
        [157, 224, 83],
        [218, 105, 73],
        [126, 169, 36]
    ];


    /**
     * Returns colors array
     *
     * @return array
     */
    public static function getColors()
    {
        return self::$colors;
    }
}

class LetterAvatar {
    /**
     * Max size to generate
     *
     * @var integer
     */
    private $maxSize = 240;

    /**
     * @var int
     */
    private $minSize = 20;

    /**
     * Path to ttf font file
     *
     * @var string
     */
    private $fontFile;

    /**
     * Image php gd resource
     *
     * @var resource
     */
    private $img;

    /**
     * Background colors palette
     *
     * @var
     */
    private $backgroundColors;

    /**
     * Background color that will be used
     *
     * @var array color used for background
     */
    private $backgroundColor;

    /**
     * Font ratio
     * Used to calculate font size from image request size
     *
     * @var float
     */
    private $fontRatio = 0.8;

    /**
     * Text color
     *
     * @var array
     */
    private $textColor;

    /**
     * Set max size
     *
     * @param int $maxSize
     * @return $this
     */
    public function setMaxSize($maxSize) {
        $this->maxSize = $maxSize;
        return $this;
    }

    /**
     * Set minimum size
     *
     * @param int $minSize
     * @return $this
     */
    public function setMinSize($minSize) {
        $this->minSize = $minSize;
        return $this;
    }

    /**
     * Returns font file path
     *
     * @return string
     */
    public function getFontFile() {
        global $clq;
        if (!$this->fontFile) {
            return $clq->get('basedir').'includes/browa.ttf';
        }
        return $this->fontFile;
    }

    /**
     * Sets font file path
     *
     * @param string $fontFile
     * @return $this
     */
    public function setFontFile($fontFile)
    {
        $this->fontFile = $fontFile;
        return $this;
    }

    /**
     * Returns color palette
     *
     * @return mixed
     */
    public function getBackgroundColors()
    {
        if (empty($this->backgroundColors)) {
            $this->backgroundColors = ColorPalette::getColors();
        }

        return $this->backgroundColors;
    }

    /**
     * Set color palette
     *
     * @param array $backgroundColors
     * @return $this
     */
    public function setBackgroundColors(array $backgroundColors)
    {
        $this->backgroundColors = $backgroundColors;
        return $this;
    }

    /**
     * Set font ratio
     *
     * @param float $fontRatio
     * @return $this
     */
    public function setFontRatio($fontRatio)
    {
        $this->fontRatio = $fontRatio;
        return $this;
    }

    /**
     * Return text color
     *
     * @return Color
     */
    public function getTextColor()
    {
        if (empty($this->textColor)) {
            $this->textColor = [255, 255, 255];
        }

        return new Color($this->textColor[0], $this->textColor[1], $this->textColor[2]);
    }

    /**
     * Set text color
     *
     * @param array $textColor (rgb)
     * @return $this
     */
    public function setTextColor(array $textColor)
    {
        $this->textColor = $textColor;
        return $this;
    }

    /**
     * Generate a letter avatar and return image content
     * Background color is picked randomly.
     *
     * @param      $letter letter or a string (first char will be picked)
     * @param null $size
     * @return $this
     */
    public function generate($letter, $size = null)
    {
        $this->createImage(
            strtoupper($letter[0]),
            $this->getBackgroundColor(),
            $this->getSize($size)
        );

        return $this;
    }

    /**
     * Save as png
     *
     * @param     $path
     * @param int $quality
     * @return $this
     */
    public function saveAsPng($path, $quality = 9)
    {
        imagepng($this->img, $path, $quality);
        imagedestroy($this->img);

        return $this;
    }

    /**
     * Save image as Jpeg
     *
     * @param     $path
     * @param int $quality
     * @return $this
     */
    public function saveAsJpeg($path, $quality = 100)
    {
        imagejpeg($this->img, $path, $quality);
        imagedestroy($this->img);

        return $this;
    }

    /**
     * Reset background color to null, so that the next generation use
     * a new random color
     */
    public function resetBackgroundColor()
    {
        $this->backgroundColor = null;
        return $this;
    }

    /**
     * Generate letter image and return image
     *
     * @param $letter
     * @param $color
     * @param $size
     * @return resource
     */
    protected function createImage($letter, $color, $size)
    {
        $this->img = imagecreatetruecolor($size, $size);
        $bgColor   = imagecolorallocate($this->img, $color[0], $color[2], $color[1]);
        imagefill($this->img, 0, 0, $bgColor);

        $box = new Box($this->img);
        $box->setFontFace($this->getFontFile());
        $box->setFontColor($this->getTextColor());
        $box->setTextShadow(new Color(0, 0, 0, 50), 2, 2);
        $box->setFontSize(round($size * $this->fontRatio));
        $box->setBox(0, 0, $size, $size);
        $box->setTextAlign('center', 'center');
        $box->draw($letter);
    }

    /**
     * Returns a random background color
     *
     * return  array rgb color
     */
    protected function getRandomBackgroundColor()
    {
        $colors = $this->getBackgroundColors();
        return $colors[array_rand($colors)];
    }

    /**
     * Returns color that will be used as background
     *
     * @return Color
     */
    protected function getBackgroundColor()
    {
        if (empty($this->backgroundColor)) {
            $this->backgroundColor = $this->getRandomBackgroundColor();
        }

        return $this->backgroundColor;
    }

    /**
     * Check size
     *
     * @param $size
     * @return bool|int
     */
    protected function getSize($size)
    {
        if (!$size) {
            return $this->maxSize;
        }

        $size = (int) $size;

        if ($size > $this->maxSize) {
            return $this->maxSize;
        }

        if ($size < $this->minSize) {
            return $this->minSize;
        }

        return $size;
    }
}

class Box {
    /**
     * @var resource
     */
    protected $im;

    /**
     * @var int
     */
    protected $fontSize = 12;

    /**
     * @var Color
     */
    protected $fontColor;

    /**
     * @var string
     */
    protected $alignX = 'left';

    /**
     * @var string
     */
    protected $alignY = 'top';

    /**
     * @var float
     */
    protected $lineHeight = 1.25;

    /**
     * @var float
     */
    protected $baseline = 0.2;

    /**
     * @var string
     */
    protected $fontFace = null;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var bool|array
     */
    protected $textShadow = false;

    /**
     * @var array
     */
    protected $box = array(
        'x' => 0,
        'y' => 0,
        'width' => 100,
        'height' => 100
    );

    public function __construct(&$image)
    {
        $this->im = $image;
        $this->fontColor = new Color(0, 0, 0);
    }

    /**
     * @param Color $color Font color
     */
    public function setFontColor(Color $color)
    {
        $this->fontColor = $color;
    }

    /**
     * @param string $path Path to the font file
     */
    public function setFontFace($path)
    {
        $this->fontFace = $path;
    }

    /**
     * @param int $v Font size in *pixels*
     */
    public function setFontSize($v)
    {
        $this->fontSize = $v;
    }

    /**
     * @param Color $color Shadow color
     * @param int $xShift Relative shadow position in pixels. Positive values move shadow to right, negative to left.
     * @param int $yShift Relative shadow position in pixels. Positive values move shadow to bottom, negative to up.
     */
    public function setTextShadow(Color $color, $xShift, $yShift)
    {
        $this->textShadow = array(
            'color' => $color,
            'x' => $xShift,
            'y' => $yShift
        );
    }

    /**
     * Allows to customize spacing between lines.
     * @param float $v Height of the single text line, in percents, proportionally to font size
     */
    public function setLineHeight($v)
    {
        $this->lineHeight = $v;
    }

    /**
     * @param float $v Position of baseline, in percents, proportionally to line height measuring from the bottom.
     */
    public function setBaseline($v)
    {
        $this->baseline = $v;
    }

    /**
     * Sets text alignment inside textbox
     * @param string $x Horizontal alignment. Allowed values are: left, center, right.
     * @param string $y Vertical alignment. Allowed values are: top, center, bottom.
     */
    public function setTextAlign($x = 'left', $y = 'top')
    {
        $xAllowed = array('left', 'right', 'center');
        $yAllowed = array('top', 'bottom', 'center');

        if (!in_array($x, $xAllowed)) {
            throw new \InvalidArgumentException('Invalid horizontal alignement value was specified.');
        }

        if (!in_array($y, $yAllowed)) {
            throw new \InvalidArgumentException('Invalid vertical alignement value was specified.');
        }

        $this->alignX = $x;
        $this->alignY = $y;
    }

    /**
     * Sets textbox position and dimensions
     * @param int $x Distance in pixels from left edge of image.
     * @param int $y Distance in pixels from top edge of image.
     * @param int $width Width of texbox in pixels.
     * @param int $height Height of textbox in pixels.
     */
    public function setBox($x, $y, $width, $height)
    {
        $this->box['x'] = $x;
        $this->box['y'] = $y;
        $this->box['width'] = $width;
        $this->box['height'] = $height;
    }

    /**
     * Enables debug mode. Whole textbox and individual lines will be filled with random colors.
     */
    public function enableDebug()
    {
        $this->debug = true;
    }

    /**
     * Draws the text on the picture.
     * @param string $text Text to draw. May contain newline characters.
     */
    public function draw($text)
    {
        if (!isset($this->fontFace)) {
            throw new \InvalidArgumentException('No path to font file has been specified.');
        }

        $lines = array();
        // Split text explicitly into lines by \n, \r\n and \r
        $explicitLines = preg_split('/\n|\r\n?/', $text);
        foreach ($explicitLines as $line) {
            // Check every line if it needs to be wrapped
            $words = explode(" ", $line);
            $line = $words[0];
            for ($i = 1; $i < count($words); $i++) {
                $box = $this->calculateBox($line." ".$words[$i]);
                if (($box[4]-$box[6]) >= $this->box['width']) {
                    $lines[] = $line;
                    $line = $words[$i];
                } else {
                    $line .= " ".$words[$i];
                }
            }
            $lines[] = $line;
        }

        if ($this->debug) {
            // Marks whole texbox area with color
            $this->drawFilledRectangle(
                $this->box['x'],
                $this->box['y'],
                $this->box['width'],
                $this->box['height'],
                new Color(rand(180, 255), rand(180, 255), rand(180, 255), 80)
            );
        }

        $lineHeightPx = $this->lineHeight * $this->fontSize;
        $textHeight = count($lines) * $lineHeightPx;
        
        switch ($this->alignY) {
            case 'center':
                $yAlign = ($this->box['height'] / 2) - ($textHeight / 2);
                break;
            case 'bottom':
                $yAlign = $this->box['height'] - $textHeight;
                break;
            case 'top':
            default:
                $yAlign = 0;
        }
        
        $n = 0;
        foreach ($lines as $line) {
            $box = $this->calculateBox($line);
            $boxWidth = $box[2] - $box[0];
            switch ($this->alignX) {
                case 'center':
                    $xAlign = ($this->box['width'] - $boxWidth) / 2;
                    break;
                case 'right':
                    $xAlign = ($this->box['width'] - $boxWidth);
                    break;
                case 'left':
                default:
                    $xAlign = 0;
            }
            $yShift = $lineHeightPx * (1 - $this->baseline);

            // current line X and Y position
            $xMOD = $this->box['x'] + $xAlign;
            $yMOD = $this->box['y'] + $yAlign + $yShift + ($n * $lineHeightPx);
            
            if ($this->debug) {
                // Marks current line with color
                $this->drawFilledRectangle(
                    $xMOD,
                    $this->box['y'] + $yAlign + ($n * $lineHeightPx),
                    $boxWidth,
                    $lineHeightPx,
                    new Color(rand(1, 180), rand(1, 180), rand(1, 180))
                );
            }
            
            if ($this->textShadow !== false) {
                $this->drawInternal(
                    $xMOD + $this->textShadow['x'],
                    $yMOD + $this->textShadow['y'],
                    $this->textShadow['color'],
                    $line
                );
            }

            $this->drawInternal(
                $xMOD,
                $yMOD,
                $this->fontColor,
                $line
            );

            $n++;
        }
    }

    protected function getFontSizeInPoints()
    {
        return 0.75 * $this->fontSize;
    }

    protected function drawFilledRectangle($x, $y, $width, $height, Color $color)
    {
        imagefilledrectangle($this->im, $x, $y, $x + $width, $y + $height,
            $color->getIndex($this->im)
        );
    }

    protected function calculateBox($text)
    {
        return imageftbbox($this->getFontSizeInPoints(), 0, $this->fontFace, $text);
    }

    protected function drawInternal($x, $y, Color $color, $text)
    {
        imagefttext(
            $this->im,
            $this->getFontSizeInPoints(),
            0, // no rotation
            $x,
            $y,
            $color->getIndex($this->im),
            $this->fontFace,
            $text
        );
    }
}

class Color {
    /**
     * @var int
     */
    protected $red;

    /**
     * @var int
     */
    protected $green;

    /**
     * @var int
     */
    protected $blue;

    /**
     * @var int|null
     */
    protected $alpha;

    /**
     * @param int $red Value of red component 0-255
     * @param int $green Value of green component 0-255
     * @param int $blue Value of blue component 0-255
     * @param int $alpha A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent.
     */
    public function __construct($red = 0, $green = 0, $blue = 0, $alpha = null)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
        $this->alpha = $alpha;
    }

    /**
     * @param resource $image GD image resource
     * @return int Returns the index of the specified color+alpha in the palette of the image,
     *             or -1 if the color does not exist in the image's palette.
     */
    public function getIndex($image)
    {
        if ($this->hasAlphaChannel()) {
            return imagecolorexactalpha(
                $image,
                $this->red,
                $this->green,
                $this->blue,
                $this->alpha
            );
        } else {
            return imagecolorexact(
                $image,
                $this->red,
                $this->green,
                $this->blue
            );
        }
    }

    /**
     * @return bool TRUE when alpha channel is specified, FALSE otherwise
     */
    public function hasAlphaChannel()
    {
        return $this->alpha !== null;
    }
}

/*
$im = imagecreatetruecolor(500, 500);
$backgroundColor = imagecolorallocate($im, 0, 18, 64);
imagefill($im, 0, 0, $backgroundColor);

$box = new Box($im);
$box->setFontFace(__DIR__.'/Franchise-Bold-hinted.ttf'); // http://www.dafont.com/franchise.font
$box->setFontColor(new Color(255, 75, 140));
$box->setTextShadow(new Color(0, 0, 0, 50), 2, 2);
$box->setFontSize(40);
$box->setBox(20, 20, 460, 460);
$box->setTextAlign('left', 'top');
$box->draw("Franchise\nBold");

$box = new Box($im);
$box->setFontFace(__DIR__.'/Pacifico.ttf'); // http://www.dafont.com/pacifico.font
$box->setFontSize(80);
$box->setFontColor(new Color(255, 255, 255));
$box->setTextShadow(new Color(0, 0, 0, 50), 0, -2);
$box->setBox(20, 20, 460, 460);
$box->setTextAlign('center', 'center');
$box->draw("Pacifico");

$box = new Box($im);
$box->setFontFace(__DIR__.'/Prisma.otf'); // http://www.dafont.com/prisma.font
$box->setFontSize(70);
$box->setFontColor(new Color(148, 212, 1));
$box->setTextShadow(new Color(0, 0, 0, 50), 0, -2);
$box->setBox(20, 20, 460, 460);
$box->setTextAlign('right', 'bottom');
$box->draw("Prisma");

header("Content-type: image/png");
imagepng($im);
*/

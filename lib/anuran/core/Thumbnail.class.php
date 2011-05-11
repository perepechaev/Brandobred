<?php

/**
 * This is a driver for the thumbnail creating
 *
 * PHP versions 4 and 5
 *
 * LICENSE:
 *
 * The PHP License, version 3.0
 *
 * Copyright (c) 1997-2005 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following url:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @author      Ildar N. Shaimordanov <ildar-sh@mail.ru>
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 */

/**
 * Maximal scaling
 */
define('THUMBNAIL_METHOD_SCALE_MAX', 0);

/**
 * Minimal scaling
 */
define('THUMBNAIL_METHOD_SCALE_MIN', 1);

/**
 * Cropping of fragment
 */
define('THUMBNAIL_METHOD_CROP',      2);

/**
 * Align constants
 */
define('THUMBNAIL_ALIGN_CENTER', 0);
define('THUMBNAIL_ALIGN_LEFT',   -1);
define('THUMBNAIL_ALIGN_RIGHT',  +1);
define('THUMBNAIL_ALIGN_TOP',    -1);
define('THUMBNAIL_ALIGN_BOTTOM', +1);

class Thumbnail
{

    const COLOR_GRAY    = 1;

    /**
     * Create a GD image resource from given input.
     *
     * This method tried to detect what the input, if it is a file the
     * createImageFromFile will be called, otherwise createImageFromString().
     *
     * @param  mixed $input The input for creating an image resource. The value
     *                      may a string of filename, string of image data or
     *                      GD image resource.
     *
     * @return resource     An GD image resource on success or false
     * @access public
     * @static
     * @see    Thumbnail::imageCreateFromFile(), Thumbnail::imageCreateFromString()
     */
    static public function imageCreate($input)
    {
        if ( is_file($input) ) {
            return Thumbnail::imageCreateFromFile($input);
        }
        else if ( is_string($input) ) {
            return Thumbnail::imageCreateFromString($input);
        }
        else {
            return $input;
        }
    }

    /**
     * Create a GD image resource from file (JPEG, PNG support).
     *
     * @param  string $filename The image filename.
     *
     * @return mixed            GD image resource on success, FALSE on failure.
     * @access public
     * @static
     */
    static public function imageCreateFromFile($filename)
    {
        if ( ! is_file($filename) || ! is_readable($filename) ) {
            user_error('Unable to open file "' . $filename . '"', E_USER_NOTICE);
            return false;
        }

        // determine image format
        list( , , $type) = getimagesize($filename);

        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filename);
                break;
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filename);
                break;
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filename);
		break;
        }
        user_error('Unsupport image type', E_USER_NOTICE);
        return false;
    }

    /**
     * Create a GD image resource from a string data.
     *
     * @param  string $string The string image data.
     *
     * @return mixed          GD image resource on success, FALSE on failure.
     * @access public
     * @static
     */
    static public function imageCreateFromString($string)
    {
        if ( ! is_string($string) || empty($string) ) {
            user_error('Invalid image value in string', E_USER_NOTICE);
            return false;
        }

        return imagecreatefromstring($string);
    }

    /**
     * Display rendered image (send it to browser or to file).
     * This method is a common implementation to render and output an image.
     * The method calls the render() method automatically and outputs the
     * image to the browser or to the file.
     *
     * @param  mixed   $input   Destination image, a filename or an image string data or a GD image resource
     * @param  array   $options Thumbnail options
     *         <pre>
     *         width   int    Width of thumbnail
     *         height  int    Height of thumbnail
     *         percent number Size of thumbnail per size of original image
     *         method  int    Method of thumbnail creating
     *         halign  int    Horizontal align
     *         valign  int    Vertical align
     *         </pre>
     *
     * @return boolean          TRUE on success or FALSE on failure.
     * @access public
     */
    static public function output($input, $output=false, $options=array())
    {
        // Load source file and render image
        $renderImage = Thumbnail::render($input, $options);
        if ( ! $renderImage ) {
            user_error('Error rendering image', E_USER_NOTICE);
            return false;
        }

        // Set output image type
        // By default PNG image
        $type = isset($options['type']) ? $options['type'] : IMAGETYPE_PNG;

        // Before output to browsers send appropriate headers
        if ( empty($output) ) {
            $content_type = image_type_to_mime_type($type);
            if ( ! headers_sent() ) {
                header('Content-Type: ' . $content_type);
            }
            else {
                user_error('Headers have already been sent. Could not display image.', E_USER_NOTICE);
                return false;
            }
        }
        else{
            File::createIsNotExistDirectory(dirname($output));
        }

        // Define outputing function
        switch ($type) {
            case IMAGETYPE_PNG:
                $result = ($output === false) ? imagepng($renderImage) : imagepng($renderImage, $output, 2);
                break;
            case IMAGETYPE_JPEG:
                $result = ($output === false) ? imagejpeg($renderImage) : imagejpeg($renderImage, $output, 100);
                break;
            case IMAGETYPE_GIF:
                $result = ($output === false) ? imagegif($renderImage) : imagegif($renderImage, $output, 100);
                break;
            default:
                user_error('Image type ' . $content_type . ' not supported by PHP', E_USER_NOTICE);
                return false;
        }

        // Output image (to browser or to file)
        if ( ! $result ) {
            user_error('Error output image', E_USER_NOTICE);
            return false;
        }

        // Free a memory from the target image
        imagedestroy($renderImage);

        return true;
    }

    /**
     * Draw thumbnail result to resource.
     *
     * @param  mixed   $input   Destination image, a filename or an image string data or a GD image resource
     * @param  array   $options Thumbnail options
     *
     * @return boolean TRUE on success or FALSE on failure.
     * @access public
     * @see    Thumbnail::output()
     */
    static public function render($input, $options=array())
    {
        // Create the source image
        $sourceImage = Thumbnail::imageCreate($input);
        if ( ! is_resource($sourceImage) ) {
            user_error('Invalid image resource', E_USER_NOTICE);
            return false;
        }
        $sourceWidth  = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        // Set default options
        static $defOptions = array(
            'width'   => 150,
            'height'  => 150,
            'method'  => THUMBNAIL_METHOD_SCALE_MAX,
            'percent' => 0,
            'halign'  => THUMBNAIL_ALIGN_CENTER,
            'valign'  => THUMBNAIL_ALIGN_CENTER,
        );
        foreach ($defOptions as $k => $v) {
            if ( ! isset($options[$k]) ) {
                $options[$k] = $v;
            }
        }

        // Estimate a rectangular portion of the source image and a size of the target image
        if ( $options['method'] == THUMBNAIL_METHOD_CROP ) {
            if ( $options['percent'] ) {
                $W = floor($options['percent'] * $sourceWidth);
                $H = floor($options['percent'] * $sourceHeight);
            }
            else {
                $W = $options['width'];
                $H = $options['height'];
            }

            $width  = $W;
            $height = $H;

            $Y = Thumbnail::_coord($options['valign'], $sourceHeight, $H);
            $X = Thumbnail::_coord($options['halign'], $sourceWidth,  $W);
        }
        else {
            $X = 0;
            $Y = 0;

            $W = $sourceWidth;
            $H = $sourceHeight;

            if ( $options['percent'] ) {
                $width  = floor($options['percent'] * $W);
                $height = floor($options['percent'] * $H);
            }
            else {
                $width  = ($options['width'] !== '*') ? $options['width'] : $width;
                $height = ($options['height'] !== '*') ? $width : floor(($sourceHeight * $width) / $sourceWidth);

                if ( $options['method'] == THUMBNAIL_METHOD_SCALE_MIN ) {
                    $Ww = $W / $width;
                    $Hh = $H / $height;
                    if ( $Ww > $Hh ) {
                        $W = floor($width * $Hh);
                        $X = Thumbnail::_coord($options['halign'], $sourceWidth,  $W);
                    }
                    else {
                        $H = floor($height * $Ww);
                        $Y = Thumbnail::_coord($options['valign'], $sourceHeight, $H);
                    }
                }
                else {
                    if ( $H > $W ) {
                        $width  = floor($height / $H * $W);
                    }
                    else {
                        $height = floor($width / $W * $H);
                    }
                }
            }
        }

        // Create the target image
        if ( function_exists('imagecreatetruecolor') ) {
            $www            = ($options['width']  !== '*') ? $options['width']  : $width;
            $hhh            = ($options['height'] !== '*') ? $options['height'] : $height;
            $targetImage    = imagecreatetruecolor($www, $hhh);
            $red            = imagecolorallocate($targetImage, 255, 255, 255);
            imagefill($targetImage, 0, 0, $red);
        }
        else {
            $targetImage = imagecreate($width, $height);
        }
        if ( ! is_resource($targetImage) ) {
            user_error('Cannot initialize new GD image stream', E_USER_NOTICE);
            return false;
        }


        // Copy the source image to the target image
        if ( $options['method'] == THUMBNAIL_METHOD_CROP ) {
            $result = imagecopy($targetImage, $sourceImage, 0, 0, $X, $Y, $W, $H);
        }
        elseif ( function_exists('imagecopyresampled') ) {
            $dY     = ($options['height'] !== '*') ? ($options['height'] - $height) / 2 : 0;
            $dX     = ($options['width'] !== $width) ? ($options['width'] - $width) / 2  : 0;
            $result = imagecopyresampled($targetImage, $sourceImage, $dX, $dY, $X, $Y, $width, $height, $W, $H);
        }
        else {
            $result = imagecopyresized($targetImage, $sourceImage, 0, 0, $X, $Y, $width, $height, $W, $H);
        }
        if ( ! $result ) {
            user_error('Cannot resize image', E_USER_NOTICE);
            return false;
        }

        // Free a memory from the source image
        imagedestroy($sourceImage);


        return $targetImage;
    }

    static public function _coord($align, $param, $src)
    {
        if ( $align < THUMBNAIL_ALIGN_CENTER ) {
            $result = 0;
        }
        elseif ( $align > THUMBNAIL_ALIGN_CENTER ) {
            $result = $param - $src;
        }
        else {
            $result = ($param - $src) >> 1;
        }
        return $result;
    }
}

?>

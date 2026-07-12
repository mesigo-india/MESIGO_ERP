<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Helper to process, crop, rotate, enhance images and mock background removal.
 */
class AiAssetHelper
{
    /**
     * Process image according to settings.
     * Metadata keys: crop (x, y, w, h), rotate (degrees), opacity (0-1), brightness (-100 to 100), contrast (-100 to 100)
     */
    public static function processImage(string $sourcePath, array $meta): string
    {
        if (!file_exists($sourcePath)) {
            return $sourcePath;
        }

        $info = getimagesize($sourcePath);
        if (!$info) {
            return $sourcePath;
        }

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $img = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $img = imagecreatefrompng($sourcePath);
                imagealphablending($img, false);
                imagesavealpha($img, true);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($sourcePath);
                break;
            default:
                return $sourcePath;
        }

        if (!$img) {
            return $sourcePath;
        }

        // 1. Crop
        if (isset($meta['crop']) && is_array($meta['crop'])) {
            $c = $meta['crop'];
            $img = imagecrop($img, [
                'x' => (int)($c['x'] ?? 0),
                'y' => (int)($c['y'] ?? 0),
                'width' => (int)($c['w'] ?? imagesx($img)),
                'height' => (int)($c['h'] ?? imagesy($img))
            ]);
        }

        // 2. Rotate
        if (isset($meta['rotate']) && (int)$meta['rotate'] !== 0) {
            $angle = (int)$meta['rotate'];
            // In GD, rotation is counter-clockwise, so negate to match clockwise CSS
            $transColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
            $img = imagerotate($img, -$angle, $transColor);
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        // 3. Brightness & Contrast
        if (isset($meta['brightness']) && (int)$meta['brightness'] !== 0) {
            imagefilter($img, IMG_FILTER_BRIGHTNESS, (int)$meta['brightness']);
        }
        if (isset($meta['contrast']) && (int)$meta['contrast'] !== 0) {
            imagefilter($img, IMG_FILTER_CONTRAST, (int)$meta['contrast']);
        }

        // 4. Background Removal Mock / Transparency filter
        if (!empty($meta['remove_bg'])) {
            $img = self::removeWhiteBackground($img);
        }

        // Save to target temporary or optimized path
        $dir = dirname($sourcePath);
        $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $outputPath = $dir . '/proc_' . uniqid() . '.' . $ext;

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($img, $outputPath, 90);
                break;
            case 'image/png':
                imagepng($img, $outputPath);
                break;
            case 'image/gif':
                imagegif($img, $outputPath);
                break;
        }

        imagedestroy($img);
        return $outputPath;
    }

    /**
     * Remove white background from image and make it transparent (mocking AI bg removal)
     */
    private static function removeWhiteBackground($img)
    {
        $width = imagesx($img);
        $height = imagesy($img);

        // Create transparent true color image
        $newImg = imagecreatetruecolor($width, $height);
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);

        $transparent = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
        imagefill($newImg, 0, 0, $transparent);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $colorIndex = imagecolorat($img, $x, $y);
                $colors = imagecolorsforindex($img, $colorIndex);

                // If color is very close to white, make it transparent
                $threshold = 235;
                if ($colors['red'] >= $threshold && $colors['green'] >= $threshold && $colors['blue'] >= $threshold) {
                    imagesetpixel($newImg, $x, $y, $transparent);
                } else {
                    $pixelColor = imagecolorallocatealpha($newImg, $colors['red'], $colors['green'], $colors['blue'], $colors['alpha']);
                    imagesetpixel($newImg, $x, $y, $pixelColor);
                }
            }
        }

        return $newImg;
    }
}

<?

// src/Utils/ImageProcessor.php

namespace App\Utils;

class ImageProcessor
{
    private int $resizeWidth;
    private int $resizeHeight;

    public function __construct(int $resizeWidth = 28, int $resizeHeight = 28)
    {
        $this->resizeWidth = $resizeWidth;
        $this->resizeHeight = $resizeHeight;
    }

    /**
     * Extrait les caractéristiques d'une image.
     */
    public function extractFeatures(string $imagePath): array
    {
        $image = @imagecreatefromstring(file_get_contents($imagePath));
        if (!$image) {
            throw new \RuntimeException("Impossible de traiter l'image : $imagePath");
        }

        $resized = imagescale($image, $this->resizeWidth, $this->resizeHeight);
        $features = [];

        for ($y = 0; $y < $this->resizeHeight; $y++) {
            for ($x = 0; $x < $this->resizeWidth; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $features[] = ($r + $g + $b) / 3;
            }
        }

        imagedestroy($image);
        imagedestroy($resized);

        return $features;
    }
}

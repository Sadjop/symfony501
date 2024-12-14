<?php

namespace App\Utils;

use RuntimeException;

class ImageProcessor
{
    private int $resizeWidth;
    private int $resizeHeight;

    /**
     * Initialise le processeur d'images avec des dimensions de redimensionnement spécifiées.
     *
     * @param int $resizeWidth La largeur à laquelle redimensionner l'image.
     * @param int $resizeHeight La hauteur à laquelle redimensionner l'image.
     */
    public function __construct(int $resizeWidth = 28, int $resizeHeight = 28)
    {
        $this->resizeWidth = $resizeWidth;
        $this->resizeHeight = $resizeHeight;
    }

    /**
     * Extrait les caractéristiques d'une image sous forme de tableau.
     * Cette méthode redimensionne l'image à une taille fixe et en extrait
     * la moyenne des valeurs RGB de chaque pixel.
     *
     * @param string $imagePath Le chemin vers l'image à traiter.
     *
     * @return array Un tableau contenant les caractéristiques extraites de l'image.
     *
     * @throws RuntimeException Si l'image ne peut pas être traitée.
     */
    public function extractFeatures(string $imagePath): array
    {
        // Chargement de l'image à partir du fichier
        $imageData = @file_get_contents($imagePath);
        if ($imageData === false) {
            throw new RuntimeException("Impossible de lire l'image : $imagePath");
        }

        $image = @imagecreatefromstring($imageData);
        if (!$image) {
            throw new RuntimeException("Impossible de créer l'image à partir des données : $imagePath");
        }

        // Redimensionnement de l'image
        $resized = imagescale($image, $this->resizeWidth, $this->resizeHeight);
        if (!$resized) {
            imagedestroy($image);
            throw new RuntimeException("Impossible de redimensionner l'image : $imagePath");
        }

        // Extraction des caractéristiques de l'image
        $features = [];
        for ($y = 0; $y < $this->resizeHeight; $y++) {
            for ($x = 0; $x < $this->resizeWidth; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                // Calcul de la moyenne des valeurs RGB
                $features[] = ($r + $g + $b) / 3;
            }
        }

        // Libération de la mémoire
        imagedestroy($image);
        imagedestroy($resized);

        return $features;
    }
}

<?php
// src/Service/Predictor.php
namespace App\Service;

use App\Utils\ImageProcessor;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Datasets\Unlabeled;

class Predictor
{
    private string $modelPath;
    private ImageProcessor $imageProcessor;

    public function __construct(string $modelPath, ImageProcessor $imageProcessor)
    {
        $this->modelPath = $modelPath;
        $this->imageProcessor = $imageProcessor;
    }

    public function loadModel(): PersistentModel
    {
        try {
            $model = PersistentModel::load(new Filesystem($this->modelPath));
            if (!$model->trained()) {
                throw new \RuntimeException("Le modèle n'est pas correctement entraîné.");
            }
            return $model;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Erreur lors du chargement du modèle : " . $e->getMessage());
        }
    }

    /**
     * Effectuer la prédiction pour une image donnée.
     */
    public function predictImage(string $imagePath): string
    {
        try {
            // Extraire les caractéristiques de l'image téléchargée
            $features = $this->imageProcessor->extractFeatures($imagePath);
            
            // Charger le modèle pré-entraîné
            $model = $this->loadModel();
            
            // Faire la prédiction
            $dataset = new Unlabeled([$features]); // Passer les caractéristiques sous forme de dataset
            $predictions = $model->predict($dataset);
            
            return $predictions[0]; // Retourner la prédiction
        } catch (\Throwable $e) {
            throw new \RuntimeException("Erreur lors de la prédiction : " . $e->getMessage());
        }
    }
}


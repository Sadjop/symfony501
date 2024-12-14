<?php

namespace App\Service;

use App\Utils\ImageProcessor;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Datasets\Unlabeled;
use RuntimeException;
use Throwable;

class Predictor
{
    private string $modelPath;
    private ImageProcessor $imageProcessor;

    /**
     * Initialise le service de prédiction avec le chemin du modèle et l'image processor.
     *
     * @param string $modelPath Le chemin vers le modèle persistant.
     * @param ImageProcessor $imageProcessor L'instance d'ImageProcessor pour l'extraction de caractéristiques.
     */
    public function __construct(string $modelPath, ImageProcessor $imageProcessor)
    {
        $this->modelPath = $modelPath;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * Charge le modèle persistant depuis le système de fichiers.
     *
     * @return PersistentModel Le modèle persistant chargé.
     *
     * @throws RuntimeException Si le modèle n'est pas correctement chargé ou s'il y a une erreur.
     */
    public function loadModel(): PersistentModel
    {
        try {
            // Chargement du modèle persistant à partir du chemin spécifié
            $model = PersistentModel::load(new Filesystem($this->modelPath));

            // Vérification si le modèle est correctement entraîné
            if (!$model->trained()) {
                throw new RuntimeException("Le modèle n'est pas correctement entraîné.");
            }

            return $model;
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors du chargement du modèle : " . $e->getMessage());
        }
    }

    /**
     * Prédit la classe d'une image donnée en utilisant un modèle spécifique.
     *
     * @param string $imagePath Le chemin de l'image à prédire.
     * @param string $modelName Le nom du modèle à utiliser pour la prédiction.
     *
     * @return string La prédiction (classe) de l'image.
     *
     * @throws RuntimeException Si le modèle n'est pas correctement chargé ou si une erreur survient durant la prédiction.
     */
    public function predictImage(string $imagePath, string $modelName): string
    {
        try {
            // Définition du chemin du modèle
            $modelPath = $this->modelPath . '/' . $modelName;
            $model = PersistentModel::load(new Filesystem($modelPath));

            // Vérification si le modèle est correctement entraîné
            if (!$model->trained()) {
                throw new RuntimeException("Le modèle n'est pas correctement entraîné.");
            }

            // Extraction des caractéristiques de l'image
            $features = $this->imageProcessor->extractFeatures($imagePath);

            // Création d'un dataset avec les caractéristiques extraites
            $dataset = new Unlabeled([$features]);

            // Prédiction basée sur les caractéristiques extraites
            $predictions = $model->predict($dataset);

            return $predictions[0]; // Retourne la première prédiction
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur lors de la prédiction : " . $e->getMessage());
        }
    }
}

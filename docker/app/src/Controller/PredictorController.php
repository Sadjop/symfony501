<?php

namespace App\Controller;

use App\Service\Predictor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PredictorController extends AbstractController
{
    private Predictor $predictor;

    /**
     * Injecte le service Predictor.
     *
     * @param Predictor $predictor Le service de prédiction.
     */
    public function __construct(Predictor $predictor)
    {
        $this->predictor = $predictor;
    }

    /**
     * Affiche la page d'accueil.
     *
     * @Route("/", name="home", methods={"GET"})
     *
     * @return Response La réponse HTTP contenant la page d'accueil.
     */
    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('prediction/home.html.twig');
    }

    /**
     * Gère l'upload de l'image et le traitement de la prédiction.
     *
     * @Route("/upload-image", name="predict", methods={"GET", "POST"})
     *
     * @param Request $request La requête HTTP.
     *
     * @return Response La réponse HTTP avec le résultat de la prédiction ou un message d'erreur.
     */
    #[Route('/upload-image', name: 'predict', methods: ['GET', 'POST'])]
    public function uploadImage(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Récupération de l'image téléchargée et du modèle sélectionné
            $file = $request->files->get('image');
            $modelName = $request->request->get('model');

            // Vérification des données envoyées
            if ($file && $modelName) {
                // Définition du répertoire de destination pour l'upload
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                // Création d'un nom unique pour le fichier
                $fileName = uniqid() . '.' . $file->guessExtension();
                // Déplacement du fichier vers le répertoire cible
                $file->move($uploadDir, $fileName);

                try {
                    // Traitement de la prédiction
                    $prediction = $this->predictor->predictImage($uploadDir . '/' . $fileName, $modelName);

                    // Suppression du fichier après traitement pour éviter l'encombrement du serveur
                    unlink($uploadDir . '/' . $fileName);

                    // Retourne le résultat de la prédiction à l'utilisateur
                    return $this->render('prediction/result.html.twig', [
                        'prediction' => $prediction,
                    ]);
                } catch (\Exception $e) {
                    // Gestion des erreurs de prédiction
                    return new Response(
                        "Erreur de prédiction : " . $e->getMessage(),
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            }

            // Si aucune image ou modèle n'est sélectionné, on renvoie une erreur
            return new Response('Aucune image ou modèle sélectionné.', Response::HTTP_BAD_REQUEST);
        }

        // Retourne le formulaire d'upload d'image
        return $this->render('prediction/upload_image.html.twig');
    }
}

<?

// src/Controller/PredictionController.php
namespace App\Controller;

use App\Service\Predictor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PredictorController extends AbstractController
{
    private Predictor $predictor;

    public function __construct(Predictor $predictor)
    {
        $this->predictor = $predictor;
    }

    #[Route('/upload-image', name: 'predict', methods: ['GET', 'POST'])]
    public function uploadImage(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('image'); // Assurez-vous que l'image est dans le champ 'image'

            if ($file) {
                // Sauvegarder l'image téléchargée temporairement
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $fileName = uniqid() . '.' . $file->guessExtension();
                $file->move($uploadDir, $fileName);

                // Utiliser le service Predictor pour effectuer la prédiction sur l'image
                try {
                    $prediction = $this->predictor->predictImage($uploadDir . '/' . $fileName);
                    return $this->render('prediction/result.html.twig', [
                        'prediction' => $prediction,
                    ]);
                } catch (\Exception $e) {
                    return new Response("Erreur de prédiction : " . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            return new Response('Aucune image téléchargée.', Response::HTTP_BAD_REQUEST);
        }

        return $this->render('prediction/upload_image.html.twig');
    }
}

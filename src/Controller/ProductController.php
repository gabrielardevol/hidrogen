<?php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class ProductController extends AbstractController
{
    public function createProduct(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->request->get('data'), true); // Use $request->request per obtenir dades de formularis

        $product = new Product();

        $product->setName($data['name'] ?? '');
        $product->setOwnerId($data['ownerId'] ?? '');
        $product->setBuyerId($data['buyerId'] ?? null);
        $product->setDescription($data['description'] ?? '');
        $product->setRemuneration($data['remuneration'] ?? 0);
        $product->setRemunerationEuros($data['remunerationEuros'] ?? null);

        $imageFiles = $request->files->get('images'); // Obtenir els fitxers d'imatges

        $imagePaths = [];

        if ($imageFiles) {
            dump($imageFiles);
            foreach ($imageFiles as $index => $image) {
                $uploadsDir = $this->getParameter('uploads_directory');
                $imagePath = $image->move($uploadsDir, $image->getClientOriginalName());
                $imagePaths[]=$imagePath->getFileName();
                if ($index === 0) {
//                    $imagePath = $movedImage->getPathname();
                    [$width, $height] = getimagesize($imagePath);
                    if ($width > 0) {
                        $thumbnailRatio = $height / $width;
                        $product->setThumbnailRatio($thumbnailRatio);
                    }
                }

            }
        }

        $product->setImages($imagePaths);

        $currentDate = new \DateTime();
        $product->setCreatedAt($currentDate);
        $product->setUpdatedAt($currentDate);

        $em->persist($product);
        $em->flush();

        return new JsonResponse(
            [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'ownerId' => $product->getOwnerId()
            ],
            201
        );
    }


    public function getAllCompact(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $allHeaders = $request->headers->all();

        $authHeader = $request->headers->get('Authorization');

        dump($allHeaders, $authHeader);

        $products = $em->getRepository(Product::class)->findAll();

        $compactData = [];
        foreach ($products as $product) {
            $compactData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'aspectRatio' => $product->getThumbnailRatio(),
                'ownerId' => $product->getOwnerId(),
                'remuneration' => $product->getRemuneration(),
                'image' => $product->getImages()[0],
            ];
        }
        return new JsonResponse($compactData);
    }
}

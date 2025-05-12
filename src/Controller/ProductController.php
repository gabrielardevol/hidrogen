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
        $data = json_decode($request->getContent(), true);

        dump($data);
        $product = new Product();

        $product->setName($data['name'] ?? '');
        $product->setOwnerId($data['ownerId'] ?? '');
        $product->setBuyerId($data['buyerId'] ?? null);
        $product->setDescription($data['description'] ?? '');
        $product->setRemuneration($data['remuneration'] ?? 0);
        $product->setRemunerationEuros($data['remunerationEuros'] ?? null);
        $product->setImages($data['images'] ?? []);

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

    public function getAllCompact(EntityManagerInterface $em): JsonResponse
    {
        $products = $em->getRepository(Product::class)->findAll();

        $compactData = [];
        foreach ($products as $product) {
            $compactData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'aspectRatio' => mt_rand(4, 30) / 10,
                'ownerId' => $product->getOwnerId(),
                'remuneration' => $product->getRemuneration(),
                'image' => $product->getImages()[0],
            ];

        }

        return new JsonResponse($compactData);
    }
}

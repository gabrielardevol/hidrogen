<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class ProductController extends AbstractController
{
    public function createProduct(Request $request, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->request->get('data'), true); // Use $request->request per obtenir dades de formularis

        $product = new Product();

        $product->setName($data['name'] ?? '');
        $owner = $em->getRepository(User::class)->find($data['ownerId']);
        $product->setOwner($owner);
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
                $imagePaths[] = $imagePath->getFileName();
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
                'ownerId' => $product->getOwner()
            ],
            201
        );
    }

    public function getById(string $id, string $userId, UserRepository $userRepository, ProductRepository $productRepository, SerializerInterface $serializer): Response
    {
        $product = $productRepository->find($id);
        dump($product);
        dump($id);

        $user = $userRepository->find($userId);

        $userFavourites = $user->getFavouriteProducts();
        $isFavourite = in_array($product->getId(), $userFavourites);

        $response['ownerName'] = $product->getOwner()->getFullName();
        $response['ownerPfp'] = $product->getOwner()->getAvatarUrl();
        $response['buyerId'] = $product->getBuyerId();
        $response['id'] = $product->getId();
        $response['name'] = $product->getName();
        $response['description'] = $product->getDescription();
        $response['remuneration'] = $product->getRemuneration();
//        $response['remunerationEuros'] = $product->getRemunerationEuros();
        $response['ownerId'] = $product->getOwner()->getId();
        $response['createdAt'] = $product->getCreatedAt();
        $response['images'] = $product->getImages();
        $response['isFavourite'] = $isFavourite;


        $json = $serializer->serialize($response, 'json');
        dump($json);
        return new Response($json, 200, ['Content-Type' => 'application/json']);

    }

    public function getAllCompact(string $userId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Suposem que reps l'userId com a header, pots canviar això si l’obtens d’un token JWT

        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], 400);
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $favourites = $user->getFavouriteProducts(); // suposadament un array d'IDs
        $products = $em->getRepository(Product::class)->findAll();

        $compactData = [];

        foreach ($products as $product) {
            dump($product->getBuyerId());

            $compactData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'aspectRatio' => $product->getThumbnailRatio(),
                'ownerId' => $product->getOwner()->getId(),
                'buyerId'=> $product->getBuyerId(),
                'remuneration' => $product->getRemuneration(),
                'image' => $product->getImages()[0] ?? null,
                'isFavourite' => in_array($product->getId(), $favourites),
            ];
        }

        dump($compactData);

        return new JsonResponse($compactData);
    }

    public function searchByTerm(
        string                 $userId,
        Request                $request,
        ProductRepository      $productRepository,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $term = $request->query->get('term', '');
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        dump($term);
        $products = $productRepository->searchByNameOrDescription($term);
        $favourites = $user->getFavouriteProducts();
        $compactData = [];

        foreach ($products as $product) {
            $compactData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'aspectRatio' => $product->getThumbnailRatio(),
                'ownerId' => $product->getOwner(),
                'remuneration' => $product->getRemuneration(),
                'image' => $product->getImages()[0] ?? null,
                'isFavourite' => in_array($product->getId(), $favourites),
            ];
        }

        return new JsonResponse($compactData);

    }

    public function searchByUserId(string $userId, ProductRepository $productRepository, EntityManagerInterface $em): JsonResponse
    {
        $products = $productRepository->createQueryBuilder('p')
            ->andWhere('p.owner = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        $user = $em->getRepository(User::class)->find($userId);
        $favourites = $user->getFavouriteProducts();
        $compactData = [];
        foreach ($products as $product) {
            $compactData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'aspectRatio' => $product->getThumbnailRatio(),
                'ownerId' => $product->getOwner(),
                'remuneration' => $product->getRemuneration(),
                'image' => $product->getImages()[0] ?? null,
                'isFavourite' => in_array($product->getId(), $favourites),
            ];
        }
        return new JsonResponse($compactData);

    }

    public function reserveProduct(string $userId, string $productId, ProductRepository $productRepository, EntityManagerInterface $em): JsonResponse {
        $product = $productRepository->find($productId);
        $product->setBuyerId($userId);
        $em->flush();
        return new JsonResponse([
            'message' => 'Product updated successfully',

        ], 200);
    }
}

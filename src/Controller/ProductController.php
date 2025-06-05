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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class ProductController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createProduct(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->request->get('data'), true); // Use $request->request per obtenir dades de formularis

        $product = new Product();

        $product->setName($data['name'] ?? '');
        $owner =  $this->entityManager->getRepository(User::class)->find($data['ownerId']);
        $product->setOwner($owner);
        $product->setBuyerId($data['buyerId'] ?? null);
        $product->setDescription($data['description'] ?? '');
        $product->setRemuneration($data['remuneration'] ?? 0);

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

        $this->entityManager->persist($product);
        $this->entityManager->flush();

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
        $response['ownerId'] = $product->getOwner()->getId();
        $response['createdAt'] = $product->getCreatedAt();
        $response['images'] = $product->getImages();
        $response['isFavourite'] = $isFavourite;


        $json = $serializer->serialize($response, 'json');
        dump($json);
        return new Response($json, 200, ['Content-Type' => 'application/json']);

    }

    public function getAllCompact(string $userId, Request $request): JsonResponse
    {

        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], 400);
        }

        $user =  $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $favourites = $user->getFavouriteProducts(); // suposadament un array d'IDs
        $products =  $this->entityManager->getRepository(Product::class)->findAll();

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

    public function deleteProduct(string $id) {
        $product = $this->entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();    }

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

    public function searchByUserId(string $userId, ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->createQueryBuilder('p')
            ->andWhere('p.owner = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        $user =  $this->entityManager->getRepository(User::class)->find($userId);
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

    public function reserveProduct(string $userId, string $productId, ProductRepository $productRepository): JsonResponse {
        $product = $productRepository->find($productId);
        $product->setBuyerId($userId);
        $this->entityManager->flush();
        return new JsonResponse([
            'message' => 'Product updated successfully',

        ], 200);
    }

    public function assignBuyer(string $productId, string $buyerId, ProductRepository $productRepository): JsonResponse {
        $product = $productRepository->find($productId);
        $product->setBuyerId($buyerId);
        $this->entityManager->flush();
        return new JsonResponse([
            'message' => 'Product updated successfully',

        ], 200);
    }
}

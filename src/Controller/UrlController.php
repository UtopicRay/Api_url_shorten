<?php

namespace App\Controller;

use App\DTO\UrlDto;
use App\Entity\Url;
use App\Service\Serializer\SerializerDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UrlController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/api/url', name: 'url',methods: ['POST'])]
    function ShortUrl(Request $request):Response
    {
        try {
            $serializer=new SerializerDTO();
            $UrlDTO=$serializer->deserialize($request->getContent(),UrlDto::class,'json');
            $UrlDTO->setCreatedAt(new \DateTime());

        }catch (\Exception $exception){
            return new Response($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new Response($serializer->serialize($UrlDTO,'json'),200);
    }
}
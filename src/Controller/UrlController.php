<?php

namespace App\Controller;

use App\DTO\UrlDto;
use App\Service\Serializer\SerializerDTO;
use App\Services;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    function ShortUrl(Request $request):JsonResponse
    {
        try {
            $serializer=new SerializerDTO();
            $header=[
                "Content-Type"=>"application/json",
                "Access-Control-Allow-Origin"=>"*",
            ];
            $service=new Services();
            $UrlDTO=$serializer->deserialize($request->getContent(),UrlDto::class,'json');
            $checkUrl=$service->checkUrl($UrlDTO->getOriginalUrl(),$this->entityManager);
            if ($service->validateUrl($UrlDTO->getOriginalUrl())){
                if ($checkUrl){
                    $UrlDTO->setOriginalUrl($checkUrl->getOriginalUrl());
                    $UrlDTO->setShortedUrl($checkUrl->getShortedUrl());
                    $UrlDTO->setCreatedAt($checkUrl->getCreatedAt());
                }else{
                    $Url=$service->AddNewUrl($UrlDTO,$this->entityManager);
                    $UrlDTO->setShortedUrl($Url->getShortedUrl());
                    $UrlDTO->setCreatedAt(new DateTimeImmutable());
                }
            }else{
                return new JsonResponse([
                    "success"=>false,
                    "message"=>"Something went wrong. Check the URL again.",
                ],Response::HTTP_OK,$header);
            }
        }catch (Exception $exception){
            return new JsonResponse([
                "success"=>false,
                "message"=>$exception->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR,$header);
        }
        return new JsonResponse([
            "success"=>true,
            "data"=>$UrlDTO,]
            ,200,$header);
    }
}
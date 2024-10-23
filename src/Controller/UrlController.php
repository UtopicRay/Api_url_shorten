<?php

namespace App\Controller;

use App\DTO\UrlDto;
use App\Entity\Url;
use App\Service\Serializer\SerializerDTO;
use App\Services;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
                    $Url=$service->AddNewUrl($UrlDTO,$this->entityManager);
                    $UrlDTO->setShortedUrl($Url->getShortedUrl());
                    $UrlDTO->setCreatedAt(new DateTimeImmutable());
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

    #[Route('/cutUrl/{code}', name: 'redirect_url',methods: ['GET'])]
    function RedirectToUrl(string $code){
        $server_name=$_ENV['SERVER_NAME'];
        $header=[
            "Access-Control-Allow-Origin"=>"*",
        ];
        $aux=$server_name .":8000/cutUrl/" . $code;
        $url=$this->entityManager->getRepository(Url::class)->findOneBy(['shorted_url'=>$aux]);
        if ($url==null){
            return new JsonResponse(
                ["message"=>'we dont check this url in our database',
                    "data"=>$url,
                    "server_name"=>$server_name .":8000/cutUrl/" . $code,
            ]);
        }else{
            $originalUrl=$url->getOriginalUrl();
            return new RedirectResponse($originalUrl,302,$header);
        }
    }
    #[Route('/genQr',name: 'gen_qr',methods: ['POST'])]
    function getQR(Request $request):Response|JsonResponse
    {
        $service=new Services();
        $serializer=new SerializerDTO();
        $url=$serializer->deserialize($request->getContent(),UrlDto::class,'json');
        if (!$service->validateUrl($url->getOriginalUrl())){
            return new JsonResponse([
                "success"=>false,
                "message"=>"Something went wrong. Check the URL again.",
            ],Response::HTTP_OK);
        }else{
            $qr=$service->GenerateQR($url->getOriginalUrl());
            $header=['Content-Type'=>$qr->getMimeType(),
                'Access-Control-Allow-Origin'=>'*',];
            return new Response($qr->getString(),200,$header);
        }
    }
}


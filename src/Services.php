<?php

namespace App;

use App\Dto\UrlDto;
use App\Entity\Url;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Services
{
    function validateUrl(string $url): bool
    {

//first we validate the url using a regex

        if (!preg_match('%^https?://(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4])|(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*\.[a-z\x{00a1}-\x{ffff}]{2,}\.?)(?::\d{2,5})?(?:[/?#]\S*)?$%uiS', $url)) {

            return false;
        }


//if the url is valid, we "curl it" and expect to get a 200 header response in order to validate it.
/*
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body (faster)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // we follow redirections
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if ($httpcode == "200") {
            return true;
        } else {
            return false;
        }
*/
        return true;
    }

    function generarCodigo($longitud):string {
        $key = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
        $max = strlen($pattern)-1;
        for($i=0;$i < $longitud;$i++)
            $key .= $pattern[mt_rand(0,$max)];
        return $key;
    }
    function AddNewUrl(UrlDto $dto, EntityManagerInterface $entityManager):Url
    {
        $isChecked=$this->checkUrl($dto->getOriginalUrl(),$entityManager);
        if ($isChecked) {
            return $isChecked;
        }else{
            $code= $this->generarCodigo(5);
            $insertData= new Url();
            $insertData->setOriginalUrl($dto->getOriginalUrl());
            $insertData->setShortedUrl('localhost:8000/cutUrl/'.$code);
            $insertData->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($insertData);
            $entityManager->flush();
            return $insertData;
        }
    }
    function checkUrl(string $url,EntityManagerInterface $entityManager):bool|url
    {
        $findUrl=$entityManager->getRepository(Url::class)->findOneBy(['original_url'=>$url]);
        if ($findUrl) {
            return $findUrl;
        }
        return false;
    }
    function GenerateQR(string $x)
    {
        $writer=new PngWriter();
        $qr = new QrCode(
            data: $x,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10
        );
        $result = $writer->write($qr);
        $writer->validateResult($result,$x);
        return $result;
    }
}
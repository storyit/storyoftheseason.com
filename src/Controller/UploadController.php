<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity;

class UploadController extends AbstractController
{
    /**
     * upload-form
     */
    public function index()
    {
        return $this->render('upload/index.html.twig');
    }

    /**
     * upload-submit
     */
    public function submit(Request $request, EntityManagerInterface $entityManager)
    {
        $file = $request->files->get('file');
        $name = $request->request->get('name');
        $caption = $request->request->get('caption');
        $date = $request->request->get('date');
        $tz = $request->request->get('tz');
        $location = $request->request->get('location');
        $tags = $request->request->get('tags');
        $message = $request->request->get('message');

        if ($file === null || $name === null || $caption === null || $date === null || $location === null || $tags === null || $message === null) {
            throw new \Exception("Missing fields: need to POST 'file', 'name', 'caption', 'date', 'location', 'tags', 'message'; received " . print_r($request->request->all(), true));
        }

        $upload = new Entity\Upload;

        if ($file->isValid()) {
            $path = '/var/www/html/public/uploads/';
            $fn = $file->getClientOriginalName();
            if (file_exists($path . $fn)) {
                $fn = uniqid() . '.' . $file->guessClientExtension();
            }
            $file->move($path, $fn);
            $upload->setFile($fn);
        }

        $upload->setName($name);
        $upload->setCaption($caption);
        $upload->setDate(new DateTime($date, new DateTimeZone($tz)));
        $upload->setLocation($location);
        $upload->setTags($tags);
        $upload->setMessage($message);

        $entityManager->persist($upload);
        $entityManager->flush();

        return new JsonResponse(true);
    }
}

<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NationaliteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiAuteurController extends AbstractController
{
    /**
     * @Route("/api/auteurs", name="api_auteurs", methods = {"GET"})
     */
    public function list(AuteurRepository $repo, SerializerInterface $serializer)
    {
        $Auteurs = $repo->findAll();
        $resultats = $serializer->serialize($Auteurs, 'json', 
    [
        'groups' => ['listeAuteurFull']
    ]
    );
        return new JsonResponse($resultats, 200, [], true);
    }

     /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_show", methods = {"GET"})
     */
    public function show(Auteur $auteur, SerializerInterface $serializer)
    {
        $resultats = $serializer->serialize($auteur, 'json', 
    [
        'groups' => ['listeAuteurSimple']
    ]
    );
        return new JsonResponse($resultats, Response::HTTP_OK, [], true);
    }

     /**
     * @Route("/api/auteurs", name="api_auteurs_create", methods = {"POST"})
     */
    public function create(Request $request, NationaliteRepository $repoNation, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');

        $auteur = new Auteur();
        $nationalite = $repoNation->find($dataTab['nationalite']['id']);
        $serializer->deserialize($data, Auteur::class, 'json', ['object_to_populate' => $auteur]);
        $auteur->setNationalite($nationalite);

        //gestion des erreurs de validation
        $errors = $validator->validate($auteur);
        if(count($errors)){
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        $manager->persist($auteur);
        $manager->flush();

        return new JsonResponse("L'auteur a bien été créé", Response::HTTP_CREATED, 
       // ["location" => "/api/auteurs/" . $auteur->getId()], 
         ["location" => $this->generateUrl(
             'api_auteurs_show', 
             ["id" => $auteur->getId()],
              UrlGeneratorInterface::ABSOLUTE_URL)],
        true);
    }  
    
     /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_edit", methods = {"PUT"})
     */
    public function edit(Auteur $auteur, NationaliteRepository $repoNation, Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $nationalite = $repoNation->find($dataTab['nationalite']['id']);
        //solution 1 
        $serializer->deserialize($data, Auteur::class, 'json', ['object_to_populate' => $auteur]); 
        $auteur->setNationalite($nationalite);
        //solution 2
        //$serializer->denormalize($dataTab['auteur'], Auteur::class, null, ['object_to_populate' => $auteur]); // object_to_populate = m.a.j l'objet Auteur
        //gestion des erreurs de validation
        $errors = $validator->validate($auteur);
        if(count($errors)){
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }        
        $manager->persist($auteur);
        $manager->flush();

        return new JsonResponse("Modification réalisé !", Response::HTTP_OK, [], true);
    }
    
     /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_delete", methods = {"DELETE"})
     */
    public function delete(Auteur $auteur, EntityManagerInterface $manager)
    {
        $manager->remove($auteur);
        $manager->flush();

        return new JsonResponse("Élément supprimé !", Response::HTTP_OK, []);
    }     
    
}

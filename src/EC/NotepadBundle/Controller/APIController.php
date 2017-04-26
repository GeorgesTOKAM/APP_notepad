<?php
/**
 * Created by PhpStorm.
 * User: Georges
 * Date: 23/03/2017
 * Time: 12:57
 */

namespace EC\NotepadBundle\Controller;

use EC\NotepadBundle\Entity\Categorie;
use EC\NotepadBundle\Entity\NoteClass;
use EC\NotepadBundle\Form\NoteClassType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EC\NotepadBundle\Form\CategorieType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/notepad/api")
 */
class APIController extends Controller
{
    private function crossOriginResource(){
        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
        {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/text');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set("Access-Control-Allow-Methods", "GET, PUT, POST, DELETE, OPTIONS");
            return $response;
        }
    }


    /**
     * @Route("/notes")
     * @Method("GET")
     */
    public function allNotesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $notes = $em->getRepository('ECNotepadBundle:NoteClass')->findAll();
        $arrayCollection = array();
        foreach($notes as $note) {
            $arrayCollection[] = array(
                'id' => $note->getId(),
                'title'=> $note->getTitle(),
                'Date' => $note->getDate() -> format('d-m-y'),
                'Categorie' => $note->getCategorie() -> getNom(),
                'Content' => $note->getContent(),

            );
        }
        return new JsonResponse($arrayCollection);
    }

    /**
     * @Route("/notes")
     * @Method("POST")
     */
    public function ajNotesAction(Request $request)
    {
        $note = new NoteClass();
        $categories = new Categorie();
        $em = $this->getDoctrine()->getManager();
        $body = $request -> getContent();
        if(empty($body)){
            return new JsonResponse(['error' => 'Contenu vide']);
        }
        $data = json_decode($body, true);
        if(!$data){
            return new JsonResponse(['error'=> 'Le Contenu non Json']);
        }
        $note->setTitle($data['title']);
        $note->setDate(new \DateTime('NOW'));
        $note->setContent($data['content']);
        $categories = $em->getRepository('ECNotepadBundle:Categorie')->findOneByNom($data['categorie']);
        if(!$categories){
            return new JsonResponse(['error' => 'Categorie non trouver']);
        }
        $note->setCategorie($categories);
        $em->persist($note);
        $em->flush();
        return new JsonResponse(['reponse'=> 'la Note a ete ajoutee']);
    }

    /**
     * @Route("/notes/{id}")
     * @Method("DELETE")
     */
    public function delNoteAction(NoteClass $note, Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $note = $em->getRepository('ECNotepadBundle:NoteClass')->findOneById($request->get('id'));
        if (empty($note)) {
            return new JsonResponse(['(404) message' => 'Pas de note trouvée pour cet ID'], Response::HTTP_NOT_FOUND);
        }
        $em->remove($note);
        $em->flush();
        return new JsonResponse(['sucess' => 'La note a ete supprimé']);
    }


    /**
     * @Route("/Categories2")
     * @Method({"GET", "OPTIONS"})
     */
    public function all2CatAction(Request $request)
    {
        $this->crossOriginResource();
        $resp = new Response();
        $resp->headers->set('Content-Type','application/json');
        $resp->headers->set('Access-Control','Allow-Origin','*');
        $resp->headers->set('Access-Control','Allow-Methods','GET, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $cats = $em->getRepository('ECNotepadBundle:Categorie')->findAll();
        if(!$cats){
            $resp->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'category not found');
            $jsoncontent = json_encode($response);
            $resp->setContent($jsoncontent);
            return $resp;
        }
        $resp->setStatusCode(Response::HTTP_OK);
        $jsoncontent = $serializer->serialize($cats, 'json');
        $resp->setContent($jsoncontent);
        return $resp;
    }






    /**
     * @Route("/Categories")
     * @Method({"GET", "OPTIONS"})
     */
    public function allCatAction()
    {
        $this->crossOriginResource();
        $res = new Response();
        $res->headers->set('Content-Type','application/json');
        $res->headers->set('Access-Control','Allow-Origin','*');
        $res->headers->set('Access-Control','Allow-Methods','GET, OPTIONS');

        $em = $this->getDoctrine()->getManager();
        $cats = $em->getRepository('ECNotepadBundle:Categorie')->findAll();
        $arrayCollection = array();
        foreach($cats as $cat) {
            $arrayCollection[] = array(
                'id' => $cat->getId(),
                'Categorie'=> $cat->getNom(),
            );
        }
        return new JsonResponse($arrayCollection);
    }
    /**
     * @Route("/Categories")
     * @Method("POST")
     */
    public function addcatAction(Request $request )
    {
        $body = $request -> getContent();
        $data = json_decode($body, true);
        $em = $this->getDoctrine()->getManager();
        $cat = $em->getRepository('ECNotepadBundle:Categorie')->find($data['categorie']);
        //$category->setNom($data['categorie']);
        $em = $this->getDoctrine()->getManager();
        //$em->persist($category);
        $em->flush();
        return new JsonResponse(['reponse' => $cat]);
    }

    /**
     * @Route("/Cate")
     * @Method("POST")
     */
    public function AddAction(Request $request )
    {
        $content = $request->getContent();
        $validator = $this->get('validator');

        if (empty($content)) {
            $msg = "Le contenu est vide";
            return new JsonResponse(['error' => $msg], self::SC_BADREQ);
        }

        $category_data = json_decode($content, true);
        if (!$category_data) {
            $msg = "Content is not a valid json";
            return new JsonResponse(['error' => $msg], self::SC_BADREQ);
        }

        $category = new Categorie();
        if (!array_key_exists('name', $category_data)){
            $category->setNom($category_data['name']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
        }
        else{
            $em = $this->getDoctrine()->getEntityManager();
            $cats = $em->getRepository('ECNotepadBundle:Categorie')->findBynom($category_data['name']);
            //$catss = $test->getNom();
            return new JsonResponse(['reponse' => $cats]);
        }


        $errors = $validator->validate($category);

        if (count($errors) > 0) {
            $response_array['error'] = "Category is not valid";
            return new JsonResponse($response_array, self::SC_BADREQ);
        }



        $em = $this->getDoctrine()->getManager();
        $cats = $em->getRepository('ECNotepadBundle:Categorie')->findAll();
        $arrayCollection = array();
        foreach($cats as $cat) {
            $arrayCollection[] = array(
                'id' => $cat->getId(),
                'Categorie'=> $cat->getNom(),
            );
        }
        return new JsonResponse($arrayCollection);
    }

    /**
     * @Route("/Categories/{id}")
     * @Method("DELETE")
     */
    public function delcatAction(Categorie $cat) {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($cat);
        $em->flush();
        return new JsonResponse(['sucess' => true]);
    }

    /**
     * @Route("/notes")
     * @Method("POST")
     */
    public function addnoteAction(Request $request )
    {
        $cat = new Categorie();
        $form = $this->createForm(CategorieType::class, $cat);
        $form->handleRequest($request);
        $body = $request -> getContent();
        $data = json_decode($body, true);
        //$em = $this->getDoctrine()->getManager();
        // On avait déjà récupéré la liste des candidatures
        $listApplications = $this->getDoctrine()
            ->getRepository('ECNotepadBundle:Categorie')
            //->findAll();
            ->findOneByNom($cat->getNom());
        ;
        return new JsonResponse(['error' => $listApplications], 400);
        /*$body = $request -> getContent();
        if (empty($body)){
            return new JsonResponse("Note vide veullez remplir", 200, array('Content-Type' => 'application/json'));
        }
        $data = json_decode($body, true);
        //$em = $this->getDoctrine()->getRepository('ECNotepadBundle:Categorie')->find($data['categorie']);
        //echo $data['categorie'];
        if (!$data){
            return new JsonResponse(['error' => "le contenue n'est pas json"], 400);
        }
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('ECNotepadBundle:Categorie')->findAll();
        //$em = $this->getDoctrine()->getRepository('ECNotepadBundle:Categorie')->find($data['categorie']);
        //$categori = $em->getRepository('ECNotepadBundle:Categorie')->find($data['categorie']);

        return new JsonResponse(['error' => $categories], 400);*/


        /*$note = new NoteClass();
        $note -> setTitle($data['title']);
        $note -> setContent($data['content']);
        $note -> setDate(new \DateTime('NOW'));


        $em = $this->getDoctrine()->getManager();
        $categori = $em->getRepository('ECNotepadBundle:Categorie')->find($data['categorie']);
        if ($categori === null){
            echo $categori;
            return new JsonResponse("Categorie null", 200, array('Content-Type' => 'application/json'));
        }
        else{
            return new JsonResponse("Categorie ok", 200, array('Content-Type' => 'application/json'));
        }*/
        //$note -> setCategorie($categori);


        //$em->persist($note);
        //$em->flush();

        //return new JsonResponse("OK", 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @Route("/notes/")
     * @Method("POST")
     */
    public function createNoteAction(Request $request) {

        $body = $request -> getContent();
        $data = json_decode($body, true);
        $note = new NoteClass();
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('ECNotepadBundle:Categorie')->find($data['categorie']);

        $note -> setTitle($data['title']);
        $note -> setContent($data['content']);
        $note -> setDate($data['date']);
        $note -> setCategorie($category);
        $em->persist($note);
        $em->flush();

        return new JsonResponse("OK", 200, array('Content-Type' => 'application/json'));
    }





    /**
     * @Route("/notes/{id}")
     * @Method("PUT")
     */
    public function updateNoteAction(Request $request, NoteClass $note) {

        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('ECNotepadBundle:Categorie')->find($data['Categorie']);

        $note->setTitle($data['title']);
        $note->setContent($data['content']);
        $note->setCategorie($category);

        $em->persist($note);
        $em->flush();

        return new Response();
    }


    /**
     * @Route("/categories")
     * @Method("GET")
     */
    function allCategoriesAction() {

        $em = $this->getDoctrine()->getManager();
        $notes = $em->getRepository('ECNotepadBundle:Categorie')->findAll();
        $arrayCollection[] = array();
        foreach($notes as $id) {
            $arrayCollection[] = array(
                'id' => $id->getId(),
                'Nom'=> $id->getNom(),
            );
        }
        return new JsonResponse($arrayCollection);
    }

    /**
     * @Route("/categories")
     * @Method("POST")
     */
    function newCatAction(Request $request) {
        $category = new Categorie();
        return $this->updateCategoryAction($request, $category);
    }

    /**
     * @Route("/categories/{id}")
     * @Method("PUT")
     */
    function updateCatAction(Request $request, Categorie $category) {
        $data = json_decode($request->getContent(), true);
        $category->setNom($data['nom']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();
        return new JsonResponse('categorie ajouter');
    }
}

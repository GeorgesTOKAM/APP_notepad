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
     * @Route("/one_note/{id}")
     * @Method({"GET"})
     */
    public function oneNoteAction($id, Request $request)
    {
        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','GET, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $body = $request -> getContent();
        $data = json_decode($body, true);
        $em = $this->getDoctrine()->getManager();
        $notes = $em->getRepository('ECNotepadBundle:NoteClass')->findOneById($id);
        //var_dump($notes);
        if(!$notes){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Notes is not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $rs->setStatusCode(Response::HTTP_OK);
        $jsonserial = $serializer->serialize($notes, 'json');
        $rs->setContent($jsonserial);
        return $rs;
    }

    /**
     * @Route("/notes")
     * @Method({"GET"})
     */
    public function allNotesAction(Request $request)
    {
        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','GET, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $notes = $em->getRepository('ECNotepadBundle:NoteClass')->findAll();
        //var_dump($notes);
        if(!$notes){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Notes is not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $rs->setStatusCode(Response::HTTP_OK);
        $jsonserial = $serializer->serialize($notes, 'json');
        $rs->setContent($jsonserial);
        return $rs;
    }
    /**
     * @Route("/Categories")
     * @Method({"GET"})
     */
    public function allCatAction(Request $request)
    {
        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','GET, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $em = $this->getDoctrine()->getManager();

        $cats = $em->getRepository('ECNotepadBundle:Categorie')->findAll();
        if(!$cats){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'category is not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $rs->setStatusCode(Response::HTTP_OK);
        $jsonserial = $serializer->serialize($cats, 'json');
        $rs->setContent($jsonserial);
        return $rs;
    }

    /**
     * @Route("/notes_post")
     * @Method({"POST", "OPTIONS"})
     */
    public function PostNotesAction(Request $request)
    {
        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','POST, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $note = new NoteClass();
        $em = $this->getDoctrine()->getManager();
        $body = $request -> getContent();

        if(empty($body)){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Notes is empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $data = json_decode($body, true);
        if(!$data){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Json Code is not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        if(empty($data['title'])){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Title is empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        if(empty($data['content'])){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'content is empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        if(empty($data['categorie'])){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'categoriy is empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $note->setTitle($data['title']);
        $note->setDate(new \DateTime('NOW'));
        $note->setContent($data['content']);
        $categories = $em->getRepository('ECNotepadBundle:Categorie')->findOneByNom($data['categorie']);
        if(!$categories){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Category does not exist');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $note->setCategorie($categories);
        $em->persist($note);
        $em->flush();

        $rs->setStatusCode(Response::HTTP_OK);
        $response = array('success'=> 'The Note is added');
        $jsoncontent = json_encode($response);
        $rs->setContent($jsoncontent);
        return $rs;
    }

    /**
     * @Route("/categorie_post")
     * @Method({"POST", "OPTIONS"})
     */
    public function PostCatAction(Request $request)
    {
        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','POST, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $categories = new Categorie();
        $em = $this->getDoctrine()->getManager();
        $body = $request -> getContent();

        if(empty($body)){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Body is empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $data = json_decode($body, true);
        if(!$data){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Json Code is not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $catego = $em->getRepository('ECNotepadBundle:Categorie')->findOneByNom($data['categorie']);
        if($catego){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'The category exists');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        if(empty($data['categorie'])){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'The category empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        $categories->setNom($data['categorie']);
        $em->persist($categories);
        $em->flush();
        $rs->setStatusCode(Response::HTTP_OK);
        $response = array('success'=> 'The category is added');
        $jsoncontent = json_encode($response);
        $rs->setContent($jsoncontent);
        return $rs;
    }

    /**
     * @Route("/notes_put/{id}")
     * @Method({"PUT", "OPTIONS"})
     */
    function putNotesAction($id, Request $request) {

        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','PUT, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $note = new NoteClass();
        $body = $request -> getContent();
        $data = json_decode($body, true);
        $em = $this->getDoctrine()->getManager();
        $note = $em->getRepository('ECNotepadBundle:NoteClass')->findOneById($id);
        if (empty($note)) {
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Note not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        elseif(!empty($data['title']) && !empty($data['content']) && !empty($data['categorie'])){
            $note->setTitle($data['title']);
            $note->setContent($data['content']);
            $categories = $em->getRepository('ECNotepadBundle:Categorie')->findOneByNom($data['categorie']);
            $note->setCategorie($categories);
            $note->setDate(new \DateTime('NOW'));
            $em->persist($note);
            $em->flush();
            $rs->setStatusCode(Response::HTTP_OK);
            $response = array('succes'=> 'Note Updated Successfully');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        elseif(empty($data['title']) && !empty($data['content'])&& !empty($data['categorie'])){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'title not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        elseif(!empty($data['title']) && empty($data['content']) && !empty($data['categorie'])){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'content not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        elseif(!empty($data['title']) && !empty($data['content']) && empty($data['categorie'])){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'categorie not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        else{
            $rs->setStatusCode(Response::HTTP_NOT_ACCEPTABLE);
            $response = array('error'=> 'note cannot be empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
    }

    /**
     * @Route("/categorie_put/{id}")
     * @Method({"PUT", "OPTIONS"})
     */
    function putCatAction($id, Request $request) {

        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','PUT, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $cate = new Categorie();
        $body = $request -> getContent();
        $data = json_decode($body, true);
        $em = $this->getDoctrine()->getManager();
        $cate = $em->getRepository('ECNotepadBundle:Categorie')->findOneById($id);
        if (empty($cate)) {
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Category not found');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        elseif(!empty($data['categorie'])){
            $cate->setNom($data['categorie']);
            $em->persist($cate);
            $em->flush();
            $rs->setStatusCode(Response::HTTP_OK);
            $response = array('succes'=> 'Category Updated Successfully');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        elseif(empty($data['categorie'])){
            $rs->setStatusCode(Response::HTTP_NOT_FOUND);
            $response = array('error'=> 'Category is empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
        else{
            $rs->setStatusCode(Response::HTTP_NOT_ACCEPTABLE);
            $response = array('error'=> 'Category cannot be empty');
            $jsoncontent = json_encode($response);
            $rs->setContent($jsoncontent);
            return $rs;
        }
    }
    /**
     * @Route("/Categories_del/{id}")
     * @Method({"DELETE", "OPTIONS"})
     */
    public function delcatAction(Categorie $cat) {
        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','DELETE, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($cat);
        $em->flush();
        $rs->setStatusCode(Response::HTTP_OK);
        $response = array('succes'=> 'Category delete Successfully');
        $jsoncontent = json_encode($response);
        $rs->setContent($jsoncontent);
        return $rs;
    }

    /**
     * @Route("/notes_delb")
     * @Method({"DELETE", "OPTIONS"})
     */
    public function delnotesAction(Request $request) {
        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','http://localhost:3000');
        $rs->headers->set('Access-Control-Allow-Methods','DELETE, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $body = $request -> getContent();
        $data = json_decode($body, true);
        $em = $this->getDoctrine()->getManager();
        $not = $em->getRepository('ECNotepadBundle:NoteClass')->findOneById($data['id']);

        //$em = $this->getDoctrine()->getEntityManager();
        $em->remove($not);
        $em->flush();
        $rs->setStatusCode(Response::HTTP_OK);
        $response = array('succes'=> 'Note delete Successfully');
        $jsoncontent = json_encode($response);
        $rs->setContent($jsoncontent);
        return $rs;
    }

    /**
     * @Route("/notes_del/{id}")
     * @Method({"DELETE", "OPTIONS"})
     */
    public function delnoteAction(NoteClass $not) {
        $this->crossOriginResource();
        $rs = new Response();
        $rs->headers->set('Content-Type','application/json');
        $rs->headers->set('Access-Control-Allow-Origin','*');
        $rs->headers->set('Access-Control-Allow-Methods','DELETE, OPTIONS');
        $encoders = array(new XmlEncoder(),new JsonEncode());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($not);
        $em->flush();
        $rs->setStatusCode(Response::HTTP_OK);
        $response = array('succes'=> 'Note delete Successfully');
        $jsoncontent = json_encode($response);
        $rs->setContent($jsoncontent);
        return $rs;
    }
}

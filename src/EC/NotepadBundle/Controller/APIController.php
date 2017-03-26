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

/**
 * @Route("/notepad/api")
 */
class APIController extends Controller
{
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
     * @Route("/notes/")
     * @Method("POST")
     */
    public function createNoteAction(Request $request) {

        $test = new NoteClass();
        $datas = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('ECNotepadBundle:Categorie')->find($datas['categorie']);
        $test->setTitle($datas['title']);
        $test->setContent($datas['content']);
        $test->setCategorie($category);
        $em = $this->getDoctrine()->getManager();
        $em->persist($test);
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
     * @Route("/notes/{id}")
     * @Method("DELETE")
     */
    public function delNoteAction(NoteClass $note) {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($note);
        $em->flush();
        return new JsonResponse(['sucess' => true]);
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

    /**
     * @Route("/categories/{id}")
     * @Method("DELETE")
     */
    public function delcatAction(Categorie $cat) {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($cat);
        $em->flush();
        return new JsonResponse(['sucess' => true]);
    }
}

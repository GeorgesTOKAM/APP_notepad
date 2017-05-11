<?php
/**
 * Created by PhpStorm.
 * User: Georges
 * Date: 02/03/2017
 * Time: 16:32
 */
namespace EC\NotepadBundle\Controller;

use EC\NotepadBundle\Form\NoteClassType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use EC\NotepadBundle\Entity\Categorie;
use EC\NotepadBundle\Form\CategorieType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EC\NotepadBundle\Entity\NoteClass;

class NotepadController extends Controller
{
    /**
     * @Route("/", name = "list_note")
     */
    public function indexAction()
    {
        $listNotes = $this->getDoctrine()
            ->getRepository('ECNotepadBundle:NoteClass')
            ->findAll();
        return $this->render('ECNotepadBundle:Notepad:index.html.twig', $mesVars = array(
            'listNotes' => $listNotes
        ));

        //$content = $this->get('templating')->render('ECNotepadBundle:Notepad:index.html.twig',array('nom' => 'Scampis à la diable', 'nom2' => 'cuisine'));
        //return new Response($content);
    }

    /**
     * @Route("/note/ajouter", name = "ajt_note")
     */
    public function ajouternoteAction(Request $request)
    {
        $titre = "Ajouter une Note";
        $note = new NoteClass(); // on cree une nouvelle note
        $form = $this->createForm(NoteClassType::class, $note);// on récupère le formulaire et on spécifi qu'il doit etre crée a partir de l'objet note
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush();
            return $this->redirectToRoute('list_note');
        }

        // on génère le HTML du formulaire crée
        $formView = $form->createView();
        return $this->render('ECNotepadBundle:Notepad:ajouternote.html.twig', array('form' => $form->createView(),'titre' => $titre));
    }

    /**
     * @Route("/note/edit/{id}", name = "edit_note", requirements = { "id" = "\d+" })
     */
    public function edinotetAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('ECNotepadBundle:Categorie')->findAll();
        $titre = "Modifier la Note";
        $note = new NoteClass();
        $note = $em->getRepository('ECNotepadBundle:NoteClass')->findOneById($id);

        $form = $this->createFormBuilder($note)
            ->add('title',   TextType::class, array(
                'data' => $note->getTitle(),
            ))
            ->add('content',   TextareaType::class, array(
                'data' => $note->getContent(),
            ))
            ->add('date',   DateType::class, array(
                'data' => $note->getDate(),
            ))
            ->add('categorie', ChoiceType::class, array(
                    'choices'    => $categories,
                    'choice_label' => function($cat, $key, $index){
                        return $cat->getNom();
                    })
            )
            ->add('Sauvegarder',   SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        $note = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $em->flush();

            return $this->redirect($this->generateUrl('list_note', array(
                'id' => $note->getId())));
        }
        return $this->render('ECNotepadBundle:Notepad:ajouternote.html.twig', array('form' => $form->createView(),'titre' => $titre));

    }

    /**
     * @Route("/note/delete/{id}", name = "del_note", requirements = { "id" = "\d+" })
     */
    public function deletenoteAction($id)
    { 
        $note = $this->getDoctrine()->getRepository('ECNotepadBundle:NoteClass')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($note);
        $em->flush();

        return $this->redirect($this->generateUrl('list_note', array('id' => $note->getId())));
    }
}
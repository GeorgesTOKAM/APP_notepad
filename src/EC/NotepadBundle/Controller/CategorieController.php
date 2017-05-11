<?php
/**
 * Created by PhpStorm.
 * User: Georges
 * Date: 02/03/2017
 * Time: 16:32
 */
namespace EC\NotepadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use EC\NotepadBundle\Entity\Categorie;
use EC\NotepadBundle\Form\CategorieType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;




class CategorieController extends Controller
{
    /**
     * @Route("/categorie/liste", name="list_cat")
     */
    public function listAction()
    {
        $catdbread = $this->getDoctrine()->getRepository('ECNotepadBundle:Categorie');

        $categories = $catdbread->findAll();

        return $this->render('ECNotepadBundle:Notepad:listcat.html.twig', array('categories' => $categories,));
    }
    /**
     * @Route("/categorie/ajouter", name = "ajt_cat")
     */
    public function ajouterAction(Request $request)
    {
        // on commence par cree une nouvelle categorie
        $categorie = new Categorie();
        $erreur ="";
        $titre = "Ajouter une categorie";
            // on recupère le formulaire
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        // si le formulaire à ete soumis
        if ($form->isSubmitted() && $form->isValid()) {
            $save = $this
                ->getDoctrine()
                ->getRepository('ECNotepadBundle:Categorie')
                ->findBynom($categorie->getNom());
            if(!$save) {
                //on enregistre le produit en base de donnée
                $em = $this->getDoctrine()->getManager();
                $em->persist($categorie); // prepare l'objet pour l'insere dans la base de donnée
                $em->flush(); // évacu les données vers la base de donnée
            }
            else {
                $erreur = "La categorie existe déja";
                $formView = $form->createView();
                return $this->render('ECNotepadBundle:Notepad:ajoutercat.html.twig', array('form'=>$formView,'erreur' => $erreur, 'class' => '', 'class' => "alert alert-danger",'titre' => $titre));
                
                //return $this->redirectToRoute('ajt_cat', array('erreur' => "Cette categorie existe déjà."));
            }
            
            return $this->redirectToRoute('list_cat');
        }
        $formView = $form->createView();
        // on genère le HTML du formulaire et on rend la vue
        return $this->render('ECNotepadBundle:Notepad:ajoutercat.html.twig', array('form'=>$formView, 'erreur' => $erreur, 'class' => '','titre' => $titre));
    }

    /**
     * @Route("/categorie/editer/{id}", name="edit_cat", requirements = { "id" = "\d+" })
     */
    public function editecatAction(Request $request, $id)
    {
        $cat = new Categorie();
        $save = $this ->getDoctrine()->getRepository('ECNotepadBundle:Categorie')->find($id);
        $erreur = "";
        $titre = "Modifier une categorie";
        $form = $this->createFormBuilder($cat) ->add('nom', TextType::class, array('data' => $save -> getNom(),))->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $save->setNom($cat->getNom());
            $repository = $this->getDoctrine()->getRepository('ECNotepadBundle:Categorie');
            $reposi = $repository->findOneBynom($cat->getNom());

            if(!$reposi){
                $em = $this->getDoctrine()->getManager();
                $em->persist($save);
                $em->flush();
            }
            else {
                $erreur = "La categorie existe déja";
                $formView = $form->createView();
                return $this->render('ECNotepadBundle:Notepad:ajoutercat.html.twig', array('form'=>$formView, 'erreur' => $erreur, 'class' => "alert alert-danger",'titre' => $titre));
            }
            return $this->redirectToRoute('list_cat');
        }
        $formView = $form->createView();
        return $this->render('ECNotepadBundle:Notepad:ajoutercat.html.twig', array('form'=>$formView, 'erreur' => $erreur, 'class' => '','titre' => $titre));
    }

    /**
     * @Route("/categorie/supprimer/{id}", name="del_cat", requirements = { "id" = "\d+" })
     */
    public function supprimercatAction($id)
    {
        $cat = $this->getDoctrine()->getRepository('ECNotepadBundle:Categorie')->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($cat);
        $em->flush();
        return $this->redirect($this->generateUrl('list_cat', array('id' => $cat->getId())));
    }

}
<?php

namespace Magend\KeywordBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\KeywordBundle\Entity\Keyword;
use Magend\KeywordBundle\Form\KeywordType;

/**
 * Keyword controller.
 *
 * @Route("/keyword")
 */
class KeywordController extends Controller
{
    /**
     * Lists all Keyword entities.
     *
     * @Route("/", name="keyword")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('MagendKeywordBundle:Keyword')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a Keyword entity.
     *
     * @Route("/{id}/show", name="keyword_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('MagendKeywordBundle:Keyword')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Keyword entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new Keyword entity.
     *
     * @Route("/new", name="keyword_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Keyword();
        $form   = $this->createForm(new KeywordType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new Keyword entity.
     *
     * @Route("/create", name="keyword_create")
     * @Method("post")
     * @Template("MagendKeywordBundle:Keyword:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Keyword();
        $request = $this->getRequest();
        $form    = $this->createForm(new KeywordType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('keyword_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Keyword entity.
     *
     * @Route("/{id}/edit", name="keyword_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('MagendKeywordBundle:Keyword')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Keyword entity.');
        }

        $editForm = $this->createForm(new KeywordType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Keyword entity.
     *
     * @Route("/{id}/update", name="keyword_update")
     * @Method("post")
     * @Template("MagendKeywordBundle:Keyword:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('MagendKeywordBundle:Keyword')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Keyword entity.');
        }

        $editForm   = $this->createForm(new KeywordType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('keyword_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Keyword entity.
     *
     * @Route("/{id}/delete", name="keyword_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('MagendKeywordBundle:Keyword')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Keyword entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('keyword'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}

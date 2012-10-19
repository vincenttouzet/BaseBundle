
    /**
     * Deletes a {{ entity }} entity.
     *
     * @param Request $request Request instance
     * @param {{ entity_class }} $entity  Entity
     *
{% if 'annotation' == format %}
     * @Route("/{id}/delete", name="{{ route_name_prefix }}_delete")
     * @Method("POST")
{% endif %}
     * @return \Symfony\Component\HttpFoundation\Response | \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, {{ entity_class }} $entity)
    {
        $form = $this->_createDeleteForm($entity->getId());
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->get('{{ bundle|camelizeBundle }}.{{ entity|lower }}_manager');
            $em->delete($entity);
        }

        return $this->redirect($this->generateUrl('{{ route_name_prefix }}'));
    }

    /**
     * Create the delete form
     *
     * @param int $id ID of the entity
     *
     * @return \Symfony\Component\Form\Form
     */
    private function _createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }

    /**
     * Edits an existing {{ entity }} entity.
     *
     * @param Request $request Request instance
     * @param {{ entity_class }} $entity  Entity
     *
{% if 'annotation' == format %}
     * @Route("/{id}/update", name="{{ route_name_prefix }}_update")
     * @Method("POST")
     * @Template("{{ bundle }}:{{ entity }}:edit.html.twig")
{% endif %}
     * @return \Symfony\Component\HttpFoundation\Response | \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, {{ entity_class }} $entity)
    {
        $em = $this->get('{{ bundle|camelizeBundle }}.{{ entity|lower }}_manager');

        $deleteForm = $this->_createDeleteForm($entity->getId());
        $editForm = $this->createForm(new {{ entity_class }}Type(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->update($entity);

            return $this->redirect($this->generateUrl('{{ route_name_prefix }}_edit', array('id' => $entity->getId())));
        }

{% if 'annotation' == format %}
        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
{% else %}
        return $this->render(
            '{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:edit.html.twig', 
            array(
                'entity'      => $entity,
                'form'   => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
            )
        );
{% endif %}
    }

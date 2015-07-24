
    /**
     * Displays a form to edit an existing {{ entity }} entity.
     *
     * @param {{ entity_class }} $entity Entity
     *
{% if 'annotation' == format %}
     * @Route("/{id}/edit", name="{{ route_name_prefix }}_edit")
     * @Template()
{% endif %}
     * @return \Symfony\Component\HttpFoundation\Response | \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction({{ entity_class }} $entity)
    {
        $editForm = $this->createForm(new {{ entity_class }}Type(), $entity);
        $deleteForm = $this->_createDeleteForm($entity->getId());

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

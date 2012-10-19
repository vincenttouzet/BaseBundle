
    /**
     * Finds and displays a {{ entity }} entity.
     *
     * @param {{ entity_class }} $entity Entity
     *
{% if 'annotation' == format %}
     * @Route("/{id}/show", name="{{ route_name_prefix }}_show")
     * @Template()
{% endif %}
     * @return \Symfony\Component\HttpFoundation\Response | \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showAction({{ entity_class }} $entity)
    {
{% if 'delete' in actions %}

        $deleteForm = $this->_createDeleteForm($entity->getId());
{% endif %}

{% if 'annotation' == format %}
        return array(
            'entity'      => $entity,
{% if 'delete' in actions %}
            'delete_form' => $deleteForm->createView(),
{% endif %}
        );
{% else %}
        return $this->render(
            '{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:show.html.twig', 
            array(
                'entity'      => $entity,
{% if 'delete' in actions %}
                'delete_form' => $deleteForm->createView(),
{% endif %}
            )
        );
{% endif %}
    }

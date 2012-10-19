
    /**
     * Displays a form to create a new {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/new", name="{{ route_name_prefix }}_new")
     * @Template()
{% endif %}
     * @return \Symfony\Component\HttpFoundation\Response | \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction()
    {
        $entity = new {{ entity_class }}();
        $form   = $this->createForm(new {{ entity_class }}Type(), $entity);

{% if 'annotation' == format %}
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
{% else %}
        return $this->render(
            '{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:edit.html.twig', 
            array(
                'entity' => $entity,
                'form'   => $form->createView(),
            )
        );
{% endif %}
    }

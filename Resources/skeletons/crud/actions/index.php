
    /**
     * Lists all {{ entity }} entities.
     *
{% if 'annotation' == format %}
     * @Route("/", name="{{ route_name_prefix }}")
     * @Template()
{% endif %}
     * @return \Symfony\Component\HttpFoundation\Response | \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction()
    {
        $em = $this->get('{{ bundle|camelizeBundle }}.{{ entity|lower }}_manager');

        $entities = $em->getRepository('{{ bundle }}:{{ entity }}')->findAll();

{% if 'annotation' == format %}
        return array(
            'entities' => $entities,
        );
{% else %}
        return $this->render(
            '{{ bundle }}:{{ entity|replace({'\\': '/'}) }}:index.html.twig', 
            array(
                'entities' => $entities,
            )
        );
{% endif %}
    }

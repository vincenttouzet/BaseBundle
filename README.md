BaseBundle
==========

This bundle define base class for improve your development.

It requires SonataAdminBundle and SonataDoctrineORMAdminBundle

Entity Management
-----------------

This bundle defines a BaseManager class that is compatible with SonataAdmin ModelManager.

Juste define a new class for your entity :
```php
namespace Acme\DemoBundle\Manager;

use VinceT\BaseBundle\Manager\BaseManager;

class PostManager extends BaseManager
{
}
```

Define the manager as a service in your services.yml :
```yml
parameters:
    post_manager.class: Acme\DemoBundle\Manager\PostManager

services:
    post_manager:
        class: %post_manager.class%
        arguments: [@service_container]
```
you can now acces this manager from any controller :
```php
[...]
$postManager = $this->container->get('post_manager');
[...]
$post = new Acme\DemoBundle\Entity\Post();
[...]
$postManager->create($post);
[...]
$postManager->update($post);
[...]
$postManager->delete($post);
[...]
```

To use this manager with SonataAdmin, add a call to setModelManager in your services.yml file
```yml
services:
    acme.demo.admin.post:
      class: Acme\DemoBundle\Admin\PostAdmin
      tags:
        - { name: sonata.admin, manager_type: orm, group: Blog, label: Post }
      arguments: [null, Acme\DemoBundle\Entity\Post, AcmeDemoBundle:PostAdmin]
      calls:
        - [ setModelManager, [ @page_manager ] ]
        - [ setTranslationDomain, [ AcmeDemoBundle ] ]
```

Your front and Admin application will now use the same entity manager.

Admin Controller
----------------

This bundle also define a BaseAdminController that catch exception throwed during an admin action. It is very easy to use :

Define your admin controller :
```php
namespace Acmd\DemoBundle\Controller;

use VinceT\BaseBundle\Controller\BaseAdminController;

class PostAdminController extends BaseAdminController
{
}
```

Don't forget to use this controller in your admin service (the third argument).
```yml
services:
    acme.demo.admin.post:
      class: Acme\DemoBundle\Admin\PostAdmin
      tags:
        - { name: sonata.admin, manager_type: orm, group: Blog, label: Post }
      arguments: [null, Acme\DemoBundle\Entity\Post, AcmeDemoBundle:PostAdmin]
```

Commands
--------

To make your development faster a command can generate the following classes for an entity, bundle or namespace :
* Admin/EntityAdmin
* Controller/Admin/EntityAdminController
* Manager/EntityManager

and create/update the following files :
* Resources/config/services.yml
* Resources/translations/YourBundle.en.yml
* Resources/translations/YourBundle.fr.yml

To use it :
```
php app/console vincet:generate MyBundle:Post
```
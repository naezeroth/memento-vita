<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class WelcomeController extends AbstractController
{
    /**
     * @Route("/", name="welcome")
     */
    public function index()
    {
        return $this->render('welcome/index.html.twig');

//        return new Response('hello', Response::HTTP_OK);
        // return new Response('hello', 200, Response:: );
    }


    //     *     defaults={"name" = "Apurva"},
    /**
     * @Route("/hello-page/{name}",
     *     name="hello_page",
     *     requirements={"name"="[A-Za-z]+"}
     *     )
     * @param Request $request
     * @return Response
     */
    public function hello($name = "Apurva", Security $security)
    {
//        $name = $request->query->get('name', 'John');
//        $db = $this->getDoctrine()->getManager();
//        $name = $db->getRepository(User::class)->f
        $user = $security->getUser();
        $name = $user->getUsername();
        return $this->render('hello_page.html.twig', [
            'name' => $name
        ]);
    }
}

<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function index(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);
        if($form->isSubmitted()) {
            dump($request);

            $contactFormData = $form->getData();

            $this->addFlash('success', 'some info');

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom($contactFormData['email'])
                ->setTo('apurvashukla123@gmail.com')
                ->setBody(
                    $contactFormData['message'],
                    'text/plain'
                );

            $mailer->send($message);

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/contact.html.twig', [
            'our_form' => $form->createView()
        ]);
    }
}

<?php

namespace kapitanluffy\PropelModelParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('PropelModelParserBundle:Default:index.html.twig', array('name' => $name));
    }
}

<?php

// src/Controller/MainController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Doctrine\DBAL\Driver\Connection;

class MainController extends Controller
{

    /**
     * @Route("/")
     */

    public function number(Connection $conn)
    {
        $number = mt_rand(0, 144);

        $nodes = $conn->fetchAll('SELECT * FROM node_field_data LIMIT 10');
        $aliases = $conn->fetchAll('SELECT * FROM url_alias LIMIT 10');

        return $this->render('main/index.html.twig', array(
            'number' => $number,
            'nodes' => $nodes,
            'aliases' => $aliases,
        ));

    }
}

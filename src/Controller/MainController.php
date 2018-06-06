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
     * @Route("/", name="homepage")
     */

    public function homepage(Connection $conn)
    {
        $number = mt_rand(0, 144);

        $nodes = $conn->fetchAll('SELECT * FROM node_field_data WHERE langcode = "uk" LIMIT 12');
        $aliases = $conn->fetchAll('SELECT * FROM url_alias LIMIT 20');

        $homeUrl = "/project/argba"; // url of the main profile in CMS DB

        $homeNode = $conn->fetchAssoc("SELECT source FROM url_alias WHERE alias =  '".$homeUrl."'");

        $homeId = str_replace('/node/', "", $homeNode['source']); // id of the main profile in CMS DB
        
        $homeImageset = $conn->fetchAll('SELECT DISTINCT
         node__field_imageset.field_imageset_target_id AS image_id,
         node__field_imageset.delta AS delta,
         node__field_imageset.field_imageset_title AS image_title,
         node__field_imageset.field_imageset_alt AS image_alt,
         file_managed.filename AS filename,
         file_managed.uri AS uri
         FROM node__field_imageset
         LEFT JOIN file_managed
         ON node__field_imageset.field_imageset_target_id = file_managed.fid
         WHERE node__field_imageset.entity_id = "'.$homeId.'"
         AND node__field_imageset.langcode = "uk"
         ORDER BY delta ASC');

        $profileImage = $conn->fetchAssoc('SELECT
         node__field_image.field_image_target_id AS image_id,
         node__field_image.field_image_title AS image_title,
         node__field_image.field_image_alt AS image_alt,
         file_managed.filename AS filename,
         file_managed.uri AS uri
         FROM node__field_image
         LEFT JOIN file_managed
         ON node__field_image.field_image_target_id = file_managed.fid
         WHERE node__field_image.entity_id = "'.$homeId.'"
         AND node__field_image.langcode = "uk"');


        return $this->render('main/index.html.twig', array(
            'number' => $number,
            'nodes' => $nodes,
            'aliases' => $aliases,
            'home_id' => $homeId,
            'home_imageset' => $homeImageset,
            'profile_image' => $profileImage,
        ));

    }
}

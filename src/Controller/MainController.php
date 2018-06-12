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
        
        $qbProfile = $conn->createQueryBuilder();
        $qbProfile->select('DISTINCT node.title, body.body_value, contact.field_contact_value,
        header.field_header_value,  promo.field_promo_value, footer.field_footer_value')
             ->from('node_field_data', 'node')
             ->leftJoin('node','node__body','body','node.nid = body.entity_id')
             ->leftJoin('node','node__field_contact','contact','node.nid = contact.entity_id')
             ->leftJoin('node','node__field_header','header','node.nid = header.entity_id')
             ->leftJoin('node','node__field_promo','promo','node.nid = promo.entity_id')
             ->leftJoin('node','node__field_footer','footer','node.nid = footer.entity_id')
             ->where('node.nid = ?')
             ->andWhere('body.langcode = ?')
             ->andWhere('contact.langcode = ?')
             ->andWhere('header.langcode = ?')
             ->andWhere('promo.langcode = ?')
             ->andWhere('footer.langcode = ?')
             ->setParameter(0, $homeId)
             ->setParameter(1, 'uk')
             ->setParameter(2, 'uk')
             ->setParameter(3, 'uk')
             ->setParameter(4, 'uk')
             ->setParameter(5, 'uk');
        $profileData = $qbProfile->execute()->fetchAll();

        return $this->render('main/index.html.twig', array(
            'number' => $number,
            'home_imageset' => $homeImageset,
            'profile_image' => $profileImage,
            'profile_data'  => $profileData[0],
        ));

    }
}

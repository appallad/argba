<?php
// src/Controller/MainController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Doctrine\DBAL\Driver\Connection;

class MainController extends AbstractController

{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage()
    {
        return $this->redirect('http://localhost/project/argba');
    }

    /**
     * @Route("/{profile}/{profileName}", name="profile")
     */
    public function profile(Connection $conn, $profile, $profileName)
    {
        $profileUrl = "/".$profile."/".$profileName; // url of the main profile in CMS DB

        $profileLang = "uk";

        $profileNode = $conn->fetchAssoc("SELECT source FROM url_alias WHERE alias =  '".$profileUrl."'");

        $profileId = str_replace('/node/', "", $profileNode['source']); // id of the profile in CMS DB

        $queryProfileMaterialsTypeCount = $conn->fetchAll("SELECT bundle, COUNT(*) as count FROM node__field_owner WHERE field_owner_target_id = ?
            AND langcode = ?
            GROUP BY bundle ",
            array($profileId, $profileLang)
        );

        $profileMaterialsTypeCount = array();
        foreach ($queryProfileMaterialsTypeCount as $type) {
            $profileMaterialsTypeCount[$type['bundle']] = $type['count'];
        }

    // Select profile fields

        $profileData = $conn->fetchAssoc('SELECT
         node_field_data.title AS title,
         node_field_data.langcode AS langcode,
         node_field_data.status AS status,
         node__body.body_value AS body_value,
         node__body.body_summary AS body_summary,
         node__field_contact.field_contact_value AS field_contact_value,
         node__field_facebook.field_facebook_uri AS facebook_uri,
         node__field_facebook.field_facebook_title AS facebook_title,
         node__field_twitter.field_twitter_uri AS twitter_uri,
         node__field_twitter.field_twitter_title AS twitter_title,
         node__field_youtube.field_youtube_uri AS youtube_uri,
         node__field_youtube.field_youtube_title AS youtube_title,
         node__field_instagram.field_instagram_uri AS instagram_uri,
         node__field_instagram.field_instagram_title AS instagram_title,
         node__field_github.field_github_uri AS github_uri,
         node__field_github.field_github_title AS github_title,
         node__field_website.field_website_uri AS website_uri,
         node__field_website.field_website_title AS website_title,
         node__field_text_color.field_text_color_value AS text_color_value,
         node__field_image.field_image_title AS image_title,
         node__field_image.field_image_alt AS image_alt,
         file_managed.uri AS image_uri
         FROM node_field_data
         LEFT JOIN node__body
         ON node_field_data.nid = node__body.entity_id AND node__body.langcode = ? AND node_field_data.title != null
         LEFT JOIN node__field_contact
         ON node_field_data.nid = node__field_contact.entity_id AND node__field_contact.langcode = ?
         LEFT JOIN node__field_facebook
         ON node_field_data.nid = node__field_facebook.entity_id AND node__field_facebook.langcode = ?
         LEFT JOIN node__field_twitter
         ON node_field_data.nid = node__field_twitter.entity_id AND node__field_twitter.langcode = ?
         LEFT JOIN node__field_youtube
         ON node_field_data.nid = node__field_youtube.entity_id AND node__field_youtube.langcode = ?
         LEFT JOIN node__field_instagram
         ON node_field_data.nid = node__field_instagram.entity_id AND node__field_instagram.langcode = ?
         LEFT JOIN node__field_github
         ON node_field_data.nid = node__field_github.entity_id AND node__field_github.langcode = ?
         LEFT JOIN node__field_website
         ON node_field_data.nid = node__field_website.entity_id AND node__field_website.langcode = ?
         LEFT JOIN node__field_text_color
         ON node_field_data.nid = node__field_text_color.entity_id
         LEFT JOIN node__field_image
         ON node_field_data.nid = node__field_image.entity_id AND node__field_image.langcode = ?
         LEFT JOIN file_managed
         ON node__field_image.field_image_target_id = file_managed.fid

         WHERE node_field_data.nid = ?
         AND node_field_data.langcode = ?',
         array($profileLang, $profileLang, $profileLang, $profileLang, $profileLang, $profileLang, $profileLang, $profileLang, $profileLang, $profileId, $profileLang)
        );

    // Select profile background

        $profileBg = $conn->fetchAssoc('SELECT
         node__field_background.field_background_target_id AS image_id,
         file_managed.filename AS filename,
         file_managed.uri AS uri
         FROM node__field_background
         LEFT JOIN file_managed
         ON node__field_background.field_background_target_id = file_managed.fid
         WHERE node__field_background.entity_id = ?
         AND node__field_background.langcode = ?',
         array($profileId, $profileLang)         
        );

    // Select profile welcom slides

        $profileSlide = $conn->fetchAll('SELECT DISTINCT
         node__field_slide.field_slide_target_id AS slide_id,
         node__field_slide.delta AS delta,
         node__field_slide.field_slide_title AS slide_title,
         node__field_slide.field_slide_alt AS slide_alt,
         file_managed.filename AS filename,
         file_managed.uri AS uri
         FROM node__field_slide
         LEFT JOIN file_managed
         ON node__field_slide.field_slide_target_id = file_managed.fid
         WHERE node__field_slide.entity_id = ?
         AND node__field_slide.langcode = ?
         ORDER BY delta ASC',
         array($profileId, $profileLang)
        );

    // Select profile Features

        if ( array_key_exists("feature", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['feature'] > 2) {

            $profileFeatures = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "feature"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileFeatures = array(); 
            }

    // Select profile Services

            if ( array_key_exists("service", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['service'] > 2) {

            $profileServices = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "service"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileServices = array(); 
            }

    // Select profile Products

            if ( array_key_exists("product", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['product'] > 2) {

            $profileProducts = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "product"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileProducts = array(); 
            }

    // Select profile Blogs

            if ( array_key_exists("blog", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['blog'] > 2) {

            $profileBlogs = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "blog"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileBlogs = array(); 
            }

    // Select profile News

            if ( array_key_exists("news", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['news'] > 2) {

            $profileNews = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "news"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileNews = array(); 
            }

    // Select profile Testimonials

            if ( array_key_exists("testimonial", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['testimonial'] > 2) {

            $profileTestimonials = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "testimonial"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileTestimonials = array(); 
            }

    // Select profile Participants

            if ( array_key_exists("participant", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['participant'] > 2) {

            $profileParticipants = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "participant"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileParticipants = array(); 
            }

    // Select profile Portfolio

            if ( array_key_exists("portfolio", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['portfolio'] > 2) {

            $profilePortfolio = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "portfolio"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profilePortfolio = array(); 
            }

    // Select profile Events

            if ( array_key_exists("event", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['event'] > 0) {

            $profileEvents = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "event"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileEvents = array(); 
            }

    // Select profile Vacancies

            if ( array_key_exists("vacancy", $profileMaterialsTypeCount) && $profileMaterialsTypeCount['vacancy'] > 0) {

            $profileVacancies = $conn->fetchAll('SELECT DISTINCT
             node_field_data.title AS title,
             node_field_data.nid AS id,
             node_field_data.langcode AS langcode,
             node__body.body_value AS body_value,
             node__body.body_summary AS body_summary,
             node__field_image.field_image_title AS image_title,
             node__field_image.field_image_alt AS image_alt,
             file_managed.uri AS image_uri
             FROM node__field_owner
             LEFT JOIN node_field_data
             ON node_field_data.nid = node__field_owner.entity_id AND node_field_data.langcode = ? AND node_field_data.status = 1 AND  node_field_data.type = "vacancy"
             LEFT JOIN node__body
             ON node__body.entity_id = node_field_data.nid AND node__body.langcode = ?
             LEFT JOIN node__field_image
             ON node__field_image.entity_id = node_field_data.nid AND node__field_image.langcode = ?
             LEFT JOIN file_managed
             ON node__field_image.field_image_target_id = file_managed.fid

             WHERE node__field_owner.field_owner_target_id = ?
             LIMIT 6',
             array($profileLang, $profileLang, $profileLang, $profileId)
            );

            } else {

                $profileVacancies = array(); 
            }

       //var_dump($profileMaterialsTypeCount);
       //var_dump($profileData);
       //var_dump($profileFeatures);

        return $this->render('test/index.html.twig', array(
            'profile_data' => $profileData,
            'profile_url' => $profileUrl,
            'profile_slide' => $profileSlide,
            'profile_background' => $profileBg,
            'profile_materials_type_count' => $profileMaterialsTypeCount,
            'profile_features' => $profileFeatures,
            'profile_services' => $profileServices,
            'profile_products' => $profileProducts,
            'profile_blogs' => $profileBlogs,
            'profile_news' => $profileNews,
            'profile_testimonials' => $profileTestimonials,
            'profile_participants' => $profileParticipants,
            'profile_portfolio' => $profilePortfolio,
            'profile_events' => $profileEvents,
            'profile_vacancies' => $profileVacancies,
        ));
    }
    

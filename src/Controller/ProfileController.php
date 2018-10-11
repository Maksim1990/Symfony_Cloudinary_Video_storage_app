<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Profile;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

class ProfileController extends AbstractController
{
    /**
     * @Route("{_locale}/profile/{id}", name="profile")
     */
    public function profile($id, Request $request)
    {

        \Cloudinary::config([
            "cloud_name" => getenv('CLOUD_NAME'),
            'api_key' => getenv('API_KEY'),
            "api_secret" =>  getenv('API_SECRET')
        ]);

        $url=cloudinary_url("samples/cloudinary-group.jpg", array("width" => 100, "height" => 150, "crop" => "fill"));
        $video=cl_video_tag("samples/elephants",array("width" => 400,
            "crop" => "pad", "background" => "gray",
            "preload" => "none", "controls" => true,
            "fallback_content" => "Your browser does not support HTML5 video tags"));

        $package = new Package(new StaticVersionStrategy('v1'));
        $image_upload=$package->getUrl('/images/cat.png');
        $video_upload=$package->getUrl('/images/test.mp4');

//        \Cloudinary\Uploader::upload(getcwd().str_replace("?v1","",$video_upload), array(
//            "folder" => "symfony/",
//            "public_id" => "video_test",
//            "resource_type" => "video",
//            "eager" => array(
//                array("width" => 300, "height" => 300,
//                    "crop" => "pad", "audio_codec" => "none"),
//                array("width" => 160, "height" => 100,
//                    "crop" => "crop", "gravity" => "south",
//                    "audio_codec" => "none"))));


        //Upload image
        \Cloudinary\Uploader::upload(getcwd().str_replace("?v1","",$image_upload),
            array("folder" => "symfony2/",
                "tag"=>"maksim",
                "public_id" => "test2"));
        $result = \Cloudinary\Uploader::add_tag('maksim', 'symfony2/test2', $options = array());
        dd($result);
//        //Rename
//        \Cloudinary\Uploader::rename('symfony/image_new', 'symfony/image_new555');


        //delete
        //\Cloudinary\Uploader::destroy('test');

        $api = new \Cloudinary\Api();
        //-- Get name of all root folders
       //$api->root_folders();
       //Get subfolders
        //$api->subfolders("samples");


        //-- Default image
        //dd($api->resources(array("resource_type" => "video")));
        //$api->delete_resources(array("symfony2/image_new777"));
        //dd($api->delete_resources_by_tag("maksim",array('folder'=>'symfony')));

        // dd($package->getUrl('/images/cat.png'));
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

//        dd($user->getProfile());
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'url' => $url,
            'video' => $video,
            'image_url' => $package->getUrl('/images/cat.png'),
        ]);
    }

    /**
     * @Route("{_locale}/update_image/{id}", name="update_image")
     */
    public function updateProfileImage($id, Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $image = new Image;
        $formImage = $this->createFormBuilder($image)
            ->add('imageFile', FileType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;')))
            ->add('submit', SubmitType::class, array('label' => 'Update image', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-bottom:15px;')))
            ->getForm();

            $formImage->handleRequest($request);


        if ($formImage->isSubmitted() && $formImage->isValid()) {


            //dd($formImage->isSubmitted()&& $formImage->isValid());
            $imageFile = $formImage['imageFile']->getData();
            $size = $imageFile->getClientSize();
            if ($size < 1500000) {
                //-- Delete old image
                $em = $this->getDoctrine()->getManager();
                $imageDelete = $user->getImage();

                if ($imageDelete) {
                    if (file_exists('images/uploads/' . $imageDelete->getImageName())) {
                        unlink('images/uploads/' . $imageDelete->getImageName());
                    }
                    $query = $em->createQuery('DELETE ' . Image::class . ' c WHERE c.id =' . $imageDelete->getId());
                    $query->execute();
                }

                $dir = 'images/uploads';
                $imageFile->move($dir, $imageFile->getClientOriginalName());


                // $redis_cluster->set('profile_image_path', $dir.'/'. $imageFile->getClientOriginalName());
                $session = $this->get('session');
                $session->set('user', array(
                    'profile_image_path' => $dir . '/' . $imageFile->getClientOriginalName(),
                ));


                $image->setImageName($imageFile->getClientOriginalName());
                $image->setImageSize($size);
                $image->setUser($user);
                $now = new\DateTime('now');
                $image->setCreatedAt($now);
                $image->setUpdatedAt($now);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('notice', 'Image successfully updated');
                return $this->redirectToRoute('profile', ['id' => $user->getId()]);
            } else {
                $this->addFlash('notice', 'Image size should not be more than 1.5 MB!');
                return $this->render('profile/update_image.html.twig', array(
                    'formImage' => $formImage->createView()));
            }
        }

        return $this->render('profile/update_image.html.twig', array(
            'formImage' => $formImage->createView()
        ));


    }

    /**
     * @Route("{_locale}/update_profile/{id}", name="update_profile")
     */
    public function updateProfileAction($id, Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $profile = $this->getDoctrine()->getRepository(Profile::class)->findOneBy(array('userId' => $id));

        $profile->setCity($profile->getCity());
        $profile->setCountry($profile->getCountry());
        $profile->setPhone($profile->getPhone());
        $profile->setStatus($profile->getStatus());

        $form = $this->createFormBuilder($profile)
            ->add('country', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;'),'required'=>false))
            ->add('city', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;'),'required'=>false))
            ->add('phone', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;'),'required'=>false))
            ->add('status', TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;'),'required'=>false))
            ->add('submit', SubmitType::class, array('label' => 'Edit', 'attr' => array('class' => 'btn btn-primary', 'style' => 'margin-bottom:15px;')))
            ->getForm();
        $form->handleRequest($request);






        if ($form->isSubmitted() && $form->isValid()) {
//dd($form->isSubmitted() && $form->isValid());
            $em = $this->getDoctrine()->getManager();
            $country = $form['country']->getData();
            $city = $form['city']->getData();
            $phone = $form['phone']->getData();
            $status = $form['status']->getData();


            $profile = $em->getRepository(Profile::class)->findOneBy(array('userId' => $id));
            $profile->setCity($city);
            $profile->setCountry($country);
            $profile->setPhone($phone);
            $profile->setStatus($status);

            $em->flush();
            $this->addFlash('notice', 'Profile updated');
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }





        return $this->render('profile/update_profile.html.twig', array(
            'todo' => $profile,
            'form' => $form->createView()
        ));


    }

}

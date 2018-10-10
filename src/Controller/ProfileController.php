<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends AbstractController
{
    /**
     * @Route("{_locale}/profile/{id}", name="profile")
     */
    public function profile($id)
    {
        $user=$this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        return $this->render('profile/index.html.twig', [
            'user'=>$user
        ]);
    }

    /**
     * @Route("{_locale}/update_profile/{id}", name="update_profile")
     */
    public function updateProfileAction($id,Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $profile=$this->getDoctrine()->getRepository(Profile::class)->findOneBy(array('userId'=>$id));
        $profile->setCity($profile->getCity());
        $profile->setCountry($profile->getCountry());
        $profile->setPhone($profile->getPhone());
        $profile->setStatus($profile->getStatus());
        $form=$this->createFormBuilder($profile)
            ->add('country',TextType::class,array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px;')))
            ->add('city',TextType::class,array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px;')))
            ->add('phone',TextType::class,array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px;')))
            ->add('status',TextType::class,array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px;')))
            ->add('submit',SubmitType::class,array('label'=>'Edit','attr'=>array('class'=>'btn btn-primary','style'=>'margin-bottom:15px;')))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $country=$form['country']->getData();
            $city=$form['city']->getData();
            $phone=$form['phone']->getData();
            $status=$form['status']->getData();


            $em=$this->getDoctrine()->getManager();
            $profile=$em->getRepository(Profile::class)->findOneBy(array('userId'=>$id));
            $profile->setCity($city);
            $profile->setCountry($country);
            $profile->setPhone($phone);
            $profile->setStatus($status);


            $em=$this->getDoctrine()->getManager();

            $em->flush();
            $this->addFlash('notice','Profile updated');
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }



//        $image = new Image;
//
//        $formImage=$this->createFormBuilder( $image)
//            ->add('imageFile',FileType::class,array('attr'=>array('class'=>'form-control','style'=>'margin-bottom:15px;')))
//            ->add('submit',SubmitType::class,array('label'=>'Update image','attr'=>array('class'=>'btn btn-primary','style'=>'margin-bottom:15px;')))
//            ->getForm();

//        $formImage->handleRequest($request);
//
//        if($formImage->isSubmitted() && $formImage->isValid()){
//
//
//
//            $imageFile=$formImage['imageFile']->getData();
//            if($imageFile->getClientSize()<1500000){
//                //-- Delete old image
//                $em=$this->getDoctrine()->getManager();
//                $imageDelete = $user->getImage();
//                if($imageDelete){
//                    unlink('images/uploads/'.$imageDelete->getImageName());
//                    $query = $em->createQuery('DELETE AppBundle:Image c WHERE c.id ='.$imageDelete->getId());
//                    $query->execute();
//                }
//
//                $dir='images/uploads';
//                $imageFile->move($dir, $imageFile->getClientOriginalName());
//
//
//                // $redis_cluster->set('profile_image_path', $dir.'/'. $imageFile->getClientOriginalName());
//                $session = $this->get('session');
//                $session->set('user', array(
//                    'profile_image_path' => $dir.'/'. $imageFile->getClientOriginalName(),
//                ));
//
//
//                $image->setImageName($imageFile->getClientOriginalName());
//                $image->setImageSize($imageFile->getClientSize());
//                $image->setUser($user);
//                $now=new\DateTime('now');
//                $image->setCreatedAt($now);
//                $image->setUpdatedAt($now);
//                $em=$this->getDoctrine()->getManager();
//                $em->persist($image);
//                $em->flush();
//                $this->addFlash('notice','Image successfully updated');
//                return $this->redirectToRoute('profile', ['id' => $user->getId()]);
//            }else{
//                $this->addFlash('notice','Image size should not be more than 1.5 MB!');
//                return $this->render('profile/update_profile.html.twig',array(
//                    'todo'=>$profile,
//                    'form'=>$form->createView(),
//                    'formImage'=>$formImage->createView()));
//            }





        return $this->render('profile/update_profile.html.twig',array(
            'todo'=>$profile,
            'form'=>$form->createView(),
            //'formImage'=>$formImage->createView()
    ));


    }
}

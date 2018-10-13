<?php

namespace App\Controller;


use App\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ContentController extends AbstractController
{


    public function authCloudinary(){
        \Cloudinary::config([
            "cloud_name" => getenv('CLOUD_NAME'),
            'api_key' => getenv('API_KEY'),
            "api_secret" => getenv('API_SECRET')
        ]);
    }


    /**
     * @Route("{_locale}/content/{id}", name="content")
     */
    public function mediaContent($id, Request $request)
    {

        $this->authCloudinary();

        $api = new \Cloudinary\Api();
        $allVideos = $api->resources(array("resource_type" => "video"));
        $allImages = $api->resources();
        //dd($allVideos['resources']);
        //dd($allImages['resources']);
        $arrContent = array_merge($allImages['resources'], $allVideos['resources']);
        //dd($arrContent);
        return $this->render('content/index.html.twig', [
            'content' => $arrContent
        ]);
    }


    /**
     * @Route("{_locale}/rename/ajax" , name="rename_file_ajax")
     */
    public function ajaxUpdateAction(Request $request) {
        $status=true;
        if($public_id=urldecode($request->request->get('public_id'))){

            $this->authCloudinary();

            $strValue=$request->request->get('new_value');
            $strFileType=$request->request->get('file_type');

            $arrPath=explode("/",$public_id);
            unset($arrPath[count($arrPath)-1]);
            $strNewValue=implode("/",$arrPath)."/".$strValue;


            if($strFileType=='image'){
                $result=\Cloudinary\Uploader::rename($public_id, $strNewValue);
            }else{
                $result=\Cloudinary\Uploader::rename($public_id, $strNewValue,array("resource_type" => "video"));
            }

        if(!$result) $status=false;
            $arrData = ['output' => $status];
            return new JsonResponse($arrData);
        }
    }


    /**
     * @Route("{_locale}/delete/ajax" , name="delete_file_ajax")
     */
    public function ajaxDeleteAction(Request $request) {
        $status=true;
        if($public_id=urldecode($request->request->get('public_id'))){

            $this->authCloudinary();

            $strFileType=$request->request->get('file_type');

            if($strFileType=='image'){
                $result=\Cloudinary\Uploader::destroy($public_id);
            }else{
                $result=\Cloudinary\Uploader::destroy($public_id,array("resource_type" => "video"));
            }

            if(!$result) $status=false;
            $arrData = ['output' => $status];
            return new JsonResponse($arrData);
        }
    }


    /**
     * @Route("{_locale}/content/item/{type}/{id}/{format}", name="content_item")
     */
    public function showContentItem($type, $id, $format, Request $request)
    {
        $this->authCloudinary();

        $api = new \Cloudinary\Api();

        switch ($type) {
            case 'video':
                $resource = $api->resource(urldecode($id), array("resource_type" => "video"));
                break;
            case 'image':
                $resource = cloudinary_url(urldecode($id) . "." . $format, array("width" => 600));

                break;
            default:
                $resource = null;
        }

        return $this->render('content/item.html.twig', [
            'resource' => $resource,
            'type' => $type,
        ]);
    }

    /**
     * @Route("{_locale}/content/upload/{type}", name="content_upload")
     */
    public function uploadActions($type, Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $image = new Image;
        $form = $this->createFormBuilder($image)
            ->add('imageFile', FileType::class, array('label' => 'Choose file', 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;')))
            ->add('imageType', ChoiceType::class, array('choices' => array(
                'video' => 'video',
                'image' => 'image',
            ), 'label' => 'File type', 'data' => $type, 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;')))
            ->add('submit', SubmitType::class, array('label' => 'Upload', 'attr' => array('class' => 'btn btn-warning', 'style' => 'margin-bottom:15px;')))
            ->getForm();

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            //dd($formImage->isSubmitted()&& $formImage->isValid());
            $imageFile = $form['imageFile']->getData();
            $fileType = $form['imageType']->getData();
            $size = $imageFile->getClientSize();
            if ($size < 10000000) {

                $dir = 'images/cloudinary';
                $imageFile->move($dir, $imageFile->getClientOriginalName());

                $this->authCloudinary();

                $package = new Package(new StaticVersionStrategy('v1'));
                $file_upload = $package->getUrl('/' . $dir . '/' . $imageFile->getClientOriginalName());

                //-- Remove extension from public_id in Cloudinary cloud
                $fileName=explode('.',$imageFile->getClientOriginalName())[0];

                try{
                    if($fileType=='image'){
                        //-- Upload image file
                        $result=\Cloudinary\Uploader::upload(getcwd() . str_replace("?v1", "", $file_upload),
                            array("folder" => "symfony/",
                                "public_id" => $fileName));

                    }else{
                        $result=\Cloudinary\Uploader::upload(getcwd() . str_replace("?v1", "", $file_upload), array(
                            "folder" => "symfony/",
                            "public_id" =>  $fileName,
                            "resource_type" => "video",
                            "eager" => array(
                                array("width" => 300, "height" => 300,
                                    "crop" => "pad", "audio_codec" => "none"),
                                array("width" => 160, "height" => 100,
                                    "crop" => "crop", "gravity" => "south",
                                    "audio_codec" => "none"))));

                    }
                    if($result){
                        \Cloudinary\Uploader::add_tag('symfony_cloudinary', 'symfony/' . $imageFile->getClientOriginalName(), $options = array());

                        //-- Remove file from server after uploading to Cloudinary cloud
                        if (file_exists('images/cloudinary/' .$imageFile->getClientOriginalName())) {
                            unlink('images/cloudinary/' . $imageFile->getClientOriginalName());
                        }
                    }
                }catch (\Exception $e){
                    $this->addFlash('notice', 'Error while uploading file to the cloud. Check chosen file type.');
                    return $this->render('content/upload.html.twig', array(
                        'type' => $type,
                        'form' => $form->createView()));
                }



                return $this->redirectToRoute('content', ['id' => $user->getId()]);
            } else {
                $this->addFlash('notice', 'File size should not be more than 1.5 MB!');
                return $this->render('content/upload.html.twig', array(
                    'type' => $type,
                    'form' => $form->createView()));
            }
        }

        return $this->render('content/upload.html.twig', [
            'type' => $type,
            'form' => $form->createView()
        ]);
    }

}

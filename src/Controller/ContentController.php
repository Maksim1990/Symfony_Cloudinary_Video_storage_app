<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ContentController extends AbstractController
{
    /**
     * @Route("{_locale}/rename/ajax" , name="rename_file_ajax")
     */
    public function ajaxAction(Request $request) {
        $status=true;
        if($public_id=urldecode($request->request->get('public_id'))){

            \Cloudinary::config([
                "cloud_name" => getenv('CLOUD_NAME'),
                'api_key' => getenv('API_KEY'),
                "api_secret" => getenv('API_SECRET')
            ]);
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
}

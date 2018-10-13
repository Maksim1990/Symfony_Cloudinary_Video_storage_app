<?php
namespace App\Twig;

use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // this simple example doesn't define any dependency, but in your own
        // extensions, you'll need to inject services using this constructor
    }

    public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$'.$price;

        return $price;
    }

    public function idFormat($id)
    {
        $id = urlencode($id);

        return $id;
    }

    public function fileNameFormat($name)
    {
        $arrPath = explode('/',$name);
        $name=$arrPath[count($arrPath)-1];

        return $name;
    }

    public function folderFormat($name)
    {
        $arrPath = explode('/',$name);
        unset($arrPath[count($arrPath)-1]);


        return implode("/",$arrPath);
    }
}
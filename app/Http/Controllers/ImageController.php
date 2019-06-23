<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Image;

class ImageController extends Controller
{

    public function getToken(Request $request)
    {
        $imageOperation = new Image();
        $token = $imageOperation->getToken();
        return array(
            "info" => "",
            "code" => 200,
            "data" => array(
                "token" => $token
            )
        );
    }

    public function update(Request $request, $id)
    {}

    public function destroy(Request $request, $id)
    {
        $image = Image::find($id);
        $image->delete();
        return array(
            "info" => "",
            "code" => 200
        );
    }
}

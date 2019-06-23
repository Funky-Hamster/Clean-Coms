<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Qiniu\Auth;
// use Qiniu\Storage\UploadManager;
// use Qiniu\Zone;
// use Qiniu\Config;

class Image extends Model
{
    protected $table = "images";
    public $accessKey = 'M9aRV9oT0KxwoygcxnaQIqy-cC9JGiOSa6C7dSr0';
    public $secretKey = 'z4dxWRdxKRQhkhIj-v7TCnCVM9GJGhw0nul2x3ZP';
    public function getToken() {
//         $filetype = explode('.', $request->file('image')->getClientOriginalName());
//         $filePath = $request->file('image')->getPathname();
//         $key = time() . mt_rand(1, 1000) . "." . $filetype[1];
        $auth = new Auth($this->accessKey, $this->secretKey);
        $bucket = 'general-game';
        $token = $auth->uploadToken($bucket);
        return $token;
//         $config = new Config(Zone::zone2());
//         $uploadMgr = new UploadManager($config);
//         list ($result, $err) = $uploadMgr->putFile($token, $key, $filePath );
//         $imageUrl = 'http://pl2q0cvnn.bkt.clouddn.com/' . $result['key'];
//         return $imageUrl;
    }
}

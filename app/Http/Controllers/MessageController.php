<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RongCloud\RongCloud;
use App\User;

class MessageController extends Controller
{
    // 发送单聊及存储历史记录
    public function store(Request $request) {
        $rongCloud = new RongCloud($this->appKey, $this->appSecret);
        $message = $rongCloud->message();
        $user = $rongCloud->user();
        $userInfo = User::where('email', $request->username)->first();
    }
}

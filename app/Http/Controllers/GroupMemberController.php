<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\GroupMember;
use Carbon\Carbon;
use RongCloud\RongCloud;

class GroupMemberController extends Controller
{
    private $appKey = 'uwd1c0sxupuf1';
    private $appSecret = 'b2wDnmqRM4';
    public function index(Request $request) {
        
    }
    
    public function show(Request $request, $id) {
        $groupMembers = GroupMember::where('group_id', $id)->get();
        foreach ($groupMembers as $groupMember) {
            $groupMember->user_info = User::where('id', $groupMember->user_id)->first();
        }
        return array(
            "code" => 200,
            "info" => "",
            "data" => $groupMembers
        );
    }
    
    public function store(Request $request) {
        try{
            GroupMember::insert([
                'group_id' => $request->user_id,
                'user_id' => $request->group_id,
                'type' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now() 
            ]);
            $rongCloud = new RongCloud($this->appKey, $this->appSecret);
            $result = $rongCloud->group()->join($request->user_id, $request->group_id, $request->group_name);
            return array(
                "code" => 200,
                "info" => "",
            );
        }
        catch (\Exception $e) {
            return array(
                "code" => 1000,
                "info" => "Add failed"
            );
        }
    }
}

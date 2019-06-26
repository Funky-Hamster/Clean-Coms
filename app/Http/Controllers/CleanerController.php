<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use App\User;
use App\Cleaner;
use App\Image;
use App\Comment;
use Carbon\Carbon;

class CleanerController extends Controller
{
    public function index(Request $request) {
        $cleaners = User::where('type', 'cleaner')->where('is_deleted', 0)->get();
        foreach($cleaners as $cleaner) {
            $companies = Company::where('cleaner_id', $cleaner->id)->get();
            $cleaner->companies = $companies;
            $cleanerInfo = Cleaner::where('cleaner_id', $cleaner->id)->first();
            if($cleanerInfo != null) {
                $supervisorId = $cleanerInfo->supervisor_id;
                $cleaner->supervisor = User::find($supervisorId);
            }
            $cleaner->images = Image::where('cleaner_id', $cleaner->id)->get();
            $cleaner->comments = Comment::where('cleaner_id', $cleaner->id)->get();
        }
        return array(
            "info" => "",
            "code" => 200,
            "data" => $cleaners
        );
    }
    
    public function show(Request $request, $id) {
        $cleaner = User::where('id', $id)->where('type', 'cleaner')->where('is_deleted', 0)->first();
        if($cleaner == null){
            return array(
                "code" => 404,
                "info" => "找不到相关用户"
            );
        }
        $companies = Company::where('cleaner_id', $cleaner->id)->get();
        $cleaner->companies = $companies;
        $cleanerInfo = Cleaner::where('cleaner_id', $cleaner->id)->first();
        $supervisorId = $cleanerInfo->supervisor_id;
        $cleaner->supervisor = User::find($supervisorId);
        $cleaner->images = Image::where('cleaner_id', $cleaner->id)->get();
        $cleaner->comments = Comment::where('cleaner_id', $cleaner->id)->get();
        return array(
            "info" => "",
            "code" => 200,
            "data" => $cleaner
        );
    }
    
    public function update(Request $request, $id) {
        if($request->images_string) {
            $images = explode(",", $request->images_string);
        }
        
        if($request->images) {
            $images = $request->images;
        }
        
        if(isset($images)) {
            foreach($images as $image) {
                Image::insert([
                    "cleaner_id" => $id,
                    "image" => $image,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }
        
        if(isset($request->image)) {
            Image::insert([
                "cleaner_id" => $id,
                "image" => str_replace("sign-percentage", "%", str_replace("sign-and", "&", $request->image)),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
        
        if($request->comments_string) {
            $comments = explode(",", $request->comments_string);
        }
        
        if($request->comments) {
            $comments = $request->comments;
        }
        
        if(isset($comments)) {
            foreach($comments as $comment) {
                Comment::insert([
                    "cleaner_id" => $id,
                    "content" => $comment,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }
        
        if(isset($request->comment)) {
            Comment::insert([
                "cleaner_id" => $id,
                "content" => $request->comment,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            
            ]);
        }
        return array(
            "info" => "",
            "code" => 200
        );
        
    }
    
    public function destroy(Request $request, $id) {
        return 204;
    }
}

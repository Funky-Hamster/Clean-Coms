<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\InspectionComment;
use Carbon\Carbon;

class InspectionCommentController extends Controller
{
    
    public function store(Request $request)
    {
        try {
            $ret = InspectionComment::insert([
                'inspection_id' => $request->inspection_id,
                'content' => $request->content,
                'image' => $request->image,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            return array(
                "info" => "Add failed",
                "code" => 500,
            );
        }
        if ($ret) {
            return array(
                "info" => "",
                "code" => 200,
            );
        }
        return array(
            "info" => "Add failed",
            "code" => 500,
        );
    }
    
    public function destroy(Request $request, $id) {
        $inspection = InspectionComment::find($id);
        $inspection->delete();
        return array(
            "info" => "",
            "code" => 200,
        );
    }
}

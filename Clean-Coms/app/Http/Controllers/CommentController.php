<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comment;

class CommentController extends Controller
{
    public function index(Request $request) {
        
    }
    public function destroy(Request $request, $id)
    {
        $comment = Comment::find($id);
        $comment->delete();
        return array(
            "info" => "",
            "code" => 200
        );
    }
}

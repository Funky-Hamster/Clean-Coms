<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\History;
use App\User;
use Carbon\Carbon;

class HistoryController extends Controller {
    public function index(Request $equest) {
        $histories = History::orderBy('created_at', 'desc')->get();
        foreach($histories as $history) {
            $history->sender = User::find($history->sender_id);
        }
        return array(
            "code" => 200,
            "info" => "",
            "data" => $histories
        );
    }
}
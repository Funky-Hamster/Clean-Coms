<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Note;
use App\User;
use App\Company;
use App\Job;
use Carbon\Carbon;

class NoteController extends Controller
{
    public function index(Request $request) {
        try{
            if(isset($request->user_id)) {
                $notes = Note::where('supervisor_id', $request->user_id)->orderby('updated_at', 'desc')->get();
                if(sizeof($notes) == 0) {
                    $notes = Note::where('cleaner_id', $request->user_id)->orderby('updated_at', 'desc')->get();
                }
            }
            
            else {
                $notes = Note::orderby('updated_at', 'desc')->get();
            }
            
            foreach($notes as $note) {
                $note->creator = User::find($note->creator_id);
                $note->company_name = Company::find($note->company_id)->name;
                $note->type = 'company_note';
            }
            
            $jobs = Job::where('pending', '<>', 1)->orderBy('updated_at', 'desc')->get();
            $notes = $notes->toArray();
            foreach($jobs as $job) {
                $name = '';
                if($job->pending == 0) {
                    $name = 'A New Job Added, Check Now! (' . $job->suburb . ')';
                }
                else {
                    $name = 'One job sold (' . $job->suburb . ')';
                }
                
                $job->company_name = $name;
                $job->type = 'job';
                array_push($notes, $job);
                
                // $note = array(
                //     "job_id" => $job->id,
                //     "company_name" => $name,
                //     "content" => $job->description
                //     "created_at"
                // );
            }
            $notesTemp = [];
            $date = Carbon::now();
            $oneWeekAgo = $date->subWeek();
            for($i = 0; $i < sizeof($notes); $i ++) {
                if($notes[$i]['updated_at'] > $oneWeekAgo) {
                    array_push($notesTemp, $notes[$i]);
                }
            }
            
            $notes = $notesTemp;
            
            for($i = 0; $i < sizeof($notes); $i ++) {
                for($j = $i + 1; $j < sizeof($notes); $j ++) {
                    if($notes[$i]['updated_at'] < $notes[$j]['updated_at']) {
                        $temp = $notes[$i];
                        $notes[$i] = $notes[$j];
                        $notes[$j] = $temp;
                    }
                }
            }
            return array(
                "info" => "",
                "code" => 200,
                "data" => $notes
            );
        } catch (\Exception $e) {
            return array(
                "info" => "",
                "code" => 200,
                "data" => []
            );
        }
        
    }
    
    public function destroy(Request $request, $id)
    {
        $note = Note::find($id);
        $note->delete();
        return array(
            "info" => "",
            "code" => 200
        );
    }
}

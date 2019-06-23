<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inspection;
use App\Company;
use App\User;
use Carbon\Carbon;
use App\InspectionComment;
use App\Customer;

class InspectionController extends Controller
{
    public function index(Request $request) {
        if(isset($request->customer_id)) {
            $companyId = Customer::where('customer_id', $request->customer_id)->first()->company_id;
            $company = Company::find($companyId);
            $inspection = new Inspection();
            $inspectionTime = $company->inspection_time->format('d/M');
            $inspection->next = $inspectionTime;
            $inspection->company_id = $companyId;
            $inspection->inspection = $company->inspection;
            return array(
                "info" => "",
                "code" => 200,
                "data" => $inspection
            );
        }
        
        if(isset($request->company_id)) {
            $company = Company::find($request->company_id);
            $inspection = new Inspection();
            $inspection->next = $company->inspection_time->format('Y-m-d');
            $histories = Inspection::where('company_id', $request->company_id)->orderBy('inspection_time', 'desc')->get();
            foreach($histories as $history) {
                $history->comments = InspectionComment::where('inspection_id', $history->id)->orderby('updated_at', 'desc')->get();
                $history->user = User::find($history->user_id);
            }
            $inspection->histories = $histories;
            return array(
                "info" => "",
                "code" => 200,
                "data" => $inspection
            );
        }
        return array(
            "info" => "",
            "code" => 200,
            "data" => Inspection::orderBy('updated_at', 'desc')->get()
        );
    }
    
    public function show(Request $request, $id) {
        $inspection = Inspection::find($id);
        $inspection->comments = InspectionComment::where('inspection_id', $inspection->id)->orderby('updated_at', 'desc')->get();
        return array(
            "info" => "",
            "code" => 200,
            "data" => $inspection
        );
    }
    
    public function store(Request $request)
    {
        try {
            $companyId = $request->company_id;
            $company = Company::find($companyId);
            $inspectionTime = $company->inspection_time;
            $company->inspection_time = Carbon::now()->addDays($company->duration);
            $company->save();
            $ret = Inspection::insertGetId([
                'inspection_time' => $inspectionTime,
                'company_id' => $request->company_id,
                'site_name' => $request->site_name,
                'user_id' => isset($request->user_id) ? $request->user_id : 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            return array(
                "info" => "Add failed",
                "code" => 500,
            );
        }
        if ($ret > 0) {
            return array(
                "info" => "",
                "code" => 200,
                "data" => $ret
            );
        }
        return array(
            "info" => "Add failed",
            "code" => 500,
        );
    }
    
    public function destroy(Request $request, $id) {
        $inspection = Inspection::find($id);
        $inspection->delete();
        return array(
            "info" => "",
            "code" => 200,
        );
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Company;
use App\Cleaner;
use App\Customer;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    public function index(Request $request) {
        $supervisors = User::where('type', 'supervisor')->where('is_deleted', 0)->get();
        foreach($supervisors as $supervisor) {
            $supervisor->companies = Company::where('supervisor_id', $supervisor->id)->where('is_deleted', 0)->get();
            $cleaners = Cleaner::where('supervisor_id', $supervisor->id)->get();
            $cleanersInfo = [];
            foreach($cleaners as $cleaner) {
				$user = User::where('id', $cleaner->cleaner_id)->where('is_deleted', 0)->first();
				if($user != null) {
					array_push($cleanersInfo, $user);
				}
            }
            $supervisor->cleaners = $cleanersInfo;
        }
        return array(
            "info" => "",
            "code" => 200,
            "data" => $supervisors
        );
    }
    
    public function show(Request $request, $id) {
        $supervisor = User::where('id', $id)->where('type', 'supervisor')->where('is_deleted', 0)->first();
        if($supervisor == null){
            return array(
                "code" => 404,
                "info" => "No found"
            );
        }
        $companies = Company::where('supervisor_id', $id)->where('is_deleted', 0)->get();
        $supervisor->companies = $companies;
        $managers = [];
        foreach($companies as $company) {
            $companyManagers = [];
            $companyId = $company->id;
            $customers = Customer::where("company_id", $companyId)->get();
            foreach($customers as $customer) {
                $user = User::find($customer->customer_id);
                if($user->is_deleted == 0) {
                    array_push($managers, User::find($customer->customer_id));
                    array_push($companyManagers, $user);
                }

            }
            $company->managers = $companyManagers;
        }
        $supervisor->managers = $managers;
        
        $cleaners = Cleaner::where('supervisor_id', $supervisor->id)->get();
        $cleanersInfo = [];
        foreach($cleaners as $cleaner) {
            $user = User::find($cleaner->cleaner_id);
            if($user->is_deleted == 0) {
                array_push($cleanersInfo, $user);
            }
            
        }
        $supervisor->cleaners = $cleanersInfo;
        if(isset($request->type)) {
            if($request->type == 'company') {
                $companiesToInspect = [];
                foreach($companies as $company) {
                    $cDate = Carbon::parse($company->inspection_time);
                    if($company->inspection_time > Carbon::now()) {
                        $company->days_to_inspection = $cDate->diffInDays();
                        array_push($companiesToInspect, $company);
                    }
                        
                    else {
                        $company->days_to_inspection = -$cDate->diffInDays();
                    }
                }
                if(isset($request->inspection)) {
                    if($request->inspection == 1) {
                        return array(
                            "info" => "",
                            "code" => 200,
                            "data" => $companiesToInspect
                        );
                    }
                }
                return array(
                    "info" => "",
                    "code" => 200,
                    "data" => $companies
                );
            }
            
            if($request->type == 'customer') {
                return array(
                    "info" => "",
                    "code" => 200,
                    "data" => $managers
                );
            }
            
            if($request->type == 'cleaner') {
                foreach($cleanersInfo as $cleaner) {
                    $companies = Company::where('cleaner_id', $cleaner->id)->select('id', 'name')->get();
                    $cleaner->companies = $companies;
                }
                return array(
                    "info" => "",
                    "code" => 200,
                    "data" => $cleanersInfo
                );
            }
        }
        return array(
            "info" => "",
            "code" => 200,
            "data" => $supervisor
        );
    }
    
    public function store(Request $request) {
        
    }
    
    public function update(Request $request, $id) {
        
    }
    
    public function destroy(Request $request, $id) {
        
    }
}

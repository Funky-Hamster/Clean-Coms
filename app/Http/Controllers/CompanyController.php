<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use App\Note;
use App\User;
use Carbon\Carbon;
use App\Customer;
use App\Admin;
use App\CleaningCompany;


class CompanyController extends Controller
{
    public function index(Request $request) {
        if(isset($request->is_deleted)) {
            if($request->is_deleted == 1 & !isset($request->delete_type)) {
                $companies = Company::where('is_deleted', 1)->get();
                foreach($companies as $company) {
                    $company->operator = User::find($company->operator_id);
                    $company->name = $company->name . ' (company)';
                    $company->cleaning_company = CleaningCompany::find($company->cleaning_company_id);
                }
                return array(
                    "info" => "",
                    "code" => 200,
                    "data" => $companies
                );
            }
            
            if(isset($request->delete_type)) {
                if($request->is_deleted == 1 && $request->delete_type == 'lost_job') {
                    $companies = Company::where('is_deleted', 1)->where('delete_reason', '<>', 'No reason')->orderby('updated_at', 'desc')->get();
                    foreach($companies as $company) {
                        $company->operator = User::find($company->operator_id);
                    }
                    return array(
                        "info" => "",
                        "code" => 200,
                        "data" => $companies
                    );
                }
            }
        }
        $sortMethod = isset($request->inspection) ? $request->inspection : 'asc';
        $companies = Company::where('is_deleted', 0)->orderBy('inspection_time', $sortMethod)->get();
        foreach($companies as $company) {
            $cDate = Carbon::parse($company->inspection_time);
            if($company->inspection_time > Carbon::now()) {
                $company->days_to_inspection = $cDate->diffInDays();
            }
                            
            else {
                $company->days_to_inspection = -$cDate->diffInDays();
            }
            
            $company->cleaning_company = CleaningCompany::find($company->cleaning_company_id);
        }
                
        return array(
            "info" => "",
            "code" => 200,
            "data" => $companies
        );
    }
    
    public function show(Request $request, $id) {
        try {
            $company = Company::find($id);
            $customers = Customer::where('company_id', $id)->get();
            $companyManagers = [];
            // $adminIds = Admin::where('accountant', 1)->get();
            // $admins = [];
            // foreach($adminIds as $adminId) {
            //     $user = User::find($adminId->admin_id);
            //     if($user->is_deleted == 0) {
            //         array_push($admins, $user);
            //     }
            // }
            foreach($customers as $customer) {
                if($customer->customer_id != $company->accountant_id) {
                    $user = User::find($customer->customer_id);
                    if($user != null) {
                        if($user->is_deleted == 0) {
                            array_push($companyManagers, $user);
                        }
                    }
    
                }
            }
            $company->accountant = User::find($company->accountant_id);
            $company->supervisor = User::find($company->supervisor_id);
            $company->cleaner = User::find($company->cleaner_id);
            $company->managers = $companyManagers;
            // $members = [];
            // foreach($customers as $customer) {
            //     if($customer->customer_id != '100') {
            //         $user = User::find($customer->customer_id);
            //         if($user != null) {
            //             if($user->is_deleted == 0) {
            //                 array_push($members, User::find($customer->customer_id));
            //             }
            //         }
            //     }
            // }
            $members = $companyManagers;
            $accountant = User::find($company->accountant_id);
            
            if($accountant != null) {
                array_push($members, $accountant);
            }
            
            $company->members = $members;
            if(isset($request->user_id)) {
                if(User::find($request->user_id)->type == 'boss' || User::find($request->user_id)->type == 'admin') {
                    $notes = Note::where('company_id', $company->id)->orderby('updated_at', 'desc')->get();
                }
                else {
                    $notes = Note::where('company_id', $company->id)->where('supervisor_id', $request->user_id)->orderby('updated_at', 'desc')->get();
                    if(sizeof($notes) == 0) {
                        $notes = Note::where('company_id', $company->id)->where('cleaner_id', $request->user_id)->orderby('updated_at', 'desc')->get();
                    }
                }
            }
            else {
                $notes = [];
            }
    
    
            foreach($notes as $note) {
                $note->creator = User::find($note->creator_id);
            }
            $company->notes = $notes;
            $company->cleaning_company = CleaningCompany::find($company->cleaning_company_id);
            return array(
                "info" => "",
                "code" => 200,
                "data" => $company
            );
        } catch (\Exception $e) {
            return array(
                "info" => $e,
                "code" => 500,
            );
        }
    }
    
    public function store(Request $request)
    {
        try {
            $ret = Company::insert([
                'name' => $request->name,
                'phone' => isset($request->phone) ? $request->phone : '',
                'address' => isset($request->address) ? $request->address : '',
                'email' => isset($request->email) ? $request->email : '',
                'accountant_id' => isset($request->accountant_id) ? $request->accountant_id : 0,
                'supervisor_id' => isset($request->supervisor_id) ? $request->supervisor_id : 0,
                'cleaner_id' => isset($request->cleaner_id) ? $request->cleaner_id : 0,
                'admin_id' => isset($request->admin_id) ? $request->admin_id : 1,
                'cleaning_company_id' => isset($request->cleaning_company_id) ? $request->cleaning_company_id : 1,
                'inspection_time' => isset($request->inspection_time) ? $request->inspection_time : Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            return array(
                "info" => $e,
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
    
    public function update(Request $request, $id) {
        
        $company = Company::where('id', $id)->first();
        $company->name = isset($request->name) ? $request->name : $company->name;
        $company->phone = isset($request->phone) ? $request->phone : $company->phone;
        $company->address = isset($request->address) ? $request->address : $company->address;
        $company->email = isset($request->email) ? $request->email : $company->email;
        $company->accountant_id = isset($request->accountant_id) ? $request->accountant_id : $company->accountant_id;
        $company->supervisor_id = isset($request->supervisor_id) ? $request->supervisor_id : $company->supervisor_id;
        $company->cleaner_id = isset($request->cleaner_id) ? $request->cleaner_id : $company->cleaner_id;
        $company->admin_id = isset($request->admin_id) ? $request->admin_id : $company->admin_id;
        $company->inspection_time = isset($request->inspection_time) ? \DateTime::createFromFormat('d-M-Y', $request->inspection_time) : $company->inspection_time;
        $company->inspection = isset($request->inspection) ? $request->inspection : $company->inspection;
        $company->duration = isset($request->duration) ? $request->duration : $company->duration;
        $company->cleaning_company_id = isset($request->cleaning_company_id) ? $request->cleaning_company_id : $company->cleaning_company_id;
        $company->updated_at = Carbon::now();
        if(isset($request->note)) {
            Note::insert([
                "company_id" => $id,
                "content" => $request->note,
                'creator_id' => isset($request->creator_id) ? $request->creator_id : 1,
                'supervisor_id' => $company->supervisor_id,
                'cleaner_id' => $company->cleaner_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
        try {
            $company->save();
        } catch (\Exception $e) {
            return array(
                "info" => "Update failed",
                "code" => 500,
            );
        }
        return array(
            "info" => "",
            "code" => 200,
        );
    }
    
    public function destroy(Request $request, $id) {
        $company = Company::where('id', $id)->first();
        
        if(isset($request->type)) {
            if($request->type == 'shandechedimeile') {
                $company->delete();
                return array(
                    "info" => "",
                    "code" => 200,
                );
            }
        }
		if($company->is_deleted == 1) {
            return array(
                "info" => "already deleted",
                "code" => 500,
            );
		}
		
        if(isset($request->delete)) {
            if($request->delete == 'lost_job') {
                $company->delete_reason = 'lost_job';
                $company->updated_at = Carbon::now();
                $cpmpany->save();
                return array(
                    "info" => "",
                    "code" => 200,
                );
            }
        }
		
        if(!isset($request->user_id)) {
            return array(
                "info" => "Unautorized deleting",
                "code" => 500,
            );
        }

        $company->is_deleted = 1;
        $company->delete_reason = isset($request->delete_reason) ? $request->delete_reason : 'No reason';
        $company->updated_at = Carbon::now();
        $company->operator_id = $request->user_id;
        $company->save();
		Note::insert([
			'company_id' => $company->id,
			'content' => $company->name,
			'creator_id' => $request->user_id,
			'supervisor_id' => $company->supervisor_id,
			'cleaner_id' => $company->cleaner_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
		]);
        return array(
            "info" => "",
            "code" => 200,
        );
    }
}

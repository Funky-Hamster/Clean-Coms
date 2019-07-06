<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Company;
use App\Customer;
use App\Cleaner;
use App\Image;
use App\Comment;

class CustomerController extends Controller
{
    public function index(Request $request) {
		if(isset($request->is_deleted)) {
			if($request->is_deleted == 1) {
				$customers = User::where('is_deleted', 1)->where('type', 'customer')->get();
				foreach($customers as $customer) {
					$customer->operator = User::find($customer->operator_id);
				}
				return array(
					"info" => "",
					"code" => 200,
					"data" => $customers
				);
			}
		}

        $customers = User::where('type', 'customer')->where('is_deleted', 0)->get();
        foreach($customers as $customer) {
            $customer = Customer::where('customer_id', $customer->id)->first();
			if($customer != null) {
				$company = Company::find($customer->company_id);
				if($company != null) {
					$customer->company = $company;
				}
			}
            
        }
        return array(
            "info" => "",
            "code" => 200,
            "data" => $customers
        );
    }
    
    public function show(Request $request, $id) {
        $customer = User::where('id', $id)->where('type', 'customer')->where('is_deleted', 0)->first();
        if($customer == null){
            return array(
                "code" => 404,
                "info" => "No found"
            );
        }
        $companyId = Customer::where('customer_id', $customer->id)->first()->company_id;
        $customer->company = Company::find($companyId);
        return array(
            "info" => "",
            "code" => 200,
            "data" => $customer
        );
    }
    
    public function update(Request $request, $id) {
        
    }
    
    public function destroy(Request $request, $id) {
        return 204;
    }
    
    public function getCleanerByCustomerId(Request $request) {
        $userId = $request->user_id;
        $companyId = Customer::where('customer_id', $userId)->first()->company_id;
        $cleanerId = Company::find($companyId)->cleaner_id;
        $cleaner = User::where('id', $cleanerId)->where('type', 'cleaner')->where('is_deleted', 0)->first();
        if($cleaner == null){
            return array(
                "code" => 404,
                "info" => "No found"
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
}

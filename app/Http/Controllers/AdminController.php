<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Admin;
use App\Company;

class AdminController extends Controller
{
    public function index(Request $request) {
        $admins = User::where('type', 'admin')->where('is_deleted', 0)->get();
        foreach ($admins as $admin) {
            $adminId = $admin->id;
            $accountantData = Admin::where('admin_id', $adminId)->first();
            $admin->accountant = isset($accountantData->accountant) ? $accountantData->accountant : '0';
        }

        if (isset($request->type) && $request->type == 'accountant') {
            $accountants = [];
            foreach ($admins as $admin) {
                if ($admin->accountant == '1') {
                    array_push($accountants, $admin);
                }
            }
            return array(
                "info" => "",
                "code" => 200,
                "data" => $accountants
            );
        }
        
        if (isset($request->type) && $request->type == 'normal') {
            $normalAdmins = [];
            foreach ($admins as $admin) {
                if ($admin->accountant == '0') {
                    array_push($normalAdmins, $admin);
                }
            }
            return array(
                "info" => "",
                "code" => 200,
                "data" => $normalAdmins
            );
        }
        
        return array(
            "info" => "",
            "code" => 200,
            "data" => $admins
        );
    }
    
    public function show(Request $request, $id) {
        $admin = User::where('id', $id)->where('type', 'admin')->where('is_deleted', 0)->first();
        $admin->accountant = Admin::where('admin_id', $id)->first()->accountant;
        if(sizeof($admin) == 0){
            return array(
                "code" => 404,
                "info" => "No found"
            );
        }
        $companies = Company::where('admin_id', $id)->get();
        if(sizeof($companies) > 0) {
            $admin->companies = $companies;
        }
        if(sizeof($companies) == 0) {
            $admin->companies = [];
        }
        return array(
            "info" => "",
            "code" => 200,
            "data" => $admin
        );
    }
    
    public function update(Request $request, $id) {
        
    }
    
    public function destroy(Request $request, $id) {
        return 204;
    }
}

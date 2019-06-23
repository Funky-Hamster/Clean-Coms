<?php

namespace App\Http\Controllers;
use App\Admin;
use App\AppVariable;
use App\Cleaner;
use App\Comment;
use App\Company;
use App\Customer;
use App\Group;
use App\GroupMember;
use App\History;
use App\Image;
use App\Inspection;
use App\InspectionComment;
use App\Job;
use App\Note;
use App\Sale;
use App\SalesProfit;
use App\User;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Excel;

class ExcelController extends Controller
{
    public function index(Request $request) {
        if($request->type == 'user') {
            $users = User::all();
            
            Excel::create("User Table",function ($excel) use ($users){
                $excel->sheet('users',function ($sheet) use ($users) {
                    $columns = Schema::getColumnListing('users');
                    $sheet->appendRow($columns);
                    foreach($users as $user) {
                        $userRow = array();
                        foreach($columns as $column) {
                            array_push($userRow, $user[$column]);
                        }
                        $sheet->appendRow($userRow);
                    }
                    
                });
            })->export('xls');
        }
        
        if($request->type == 'company') {
            $companies = Company::all();
            
            Excel::create("Company Table",function ($excel) use ($companies){
                $excel->sheet('companies',function ($sheet) use ($companies) {
                    $columns = Schema::getColumnListing('companies');
                    $sheet->appendRow($columns);
                    foreach($companies as $company) {
                        $companyRow = array();
                        foreach($columns as $column) {
                            array_push($companyRow, $company[$column]);
                        }
                        $sheet->appendRow($companyRow);
                    }
                    
                });
            })->export('xls');
        }
        
        // Print all
        
        $admins = Admin::all();
        $appVariables = AppVariable::all();
        $cleaners = Cleaner::all();
        $comments = Comment::all();
        $companies = Company::all();
        $customers = Customer::all();
        $groups = Group::all();
        $groupMembers = GroupMember::all();
        $histories = History::all();
        $images = Image::all();
        $inspections = Inspection::all();
        $inspectionComments = InspectionComment::all();
        $jobs = Job::all();
        $notes = Note::all();
        $sales = Sale::all();
        $salesProfits = SalesProfit::all();
        $users = User::all();
        $lists = array();
        $names = array();
        array_push($lists, $admins);
        array_push($lists, $appVariables);
        array_push($lists, $cleaners);
        array_push($lists, $comments);
        array_push($lists, $companies);
        array_push($lists, $customers);
        array_push($lists, $groups);
        array_push($lists, $groupMembers);
        array_push($lists, $histories);
        array_push($lists, $images);
        array_push($lists, $inspections);
        array_push($lists, $inspectionComments);
        array_push($lists, $jobs);
        array_push($lists, $notes);
        array_push($lists, $sales);
        array_push($lists, $salesProfits);
        array_push($lists, $users);
        
        array_push($names, 'admins');
        array_push($names, 'app_variables');
        array_push($names, 'cleaners');
        array_push($names, 'comments');
        array_push($names, 'companies');
        array_push($names, 'customers');
        array_push($names, 'groups');
        array_push($names, 'group_members');
        array_push($names, 'histories');
        array_push($names, 'images');
        array_push($names, 'inspections');
        array_push($names, 'inspection_comments');
        array_push($names, 'jobs');
        array_push($names, 'notes');
        array_push($names, 'sales');
        array_push($names, 'salesProfits');
        array_push($names, 'users');
        $trasferringData = array();
        array_push($trasferringData, $lists);
        array_push($trasferringData, $names);
        
        Excel::create("Database",function ($excel) use ($trasferringData){
            $lists = $trasferringData[0];
            $names = $trasferringData[1];
            for($i = 0; $i < sizeof($lists); $i ++) {
                $dataAndNames = array();
                array_push($dataAndNames, $lists[$i]);
                array_push($dataAndNames, $names[$i]);
                $excel->sheet($names[$i], function ($sheet) use ($dataAndNames) {
                    $lists = $dataAndNames[0];
                    $name = $dataAndNames[1];
                    $columns = Schema::getColumnListing($name);
                    $sheet->appendRow($columns);
                    foreach($lists as $list) {
                        $rows = array();
                        foreach($columns as $column) {
                            array_push($rows, $list[$column]);
                        }
                        $sheet->appendRow($rows);
                    }
                });
            }
        })->export('xls');
    }
}
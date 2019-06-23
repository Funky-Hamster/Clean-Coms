<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CleaningCompany;

class CleaningCompanyController extends Controller
{
    public function index(Request $request) {
        try {
            $cleaningCompanies = CleaningCompany::all();
        } catch (\Exception $e) {
            return array(
                "info" => $e,
                "code" => 500,
            );
        }
        
        return array(
            "info" => "",
            "code" => 200,
            "data" => $cleaningCompanies
        );
    }
    
    public function show(Request $request, $id) {
        try {
            $cleaningCompany = CleaningCompany::find($id);
        } catch (\Exception $e) {
            return array(
                "info" => $e,
                "code" => 500,
            );
        }
        
        return array(
            "info" => "",
            "code" => 200,
            "data" => $cleaningCompany
        );
    }
    
    public function update(Request $request, $id) {
        
    }
    
    public function destroy(Request $request, $id) {
        return 204;
    }
}

<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\SalesProfit;
use App\Sale;
use App\Job;

class SalesController extends Controller
{
    public function index(Request $request) {
        $saleses = User::where('type', 'sales')->where('is_deleted', 0)->get();
        return array(
            "info" => "",
            "code" => 200,
            "data" => $saleses
        );
    }
    
    public function show(Request $request, $id) {
        // $sales = User::where('username', $id)->first();
        $saleIdList = SalesProfit::where('sales_id', $id)->orderby('updated_at', 'desc')->get();
        $saleList = [];
        foreach($saleIdList as $saleId) {
            array_push($saleList, $saleId->sale_id);
        }
        $months = Sale::where('approval', 1)->whereIn('id', $saleList)->distinct('month')->orderby('month', 'desc')->select('month')->get();
        foreach($months as $month) {
            $saleList = Sale::where('month', $month->month)->where('approval', 1)->orderby('created_at', 'desc')->get();
            $totalAmount = 0;
            $saleArray = array();
            $profitList = [];
            
            foreach($saleList as $sale) {
                if(SalesProfit::where('sales_id', $id)->where('sale_id', $sale->id)->count() > 0) {
                    $salesProfit = SalesProfit::where('sales_id', $id)->where('sale_id', $sale->id)->first();
                    $saleArray['amount'] = $salesProfit->amount;
                    $saleArray['date'] = $salesProfit->created_at->format('d-M-Y');
                    $totalAmount += $saleArray['amount'];
                    $job = Job::find($sale->job_id);
                    $saleArray['job'] = $job;
                    $saleArray['delete_date'] = $job->created_at->format('d-M-Y');
                    $saleArray['payment'] = $sale->payment;
                    array_push($profitList, $saleArray);
                }
            }
            $month->total_amount = sprintf("%.2f",$totalAmount);
            $month->profits = $profitList;
        }
        return array(
            "info" => "",
            "code" => 200,
            "data" => $months
        );
    }
    
    public function update(Request $request, $id) {
        if(isset($request->job_id)) {
            $sale = Sale::where('job_id', $request->job_id)->first();
        }
        else {
            $sale = Sale::find($id);
        }
        $sale->payment = 1;
        return array(
            "info" => "",
            "code" => 200
        );
    }
    
}

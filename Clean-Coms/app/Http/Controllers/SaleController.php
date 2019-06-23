<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sale;
use App\Job;
use App\User;
use App\SalesProfit;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index(Request $request) {
        // $months = Sale::where('approval', 1)->distinct('month')->orderby('month', 'desc')->select('month')->get();
        // foreach($months as $month) {
        //     $sales = Sale::where('month', $month->month)->where('approval', 1)->orderby('created_at', 'asc')->get();
        //     $month->jobs = $sales;
        //     $totalAmount = 0;
        //     foreach($sales as $sale) {
        //         $totalAmount += $sale->amount;
        //         $sale->job = Job::find($sale->job_id);
        //     }
        //     $month->total_amount = $totalAmount;
        // }
        $jobs = Job::where('pending', 2)->orderby('updated_at', 'desc')->get();
        $saleList = [];
        foreach($jobs as $job) {
            $sale = Sale::where('job_id', $job->id)->first();
            $salesProfits = SalesProfit::where('sale_id', $sale->id)->get();
            foreach($salesProfits as $salesProfit) {
                $salesProfit->sales = User::where('username', $salesProfit->sales_id)->first();
            }
            $job->sales_profits = $salesProfits;
            $job->payment = $sale->payment;
            $job->delete_date = $job->created_at->format('d-M-Y');
            array_push($saleList, Sale::where('job_id', $job->id)->first());
        }
        
        
        return array(
            "code" => 200,
            "info" => "",
            "data" => $jobs
        );
    }
    
    public function show(Request $request, $id) {
        $sale = Sale::find($id);
        $job = Job::find($sale->job_id);
        $sale->job = $job;
        $saleses = SalesProfit::where('sale_id', $id)->orderby('updated_at', 'desc')->get();
        foreach($saleses as $sales) {
            $sales->user = User::where('username', $sales->sales_id)->first();
        }
        $sale->saleses = $saleses;
        $sale->delete_date = $job->created_at->format('d-M-Y');
        return array(
            "code" => 200,
            "info" => "",
            "data" => $sale
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
        $sale->updated_at = Carbon::now();
        $sale->save();
        return array(
            "info" => "",
            "code" => 200
        );
    }
}

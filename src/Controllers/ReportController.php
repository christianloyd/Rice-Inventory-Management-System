<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Services\ReportService;
use App\Services\DailyStockSummaryService;

class ReportController
{
    private ReportService $reportService;
    private DailyStockSummaryService $dailyStockSummaryService;

    public function __construct()
    {
        $this->reportService = new ReportService();
        $this->dailyStockSummaryService = new DailyStockSummaryService();
    }

    public function index(Request $request): Response
    {
        ['start_date' => $startDate, 'end_date' => $endDate, 'filter_type' => $filterType] = $this->parseDateRange($request);

        $salesSummary = $this->reportService->getSalesSummary($startDate, $endDate);
        $comprehensive = $this->reportService->getComprehensiveReport($startDate, $endDate);

        $totalRevenue = array_sum(array_column($salesSummary, 'total_revenue'));
        $totalTransactions = array_sum(array_column($salesSummary, 'total_transactions'));

        $dailyCoins = $this->reportService->getCoins($startDate, $endDate);
        $totalCoins = $this->reportService->getTotalCoins($startDate, $endDate);

        $globalDiscountAll = $this->reportService->getTotalGlobalDiscounts($startDate, $endDate, 'all');

        $todayChequeSales = $this->reportService->getVariantSales($startDate, $endDate, 'cheque');
        $totalChequeGross = array_sum(array_column($todayChequeSales, 'total_revenue'));
        $globalDiscountCheque = $this->reportService->getTotalGlobalDiscounts($startDate, $endDate, 'cheque');
        $totalChequeRevenue = $totalChequeGross - $globalDiscountCheque;

        $expenses = $this->reportService->getExpenses($startDate, $endDate);
        $totalExpenses = array_sum(array_column($expenses, 'amount'));

        $netTotalDay = $totalRevenue + $totalCoins - $totalExpenses;
        $cashInStoreTotal = $netTotalDay; // User considers Cheque to be part of the total cash/net earnings minus expenses

        return View::render('reports/index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
            'salesSummary' => $salesSummary,
            'comprehensive' => $comprehensive,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'dailyCoins' => $dailyCoins,
            'totalCoins' => $totalCoins,
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'netTotalDay' => $netTotalDay,
            'cashInStoreTotal' => $cashInStoreTotal,
            'globalDiscountAll' => $globalDiscountAll
        ]);
    }

    public function deliveries(Request $request): Response
    {
        ['start_date' => $startDate, 'end_date' => $endDate, 'filter_type' => $filterType] = $this->parseDateRange($request);

        $deliveryReport = $this->reportService->getDeliveryReport($startDate, $endDate);
        
        $totalRevenue = array_sum(array_column($deliveryReport, 'total_revenue'));
        $totalDeliveries = array_sum(array_column($deliveryReport, 'total_quantity_delivered'));

        return View::render('reports/deliveries', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
            'deliveryReport' => $deliveryReport,
            'totalRevenue' => $totalRevenue,
            'totalDeliveries' => $totalDeliveries
        ]);
    }

    public function utang(Request $request): Response
    {
        ['start_date' => $startDate, 'end_date' => $endDate, 'filter_type' => $filterType] = $this->parseDateRange($request);

        $utangReport = $this->reportService->getUtangReport($startDate, $endDate);
        
        $totalUtangAmount = array_sum(array_column($utangReport, 'total_amount'));
        $totalUtangQuantity = array_sum(array_column($utangReport, 'total_quantity'));

        return View::render('reports/utang_print', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
            'utangReport' => $utangReport,
            'totalUtangAmount' => $totalUtangAmount,
            'totalUtangQuantity' => $totalUtangQuantity
        ]);
    }

    public function printDeliveries(Request $request): Response
    {
        ['start_date' => $startDate, 'end_date' => $endDate, 'filter_type' => $filterType] = $this->parseDateRange($request);

        $deliveryReport = $this->reportService->getDeliveryReport($startDate, $endDate);
        
        $totalRevenue = array_sum(array_column($deliveryReport, 'total_revenue'));
        $totalDeliveries = array_sum(array_column($deliveryReport, 'total_quantity_delivered'));

        return View::render('reports/print_deliveries', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
            'deliveryReport' => $deliveryReport,
            'totalRevenue' => $totalRevenue,
            'totalDeliveries' => $totalDeliveries
        ]);
    }

    private function parseDateRange(Request $request): array
    {
        $filterType = $request->input('filter_type', 'daily');

        if ($filterType === 'daily') {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        } elseif ($filterType === 'weekly') {
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($filterType === 'monthly') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        } else {
            // Custom
            $startDate = $request->input('start_date', date('Y-m-d'));
            $endDate = $request->input('end_date', date('Y-m-d'));
        }

        return ['start_date' => $startDate, 'end_date' => $endDate, 'filter_type' => $filterType];
    }

    public function print(Request $request): Response
    {
        ['start_date' => $startDate, 'end_date' => $endDate, 'filter_type' => $filterType] = $this->parseDateRange($request);

        $salesSummary = $this->reportService->getSalesSummary($startDate, $endDate);
        $comprehensive = $this->reportService->getComprehensiveReport($startDate, $endDate);

        $todayVariantSales = $this->reportService->getVariantSales($startDate, $endDate, 'cash');
        $todayTotalGrossRevenue = array_sum(array_column($todayVariantSales, 'total_revenue'));
        $todayTotalQuantity = array_sum(array_column($todayVariantSales, 'total_quantity_sold'));
        $globalDiscountCash = $this->reportService->getTotalGlobalDiscounts($startDate, $endDate, 'cash');
        $todayTotalRevenue = $todayTotalGrossRevenue - $globalDiscountCash;

        $todayChequeSales = $this->reportService->getVariantSales($startDate, $endDate, 'cheque');
        $todayTotalChequeGross = array_sum(array_column($todayChequeSales, 'total_revenue'));
        $todayTotalChequeQuantity = array_sum(array_column($todayChequeSales, 'total_quantity_sold'));
        $globalDiscountCheque = $this->reportService->getTotalGlobalDiscounts($startDate, $endDate, 'cheque');
        $todayTotalChequeRevenue = $todayTotalChequeGross - $globalDiscountCheque;

        $todayExpenses = $this->reportService->getExpenses($startDate, $endDate);
        $todayTotalExpenses = array_sum(array_column($todayExpenses, 'amount'));

        $todayCoins = $this->reportService->getCoins($startDate, $endDate);
        $todayTotalCoins = $this->reportService->getTotalCoins($startDate, $endDate);

        $totalRevenue = array_sum(array_column($salesSummary, 'total_revenue'));
        $totalTransactions = array_sum(array_column($salesSummary, 'total_transactions'));

        return View::render('reports/print', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'comprehensive' => $comprehensive,
            'todayVariantSales' => $todayVariantSales,
            'todayTotalRevenue' => $todayTotalRevenue,
            'todayTotalQuantity' => $todayTotalQuantity,
            'todayChequeSales' => $todayChequeSales,
            'todayTotalChequeRevenue' => $todayTotalChequeRevenue,
            'todayTotalChequeQuantity' => $todayTotalChequeQuantity,
            'todayExpenses' => $todayExpenses,
            'todayTotalExpenses' => $todayTotalExpenses,
            'todayCoins' => $todayCoins,
            'todayTotalCoins' => $todayTotalCoins,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'todayTotalGrossRevenue' => $todayTotalGrossRevenue,
            'globalDiscountCash' => $globalDiscountCash,
            'todayTotalChequeGross' => $todayTotalChequeGross,
            'globalDiscountCheque' => $globalDiscountCheque,
            'globalDiscountAll' => $this->reportService->getTotalGlobalDiscounts($startDate, $endDate, 'all')
        ]);
    }

    public function printComprehensive(Request $request): Response
    {
        ['start_date' => $startDate, 'end_date' => $endDate, 'filter_type' => $filterType] = $this->parseDateRange($request);
        $comprehensive = $this->reportService->getComprehensiveReport($startDate, $endDate);
        $globalDiscountAll = $this->reportService->getTotalGlobalDiscounts($startDate, $endDate, 'all');

        return View::render('reports/print_comprehensive', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'comprehensive' => $comprehensive,
            'globalDiscountAll' => $globalDiscountAll
        ]);
    }

    public function dailyStockSummary(Request $request): Response
    {
        $date = $request->input('date', date('Y-m-d'));
        $summary = $this->dailyStockSummaryService->forDate($date);

        return View::render('reports/daily-stock-summary', [
            'date' => $date,
            'summary' => $summary
        ]);
    }

    public function storeCoins(Request $request): Response
    {
        $amount = (float) $request->input('amount', 0);
        $coinDate = $request->input('coin_date', date('Y-m-d'));
        $notes = trim($request->input('notes', 'Coins for change'));

        if ($amount <= 0) {
            Response::setFlash('error', 'Please enter a valid coin amount greater than 0.');
            return $this->redirectWithParams($request);
        }

        $userId = $_SESSION['user_id'] ?? null;
        $this->reportService->addCoin($coinDate, $amount, $notes, $userId);

        Response::setFlash('success', 'Coins of ₱' . number_format($amount, 2) . ' recorded successfully.');
        return $this->redirectWithParams($request);
    }

    public function deleteCoins(Request $request, int $id): Response
    {
        $this->reportService->deleteCoin($id);
        Response::setFlash('success', 'Coin record deleted successfully.');
        return $this->redirectWithParams($request);
    }

    private function redirectWithParams(Request $request): Response
    {
        $params = [];
        if ($filterType = $request->input('redirect_filter_type')) {
            $params['filter_type'] = $filterType;
        }
        if ($startDate = $request->input('redirect_start_date')) {
            $params['start_date'] = $startDate;
        }
        if ($endDate = $request->input('redirect_end_date')) {
            $params['end_date'] = $endDate;
        }
        $query = !empty($params) ? '?' . http_build_query($params) : '';
        return Response::redirect(url('/reports' . $query));
    }
}

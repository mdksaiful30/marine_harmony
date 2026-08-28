<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Setting;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $totalDeposits = FinanceService::totalApprovedDeposits();
        $totalIncome = FinanceService::totalApprovedIncome();
        $totalExpenses = FinanceService::totalApprovedExpenses();
        $totalInvestments = FinanceService::totalApprovedInvestments();
        $officialBankBalance = FinanceService::officialBankBalance();
        $netFund = FinanceService::netFund();

        $pendingCount = FinanceService::pendingApprovalsCount();
        $pendingTotal = FinanceService::pendingApprovalsTotal();

        // Bank Reconciliation (Admin)
        $actualBankBalance = (float) Setting::get('actual_bank_balance', $officialBankBalance);
        $diff = $officialBankBalance - $actualBankBalance;
        $isMatched = abs($diff) < 0.01;

        // Recent Transactions
        $depositQuery = Deposit::query();
        if (! $isAdmin) {
            $depositQuery->where('status', 'Approved');
        }
        $recentDeposits = $depositQuery->orderBy('date', 'desc')->take(8)->get();

        return view('dashboard', compact(
            'isAdmin',
            'totalDeposits',
            'totalIncome',
            'totalExpenses',
            'totalInvestments',
            'officialBankBalance',
            'netFund',
            'pendingCount',
            'pendingTotal',
            'actualBankBalance',
            'diff',
            'isMatched',
            'recentDeposits'
        ));
    }

    public function updateActualBalance(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->isAdmin()) {
            return back()->with('error', 'Only Admin can update bank balance verification.');
        }

        $request->validate([
            'actual_bank_balance' => 'required|numeric',
        ]);

        Setting::set('actual_bank_balance', (string) $request->input('actual_bank_balance'));

        return back()->with('success', 'Actual bank balance saved successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Investment;
use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $type = $request->input('type', 'Ledger');
        $selectedMember = $request->input('member', '');
        $fromDate = $request->input('from_date', '');
        $toDate = $request->input('to_date', '');
        $search = $request->input('search', '');

        $members = User::orderBy('id')->get();
        $allMonths = FinanceService::getMonthsList('2024-01', FinanceService::getTargetDueMonth());

        $transactions = [];
        $ledgerData = null;
        $allMembersLedger = [];

        if ($type === 'Ledger') {
            if (! empty($selectedMember)) {
                // Individual member ledger
                $targetMember = User::where('name', $selectedMember)->first();
                $memberName = $targetMember ? $targetMember->name : $selectedMember;
                $summary = FinanceService::getMemberLedgerSummary($memberName);

                // Build monthly detail list
                $deposits = Deposit::where('member', $memberName)
                    ->where('status', 'Approved')
                    ->orderBy('date', 'asc')
                    ->get();

                $depositMap = [];
                foreach ($deposits as $d) {
                    foreach ($d->periods_list as $p) {
                        $depositMap[$p] = $d;
                    }
                }

                $monthlyBreakdown = [];
                foreach ($allMonths as $m) {
                    $dep = $depositMap[$m] ?? null;
                    $isPaid = ! empty($dep);
                    $monthlyBreakdown[] = [
                        'period' => $m,
                        'label' => FinanceService::monthLabel($m),
                        'status' => $isPaid ? 'Paid' : 'Due',
                        'date' => $dep && $dep->date ? $dep->date->format('Y-m-d') : ($isPaid ? 'Historical' : '-'),
                        'method' => $dep ? $dep->method : '-',
                        'amount' => $isPaid ? FinanceService::MONTHLY_INSTALLMENT : 0,
                    ];
                }

                $ledgerData = [
                    'member' => $targetMember,
                    'memberName' => $memberName,
                    'summary' => $summary,
                    'breakdown' => $monthlyBreakdown,
                    'deposits' => $deposits,
                ];
            } else {
                // All members summary ledger
                foreach ($members as $m) {
                    $allMembersLedger[] = [
                        'user' => $m,
                        'summary' => FinanceService::getMemberLedgerSummary($m->name),
                    ];
                }
            }
        } else {
            // General transactions report
            $queryDeposits = Deposit::query();
            $queryIncome = Income::query();
            $queryExpenses = Expense::query();
            $queryInvestments = Investment::query();

            if (! $isAdmin) {
                $queryDeposits->where('status', 'Approved');
                $queryIncome->where('status', 'Approved');
                $queryExpenses->where('status', 'Approved');
                $queryInvestments->where('status', 'Approved');
            }

            if (! empty($selectedMember)) {
                $queryDeposits->where('member', $selectedMember);
                $queryIncome->where('submitted_by', $selectedMember);
                $queryExpenses->where('submitted_by', $selectedMember);
                $queryInvestments->where('submitted_by', $selectedMember);
            }

            if (! empty($fromDate)) {
                $queryDeposits->where('date', '>=', $fromDate);
                $queryIncome->where('date', '>=', $fromDate);
                $queryExpenses->where('date', '>=', $fromDate);
                $queryInvestments->where('date', '>=', $fromDate);
            }

            if (! empty($toDate)) {
                $queryDeposits->where('date', '<=', $toDate);
                $queryIncome->where('date', '<=', $toDate);
                $queryExpenses->where('date', '<=', $toDate);
                $queryInvestments->where('date', '<=', $toDate);
            }

            $list = collect();

            if ($type === '' || $type === 'All' || $type === 'Deposit') {
                $list = $list->concat($queryDeposits->get()->map(fn ($r) => [
                    'type' => 'Deposit',
                    'date' => $r->date ? $r->date->format('Y-m-d') : ($r->period ?: '-'),
                    'member' => $r->member ?: '-',
                    'source' => $r->method ?: '-',
                    'category' => '-',
                    'amount' => $r->amount,
                    'details' => ($r->period ? 'Period: '.$r->period.' | ' : '').($r->remarks ?: '-'),
                    'historical' => $r->historical ? ($r->historical_type ?: 'Yes') : 'No',
                    'status' => $r->status,
                ]));
            }

            if ($type === '' || $type === 'All' || $type === 'Income') {
                $list = $list->concat($queryIncome->get()->map(fn ($r) => [
                    'type' => 'Income',
                    'date' => $r->date ? $r->date->format('Y-m-d') : '-',
                    'member' => $r->submitted_by ?: '-',
                    'source' => $r->source ?: '-',
                    'category' => '-',
                    'amount' => $r->amount,
                    'details' => ($r->purpose ? $r->purpose.' | ' : '').($r->details ?: '-'),
                    'historical' => $r->historical ? ($r->historical_type ?: 'Yes') : 'No',
                    'status' => $r->status,
                ]));
            }

            if ($type === '' || $type === 'All' || $type === 'Expense') {
                $list = $list->concat($queryExpenses->get()->map(fn ($r) => [
                    'type' => 'Expense',
                    'date' => $r->date ? $r->date->format('Y-m-d') : '-',
                    'member' => $r->submitted_by ?: '-',
                    'source' => '-',
                    'category' => $r->category ?: '-',
                    'amount' => $r->amount,
                    'details' => ($r->description ? $r->description.' | ' : '').($r->details ?: '-'),
                    'historical' => $r->historical ? ($r->historical_type ?: 'Yes') : 'No',
                    'status' => $r->status,
                ]));
            }

            if ($type === '' || $type === 'All' || $type === 'Investment') {
                $list = $list->concat($queryInvestments->get()->map(fn ($r) => [
                    'type' => 'Investment',
                    'date' => $r->date ? $r->date->format('Y-m-d') : '-',
                    'member' => $r->submitted_by ?: '-',
                    'source' => $r->institution ?: '-',
                    'category' => '-',
                    'amount' => $r->amount,
                    'details' => ($r->purpose ? $r->purpose.' | ' : '').($r->details ?: '-'),
                    'historical' => $r->historical ? 'Yes' : 'No',
                    'status' => $r->status,
                ]));
            }

            if (! empty($search)) {
                $q = strtolower($search);
                $list = $list->filter(function ($item) use ($q) {
                    return str_contains(strtolower($item['member']), $q)
                        || str_contains(strtolower($item['source']), $q)
                        || str_contains(strtolower($item['category']), $q)
                        || str_contains(strtolower($item['details']), $q)
                        || str_contains(strtolower($item['type']), $q);
                });
            }

            $transactions = $list->sortByDesc('date')->values();
        }

        return view('reports.index', compact(
            'isAdmin',
            'type',
            'selectedMember',
            'fromDate',
            'toDate',
            'search',
            'members',
            'transactions',
            'ledgerData',
            'allMembersLedger'
        ));
    }
}

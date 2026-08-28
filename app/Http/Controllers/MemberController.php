<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $members = User::orderBy('id')->get();
        $memberSummaries = [];

        foreach ($members as $m) {
            $memberSummaries[] = [
                'user' => $m,
                'summary' => FinanceService::getMemberLedgerSummary($m->name),
            ];
        }

        return view('members.index', compact('isAdmin', 'members', 'memberSummaries'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $member = User::findOrFail($id);
        $summary = FinanceService::getMemberLedgerSummary($member->name);

        $deposits = Deposit::where('member', $member->name)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $monthsList = FinanceService::getMonthsList(FinanceService::START_MONTH, now()->addMonths(1)->format('Y-m'));

        return view('members.show', compact('member', 'summary', 'deposits', 'monthsList', 'isAdmin'));
    }
}

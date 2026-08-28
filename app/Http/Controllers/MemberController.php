<?php

namespace App\Http\Controllers;

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
}

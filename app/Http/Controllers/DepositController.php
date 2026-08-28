<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        // Deposits List
        $query = Deposit::query();
        if (! $isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('status', 'Approved')
                    ->orWhere('submitted_by', $user->name)
                    ->orWhere('member', $user->name);
            });
        }
        $deposits = $query->orderBy('date', 'desc')->get();

        // Members and Status
        $members = User::orderBy('id')->get();
        $memberSummaries = [];
        foreach ($members as $m) {
            $memberSummaries[] = FinanceService::getMemberLedgerSummary($m->name);
        }

        // Installment Months Range 2024-01 to 2026-12
        $allMonths = FinanceService::getMonthsList('2024-01', '2026-12');

        // Precalculated approved and pending months for current user/selected member
        $currentMemberName = $isAdmin ? ($members->first()->name ?? $user->name) : $user->name;
        $approvedMonths = FinanceService::getApprovedPeriodsForMember($currentMemberName);
        $pendingMonths = FinanceService::getPendingPeriodsForMember($currentMemberName);

        return view('deposits.index', compact(
            'isAdmin',
            'deposits',
            'members',
            'memberSummaries',
            'allMonths',
            'currentMemberName',
            'approvedMonths',
            'pendingMonths'
        ));
    }

    public function getMemberMonths(string $memberName)
    {
        $approved = FinanceService::getApprovedPeriodsForMember($memberName);
        $pending = FinanceService::getPendingPeriodsForMember($memberName);
        $allMonths = FinanceService::getMonthsList('2024-01', '2026-12');

        $options = [];
        foreach ($allMonths as $m) {
            $label = FinanceService::monthLabel($m);
            $isApproved = ! empty($approved[$m]);
            $isPending = ! empty($pending[$m]);

            $options[] = [
                'period' => $m,
                'label' => $label,
                'is_approved' => $isApproved,
                'is_pending' => $isPending,
                'disabled' => $isApproved || $isPending,
                'text' => $label.($isApproved ? ' (Paid)' : ($isPending ? ' (Approval Pending)' : '')),
            ];
        }

        $summary = FinanceService::getMemberLedgerSummary($memberName);

        return response()->json([
            'options' => $options,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $request->validate([
            'date' => 'required|date',
            'member' => 'required|string',
            'installment_months' => 'required|array|min:1',
            'method' => 'required|in:Bank,Mobile Banking,Cash',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120',
        ]);

        $member = $isAdmin ? $request->input('member') : $user->name;
        $selectedMonths = $request->input('installment_months');
        $monthCount = count($selectedMonths);
        $amount = $monthCount * FinanceService::MONTHLY_INSTALLMENT;

        // Attachment handling
        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $path = $file->store('attachments/deposits', 'public');
            $attachmentPath = '/storage/'.$path;
        }

        $depositId = 'DEP-'.strtoupper(Str::random(4)).'-'.date('YmdHis');

        $deposit = new Deposit;
        $deposit->id = $depositId;
        $deposit->member = $member;
        $deposit->date = $request->input('date');
        $deposit->period = implode(', ', $selectedMonths);
        $deposit->method = $request->input('method');
        $deposit->amount = $amount;

        // Bank fields
        if ($request->input('method') === 'Bank') {
            $deposit->bank_name = $request->input('bank_name');
            $deposit->branch = $request->input('branch');
            $deposit->bank_ref = $request->input('bank_ref');
            $deposit->tx_type = $request->input('tx_type');
        }

        // Mobile Banking fields
        if ($request->input('method') === 'Mobile Banking') {
            $deposit->mobile_wallet = $request->input('mobile_wallet');
            $deposit->mobile_number = $request->input('mobile_number');
            $deposit->mobile_ref = $request->input('mobile_ref');
        }

        // Cash fields
        if ($request->input('method') === 'Cash') {
            $deposit->receiver_name = $request->input('receiver_name');
            $deposit->cash_location = $request->input('cash_location');
        }

        $deposit->special = $request->input('special', 'No');
        $deposit->remarks = $request->input('remarks');
        $deposit->attachment_path = $attachmentPath;
        $deposit->attachment_name = $attachmentName;
        $deposit->submitted_by = $user->name;

        if ($isAdmin) {
            $deposit->status = 'Approved';
            $deposit->approved_by = $user->name;
            $deposit->approval_date = now()->format('Y-m-d');
        } else {
            $deposit->status = 'Pending';
        }

        $deposit->save();

        $msg = $isAdmin
            ? 'Deposit of '.FinanceService::formatMoney($amount).' recorded and approved successfully.'
            : 'Deposit of '.FinanceService::formatMoney($amount).' submitted successfully. It is Pending Admin approval.';

        return redirect()->route('deposits.index')->with('success', $msg);
    }

    public function destroy(string $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->isAdmin()) {
            return back()->with('error', 'Only Admin can delete records.');
        }

        $deposit = Deposit::findOrFail($id);
        $deposit->delete();

        return back()->with('success', 'Deposit record deleted successfully.');
    }
}

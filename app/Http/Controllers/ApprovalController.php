<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user || ! $user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Only Admin can access the approval queue.');
        }

        $pending = collect();

        // Pending Deposits
        $deposits = Deposit::where('status', 'Pending')->orderBy('date', 'desc')->get();
        foreach ($deposits as $dep) {
            $pending->push([
                'id' => $dep->id,
                'type' => 'Deposit',
                'date' => $dep->date ? $dep->date->format('Y-m-d') : ($dep->period ?: '-'),
                'submitted_by' => $dep->submitted_by ?: $dep->member,
                'label' => $dep->member,
                'amount' => $dep->amount,
                'details' => 'Period: '.($dep->period ?: '-').' | Method: '.$dep->method.($dep->remarks ? ' | '.$dep->remarks : ''),
                'attachment_path' => $dep->attachment_path,
            ]);
        }

        // Pending Incomes
        $incomes = Income::where('status', 'Pending')->orderBy('date', 'desc')->get();
        foreach ($incomes as $inc) {
            $pending->push([
                'id' => $inc->id,
                'type' => 'Income',
                'date' => $inc->date ? $inc->date->format('Y-m-d') : '-',
                'submitted_by' => $inc->submitted_by ?: '-',
                'label' => $inc->source ?: 'Income',
                'amount' => $inc->amount,
                'details' => ($inc->purpose ? $inc->purpose.' - ' : '').($inc->details ?: '-'),
                'attachment_path' => null,
            ]);
        }

        // Pending Expenses
        $expenses = Expense::where('status', 'Pending')->orderBy('date', 'desc')->get();
        foreach ($expenses as $exp) {
            $pending->push([
                'id' => $exp->id,
                'type' => 'Expense',
                'date' => $exp->date ? $exp->date->format('Y-m-d') : '-',
                'submitted_by' => $exp->submitted_by ?: '-',
                'label' => $exp->category ?: 'Expense',
                'amount' => $exp->amount,
                'details' => ($exp->description ? $exp->description.' - ' : '').($exp->details ?: '-'),
                'attachment_path' => null,
            ]);
        }

        // Pending Investments
        $investments = Investment::where('status', 'Pending')->orderBy('date', 'desc')->get();
        foreach ($investments as $inv) {
            $pending->push([
                'id' => $inv->id,
                'type' => 'Investment',
                'date' => $inv->date ? $inv->date->format('Y-m-d') : '-',
                'submitted_by' => $inv->submitted_by ?: '-',
                'label' => $inv->institution ?: 'Investment',
                'amount' => $inv->amount,
                'details' => ($inv->purpose ? $inv->purpose.' - ' : '').($inv->details ?: '-'),
                'attachment_path' => $inv->attachment_path,
            ]);
        }

        return view('approval.index', compact('pending'));
    }

    public function decide(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Only Admin can approve or reject items.');
        }

        $request->validate([
            'type' => 'required|in:Deposit,Income,Expense,Investment',
            'id' => 'required|string',
            'decision' => 'required|in:Approved,Rejected',
            'rejection_reason' => 'nullable|string',
        ]);

        $type = $request->input('type');
        $id = $request->input('id');
        $decision = $request->input('decision');
        $reason = $request->input('rejection_reason');

        $model = match ($type) {
            'Deposit' => Deposit::findOrFail($id),
            'Income' => Income::findOrFail($id),
            'Expense' => Expense::findOrFail($id),
            'Investment' => Investment::findOrFail($id),
        };

        $model->status = $decision;
        $model->approved_by = $user->name;
        $model->approval_date = now()->format('Y-m-d');

        if ($decision === 'Rejected') {
            $model->rejection_reason = $reason ?? null;
        } else {
            $model->rejection_reason = null;
        }

        $model->save();

        return redirect()->route('approval.index')->with('success', "{$type} {$id} has been {$decision} successfully.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $query = Expense::query();
        if (! $isAdmin) {
            $query->where('status', 'Approved');
        }
        $expenses = $query->orderBy('date', 'desc')->get();

        $categories = ['VAT', 'Source Tax', 'Bank Charge', 'Excise Duty', 'Others'];

        return view('expenses.index', compact('isAdmin', 'expenses', 'categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $request->validate([
            'date' => 'required|date',
            'category' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'details' => 'required|string',
        ]);

        $id = 'EXP-'.strtoupper(Str::random(4)).'-'.date('YmdHis');

        $expense = new Expense;
        $expense->id = $id;
        $expense->date = $request->input('date');
        $expense->category = $request->input('category');
        $expense->description = $request->input('description');
        $expense->amount = (float) $request->input('amount');
        $expense->details = $request->input('details');
        $expense->ref = $request->input('ref');
        $expense->submitted_by = $user->name;

        if ($isAdmin) {
            $expense->status = 'Approved';
            $expense->approved_by = $user->name;
            $expense->approval_date = now()->format('Y-m-d');
        } else {
            $expense->status = 'Pending';
        }

        $expense->save();

        $msg = $isAdmin
            ? 'Expenditure recorded and approved successfully.'
            : 'Expenditure submitted successfully. It is Pending Admin approval.';

        return redirect()->route('expenses.index')->with('success', $msg);
    }

    public function destroy(string $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->isAdmin()) {
            return back()->with('error', 'Only Admin can delete records.');
        }

        $expense = Expense::findOrFail($id);
        $expense->delete();

        return back()->with('success', 'Expenditure record deleted successfully.');
    }
}

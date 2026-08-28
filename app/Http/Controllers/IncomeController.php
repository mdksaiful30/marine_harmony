<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IncomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $query = Income::query();
        if (! $isAdmin) {
            $query->where('status', 'Approved');
        }
        $incomes = $query->orderBy('date', 'desc')->get();

        $sources = ['Bank Profit', 'Land', 'Rent', 'Business', 'Others'];

        return view('income.index', compact('isAdmin', 'incomes', 'sources'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $request->validate([
            'date' => 'required|date',
            'source' => 'required|string',
            'purpose' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'details' => 'required|string',
        ]);

        $id = 'INC-'.strtoupper(Str::random(4)).'-'.date('YmdHis');

        $income = new Income;
        $income->id = $id;
        $income->date = $request->input('date');
        $income->source = $request->input('source');
        $income->purpose = $request->input('purpose');
        $income->amount = (float) $request->input('amount');
        $income->details = $request->input('details');
        $income->ref = $request->input('ref');
        $income->submitted_by = $user->name;

        if ($isAdmin) {
            $income->status = 'Approved';
            $income->approved_by = $user->name;
            $income->approval_date = now()->format('Y-m-d');
        } else {
            $income->status = 'Pending';
        }

        $income->save();

        $msg = $isAdmin
            ? 'Income recorded and approved successfully.'
            : 'Income submitted successfully. It is Pending Admin approval.';

        return redirect()->route('income.index')->with('success', $msg);
    }

    public function destroy(string $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->isAdmin()) {
            return back()->with('error', 'Only Admin can delete records.');
        }

        $income = Income::findOrFail($id);
        $income->delete();

        return back()->with('success', 'Income record deleted successfully.');
    }
}

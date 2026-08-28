<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvestmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $query = Investment::query();
        if (! $isAdmin) {
            $query->where('status', 'Approved');
        }
        $investments = $query->orderBy('date', 'desc')->get();

        return view('investments.index', compact('isAdmin', 'investments'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();

        $request->validate([
            'date' => 'required|date',
            'institution' => 'required|string',
            'purpose' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'details' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:5120',
        ]);

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $path = $file->store('attachments/investments', 'public');
            $attachmentPath = '/storage/'.$path;
        }

        $id = 'INV-'.strtoupper(Str::random(4)).'-'.date('YmdHis');

        $inv = new Investment;
        $inv->id = $id;
        $inv->date = $request->input('date');
        $inv->institution = $request->input('institution');
        $inv->purpose = $request->input('purpose');
        $inv->amount = (float) $request->input('amount');
        $inv->details = $request->input('details');
        $inv->ref = $request->input('ref');
        $inv->term_months = $request->input('term_months') ? (int) $request->input('term_months') : null;
        $inv->maturity_date = $request->input('maturity_date');
        $inv->auto_renew = $request->boolean('auto_renew');
        $inv->attachment_path = $attachmentPath;
        $inv->attachment_name = $attachmentName;
        $inv->submitted_by = $user->name;

        if ($isAdmin) {
            $inv->status = 'Approved';
            $inv->approved_by = $user->name;
            $inv->approval_date = now()->format('Y-m-d');
        } else {
            $inv->status = 'Pending';
        }

        $inv->save();

        $msg = $isAdmin
            ? 'Investment recorded and approved successfully.'
            : 'Investment submitted successfully. It is Pending Admin approval.';

        return redirect()->route('investments.index')->with('success', $msg);
    }

    public function destroy(string $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->isAdmin()) {
            return back()->with('error', 'Only Admin can delete records.');
        }

        $investment = Investment::findOrFail($id);
        $investment->delete();

        return back()->with('success', 'Investment record deleted successfully.');
    }
}

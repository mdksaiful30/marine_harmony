<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarineHarmonyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_login_screen_renders_with_members(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Marine Harmony');
        $response->assertSee('Mohammad Nizam Uddin');
    }

    public function test_admin_can_login_and_view_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->post('/login', [
            'name' => $admin->name,
            'pin' => 'nizam',
        ]);

        $response->assertRedirect('/dashboard');

        $dash = $this->actingAs($admin)->get('/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('Approved Deposits');
        $dash->assertSee('Admin Bank Balance Verification');
    }

    public function test_standard_member_view_restriction(): void
    {
        $member = User::where('role', 'member')->first();
        $this->assertNotNull($member);

        $response = $this->actingAs($member)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('Admin Bank Balance Verification');

        // Approval page should redirect
        $approval = $this->actingAs($member)->get('/approval');
        $approval->assertRedirect('/dashboard');
    }

    public function test_deposit_submission_flow(): void
    {
        $member = User::where('role', 'member')->first();

        $response = $this->actingAs($member)->post('/deposits', [
            'date' => '2026-09-01',
            'member' => $member->name,
            'installment_months' => ['2026-09', '2026-10'],
            'method' => 'Bank',
            'bank_name' => 'Al-Arafah Islami Bank PLC',
            'branch' => 'Principal Branch',
            'bank_ref' => 'TEST-SLIP-001',
            'tx_type' => 'Deposit Slip',
        ]);

        $response->assertRedirect('/deposits');

        $this->assertDatabaseHas('deposits', [
            'member' => $member->name,
            'amount' => 10000,
            'status' => 'Pending',
        ]);
    }

    public function test_financial_calculations(): void
    {
        $deposits = FinanceService::totalApprovedDeposits();
        $income = FinanceService::totalApprovedIncome();
        $expenses = FinanceService::totalApprovedExpenses();
        $investments = FinanceService::totalApprovedInvestments();
        $bankBal = FinanceService::officialBankBalance();
        $netFund = FinanceService::netFund();

        $this->assertEquals($deposits + $income - $expenses - $investments, $bankBal);
        $this->assertEquals($deposits + $income - $expenses, $netFund);
    }

    public function test_admin_can_view_approval_queue_and_approve_transaction(): void
    {
        $admin = User::where('role', 'admin')->first();
        $member = User::where('role', 'member')->first();

        // Submit a pending deposit
        $this->actingAs($member)->post('/deposits', [
            'date' => '2026-09-01',
            'member' => $member->name,
            'installment_months' => ['2026-09'],
            'method' => 'Bank',
            'bank_name' => 'Al-Arafah Islami Bank PLC',
            'branch' => 'Principal Branch',
            'bank_ref' => 'TEST-SLIP-APPROVE',
            'tx_type' => 'Deposit Slip',
        ]);

        $pendingDeposit = Deposit::where('status', 'Pending')->first();
        $this->assertNotNull($pendingDeposit);

        // Admin views approval queue
        $approvalView = $this->actingAs($admin)->get('/approval');
        $approvalView->assertStatus(200);
        $approvalView->assertSee('Approval Queue');
        $approvalView->assertSee($member->name);

        // Admin approves transaction
        $approveResponse = $this->actingAs($admin)->post('/approval/decide', [
            'type' => 'Deposit',
            'id' => $pendingDeposit->id,
            'decision' => 'Approved',
        ]);

        $approveResponse->assertRedirect('/approval');
        $this->assertDatabaseHas('deposits', [
            'id' => $pendingDeposit->id,
            'status' => 'Approved',
            'approved_by' => $admin->name,
        ]);
    }

    public function test_admin_can_reject_transaction_with_reason(): void
    {
        $admin = User::where('role', 'admin')->first();
        $member = User::where('role', 'member')->first();

        // Submit a pending deposit
        $this->actingAs($member)->post('/deposits', [
            'date' => '2026-09-01',
            'member' => $member->name,
            'installment_months' => ['2026-09'],
            'method' => 'Bank',
            'bank_name' => 'Al-Arafah Islami Bank PLC',
            'branch' => 'Principal Branch',
            'bank_ref' => 'TEST-SLIP-REJECT',
            'tx_type' => 'Deposit Slip',
        ]);

        $pendingDeposit = Deposit::where('status', 'Pending')->first();
        $this->assertNotNull($pendingDeposit);

        // Admin rejects transaction
        $rejectResponse = $this->actingAs($admin)->post('/approval/decide', [
            'type' => 'Deposit',
            'id' => $pendingDeposit->id,
            'decision' => 'Rejected',
            'rejection_reason' => 'Invalid bank slip reference image',
        ]);

        $rejectResponse->assertRedirect('/approval');
        $this->assertDatabaseHas('deposits', [
            'id' => $pendingDeposit->id,
            'status' => 'Rejected',
            'rejection_reason' => 'Invalid bank slip reference image',
            'approved_by' => $admin->name,
        ]);
    }
}

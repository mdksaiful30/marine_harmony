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
        $response->assertSee('Quick Member Select');
        $response->assertSee('Email / Username');
        $response->assertSee('Remember me');
        $response->assertSee('Forgot password?');
        $response->assertSee('Sign up');
    }

    public function test_brand_links_are_clickable_on_login_and_dashboard(): void
    {
        $loginPage = $this->get('/login');
        $loginPage->assertStatus(200);
        $loginPage->assertSee('href="'.url('/').'"', false);

        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $dashboardPage = $this->actingAs($admin)->get('/dashboard');
        $dashboardPage->assertStatus(200);
        $dashboardPage->assertSee('href="'.route('dashboard').'"', false);
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

    public function test_admin_can_access_tyro_dashboard_and_resources(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);

        $depositsResource = $this->actingAs($admin)->get('/admin/resources/deposits');
        $depositsResource->assertStatus(200);
        $depositsResource->assertSee('Deposits');
    }

    public function test_login_with_email_or_username_and_remember_me(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // Login via username
        $responseUsername = $this->post('/login', [
            'login' => $admin->username,
            'pin' => 'nizam',
            'remember' => '1',
        ]);
        $responseUsername->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);

        // Logout
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();

        // Login via email
        $responseEmail = $this->post('/login', [
            'login' => $admin->email,
            'password' => 'nizam',
            'remember' => '1',
        ]);
        $responseEmail->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_can_view_registration_and_sign_up(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create an account');

        $registerResponse = $this->post('/register', [
            'name' => 'New Test Member',
            'email' => 'newmember@marineharmony.com',
            'password' => 'SecretPassword123!',
            'password_confirmation' => 'SecretPassword123!',
        ]);

        $registerResponse->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'newmember@marineharmony.com',
            'name' => 'New Test Member',
            'role' => 'member',
        ]);

        $newUser = User::where('email', 'newmember@marineharmony.com')->first();
        $this->assertNotNull($newUser);
        $this->assertNotNull($newUser->username);
        $this->assertAuthenticatedAs($newUser);
    }

    public function test_forgot_password_screen_renders(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Reset');
    }

    public function test_members_directory_links_to_individual_member_profiles(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/members');
        $response->assertStatus(200);
        $response->assertSee('Marine Harmony Members Directory');

        $firstMember = User::first();
        $response->assertSee(route('members.show', $firstMember->id));
    }

    public function test_individual_member_profile_page_renders_dynamically(): void
    {
        $admin = User::where('role', 'admin')->first();
        $member = User::where('role', 'member')->first() ?? $admin;

        $response = $this->actingAs($admin)->get('/members/'.$member->id);
        $response->assertStatus(200);
        $response->assertSee($member->name);
        $response->assertSee('Total Approved Deposits');
        $response->assertSee('Installments Completed');
        $response->assertSee('Monthly Installment Schedule');
        $response->assertSee('Detailed Ledger');

        // Test non-existent member returns 404
        $invalidResponse = $this->actingAs($admin)->get('/members/99999');
        $invalidResponse->assertStatus(404);
    }

    public function test_user_can_switch_active_account_to_another_member(): void
    {
        $taposh = User::where('name', 'Taposh Kumar Biswas')->first();
        $mohibur = User::where('name', 'Md Mohibur Rahman')->first();

        $this->assertNotNull($taposh);
        $this->assertNotNull($mohibur);

        // Authenticate as Taposh
        $this->actingAs($taposh);
        $this->assertAuthenticatedAs($taposh);

        // Switch to Mohibur
        $switchResponse = $this->get(route('auth.switch-member', $mohibur->id));
        $switchResponse->assertStatus(302);
        $this->assertAuthenticatedAs($mohibur);

        // Visit Mohibur profile as Mohibur
        $profileResponse = $this->get(route('members.show', $mohibur->id));
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee($mohibur->name);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Investment;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HistoricalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            'Md. Abu Bakar Siddique',
            'A.Z.M. Monjur Hossain',
            'Taposh Kumar Biswas',
            'Md Kamruzzaman',
            'Ram Prasad Chakraborty',
            'Muhammad Mizanur Rahman',
            'Mohammad Nizam Uddin',
            'Md Mohibur Rahman',
            'Md. Mostafa Shamsuzzaman',
            'Mohammad Ziaur Rahaman',
            'Proshanta Podder',
            'Md Tarek Salah Uddin',
        ];
        $admin = 'Mohammad Nizam Uddin';
        $monthlyInstallment = 5000;

        // 1. Seed Opening Balance
        Deposit::updateOrCreate(
            ['id' => 'HIST-OPEN-20231231'],
            [
                'date' => '2023-12-31',
                'period' => 'Before Jan 2024',
                'member' => 'All Members',
                'method' => 'Historical Balance',
                'amount' => 279500,
                'special' => 'Yes',
                'remarks' => 'Opening balance before January 2024 (audited net fund carried forward).',
                'status' => 'Approved',
                'historical' => true,
                'historical_type' => 'Opening Balance',
                'submitted_by' => 'System',
                'approved_by' => $admin,
                'approval_date' => '2023-12-31',
            ]
        );

        // 2. Seed Monthly Historical Installments Jan 2024 - Oct 2025 (22 months)
        $months = [];
        $fy = 2024;
        $fm = 1;
        while ($fy < 2025 || ($fy === 2025 && $fm <= 10)) {
            $months[] = sprintf('%04d-%02d', $fy, $fm);
            $fm++;
            if ($fm > 12) {
                $fm = 1;
                $fy++;
            }
        }

        foreach ($members as $member) {
            $slug = Str::slug($member);

            foreach ($months as $period) {
                $id = 'HIST-DEP-'.$slug.'-'.str_replace('-', '', $period);
                Deposit::updateOrCreate(
                    ['id' => $id],
                    [
                        'date' => $period.'-01',
                        'period' => $period,
                        'member' => $member,
                        'method' => 'Historical Record',
                        'amount' => $monthlyInstallment,
                        'special' => 'No',
                        'remarks' => 'Monthly installment BDT 5,000/-; historical record. Jan 2024-Oct 2025 fully paid.',
                        'status' => 'Approved',
                        'historical' => true,
                        'submitted_by' => 'System',
                        'approved_by' => $admin,
                        'approval_date' => $period.'-01',
                    ]
                );
            }
        }

        // 3. Seed Nizam Nov 2025
        Deposit::updateOrCreate(
            ['id' => 'DEP-NIZAM-202511'],
            [
                'date' => '2025-11-01',
                'period' => '2025-11',
                'member' => 'Mohammad Nizam Uddin',
                'method' => 'Bank',
                'amount' => 5000,
                'bank_name' => 'Al-Arafah Islami Bank PLC',
                'branch' => 'Principal Branch',
                'bank_ref' => 'DEP-MNU-NOV2025',
                'tx_type' => 'Deposit Slip',
                'special' => 'No',
                'remarks' => 'Monthly installment for Nov 2025',
                'status' => 'Approved',
                'historical' => false,
                'submitted_by' => 'Mohammad Nizam Uddin',
                'approved_by' => $admin,
                'approval_date' => '2025-11-01',
            ]
        );

        // 4. Seed Mostafa Aug 2026
        Deposit::updateOrCreate(
            ['id' => 'USER-DEP-MOSTAFA-202608'],
            [
                'date' => '2026-08-26',
                'period' => '2026-08',
                'member' => 'Md. Mostafa Shamsuzzaman',
                'method' => 'Bank',
                'amount' => 5000,
                'bank_name' => 'Al-Arafah Islami Bank PLC',
                'branch' => 'Principal Branch',
                'bank_ref' => 'DEP-MMS-AUG2026',
                'tx_type' => 'Deposit Slip',
                'special' => 'No',
                'remarks' => 'Monthly installment for August 2026',
                'status' => 'Approved',
                'historical' => false,
                'submitted_by' => 'Md. Mostafa Shamsuzzaman',
                'approved_by' => $admin,
                'approval_date' => '2026-08-26',
            ]
        );

        // 5. Seed Expense Aug 2026
        Expense::updateOrCreate(
            ['id' => 'USER-EXP-5000-20260826'],
            [
                'date' => '2026-08-26',
                'category' => 'Others',
                'description' => 'General Operational Expenditure',
                'amount' => 5000,
                'details' => 'Operational expense approved by Admin',
                'ref' => 'EXP-20260826-5000',
                'status' => 'Approved',
                'historical' => false,
                'submitted_by' => 'Mohammad Nizam Uddin',
                'approved_by' => $admin,
                'approval_date' => '2026-08-26',
            ]
        );

        // 6. Seed MTDR Investment
        Investment::updateOrCreate(
            ['id' => 'INV-MTDR-ALARAFA-1000K'],
            [
                'date' => '2024-05-15',
                'institution' => 'Al-Arafah Islami Bank PLC',
                'purpose' => 'MTDR (Monthly Term Deposit Receipt) Fund Investment',
                'amount' => 1000000,
                'details' => '3-Month Term MTDR Investment with Auto-Renewal. Account / FDR No: AIB-MTDR-MH-2024-01.',
                'ref' => 'AIB-MTDR-1000K-01',
                'auto_renew' => true,
                'term_months' => 3,
                'maturity_date' => '2026-11-15',
                'status' => 'Approved',
                'historical' => true,
                'submitted_by' => 'System',
                'approved_by' => $admin,
                'approval_date' => '2024-05-15',
            ]
        );

        // 7. Seed Bank Profit Income
        Income::updateOrCreate(
            ['id' => 'HIST-INC-BANKPROFIT-2024'],
            [
                'date' => '2024-12-31',
                'source' => 'Bank Profit',
                'purpose' => 'Profit on MTDR and Savings Account for 2024',
                'amount' => 65420,
                'details' => 'Annual profit accrued on Al-Arafah MTDR and operational savings account.',
                'ref' => 'AIB-PROFIT-2024',
                'status' => 'Approved',
                'historical' => true,
                'submitted_by' => 'System',
                'approved_by' => $admin,
                'approval_date' => '2024-12-31',
            ]
        );

        // 8. Seed Default Settings
        Setting::set('monthly_installment_amount', '5000');
        Setting::set('project_start_date', '2024-01-01');
    }
}

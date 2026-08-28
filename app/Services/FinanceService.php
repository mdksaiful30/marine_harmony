<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Investment;
use Carbon\Carbon;

class FinanceService
{
    public const MONTHLY_INSTALLMENT = 5000;

    public const START_MONTH = '2024-01';

    public static function getTargetDueMonth(): string
    {
        return now()->format('Y-m');
    }

    public static function formatMoney($val): string
    {
        return 'BDT '.number_format((float) $val, 2, '.', ',');
    }

    public static function formatMoneyShort($val): string
    {
        return number_format((float) $val, 2, '.', ',');
    }

    public static function getMonthsList(string $from = '2024-01', ?string $to = null): array
    {
        $to = $to ?: self::getTargetDueMonth();
        $out = [];
        [$fy, $fm] = explode('-', $from);
        [$ty, $tm] = explode('-', $to);
        $fy = (int) $fy;
        $fm = (int) $fm;
        $ty = (int) $ty;
        $tm = (int) $tm;

        while ($fy < $ty || ($fy === $ty && $fm <= $tm)) {
            $out[] = sprintf('%04d-%02d', $fy, $fm);
            $fm++;
            if ($fm > 12) {
                $fm = 1;
                $fy++;
            }
        }

        return $out;
    }

    public static function monthLabel(string $ym): string
    {
        if (! preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
            return $ym;
        }
        $dt = Carbon::createFromDate((int) $m[1], (int) $m[2], 1);

        return $dt ? $dt->format('M Y') : $ym;
    }

    public static function totalApprovedDeposits(): float
    {
        return (float) Deposit::where('status', 'Approved')->sum('amount');
    }

    public static function totalApprovedIncome(): float
    {
        return (float) Income::where('status', 'Approved')->sum('amount');
    }

    public static function totalApprovedExpenses(): float
    {
        return (float) Expense::where('status', 'Approved')->sum('amount');
    }

    public static function totalApprovedInvestments(): float
    {
        return (float) Investment::where('status', 'Approved')->sum('amount');
    }

    public static function officialBankBalance(): float
    {
        return self::totalApprovedDeposits() + self::totalApprovedIncome() - self::totalApprovedExpenses() - self::totalApprovedInvestments();
    }

    public static function netFund(): float
    {
        return self::totalApprovedDeposits() + self::totalApprovedIncome() - self::totalApprovedExpenses();
    }

    public static function pendingApprovalsCount(): int
    {
        return Deposit::where('status', 'Pending')->count()
            + Income::where('status', 'Pending')->count()
            + Expense::where('status', 'Pending')->count()
            + Investment::where('status', 'Pending')->count();
    }

    public static function pendingApprovalsTotal(): float
    {
        return (float) Deposit::where('status', 'Pending')->sum('amount')
            + (float) Income::where('status', 'Pending')->sum('amount')
            + (float) Expense::where('status', 'Pending')->sum('amount')
            + (float) Investment::where('status', 'Pending')->sum('amount');
    }

    public static function getApprovedPeriodsForMember(string $memberName): array
    {
        $deposits = Deposit::where('member', $memberName)
            ->where('status', 'Approved')
            ->whereNotNull('period')
            ->get();

        $periods = [];
        foreach ($deposits as $d) {
            foreach ($d->periods_list as $p) {
                if (preg_match('/^\d{4}-\d{2}$/', $p)) {
                    $periods[$p] = true;
                }
            }
        }

        return $periods;
    }

    public static function getPendingPeriodsForMember(string $memberName): array
    {
        $deposits = Deposit::where('member', $memberName)
            ->where('status', 'Pending')
            ->whereNotNull('period')
            ->get();

        $periods = [];
        foreach ($deposits as $d) {
            foreach ($d->periods_list as $p) {
                if (preg_match('/^\d{4}-\d{2}$/', $p)) {
                    $periods[$p] = true;
                }
            }
        }

        return $periods;
    }

    public static function getMemberLedgerSummary(string $memberName): array
    {
        $targetDue = self::getTargetDueMonth();
        $allMonths = self::getMonthsList(self::START_MONTH, $targetDue);
        $approvedMap = self::getApprovedPeriodsForMember($memberName);
        $pendingMap = self::getPendingPeriodsForMember($memberName);

        $paidCount = count($approvedMap);
        $pendingCount = count($pendingMap);

        // Due count: months up to targetDue that are neither approved nor pending
        $dueCount = 0;
        $dueMonths = [];
        foreach ($allMonths as $m) {
            if (empty($approvedMap[$m]) && empty($pendingMap[$m])) {
                $dueCount++;
                $dueMonths[] = $m;
            }
        }

        $totalDeposited = (float) Deposit::where('member', $memberName)
            ->where('status', 'Approved')
            ->sum('amount');

        // Status badge calculation
        $statusClass = 'mh-status-ok';
        $statusLabel = 'OK / Up to Date';

        if ($dueCount === 0 && $pendingCount > 0) {
            $statusClass = 'mh-status-pending';
            $statusLabel = 'Approval Pending';
        } elseif ($dueCount === 1) {
            $statusClass = 'mh-status-warn';
            $statusLabel = '1 Month Due';
        } elseif ($dueCount > 1) {
            $statusClass = 'mh-status-alarm';
            $statusLabel = "{$dueCount} Months Due";
        }

        return [
            'name' => $memberName,
            'paid_months' => $paidCount,
            'pending_months' => $pendingCount,
            'due_count' => $dueCount,
            'due_months' => $dueMonths,
            'total_deposited' => $totalDeposited,
            'status_class' => $statusClass,
            'status_label' => $statusLabel,
            'approved_map' => $approvedMap,
            'pending_map' => $pendingMap,
        ];
    }
}

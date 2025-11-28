<?php

namespace App\Traits;

trait AppliesDateFilter
{
    
    protected function applyDateFilter($query, $filter)
    {
        $now = now();
        
        switch ($filter) {
            case 'today':
                $query->whereDate('created_at', $now->toDateString());
                break;
            case 'last_week':
                $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
                $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();
                $query->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd]);
                break;
            case 'last_month':
                $query->whereMonth('created_at', $now->copy()->subMonth()->month)
                      ->whereYear('created_at', $now->copy()->subMonth()->year);
                break;
            case 'this_month':
            default:
                $query->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year);
                break;
        }
    }
}


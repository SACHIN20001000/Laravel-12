<?php

namespace App\Http\Controllers\Admin;

use App\Traits\AppliesDateFilter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;

class DashboardController extends Controller
{
    use AppliesDateFilter;
    public function index(Request $request)
    {
        $user = Auth::user();
        $dateFilter = $request->get('date_filter', 'this_month');
        
        if ($user->hasRole('SuperAdmin')) {
            $search = $request->get('search');
            $companiesQuery = Company::withCount(['users', 'shortUrls']);
            
            if ($search) {
                $companiesQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            $companies = $companiesQuery->latest()->paginate(10)->withQueryString();
            
            $companies->getCollection()->transform(function($company) {
                $company->total_hits = $company->shortUrls()->sum('clicks');
                return $company;
            });
            
            return view('admin.dashboard', compact('user', 'companies', 'dateFilter', 'search'));
        }
        
        if ($user->hasRole('Admin')) {
            $company = $user->company;
            
            if ($company) {
                $search = $request->get('search');
                $shortUrlsQuery = ShortUrl::where('company_id', $company->id)->with(['user', 'company']);
                
                if ($search) {
                    $shortUrlsQuery->where(function($q) use ($search) {
                        $q->where('original_url', 'like', "%{$search}%")
                          ->orWhere('short_code', 'like', "%{$search}%")
                          ->orWhereHas('user', function($query) use ($search) {
                              $query->where('name', 'like', "%{$search}%");
                          });
                    });
                }
                
                $this->applyDateFilter($shortUrlsQuery, $dateFilter);
                $shortUrls = $shortUrlsQuery->latest()->paginate(10)->withQueryString();
                
                $teamMembers = User::where('company_id', $company->id)
                    ->with('roles')
                    ->withCount('shortUrls')
                    ->get()
                    ->map(function($member) {
                        $member->total_hits = $member->shortUrls()->sum('clicks');
                        return $member;
                    });
                
                return view('admin.dashboard', compact('user', 'company', 'shortUrls', 'teamMembers', 'dateFilter', 'search'));
            }
        }
        
        if ($user->hasRole('Member')) {
            $company = $user->company;
            
            if ($company) {
                $search = $request->get('search');
                $shortUrlsQuery = ShortUrl::where('user_id', $user->id)->with(['user', 'company']);
                
                if ($search) {
                    $shortUrlsQuery->where(function($q) use ($search) {
                        $q->where('original_url', 'like', "%{$search}%")
                          ->orWhere('short_code', 'like', "%{$search}%");
                    });
                }
                
                $this->applyDateFilter($shortUrlsQuery, $dateFilter);
                $shortUrls = $shortUrlsQuery->latest()->paginate(10)->withQueryString();
                
                return view('admin.dashboard', compact('user', 'company', 'shortUrls', 'dateFilter', 'search'));
            }
        }
        
        return view('admin.dashboard', compact('user'));
    }
}

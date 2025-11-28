<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('SuperAdmin')) {
            return redirect()->route('admin.dashboard');
        }

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

        return view('admin.companies.index', compact('companies', 'search'));
    }

    public function show(Company $company)
    {
        $user = Auth::user();

        if (!$user->hasRole('SuperAdmin')) {
            if ($user->company_id !== $company->id) {
                return redirect()->route('admin.dashboard');
            }
        }

        $company->loadCount(['users', 'shortUrls']);
        $company->load([
            'users.roles',
            'users' => function ($query) {
                $query->withCount('shortUrls');
            },
            'shortUrls.user:id,name'
        ]);

        $company->users->transform(function($user) {
            $user->total_hits = $user->shortUrls()->sum('clicks');
            return $user;
        });

        return view('admin.companies.show', compact('company'));
    }

    public function showMyCompany()
    {
        $user = Auth::user();

        if (!$user->company_id) {
            return redirect()->route('admin.dashboard');
        }

        $company = Company::findOrFail($user->company_id);
        
        return $this->show($company);
    }

    public function store(CompanyRequest $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('SuperAdmin')) {
            return redirect()->back()->with('error', 'Only SuperAdmin can invite companies.');
        }

        $validated = $request->validated();

        Company::create($validated);

        return redirect()->back()->with('success', 'Company invitation sent successfully.');
    }
}

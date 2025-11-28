<?php

namespace App\Http\Controllers\Admin;

use App\Traits\AppliesDateFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShortUrlRequest;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShortUrlController extends Controller
{
    use AppliesDateFilter;
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ShortUrl::with(['user', 'company']);

        if ($user->hasRole('SuperAdmin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('Admin')) {
            $query->where('company_id', $user->company_id);
        }

        if ($user->hasRole('Member')) {
            $query->where('user_id', $user->id);
        }

        $search = $request->get('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('original_url', 'like', "%{$search}%")
                  ->orWhere('short_code', 'like', "%{$search}%");
                if ($user->hasRole('Admin')) {
                    $q->orWhereHas('user', function($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
                }
            });
        }

        $dateFilter = $request->get('date_filter', 'this_month');
        $this->applyDateFilter($query, $dateFilter);

        $shortUrls = $query->latest()->paginate(15)->withQueryString();

        return view('admin.short-urls.index', compact('shortUrls', 'dateFilter', 'search'));
    }

    public function download(Request $request)
    {
        $user = Auth::user();
        
        if ($user->hasRole('SuperAdmin')) {
            return redirect()->route('admin.dashboard')->with('error', 'SuperAdmin cannot download short URLs.');
        }
        
        $query = ShortUrl::with(['user', 'company']);

        if ($user->hasRole('Admin')) {
            $query->where('company_id', $user->company_id);
        }

        if ($user->hasRole('Member')) {
            $query->where('user_id', $user->id);
        }

        $dateFilter = $request->get('date_filter', 'this_month');
        $this->applyDateFilter($query, $dateFilter);

        $shortUrls = $query->latest()->get();

        $filename = 'short_urls_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($shortUrls, $user) {
            $file = fopen('php://output', 'w');
            
            if ($user->hasRole('Admin')) {
                fputcsv($file, ['Short URL', 'Long URL', 'Hits', 'User', 'Created On']);
                foreach ($shortUrls as $url) {
                    fputcsv($file, [
                        url('/s/' . $url->short_code),
                        $url->original_url,
                        $url->clicks,
                        $url->user->name,
                        $url->created_at->format('d M Y')
                    ]);
                }
            } else {
                fputcsv($file, ['Short URL', 'Long URL', 'Hits', 'Created On']);
                foreach ($shortUrls as $url) {
                    fputcsv($file, [
                        url('/s/' . $url->short_code),
                        $url->original_url,
                        $url->clicks,
                        $url->created_at->format('d M Y')
                    ]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(ShortUrlRequest $request)
    {
        $user = Auth::user();

        if ($user->hasRole('SuperAdmin')) {
            return redirect()->back()->with('error', 'SuperAdmin cannot create short URLs.');
        }

        if (!$user->hasAnyRole(['Admin', 'Member'])) {
            return redirect()->back()->with('error', 'You do not have permission to create short URLs.');
        }

        $validated = $request->validated();

        $shortCode = $this->generateShortCode();

        ShortUrl::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'original_url' => $validated['original_url'],
            'short_code' => $shortCode,
        ]);

        return redirect()->back()->with('success', 'Short URL created successfully.');
    }

    private function generateShortCode(): string
    {
        do {
            $code = Str::random(6);
        } while (ShortUrl::where('short_code', $code)->exists());

        return $code;
    }
}

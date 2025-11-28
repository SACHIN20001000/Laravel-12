@extends('layouts.app')

@section('title', 'Short URLs')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
@if(auth()->user()->hasRole('Admin'))
    <div class="card mb-4">
        <div class="card-header text-white" style="background-color: #0d6efd;">
            <h4 class="mb-0">Client Admin Dashboard</h4>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
    </div>
@elseif(auth()->user()->hasRole('Member'))
    <div class="card mb-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #198754;">
            <h4 class="mb-0">Client Member Dashboard</h4>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
        </div>
    </div>
@endif

@if(auth()->user()->hasAnyRole(['Admin', 'Member']))
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Generate Short URL</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.short-urls.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="original_url" class="form-label">Long URL</label>
                    <input type="url" class="form-control" id="original_url" name="original_url" placeholder="http://ersachinkumar.in/" required>
                </div>
                <button type="submit" class="btn btn-primary">Generate</button>
            </form>
        </div>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Generated Short URLs</h5>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="dateFilter" onchange="updateDateFilter(this.value)" style="width: auto;">
                    <option value="this_month" {{ ($dateFilter ?? 'this_month') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ ($dateFilter ?? '') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_week" {{ ($dateFilter ?? '') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                    <option value="today" {{ ($dateFilter ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                </select>
                <a href="{{ route('admin.short-urls.download', ['date_filter' => $dateFilter ?? 'this_month']) }}" class="btn btn-primary btn-sm">Download</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <x-search 
            route="{{ route('admin.short-urls.index') }}" 
            placeholder="Search by URL or user name..." 
            value="{{ $search ?? '' }}"
            :hiddenFields="['date_filter' => $dateFilter ?? 'this_month']"
        />
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Short URL</th>
                        <th>Long URL</th>
                        <th>Hits</th>
                        @if(auth()->user()->hasRole('Admin'))
                            <th>User</th>
                        @endif
                        <th>Created On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shortUrls as $shortUrl)
                        <tr>
                            <td><a href="{{ route('short-url.redirect', $shortUrl->short_code) }}" target="_blank">{{ url('/s/' . $shortUrl->short_code) }}</a></td>
                            <td>{{ Str::limit($shortUrl->original_url, 50) }}</td>
                            <td>{{ $shortUrl->clicks }}</td>
                            @if(auth()->user()->hasRole('Admin'))
                                <td>{{ $shortUrl->user->name }}</td>
                            @endif
                            <td>{{ $shortUrl->created_at->format('d M \'y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('Admin') ? '5' : '4' }}" class="text-center">No short URLs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span>Showing {{ $shortUrls->count() }} of total {{ $shortUrls->total() }}</span>
            <div>
                {{ $shortUrls->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function updateDateFilter(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('date_filter', value);
    @if(isset($search) && $search)
        url.searchParams.set('search', '{{ $search }}');
    @endif
    window.location.href = url.toString();
}
</script>
@endsection

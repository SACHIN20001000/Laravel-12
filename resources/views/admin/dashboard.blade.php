@extends('layouts.app')

@section('title', 'Dashboard')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
@if($user->hasRole('SuperAdmin'))
    <div class="card mb-4">
        <div class="card-header text-white" style="background-color: #ffc107;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Super Admin Dashboard</h4>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Clients</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteClientModal">Invite</button>
            </div>
        </div>
        <div class="card-body">
            <x-search 
                route="{{ route('admin.dashboard') }}" 
                placeholder="Search by name or email..." 
                value="{{ $search ?? '' }}"
            />
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Users</th>
                            <th>Total Generated URLs</th>
                            <th>Total URL Hits</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies ?? [] as $company)
                            <tr>
                                <td>{{ $company->name }}<br><small class="text-muted">{{ $company->email }}</small></td>
                                <td>{{ $company->users_count }}</td>
                                <td>{{ $company->short_urls_count }}</td>
                                <td>{{ $company->total_hits ?? $company->shortUrls()->sum('clicks') }}</td>
                                <td>
                                    <a href="{{ route('admin.companies.show', $company->id) }}" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($companies))
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span>Showing {{ $companies->count() }} of total {{ $companies->total() }}</span>
                    <div>
                        {{ $companies->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="inviteClientModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invite New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.companies.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Client Name...." required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="sachindts98@gmail.com" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Send Invitation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@elseif($user->hasRole('Admin'))
    <div class="card mb-4">
        <div class="card-header text-white" style="background-color: #0d6efd;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Client Admin Dashboard</h4>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Generate Short URL</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.short-urls.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="original_url" class="form-label">Long URL</label>
                    <input type="url" class="form-control" id="original_url" name="original_url" placeholder="https://ersachinkumar.in/" required>
                </div>
                <button type="submit" class="btn btn-primary">Generate</button>
            </form>
        </div>
    </div>

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
                route="{{ route('admin.dashboard') }}" 
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
                            <th>User</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shortUrls ?? [] as $shortUrl)
                            <tr>
                                <td><a href="{{ route('short-url.redirect', $shortUrl->short_code) }}" target="_blank">{{ url('/s/' . $shortUrl->short_code) }}</a></td>
                                <td>{{ Str::limit($shortUrl->original_url, 50) }}</td>
                                <td>{{ $shortUrl->clicks }}</td>
                                <td>{{ $shortUrl->user->name }}</td>
                                <td>{{ $shortUrl->created_at->format('d M \'y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No short URLs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($shortUrls))
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span>Showing {{ $shortUrls->count() }} of total {{ $shortUrls->total() }}</span>
                    <div>
                        {{ $shortUrls->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Team Members</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteModal">Invite</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Total Generated URLs</th>
                            <th>Total URL Hits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teamMembers ?? [] as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    @foreach($member->roles as $role)
                                        <span class="badge bg-primary">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $member->short_urls_count }}</td>
                                <td>{{ $member->total_hits ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No team members found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.partials.invite-modal')

@elseif($user->hasRole('Member'))
    <div class="card mb-4">
        <div class="card-header text-white" style="background-color: #198754;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Client Member Dashboard</h4>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Generated Short URLs</h5>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateUrlModal">Generate</button>
                    <select class="form-select form-select-sm" id="dateFilterMember" onchange="updateDateFilter(this.value)" style="width: auto;">
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
                route="{{ route('admin.dashboard') }}" 
                placeholder="Search by URL..." 
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
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shortUrls ?? [] as $shortUrl)
                            <tr>
                                <td><a href="{{ route('short-url.redirect', $shortUrl->short_code) }}" target="_blank">{{ url('/s/' . $shortUrl->short_code) }}</a></td>
                                <td>{{ Str::limit($shortUrl->original_url, 50) }}</td>
                                <td>{{ $shortUrl->clicks }}</td>
                                <td>{{ $shortUrl->created_at->format('d M \'y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No short URLs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($shortUrls))
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span>Showing {{ $shortUrls->count() }} of total {{ $shortUrls->total() }}</span>
                    <div>
                        {{ $shortUrls->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="generateUrlModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Short URL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.short-urls.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="original_url" class="form-label">Long URL</label>
                            <input type="url" class="form-control" id="original_url" name="original_url" placeholder="http://ersachinkumar.in/" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

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

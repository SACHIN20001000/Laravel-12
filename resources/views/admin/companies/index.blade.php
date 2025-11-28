@extends('layouts.app')

@section('title', 'Companies')

@section('content')
<div class="card mb-4">
    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #ffc107;">
        <h4 class="mb-0">Super Admin Dashboard</h4>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Clients</h5>
            @if(auth()->user()->hasRole('SuperAdmin'))
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteClientModal">Invite</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <x-search 
            route="{{ route('admin.companies.index') }}" 
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
                    @forelse($companies as $company)
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
                            <td colspan="5" class="text-center">No companies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span>Showing {{ $companies->count() }} of total {{ $companies->total() }}</span>
            <div>
                {{ $companies->links() }}
            </div>
        </div>
    </div>
</div>

@include('admin.companies.partials.modal')
@endsection

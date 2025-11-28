@extends('layouts.app')

@section('title', 'Company Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Company Details</h2>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">Back to Companies</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Company Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Name:</th>
                        <td>{{ $company->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $company->email }}</td>
                    </tr>
                    <tr>
                        <th>Total URLs:</th>
                        <td>{{ $company->short_urls_count }}</td>
                    </tr>
                    <tr>
                        <th>Team Members:</th>
                        <td>{{ $company->users_count }}</td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $company->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        @if(auth()->user()->hasRole('SuperAdmin') || (auth()->user()->hasRole('Admin') && auth()->user()->company_id == $company->id))
            <div class="card">
                <div class="card-header">
                    <h5>Invite Team Member</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.invitations.store') }}">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company->id }}">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                @if(auth()->user()->hasRole('SuperAdmin'))
                                    <option value="Admin">Admin</option>
                                    <option value="Member">Member</option>
                                @else
                                    <option value="Member">Member</option>
                                @endif
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Invitation</button>
                    </form>
                </div>
            </div>
        @endif

        @if(auth()->user()->hasAnyRole(['Admin', 'Member']) && auth()->user()->company_id == $company->id)
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Generate Short URL</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.short-urls.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="original_url" class="form-label">Long URL</label>
                            <input type="url" class="form-control" id="original_url" name="original_url" placeholder="https://ersachinkumar.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Team Members</h5>
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
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $user->short_urls_count ?? $user->shortUrls->count() }}</td>
                                    <td>{{ $user->shortUrls()->sum('clicks') }}</td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No team members found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Generated Short URLs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Long URL</th>
                                <th>Short URL</th>
                                <th>Created By</th>
                                <th>Hits</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->shortUrls as $shortUrl)
                                <tr>
                                    <td>{{ \Illuminate\Support\Str::limit($shortUrl->original_url, 50) }}</td>
                                    <td><a href="{{ route('short-url.redirect', $shortUrl->short_code) }}" target="_blank">{{ url('/s/' . $shortUrl->short_code) }}</a></td>
                                    <td>{{ $shortUrl->user->name }}</td>
                                    <td>{{ $shortUrl->clicks }}</td>
                                    <td>{{ $shortUrl->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No short URLs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvitationRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{

    public function store(InvitationRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        if ($user->hasRole('SuperAdmin')) {
            if (isset($validated['company_id'])) {
                $company = Company::findOrFail($validated['company_id']);
                $newUser = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'company_id' => $company->id,
                ]);
                $newUser->syncRoles([$validated['role']]);
            } else {
                if ($validated['role'] !== 'Admin') {
                    return redirect()->back()->with('error', 'SuperAdmin can only invite Admins for new companies.');
                }
                
                $company = Company::create([
                    'name' => $validated['name'] . ' Company',
                    'email' => $validated['email'],
                ]);

                $newUser = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'company_id' => $company->id,
                ]);

                $newUser->syncRoles(['Admin']);
            }
        } elseif ($user->hasRole('Admin')) {
            if ($validated['role'] === 'Admin') {
                return redirect()->back()->with('error', 'Admin cannot invite another Admin.');
            }

            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $user->company_id,
            ]);

            $newUser->syncRoles([$validated['role']]);
        } else {
            return redirect()->back()->with('error', 'You do not have permission to invite users.');
        }

        return redirect()->back()->with('success', 'Invitation sent successfully.');
    }
}

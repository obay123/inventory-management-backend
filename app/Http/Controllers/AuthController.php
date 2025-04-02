<?php

namespace App\Http\Controllers;
use App\Models\User;
use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'company' => 'required|string|unique:tenants,name'
        ]);

        // Step 1: Create a new tenant (schema)
        $tenant = Tenant::create([
            'name' => $request->company,
            'domain' => strtolower(str_replace(' ', '-', $request->company)) . '.yourapp.com'
        ]);

        // Step 2: Switch to the new tenant
        $tenant->makeCurrent();

        // Step 3: Run migrations for this tenant
        $tenant->createDatabase();
        $tenant->runMigrations();

        // Step 4: Create the first user in this tenant's schema
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => 'Tenant and user created successfully!',
            'tenant' => $tenant,
            'user' => $user
        ]);
        
    }
}

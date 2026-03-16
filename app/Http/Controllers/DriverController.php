<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    /**
     * List all drivers.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');


        $drivers = User::where('role', 'driver')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('drivers.index', compact('drivers', 'search'));
    }

    /**
     * Show driver details.
     */
    public function show(User $driver)
    {
        abort_if($driver->role !== 'driver', 403);
        return view('drivers.show', compact('driver'));
    }

    /**
     * Show create form.
     */
    // public function create()
    // {
    //     return view('drivers.create');
    // }

    /**
     * Store new driver.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'required|string|max:20',
            'password'       => 'required|string|min:8|confirmed',
            'license_number' => 'nullable|string|max:100',
            'truck_info'     => 'nullable|string|max:255',
            'user_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'role'           => 'driver',
            'full_name'      => $request->full_name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'password'       => Hash::make($request->password),
            'license_number' => $request->license_number,
            'truck_info'     => $request->truck_info,
            'is_active'      => 1,
        ];

        if ($request->hasFile('user_image')) {
            $data['user_image'] = $request->file('user_image')->store('images', 'public');
        }

        User::create($data);

        return redirect()->back()->with('success', 'Driver created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(User $driver)
    {
        abort_if($driver->role !== 'driver', 403);
        return view('drivers.edit', compact('driver'));
    }

    /**
     * Update driver.
     */
    public function update(Request $request, User $driver)
    {
        Log::debug('Driver update hit', ['id' => $driver->id, 'data' => $request->all()]);
        abort_if($driver->role !== 'driver', 403);

        $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => ['required', 'email', Rule::unique('users')->ignore($driver->id)],
            'phone'          => 'required|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'truck_info'     => 'nullable|string|max:255',
            'is_active'      => 'nullable|boolean',
            'user_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'full_name'      => $request->full_name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'license_number' => $request->license_number,
            'truck_info'     => $request->truck_info,
            'is_active'      => $request->has('is_active') ? 1 : 0,
        ];

        if ($request->hasFile('user_image')) {
            // Delete old image if exists
            if ($driver->user_image) {
                Storage::disk('public')->delete($driver->user_image);
            }
            $data['user_image'] = $request->file('user_image')->store('images', 'public');
        }

        $driver->update($data);
        Log::debug('Driver updated', ['id' => $driver->id]);

        return redirect()->back()->with('success', 'Driver updated successfully.');
    }

    /**
     * Delete driver.
     */
    public function destroy(User $driver)
    {
        abort_if($driver->role !== 'driver', 403);
        if ($driver->user_image) {
            Storage::disk('public')->delete($driver->user_image);
        }
        $driver->delete();
        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully.');
    }

    /**
     * Toggle active status quickly.
     */
    public function toggleStatus(User $driver)
    {
        abort_if($driver->role !== 'driver', 403);
        $driver->update(['is_active' => !$driver->is_active]);
        return back()->with('success', 'Driver status updated.');
    }
}

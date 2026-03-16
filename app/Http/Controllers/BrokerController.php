<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrokerController extends Controller
{
    /**
     * Display a listing of brokers.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $brokers = User::where('role', 'broker')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('brokers.index', compact('brokers', 'search'));
    }

    /**
     * Display the specified broker.
     */
    public function show(User $broker)
    {
        abort_if($broker->role !== 'broker', 403);
        return view('brokers.show', compact('broker'));
    }

    /**
     * Store a newly created broker in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'user_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'broker',
            'is_active' => true,
        ];

        if ($request->hasFile('user_image')) {
            $data['user_image'] = $request->file('user_image')->store('images', 'public');
        }

        User::create($data);

        return redirect()->route('brokers.index')->with('success', 'Broker created successfully.');
    }

    /**
     * Update the specified broker in storage.
     */
    public function update(Request $request, User $broker)
    {
        Log::debug('Broker update hit', ['id' => $broker->id, 'data' => $request->all()]);
        if ($broker->role !== 'broker') {
            abort(403);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($broker->id),
            ],
            'phone' => 'required|string|max:20',
            'is_active' => 'nullable|boolean',
            'user_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('user_image')) {
            if ($broker->user_image) {
                Storage::disk('public')->delete($broker->user_image);
            }
            $data['user_image'] = $request->file('user_image')->store('images', 'public');
        }

        $broker->update($data);

        Log::debug('Broker updated', ['id' => $broker->id]);

        return redirect()->route('brokers.index')->with('success', 'Broker updated successfully.');
    }

    /**
     * Remove the specified broker from storage.
     */
    public function destroy(User $broker)
    {
        if ($broker->role !== 'broker') {
            abort(403);
        }

        if ($broker->user_image) {
            Storage::disk('public')->delete($broker->user_image);
        }

        $broker->delete();

        return redirect()->route('brokers.index')->with('success', 'Broker deleted successfully.');
    }

    /**
     * Toggle the status of the specified broker.
     */
    public function toggleStatus(User $broker)
    {
        if ($broker->role !== 'broker') {
            abort(403);
        }

        $broker->update([
            'is_active' => !$broker->is_active,
        ]);

        return redirect()->route('brokers.index')->with('success', 'Broker status updated.');
    }
}

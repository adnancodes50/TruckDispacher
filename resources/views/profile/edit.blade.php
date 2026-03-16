<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>Profile Management</span>
        </div>
    </x-slot>

    <div class="max-w-none mx-auto space-y-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Profile Settings</h1>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-800">Account Information</h2>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="px-6 pt-4 pb-6 space-y-2">
                @csrf
                @method('PUT')

                {{-- USER INFO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <x-input-label for="full_name" :value="__('Full Name')" class="text-xs font-semibold uppercase text-slate-500 mb-1" />
                        <x-text-input id="full_name" name="full_name" type="text"
                            class="w-full rounded-xl border-slate-200 text-sm py-2.5"
                            :value="old('full_name', $user->full_name)" required autofocus autocomplete="name" />
                        <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email Address')" class="text-xs font-semibold uppercase text-slate-500 mb-1" />
                        <x-text-input id="email" name="email" type="email"
                            class="w-full rounded-xl border-slate-200 text-sm py-2.5"
                            :value="old('email', $user->email)" required autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone Number')" class="text-xs font-semibold uppercase text-slate-500 mb-1" />
                        <x-text-input id="phone" name="phone" type="text"
                            class="w-full rounded-xl border-slate-200 text-sm py-2.5"
                            :value="old('phone', $user->phone)" placeholder="555-123-4567" />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    </div>

                </div>

                {{-- PASSWORD UPDATE --}}
                <div class="border-t border-slate-100 pt-6">
                    <h2 class="text-sm font-semibold text-slate-800 mb-4">Update Password</h2>
                    <p class="text-xs text-slate-500 mb-6">Leave blank if you don't want to change your password.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <x-input-label for="password" :value="__('New Password')" class="text-xs font-semibold uppercase text-slate-500 mb-1" />
                            <x-text-input id="password" name="password" type="password"
                                class="w-full rounded-xl border-slate-200 text-sm py-2.5"
                                autocomplete="new-password" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" class="text-xs font-semibold uppercase text-slate-500 mb-1" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                class="w-full rounded-xl border-slate-200 text-sm py-2.5"
                                autocomplete="new-password" />
                            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                        </div>

                    </div>
                </div>

                {{-- PROFILE IMAGE MOVED TO BOTTOM --}}
                <div class="border-t border-slate-100 pt-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">

                        <div class="flex items-center gap-4">

                            <div
                                class="h-20 w-20 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center">

                                @if($user->user_image)
                                    <img id="profile_image_preview"
                                        src="{{ asset('storage/' . $user->user_image) }}"
                                        alt="Profile Image"
                                        class="h-full w-full object-cover" />

                                    <div id="profile_initials"
                                        class="hidden text-xl font-semibold text-slate-500"></div>
                                @else
                                    <div id="profile_initials"
                                        class="text-xl font-semibold text-slate-500">
                                        {{ strtoupper(substr($user->full_name, 0, 2)) }}
                                    </div>

                                    <img id="profile_image_preview"
                                        src=""
                                        alt="Profile Image"
                                        class="hidden h-full w-full object-cover" />
                                @endif

                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1"
                                    for="user_image">
                                    Profile Image
                                </label>

                                <input id="user_image"
                                    name="user_image"
                                    type="file"
                                    accept="image/*"
                                    class="text-sm rounded-xl border border-slate-200 p-2" />

                                <x-input-error class="mt-2" :messages="$errors->get('user_image')" />
                            </div>

                        </div>

                    </div>

                </div>

                {{-- SUBMIT --}}
                <div class="flex items-center justify-end gap-4 border-t border-slate-100 pt-6">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2.5 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
                        style="background:#0f766e;">
                        {{ __('Save Changes') }}
                    </button>
                </div>

            </form>
        </div>
    </div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const profileName = @json($user->full_name);
    const currentImageUrl = @json($user->user_image ? asset('storage/' . $user->user_image) : null);

    setProfilePreview({
        previewId: 'profile_image_preview',
        initialsId: 'profile_initials',
        imageUrl: currentImageUrl,
        name: profileName
    });

    const input = document.getElementById('user_image');

    if (!input) return;

    input.addEventListener('change', function (event) {

        const file = event.target.files?.[0];
        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {

            setProfilePreview({
                previewId: 'profile_image_preview',
                initialsId: 'profile_initials',
                imageUrl: e.target.result,
                name: profileName
            });

        };

        reader.readAsDataURL(file);

    });

});

</script>

</x-app-layout>

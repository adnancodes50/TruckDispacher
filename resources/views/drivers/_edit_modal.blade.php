<!-- ============================================================ -->
<!-- EDIT MODAL -->
<!-- ============================================================ -->
<div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white rounded-t-2xl">
            <h2 class="text-base font-semibold text-slate-900">Edit Driver</h2>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
        </div>
        <form id="editForm" method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Full Name *</label>
                    <input type="text" id="edit_full_name" name="full_name" required class="w-full rounded-xl border-slate-200 text-sm" />
                    <x-input-error class="mt-1" :messages="$errors->get('full_name')" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Phone *</label>
                    <input type="text" id="edit_phone" name="phone" required class="w-full rounded-xl border-slate-200 text-sm" />
                    <x-input-error class="mt-1" :messages="$errors->get('phone')" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Email *</label>
                <input type="email" id="edit_email" name="email" required class="w-full rounded-xl border-slate-200 text-sm" />
                <x-input-error class="mt-1" :messages="$errors->get('email')" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">License Number</label>
                    <input type="text" id="edit_license_number" name="license_number" class="w-full rounded-xl border-slate-200 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Truck Info</label>
                    <input type="text" id="edit_truck_info" name="truck_info" class="w-full rounded-xl border-slate-200 text-sm" />
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Profile Image</label>
                <div class="mb-2">
                    <div id="edit_initials" class="h-20 w-20 rounded-lg bg-slate-100 grid place-items-center text-xl font-semibold text-slate-500"></div>
                    <img id="edit_image_preview" src="" alt="Preview" class="hidden h-20 w-20 rounded-lg object-cover border border-slate-200" />
                </div>
                <input id="edit_user_image" type="file" name="user_image" accept="image/*" class="w-full rounded-xl border-slate-200 text-sm" />
            </div>
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded border-slate-300" />
                <label for="edit_is_active" class="text-sm text-slate-700">Driver is Active</label>
            </div>
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-white text-sm font-medium hover:opacity-90" style="background:#0f766e;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

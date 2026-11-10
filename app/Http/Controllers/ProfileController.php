<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilePhotoRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $stats = null;
        if (! $user->isAdmin()) {
            Loan::markOverdue();
            $stats = [
                'borrowed' => $user->loans()->active()->count(),
                'overdue' => $user->loans()->where('status', 'terlambat')->count(),
                'total' => $user->loans()->count(),
            ];
        }

        return view('profile.edit', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Simpan atau ganti foto profil.
     */
    public function updatePhoto(ProfilePhotoRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Nama file diacak otomatis oleh Laravel, nama asli dari pengguna tidak dipakai
        $newPath = $request->file('photo')->store('profile-photos', 'public');

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->forceFill(['photo' => $newPath])->save();
        ActivityLog::record('update_photo', "{$user->name} mengubah foto profil");

        return Redirect::route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil (kembali ke inisial).
     */
    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->forceFill(['photo' => null])->save();
            ActivityLog::record('delete_photo', "{$user->name} menghapus foto profil");
        }

        return Redirect::route('profile.edit')->with('success', 'Foto profil dihapus.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Admin dan akun yang punya riwayat peminjaman tidak boleh dihapus sendiri
        if ($user->isAdmin() || $user->loans()->withTrashed()->exists()) {
            return back()->withErrors(
                ['password' => 'Akun ini tidak bisa dihapus sendiri. Hubungi Admin.'],
                'userDeletion'
            );
        }

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
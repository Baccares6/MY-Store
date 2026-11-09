<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    // Batas jumlah alamat per pengguna agar buku alamat tetap rapi
    private const MAX_ADDRESSES = 10;

    /**
     * Menyimpan alamat baru. Alamat pertama otomatis menjadi alamat utama.
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        $count = DB::table('addresses')->where('user_id', $userId)->count();

        if ($count >= self::MAX_ADDRESSES) {
            return redirect()->back()->with('error', 'Anda sudah memiliki ' . self::MAX_ADDRESSES . ' alamat. Hapus salah satu alamat lama sebelum menambah yang baru.');
        }

        $data = $this->validateAddress($request);
        $makeDefault = $count === 0 || $request->boolean('is_default');

        $id = DB::transaction(function () use ($data, $userId, $makeDefault) {
            if ($makeDefault) {
                DB::table('addresses')->where('user_id', $userId)->update(['is_default' => false]);
            }

            return DB::table('addresses')->insertGetId($data + [
                'user_id'    => $userId,
                'is_default' => $makeDefault,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        // selected_address_id dipakai halaman keranjang agar alamat yang baru dibuat langsung terpilih
        return redirect()->back()
            ->with('success', 'Alamat berhasil disimpan.')
            ->with('selected_address_id', $id);
    }

    /**
     * Memperbarui alamat (Dilindungi dari IDOR)
     */
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $address = $this->findOwnedAddress($id);

        if (!$address) {
            return redirect()->back()->with('error', 'Alamat tidak ditemukan.');
        }

        $data = $this->validateAddress($request);

        DB::transaction(function () use ($data, $userId, $address, $request) {
            if ($request->boolean('is_default') && !$address->is_default) {
                DB::table('addresses')->where('user_id', $userId)->update(['is_default' => false]);
                $data['is_default'] = true;
            }

            DB::table('addresses')
                ->where('id', $address->id)
                ->where('user_id', $userId)
                ->update($data + ['updated_at' => now()]);
        });

        return redirect()->back()->with('success', 'Alamat berhasil diperbarui.');
    }

    /**
     * Menghapus alamat. Jika yang dihapus adalah alamat utama,
     * alamat terbaru yang tersisa otomatis dijadikan alamat utama.
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $address = $this->findOwnedAddress($id);

        if (!$address) {
            return redirect()->back()->with('error', 'Alamat tidak ditemukan.');
        }

        DB::transaction(function () use ($userId, $address) {
            DB::table('addresses')->where('id', $address->id)->where('user_id', $userId)->delete();

            if ($address->is_default) {
                $next = DB::table('addresses')->where('user_id', $userId)->orderByDesc('id')->first();
                if ($next) {
                    DB::table('addresses')->where('id', $next->id)->update(['is_default' => true]);
                }
            }
        });

        return redirect()->back()->with('success', 'Alamat berhasil dihapus.');
    }

    /**
     * Menjadikan sebuah alamat sebagai alamat utama
     */
    public function makeDefault($id)
    {
        $userId = Auth::id();
        $address = $this->findOwnedAddress($id);

        if (!$address) {
            return redirect()->back()->with('error', 'Alamat tidak ditemukan.');
        }

        DB::transaction(function () use ($userId, $address) {
            DB::table('addresses')->where('user_id', $userId)->update(['is_default' => false]);
            DB::table('addresses')->where('id', $address->id)->update(['is_default' => true]);
        });

        return redirect()->back()->with('success', 'Alamat utama berhasil diganti.');
    }

    private function findOwnedAddress($id)
    {
        return DB::table('addresses')
            ->where('id', (int) $id)
            ->where('user_id', Auth::id())
            ->first();
    }

    /**
     * Validasi form alamat. Error disimpan di error bag "address"
     * supaya form alamat bisa dibuka kembali lengkap dengan pesan kesalahannya.
     */
    private function validateAddress(Request $request): array
    {
        // Samakan format nomor HP: buang spasi/tanda hubung, ubah awalan +62/62 menjadi 0
        $phone = preg_replace('/[\s\-().]/', '', (string) $request->input('phone'));
        $phone = preg_replace('/^(\+62|62)/', '0', $phone);
        $request->merge(['phone' => $phone]);

        $validated = $request->validateWithBag('address', [
            'label'          => 'nullable|string|max:30',
            'recipient_name' => 'required|string|max:100',
            'phone'          => ['required', 'regex:/^08[0-9]{8,12}$/'],
            'province'       => ['required', Rule::in(config('wilayah.provinces'))],
            'city'           => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'postal_code'    => ['required', 'digits:5'],
            'address_detail' => 'required|string|min:10|max:255',
        ], [
            'required'           => ':attribute wajib diisi.',
            'max'                => ':attribute maksimal :max karakter.',
            'phone.regex'        => 'Nomor HP tidak valid. Gunakan nomor yang diawali 08, contoh 081234567890.',
            'province.in'        => 'Pilih provinsi dari daftar yang tersedia.',
            'postal_code.digits' => 'Kode pos harus terdiri dari 5 angka.',
            'address_detail.min' => 'Alamat lengkap terlalu singkat. Tuliskan nama jalan, nomor rumah, dan RT/RW.',
        ], [
            'label'          => 'Label alamat',
            'recipient_name' => 'Nama penerima',
            'phone'          => 'Nomor HP',
            'province'       => 'Provinsi',
            'city'           => 'Kota/Kabupaten',
            'district'       => 'Kecamatan',
            'postal_code'    => 'Kode pos',
            'address_detail' => 'Alamat lengkap',
        ]);

        $validated['label'] = $validated['label'] ?? null;

        return $validated;
    }
}

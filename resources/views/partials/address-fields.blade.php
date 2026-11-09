{{--
    Kolom-kolom form alamat pengiriman.
    Dipakai di halaman Keranjang (tambah alamat) dan Dashboard (tambah & ubah alamat).
    Halaman pemanggil yang membuat tag <form> beserta tombolnya.
--}}
@php($addrErrors = $errors->getBag('address'))

<style>
    .af-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 16px; }
    .af-full { grid-column: 1 / -1; }
    .af-field label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
    .af-field label .af-optional { font-weight: 400; color: #94a3b8; }
    .af-input {
        width: 100%; padding: 10px 12px; font: inherit; font-size: 14px; color: #0f172a;
        background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .af-input:focus { border-color: #870000; box-shadow: 0 0 0 3px rgba(135, 0, 0, .12); }
    .af-input.is-invalid { border-color: #dc2626; }
    textarea.af-input { resize: vertical; min-height: 76px; }
    .af-hint { font-size: 12px; color: #64748b; margin-top: 5px; }
    .af-error { font-size: 12px; color: #dc2626; margin-top: 5px; }
    .af-check { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #334155; cursor: pointer; }
    .af-check input { width: 16px; height: 16px; accent-color: #870000; }
    @media (max-width: 600px) { .af-grid { grid-template-columns: 1fr; } }
</style>

<div class="af-grid">
    <div class="af-field">
        <label for="af-recipient">Nama penerima</label>
        <input id="af-recipient" name="recipient_name" type="text" maxlength="100" autocomplete="name"
               class="af-input {{ $addrErrors->has('recipient_name') ? 'is-invalid' : '' }}"
               value="{{ old('recipient_name') }}" required>
        @if ($addrErrors->has('recipient_name'))<div class="af-error">{{ $addrErrors->first('recipient_name') }}</div>@endif
    </div>

    <div class="af-field">
        <label for="af-phone">Nomor HP</label>
        <input id="af-phone" name="phone" type="tel" inputmode="numeric" maxlength="16" autocomplete="tel"
               placeholder="081234567890"
               class="af-input {{ $addrErrors->has('phone') ? 'is-invalid' : '' }}"
               value="{{ old('phone') }}" required>
        @if ($addrErrors->has('phone'))
            <div class="af-error">{{ $addrErrors->first('phone') }}</div>
        @else
            <div class="af-hint">Dipakai kurir untuk menghubungi Anda.</div>
        @endif
    </div>

    <div class="af-field">
        <label for="af-province">Provinsi</label>
        <select id="af-province" name="province" class="af-input {{ $addrErrors->has('province') ? 'is-invalid' : '' }}" required>
            <option value="" disabled {{ old('province') ? '' : 'selected' }}>Pilih provinsi</option>
            @foreach ($provinces as $province)
                <option value="{{ $province }}" {{ old('province') === $province ? 'selected' : '' }}>{{ $province }}</option>
            @endforeach
        </select>
        @if ($addrErrors->has('province'))<div class="af-error">{{ $addrErrors->first('province') }}</div>@endif
    </div>

    <div class="af-field">
        <label for="af-city">Kota/Kabupaten</label>
        <input id="af-city" name="city" type="text" maxlength="100" placeholder="Contoh: Kota Yogyakarta"
               class="af-input {{ $addrErrors->has('city') ? 'is-invalid' : '' }}"
               value="{{ old('city') }}" required>
        @if ($addrErrors->has('city'))<div class="af-error">{{ $addrErrors->first('city') }}</div>@endif
    </div>

    <div class="af-field">
        <label for="af-district">Kecamatan</label>
        <input id="af-district" name="district" type="text" maxlength="100" placeholder="Contoh: Depok"
               class="af-input {{ $addrErrors->has('district') ? 'is-invalid' : '' }}"
               value="{{ old('district') }}" required>
        @if ($addrErrors->has('district'))<div class="af-error">{{ $addrErrors->first('district') }}</div>@endif
    </div>

    <div class="af-field">
        <label for="af-postal">Kode pos</label>
        <input id="af-postal" name="postal_code" type="text" inputmode="numeric" maxlength="5" pattern="[0-9]{5}"
               autocomplete="postal-code" placeholder="55281"
               class="af-input {{ $addrErrors->has('postal_code') ? 'is-invalid' : '' }}"
               value="{{ old('postal_code') }}" required>
        @if ($addrErrors->has('postal_code'))<div class="af-error">{{ $addrErrors->first('postal_code') }}</div>@endif
    </div>

    <div class="af-field af-full">
        <label for="af-detail">Alamat lengkap</label>
        <textarea id="af-detail" name="address_detail" maxlength="255" autocomplete="street-address"
                  placeholder="Nama jalan, nomor rumah, RT/RW"
                  class="af-input {{ $addrErrors->has('address_detail') ? 'is-invalid' : '' }}" required>{{ old('address_detail') }}</textarea>
        @if ($addrErrors->has('address_detail'))
            <div class="af-error">{{ $addrErrors->first('address_detail') }}</div>
        @else
            <div class="af-hint">Tambahkan patokan jika alamat sulit ditemukan, misalnya "depan masjid".</div>
        @endif
    </div>

    <div class="af-field">
        <label for="af-label">Label alamat <span class="af-optional">(opsional)</span></label>
        <input id="af-label" name="label" type="text" maxlength="30" placeholder="Rumah, Kantor, Kos"
               class="af-input {{ $addrErrors->has('label') ? 'is-invalid' : '' }}"
               value="{{ old('label') }}">
        @if ($addrErrors->has('label'))<div class="af-error">{{ $addrErrors->first('label') }}</div>@endif
    </div>

    {{-- Alamat pertama otomatis menjadi alamat utama, jadi pilihan ini hanya muncul jika sudah ada alamat lain --}}
    @if (empty($isFirstAddress))
        <div class="af-field" style="display:flex; align-items:flex-end; padding-bottom:10px;">
            <label class="af-check" style="margin:0; font-weight:400;">
                <input id="af-default" type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                Jadikan alamat utama
            </label>
        </div>
    @endif
</div>

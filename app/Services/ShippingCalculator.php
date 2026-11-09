<?php

namespace App\Services;

/**
 * Menghitung ongkos kirim berdasarkan tabel tarif di config/shipping.php.
 * Seluruh perhitungan dilakukan di server; ongkir yang dikirim dari browser tidak pernah dipakai.
 */
class ShippingCalculator
{
    /**
     * Menentukan zona pengiriman dari provinsi tujuan
     */
    public function zoneFor(string $province): ?string
    {
        foreach (config('shipping.zones') as $code => $zone) {
            if (in_array($province, $zone['provinces'], true)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Berat yang ditagihkan dalam kg (minimal 1 kg, dengan toleransi pembulatan seperti kurir)
     */
    public function chargeableKg(int $grams): int
    {
        $tolerance = (int) config('shipping.weight_tolerance', 0);

        return max(1, (int) ceil(max($grams - $tolerance, 0) / 1000));
    }

    /**
     * Daftar layanan kurir yang tersedia ke provinsi tujuan, diurutkan dari yang termurah
     */
    public function options(string $province, int $grams): array
    {
        $zone = $this->zoneFor($province);
        if (!$zone) {
            return [];
        }

        $kg = $this->chargeableKg($grams);
        $options = [];

        foreach (config('shipping.services') as $code => $service) {
            if (!isset($service['rates'][$zone])) {
                continue;
            }

            $options[] = [
                'code'    => $code,
                'courier' => $service['courier'],
                'service' => $service['service'],
                'etd'     => $service['etd'][$zone],
                'cost'    => $service['rates'][$zone] * $kg,
            ];
        }

        usort($options, fn ($a, $b) => $a['cost'] <=> $b['cost']);

        return $options;
    }

    /**
     * Mengambil satu layanan yang dipilih pembeli, atau null jika tidak tersedia ke tujuan tersebut
     */
    public function quote(string $code, string $province, int $grams): ?array
    {
        foreach ($this->options($province, $grams) as $option) {
            if ($option['code'] === $code) {
                return $option;
            }
        }

        return null;
    }
}

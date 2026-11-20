<?php

namespace App\Services;

use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Menghitung skor reputasi peminjam dengan Beta Reputation System.
 *
 * Kelas ini murni logika: tidak menyentuh database atau HTTP,
 * sehingga mudah diuji dan parameternya mudah divariasikan untuk simulasi.
 */
class ReputationScorer
{
    /** Bobot pelanggaran (asumsi awal, dikaji lewat simulasi). */
    public const VIOLATION_WEIGHTS = [
        'late_light' => 0.5,        // telat ringan (1 sampai 3 hari)
        'late_heavy' => 1.0,        // telat berat (lebih dari 3 hari)
        'damage_negligent' => 2.0,  // rusak karena lalai
        'damage_wear' => 0.0,       // rusak wajar (aus): tidak dipenalti
    ];

    /**
     * Urut dari tier tertinggi: nama => [skor minimum, maksimal barang aktif].
     *
     * @var array<string, array{0: float, 1: int}>
     */
    private array $tiers = [
        'terpercaya' => [0.70, 5],
        'standar' => [0.40, 3],
        'terbatas' => [0.0, 1],
    ];

    public function __construct(
        private float $halfLifeDays = 90.0,
        private bool $decayPositive = true,
    ) {
        if ($halfLifeDays <= 0) {
            throw new InvalidArgumentException('Waktu paruh harus lebih dari 0 hari.');
        }
    }

    /**
     * @param  iterable<array{kind: string, at: CarbonInterface}>  $events
     *   kind: good, late_light, late_heavy, damage_negligent, atau damage_wear
     * @return array{alpha: float, beta: float, score: float}
     */
    public function compute(iterable $events, CarbonInterface $now): array
    {
        $alpha = 1.0; // prior: belum ada bukti
        $beta = 1.0;

        foreach ($events as $event) {
            $kind = $event['kind'];

            // Umur kejadian dalam hari; kejadian bertanggal masa depan tidak diberi diskon
            $age = max(0.0, ($now->getTimestamp() - $event['at']->getTimestamp()) / 86400);
            $decay = 2 ** (-$age / $this->halfLifeDays);

            if ($kind === 'good') {
                $alpha += $this->decayPositive ? $decay : 1.0;

                continue;
            }

            if (! array_key_exists($kind, self::VIOLATION_WEIGHTS)) {
                throw new InvalidArgumentException("Jenis kejadian tidak dikenal: {$kind}");
            }

            $beta += self::VIOLATION_WEIGHTS[$kind] * $decay;
        }

        return [
            'alpha' => $alpha,
            'beta' => $beta,
            'score' => $alpha / ($alpha + $beta),
        ];
    }

    /**
     * @return array{name: string, limit: int}
     */
    public function tier(float $score): array
    {
        foreach ($this->tiers as $name => [$minimum, $limit]) {
            if ($score >= $minimum) {
                return ['name' => $name, 'limit' => $limit];
            }
        }

        return ['name' => 'terbatas', 'limit' => 1];
    }
}

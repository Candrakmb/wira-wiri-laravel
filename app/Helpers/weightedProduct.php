<?php

namespace App\Helpers;

class weightedProduct
{
    public static function weightedProducts($drivers, $weights, $types)
    {
        // Normalisasi bobot
        $totalWeight = array_sum($weights);
        foreach ($weights as $key => $weight) {
            $weights[$key] = $weight / $totalWeight;
        }

        // Hitung skor untuk setiap driver
        $scores = [];
        foreach ($drivers as $driver) {
            $score = 1;
            foreach ($weights as $key => $weight) {
                if ($types[$key] == 'cost') {
                    // Untuk tipe cost, gunakan pangkat negatif
                    $score *= pow($driver[$key], -$weight);
                } elseif ($types[$key] == 'benefit') {
                    // Untuk tipe benefit, gunakan pangkat positif
                    $score *= pow($driver[$key], $weight);
                }
            }
            $scores[] = $score; // Menyimpan skor untuk setiap driver
        }

        // Hitung total skor
        $totalScore = array_sum($scores);

        // Hitung skor akhir dengan membagi setiap skor alternatif dengan jumlah seluruh skor
        $finalScores = [];
        foreach ($scores as $index => $score) {
            $finalScores[] = [
                'driver_id' => $drivers[$index]['id'],
                'score' => $score / $totalScore
            ];
        }

        // Urutkan berdasarkan skor
        usort($finalScores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        return $finalScores;
    }

}

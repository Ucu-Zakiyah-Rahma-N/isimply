<?php
namespace App\Helpers;

class Terbilang
{
    private static $baca = [
        "", "Satu", "Dua", "Tiga", "Empat", "Lima",
        "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"
    ];

    public static function convert($angka)
    {
        $angka = abs((int)$angka);

        if ($angka < 12) {
            return self::$baca[$angka];
        } elseif ($angka < 20) {
            return self::convert($angka - 10) . " Belas";
        } elseif ($angka < 100) {
            return self::convert(floor($angka / 10)) . " Puluh " . self::convert($angka % 10);
        } elseif ($angka < 200) {
            return "Seratus " . self::convert($angka - 100);
        } elseif ($angka < 1000) {
            return self::convert(floor($angka / 100)) . " Ratus " . self::convert($angka % 100);
        } elseif ($angka < 2000) {
            return "Seribu " . self::convert($angka - 1000);
        } elseif ($angka < 1000000) {
            return self::convert(floor($angka / 1000)) . " Ribu " . self::convert($angka % 1000);
        } elseif ($angka < 1000000000) {
            return self::convert(floor($angka / 1000000)) . " Juta " . self::convert($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            return self::convert(floor($angka / 1000000000)) . " Miliar " . self::convert($angka % 1000000000);
        }

        return "";
    }
}

<?php

namespace Database\Seeders;

use App\Models\Bagian;
use App\Models\Profile;
use App\Models\User;
use App\Models\Customer;
use App\Models\KawasanIndustri;
use App\Models\Marketing;
use App\Models\Cabang;
use App\Models\SatuanPerizinan;
use App\Models\Perizinan;
use App\Models\Tahapan;
use App\Models\Wilayah;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PerizinanSeeder::class,
            CeklisPerizinanSeeder::class,
            WilayahSeeder::class,
            KawasanIndustriSeeder::class,
            TahapanSeeder::class,
            SubTahapanSeeder::class,
        ]);

        Marketing::insert([
            // ==== INTERNAL ====
            ['status' => 'internal', 'nama' => 'Rafiudin Firdaus', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Jaenudin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Riki Himawan', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Pramesti Anggun Fari Anggraeni', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Rendi Sugara', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Zenita Amalia Julhijah', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Teddi Ariono', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Soni Ahmad Sahidin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Melasari Nugraha', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Gilang Putra Persada', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Kresna Baskoro Rosadi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Restu Nugraha Triputra', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Shopi Yanti Anggraeni', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Deri Rudian', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => "Imam Abdullah Syafi'i", 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Muhammad Abdul Aziz', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Mohamad Fajar Rhamadhan', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Annisa Siti Maulyda', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Nabila Putri Khairunnisa Soetrisno', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Naufaldi Muti Wiharjanto', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'internal', 'nama' => 'Ucu Zakiyah Rahma Nindya', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // ==== CABANG ====
            ['status' => 'cabang', 'nama' => 'Chairul Mustafa', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'cabang', 'nama' => 'Rifqi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'cabang', 'nama' => 'Asep Ake', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

            // ==== FREELANCE ====
            ['status' => 'freelance', 'nama' => 'Dani Ubed', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Wilono', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Suyatno', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Shofa Zakaria', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Parno', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Ubaydillah', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Sultan', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Anjar Septianto', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Firdaus', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Eris', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Tubagus D', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Nuryasin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Koni', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Rizal', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Winda', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Nur Albantany', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Revi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Endi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Kustiyanto', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Eka', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Firman', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Ilman', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Faisal', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Fahmi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Heri', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Muh. Ningamullah', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Ahmad Yopie', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Taufiq Rahman', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Anton', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Asep Almahdi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Seno', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Kamilla', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Uswatun', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Bayu', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Nurani', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Tobi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Prama', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Gingin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Imam', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Agung Prasojo', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['status' => 'freelance', 'nama' => 'Ahyadi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
        
        // Akun Admin
        User::insert([
            [
                'customer_id' => null,
                'username'    => 'Anggun',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'admin 2',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Annisa',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'admin 1',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Ucu Zakiyah',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'superadmin',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Rafiudin',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'CEO',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Jaenudin',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'direktur',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Zenita',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'manager marketing',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Riki',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'manager projek',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Mela',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'manager finance',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Cabang Depok',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'admin marketing',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Cabang Bogor',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'admin marketing',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Cabang Serang',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'admin marketing',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
            [
                'customer_id' => null,
                'username'    => 'Cabang Sukabumi',
                'password'    => bcrypt('Jalurlangit123'),
                'role'        => 'admin marketing',
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ],
        ]);

        Cabang::insert([
            ['nama_cabang' => 'HO', 'kode_sph' => 'SDI'],
            ['nama_cabang' => 'Jakarta', 'kode_sph' => 'CJKT/SDI'],
            ['nama_cabang' => 'Depok', 'kode_sph' => 'CDPK/SDI'],
            ['nama_cabang' => 'Bogor', 'kode_sph' => 'CBGR/SDI'],
            ['nama_cabang' => 'Serang', 'kode_sph' => 'CSRG/SDI'],
            ['nama_cabang' => 'Sukabumi', 'kode_sph' => 'CSKB/SDI'],
        ]);
        
        SatuanPerizinan::insert([
            ['nama' => 'ls', 'is_active' => 1],
            ['nama' => 'unit', 'is_active' => 1],
            ['nama' => 'titik', 'is_active' => 1],
            ['nama' => 'paket', 'is_active' => 1],
            ['nama' => 'set', 'is_active' => 1],
            ['nama' => 'dokumen', 'is_active' => 1],
        ]);

    }
}

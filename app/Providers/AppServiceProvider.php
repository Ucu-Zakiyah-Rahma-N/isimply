<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // public function register(): void
    // {
    //     //
    // }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('admin', function () {
            if (!Auth::check()) return false;

            $role = strtolower(trim(Auth::user()->role));
            return in_array($role, ['admin 1', 'admin 2', 'admin 3', 'admin 4', 'admin 5', 'admin 6', 'admin 7', 'admin 8', 'admin 9', 'admin 10', 'admin 11']);
        });


        Paginator::useBootstrap();

        // View::composer('*', function ($view) {
        // $jumlahSuratUmum = \App\Models\Surat::where('tgl_diterima', '=',null)
        //     ->where('ditujukan','=', Auth::user()?->bagian_id)
        //     ->count();
        // $jumlahDisposisiSekda = \App\Models\LembarDisposisiSekda::where('tgl_diterima', '=',null)
        //     ->where('ditujukan','=', Auth::user()?->bagian_id)
        //     ->count();
        // $jumlahDisposisiAsda = \App\Models\LembarDisposisiAsda::where('tgl_diterima', '=',null)
        //     ->where('ditujukan','=', Auth::user()?->bagian_id)
        //     ->count();
        // $jumlahKartuDisposisi = \App\Models\KartuDisposisi::where('tgl_diterima_asda', '=',null)
        //     ->where('ditujukan','=', Auth::user()?->bagian_id)
        //     ->count();

        // $view->with('smbd', $jumlahSuratUmum);
        // $view->with('jds', $jumlahDisposisiSekda);
        // $view->with('jda', $jumlahDisposisiAsda);
        // $view->with('jkd', $jumlahKartuDisposisi);

        // });
    }
}

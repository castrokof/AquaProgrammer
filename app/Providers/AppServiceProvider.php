<?php

namespace App\Providers;

use App\Models\Admin\Menu;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view::composer("theme.lte.aside", function($view){
            $menus = Menu::getMenu(true);
            $view->with('menusComposer',$menus);
        });

        View::share('theme', 'lte');    
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      

     }
    
}

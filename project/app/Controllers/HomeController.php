<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dish;
use App\Models\Restaurant;

class HomeController extends Controller
{
    public function index(): void
    {
        $category = $_GET['category'] ?? null;
        $search = trim($_GET['search'] ?? '');

        $restaurants = Restaurant::getWithDishCount();
        $dishes = Dish::getAll([
            'category' => $category,
            'search' => $search,
            'is_available' => 1
        ]);

        $this->render('home/index', [
            'title' => 'Болаззат — Доставка еды и напитков в Душанбе',
            'restaurants' => $restaurants,
            'dishes' => $dishes,
            'currentCategory' => $category,
            'search' => $search,
            'categories' => Dish::CATEGORIES,
        ]);
    }

    public function about(): void
    {
        $this->render('home/about', [
            'title' => 'О платформе Болаззат — Автоматизация доставки еды',
        ]);
    }
}

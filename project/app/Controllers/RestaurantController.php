<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\Review;

class RestaurantController extends Controller
{
    public function show(int|string $id): void
    {
        $id = (int)$id;
        $restaurant = Restaurant::findById($id);

        if (!$restaurant) {
            $this->redirect('/');
        }

        $allDishes = Dish::getByRestaurant($id, false);
        $reviews = Review::getByRestaurant($id);

        // Группировка блюд по категориям
        $groupedDishes = [];
        foreach ($allDishes as $dish) {
            $cat = $dish['category'];
            $groupedDishes[$cat][] = $dish;
        }

        $this->render('restaurant/show', [
            'title' => htmlspecialchars($restaurant['name']) . ' — Меню и доставка',
            'restaurant' => $restaurant,
            'groupedDishes' => $groupedDishes,
            'reviews' => $reviews,
            'categories' => Dish::CATEGORIES,
        ]);
    }
}

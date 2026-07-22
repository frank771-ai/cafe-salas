<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/** Prepara la portada con productos destacados, categorías y visitas recientes. */
class HomeController extends Controller
{
    /** Atiende la ruta principal mediante un controlador invocable. */
    public function __invoke(Request $request)
    {
        $featured = Product::active()->with('category')->where('featured', true)->take(6)->get();
        $categories = Category::withCount(['products' => fn ($query) => $query->active()])->get();
        // La cookie guarda solo identificadores; los datos visibles siempre se releen de la base.
        $ids = array_values(array_filter(json_decode((string) $request->cookie('recent_products', '[]'), true) ?: [], 'is_numeric'));
        $recentMap = Product::active()->with('category')->whereIn('id', $ids)->get()->keyBy('id');
        $recent = collect($ids)->map(fn ($id) => $recentMap->get((int) $id))->filter()->values();

        return view('home', compact('featured', 'categories', 'recent'));
    }
}

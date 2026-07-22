<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\RecentProductsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/** Controla el catálogo público, sus filtros y la cookie de productos recientes. */
class ProductController extends Controller
{
    public function __construct(private readonly RecentProductsService $recentProducts) {}

    /** Valida los filtros y construye una consulta Eloquent paginada. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'max_price' => [
                'nullable',
                'integer',
                'min:0',
                'max:10000000',
                // Solo compare ambos precios cuando el usuario realmente indicó un mínimo.
                ...($request->filled('min_price') ? ['gte:min_price'] : []),
            ],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,name'],
        ]);

        $products = Product::active()->with('category')
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")))
            ->when($filters['category'] ?? null, fn ($query, $slug) => $query->whereHas('category', fn ($category) => $category->where('slug', $slug)))
            ->when(isset($filters['min_price']), fn ($query) => $query->where('price', '>=', $filters['min_price']))
            ->when(isset($filters['max_price']), fn ($query) => $query->where('price', '<=', $filters['max_price']));

        // Se limita el ordenamiento a una lista blanca para no aceptar columnas arbitrarias.
        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            'name' => $products->orderBy('name'),
            default => $products->latest(),
        };

        return view('products.index', [
            'products' => $products->paginate(9)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** Muestra el detalle y actualiza la lista cifrada de visitas recientes. */
    public function show(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);
        $recent = $this->recentProducts->record($request->cookie('recent_products'), $product->id);
        // HttpOnly y SameSite=Lax reducen exposición de la cookie a scripts y solicitudes cruzadas.
        Cookie::queue(cookie('recent_products', json_encode($recent), 43200, '/', null, $request->isSecure(), true, false, 'lax'));

        return view('products.show', [
            'product' => $product->load('category'),
            'related' => Product::active()->where('category_id', $product->category_id)->where('id', '!=', $product->id)->take(3)->get(),
        ]);
    }
}

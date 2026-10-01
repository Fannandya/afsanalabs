<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $query = Portfolio::with('category')->where('is_active', true)->orderBy('sort_order')->orderBy('id');
        if ($slug = $request->query('category')) {
            $category = Category::where('slug', $slug)->first();
            if (! $category) {
                return response()->json(['data' => []]);
            }
            $query->where('category_id', $category->id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function categories()
    {
        return response()->json(['data' => Category::orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function testimonials()
    {
        return response()->json(['data' => Testimonial::active()->ordered()->get()]);
    }
}

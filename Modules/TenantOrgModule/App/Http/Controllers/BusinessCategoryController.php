<?php

namespace Modules\TenantOrgModule\App\Http\Controllers;

use Modules\TenantOrgModule\App\Models\BusinessCategory;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class BusinessCategoryController extends Controller
{
    public function index()
    {
        return response()->json(BusinessCategory::with([
            'subUnits' => function ($query) {
                $query->select('id', 'category_id', 'name');
            }
        ])->get([
                    'id',
                    'name'
                ]));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $category = BusinessCategory::create($request->all());
        return response()->json($category, 201);
    }

    public function show($id)
    {
        return response()->json(BusinessCategory::with([
            'subUnits' => function ($query) {
                $query->select('id', 'category_id', 'name');
            }
        ])->findOrFail($id, ['id', 'name']));
    }

    public function update(Request $request, $id)
    {
        $category = BusinessCategory::findOrFail($id);
        $request->validate(['name' => 'required|string']);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = BusinessCategory::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}

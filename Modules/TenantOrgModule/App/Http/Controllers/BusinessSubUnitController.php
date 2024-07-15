<?php

namespace Modules\TenantOrgModule\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\TenantOrgModule\App\Models\BusinessSubUnit;
use Illuminate\Http\Request;

class BusinessSubUnitController extends Controller
{
    public function index()
    {
        return response()->json(BusinessSubUnit::with([
            'category' => function ($query) {
                $query->select('id', 'name');
            }
        ])->get([
                    'id',
                    'category_id',
                    'name'
                ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:business_categories,id',
            'name' => 'required|string',
        ]);

        $subUnit = BusinessSubUnit::create($request->all());
        return response()->json($subUnit, 201);
    }

    public function show($id)
    {
        return response()->json(BusinessSubUnit::with([
            'category' => function ($query) {
                $query->select('id', 'name');
            }
        ])->findOrFail($id, [
                    'id',
                    'category_id',
                    'name'
                ]));
    }

    public function update(Request $request, $id)
    {
        $subUnit = BusinessSubUnit::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:business_categories,id',
            'name' => 'required|string',
        ]);

        $subUnit->update($request->all());
        return response()->json($subUnit);
    }

    public function destroy($id)
    {
        $subUnit = BusinessSubUnit::findOrFail($id);
        $subUnit->delete();
        return response()->json(null, 204);
    }
}

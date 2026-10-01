<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSectionHeaderRequest;
use App\Models\SectionHeader;

class SectionHeaderController extends Controller
{
    public const KEYS = ['value_props', 'process', 'objection', 'services', 'portfolio', 'pricing', 'faq', 'about', 'team'];

    public function index()
    {
        return response()->json(['data' => SectionHeader::orderBy('section_key')->get()]);
    }

    public function update(UpdateSectionHeaderRequest $request, string $sectionKey)
    {
        if (! in_array($sectionKey, self::KEYS, true)) {
            return response()->json(['error' => ['message' => 'Section tidak ditemukan.']], 404);
        }
        $row = SectionHeader::where('section_key', $sectionKey)->first();
        if (! $row) {
            return response()->json(['error' => ['message' => 'Section tidak ditemukan.']], 404);
        }
        $row->update($request->validated());

        return response()->json(['data' => $row->fresh()]);
    }
}

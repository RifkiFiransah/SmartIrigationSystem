<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function index()
    {
        try {
            $nodes = Node::all();
            return response()->json([
                'success' => true,
                'data' => $nodes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $node = Node::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $node
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'node_id' => 'required|integer',
                'group' => 'nullable|string|max:10',
                'kode_perlakuan' => 'nullable|string|max:20',
                'lokasi' => 'nullable|string|max:100',
                'keterangan' => 'nullable|string',
            ]);

            $node = Node::create($data);
            return response()->json([
                'success' => true,
                'data' => $node
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $node = Node::findOrFail($id);
            $data = $request->validate([
                'node_id' => 'sometimes|integer',
                'group' => 'nullable|string|max:10',
                'kode_perlakuan' => 'nullable|string|max:20',
                'lokasi' => 'nullable|string|max:100',
                'keterangan' => 'nullable|string',
            ]);

            $node->update($data);
            return response()->json([
                'success' => true,
                'data' => $node
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $node = Node::findOrFail($id);
            $node->delete();
            return response()->json([
                'success' => true,
                'message' => 'Node deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
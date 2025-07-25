<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourtController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/courts",
     *     tags={"Courts"},
     *     summary="Dapatkan semua lapangan aktif",
     *     description="Mengambil daftar semua lapangan badminton yang memiliki status aktif",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil data lapangan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Courts retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Court")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $courts = Court::active()->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Courts retrieved successfully',
                'data' => $courts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving courts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courts/{id}",
     *     tags={"Courts"},
     *     summary="Dapatkan detail lapangan",
     *     description="Mengambil detail lapangan badminton berdasarkan ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID lapangan",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil detail lapangan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Court retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Court")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lapangan tidak ditemukan",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $court = Court::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Court retrieved successfully',
                'data' => $court
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Court not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/courts",
     *     tags={"Courts"},
     *     summary="Buat lapangan baru",
     *     description="Membuat lapangan badminton baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price_per_hour"},
     *             @OA\Property(property="name", type="string", example="Lapangan A", description="Nama lapangan"),
     *             @OA\Property(property="description", type="string", example="Lapangan premium dengan lantai kayu", description="Deskripsi lapangan"),
     *             @OA\Property(property="price_per_hour", type="number", format="decimal", example=50000, description="Harga per jam"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active", description="Status lapangan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lapangan berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Court created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Court")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price_per_hour' => 'required|numeric|min:0',
                'status' => 'in:active,inactive'
            ]);

            $court = Court::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Court created successfully',
                'data' => $court
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating court',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/courts/{id}",
     *     tags={"Courts"},
     *     summary="Update lapangan",
     *     description="Mengupdate data lapangan badminton",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID lapangan",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Lapangan A Updated", description="Nama lapangan"),
     *             @OA\Property(property="description", type="string", example="Deskripsi lapangan yang diupdate", description="Deskripsi lapangan"),
     *             @OA\Property(property="price_per_hour", type="number", format="decimal", example=60000, description="Harga per jam"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active", description="Status lapangan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lapangan berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Court updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Court")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lapangan tidak ditemukan",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $court = Court::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price_per_hour' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:active,inactive'
            ]);

            $court->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Court updated successfully',
                'data' => $court
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating court',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/courts/{id}",
     *     tags={"Courts"},
     *     summary="Hapus lapangan",
     *     description="Menghapus lapangan badminton",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID lapangan",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lapangan berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Court deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lapangan tidak ditemukan",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $court = Court::findOrFail($id);
            $court->delete();

            return response()->json([
                'success' => true,
                'message' => 'Court deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting court',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
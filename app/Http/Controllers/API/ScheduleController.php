<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/schedules",
     *     tags={"Schedules"},
     *     summary="Dapatkan semua jadwal",
     *     description="Mengambil semua jadwal lapangan badminton yang diurutkan berdasarkan tanggal dan waktu mulai",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil data jadwal",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedules retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Schedule")
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
            $schedules = Schedule::with('court')->orderBy('date')->orderBy('start_time')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Schedules retrieved successfully',
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/schedules-available",
     *     tags={"Schedules"},
     *     summary="Dapatkan jadwal yang tersedia",
     *     description="Mengambil semua jadwal lapangan yang memiliki status 'available' untuk booking",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil jadwal yang tersedia",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Available schedules retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Schedule")
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
    public function getAvailable(): JsonResponse
    {
        try {
            $schedules = Schedule::with('court')
                               ->available()
                               ->orderBy('date')
                               ->orderBy('start_time')
                               ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Available schedules retrieved successfully',
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving available schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/schedules",
     *     tags={"Schedules"},
     *     summary="Buat jadwal baru",
     *     description="Membuat jadwal baru untuk lapangan badminton",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"court_id", "date", "start_time", "end_time"},
     *             @OA\Property(property="court_id", type="integer", example=1, description="ID lapangan"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-01-15", description="Tanggal jadwal"),
     *             @OA\Property(property="start_time", type="string", format="time", example="09:00", description="Waktu mulai (format HH:mm)"),
     *             @OA\Property(property="end_time", type="string", format="time", example="11:00", description="Waktu selesai (format HH:mm)"),
     *             @OA\Property(property="status", type="string", enum={"available", "booked"}, example="available", description="Status jadwal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Jadwal berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedule created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Schedule")
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
                'court_id' => 'required|exists:courts,id',
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'status' => 'in:available,booked'
            ]);

            $schedule = Schedule::create($validated);
            $schedule->load('court');

            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully',
                'data' => $schedule
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/schedules/{id}",
     *     tags={"Schedules"},
     *     summary="Update jadwal",
     *     description="Mengupdate data jadwal lapangan badminton",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID jadwal",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="court_id", type="integer", example=1, description="ID lapangan"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-01-15", description="Tanggal jadwal"),
     *             @OA\Property(property="start_time", type="string", format="time", example="09:00", description="Waktu mulai (format HH:mm)"),
     *             @OA\Property(property="end_time", type="string", format="time", example="11:00", description="Waktu selesai (format HH:mm)"),
     *             @OA\Property(property="status", type="string", enum={"available", "booked"}, example="available", description="Status jadwal")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jadwal berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedule updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Schedule")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Jadwal tidak ditemukan",
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
            $schedule = Schedule::findOrFail($id);
            
            $validated = $request->validate([
                'court_id' => 'sometimes|exists:courts,id',
                'date' => 'sometimes|date',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i|after:start_time',
                'status' => 'sometimes|in:available,booked'
            ]);

            $schedule->update($validated);
            $schedule->load('court');

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/schedules/{id}",
     *     tags={"Schedules"},
     *     summary="Hapus jadwal",
     *     description="Menghapus jadwal lapangan badminton",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID jadwal",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jadwal berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Schedule deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Jadwal tidak ditemukan",
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
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
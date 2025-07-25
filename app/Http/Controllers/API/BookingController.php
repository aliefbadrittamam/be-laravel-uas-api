<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/bookings",
     *     tags={"Bookings"},
     *     summary="Dapatkan semua booking",
     *     description="Mengambil semua data booking lapangan badminton beserta informasi jadwal dan lapangan",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil data booking",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bookings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Booking")
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
            $bookings = Booking::with(['schedule.court'])->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving bookings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Dapatkan detail booking",
     *     description="Mengambil detail booking berdasarkan ID beserta informasi jadwal dan lapangan",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID booking",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil detail booking",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Booking")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking tidak ditemukan",
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
            $booking = Booking::with(['schedule.court'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Booking retrieved successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings",
     *     tags={"Bookings"},
     *     summary="Buat booking baru",
     *     description="Membuat booking baru untuk lapangan badminton. Sistem akan otomatis menghitung total harga berdasarkan durasi dan harga per jam lapangan.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"schedule_id", "customer_name", "customer_phone"},
     *             @OA\Property(property="schedule_id", type="integer", example=1, description="ID jadwal yang akan dibooking"),
     *             @OA\Property(property="customer_name", type="string", example="John Doe", description="Nama customer"),
     *             @OA\Property(property="customer_phone", type="string", example="08123456789", description="Nomor telepon customer"),
     *             @OA\Property(property="customer_email", type="string", format="email", example="john@email.com", description="Email customer (opsional)"),
     *             @OA\Property(property="notes", type="string", example="Booking untuk latihan", description="Catatan tambahan (opsional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Booking")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error atau jadwal tidak tersedia",
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
        DB::beginTransaction();
        
        try {
            // Validate input
            $validated = $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'notes' => 'nullable|string'
            ]);

            // Check if schedule is available
            $schedule = Schedule::with('court')
                              ->where('id', $validated['schedule_id'])
                              ->where('status', 'available')
                              ->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not available or not found',
                    'error' => 'The selected schedule is not available for booking'
                ], 422);
            }

            // Calculate total price with better error handling
            try {
                $duration = $schedule->getDurationInHours();
                $validated['total_price'] = $schedule->court->price_per_hour * $duration;
            } catch (\Exception $e) {
                Log::error('Error calculating price: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error calculating price',
                    'error' => 'Could not calculate total price for this booking'
                ], 500);
            }

            // Create booking
            $booking = Booking::create($validated);

            // Update schedule status to booked
            $schedule->update(['status' => 'booked']);

            // Load relationships for response
            $booking->load(['schedule.court']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating booking: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Update booking",
     *     description="Mengupdate data booking (data customer dan catatan saja, tidak bisa mengubah jadwal)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID booking",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_name", type="string", example="John Doe Updated", description="Nama customer"),
     *             @OA\Property(property="customer_phone", type="string", example="08123456790", description="Nomor telepon customer"),
     *             @OA\Property(property="customer_email", type="string", format="email", example="john.updated@email.com", description="Email customer"),
     *             @OA\Property(property="notes", type="string", example="Catatan yang diupdate", description="Catatan tambahan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Booking")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking tidak ditemukan",
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
            $booking = Booking::findOrFail($id);
            
            $validated = $request->validate([
                'customer_name' => 'sometimes|string|max:255',
                'customer_phone' => 'sometimes|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'notes' => 'nullable|string'
            ]);

            $booking->update($validated);
            $booking->load(['schedule.court']);

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Hapus booking",
     *     description="Menghapus booking dan mengubah status jadwal kembali menjadi 'available'",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID booking",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking tidak ditemukan",
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
        DB::beginTransaction();
        
        try {
            $booking = Booking::with('schedule')->findOrFail($id);
            
            // Update schedule back to available
            $booking->schedule->update(['status' => 'available']);
            
            // Delete booking
            $booking->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
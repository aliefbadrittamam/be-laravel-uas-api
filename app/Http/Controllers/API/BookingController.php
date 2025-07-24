<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/bookings",
     *     tags={"Bookings"},
     *     summary="Ambil semua booking",
     *     description="Mengambil daftar semua booking dengan opsi filter",
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter berdasarkan tanggal booking",
     *         @OA\Schema(type="string", format="date", example="2024-01-15")
     *     ),
     *     @OA\Parameter(
     *         name="court_id",
     *         in="query",
     *         description="Filter berdasarkan ID lapangan",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status booking",
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "cancelled"}, example="confirmed")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mengambil data booking",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/Booking")
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Booking::with('court');
            
            if ($request->has('date')) {
                $query->byDate($request->date);
            }
            
            if ($request->has('court_id')) {
                $query->where('court_id', $request->court_id);
            }
            
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            $bookings = $query->orderBy('booking_date', 'desc')
                            ->orderBy('start_time', 'asc')
                            ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings",
     *     tags={"Bookings"},
     *     summary="Buat booking baru",
     *     description="Membuat pemesanan lapangan baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="court_id", type="integer", example=1),
     *             @OA\Property(property="customer_name", type="string", example="John Doe"),
     *             @OA\Property(property="customer_phone", type="string", example="08123456789"),
     *             @OA\Property(property="customer_email", type="string", format="email", example="john@email.com"),
     *             @OA\Property(property="booking_date", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="start_time", type="string", format="time", example="09:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="11:00"),
     *             @OA\Property(property="notes", type="string", example="Booking untuk latihan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking berhasil dibuat",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(property="data", ref="#/components/schemas/Booking")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Lapangan tidak tersedia",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'court_id' => 'required|exists:courts,id',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'booking_date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'notes' => 'nullable|string'
            ]);

            if (!$this->isCourtAvailable($validated['court_id'], $validated['booking_date'], 
                                        $validated['start_time'], $validated['end_time'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Court is not available for the selected time slot'
                ], 409);
            }

            $court = Court::findOrFail($validated['court_id']);
            $duration = $this->calculateDuration($validated['start_time'], $validated['end_time']);
            $validated['total_price'] = $court->price_per_hour * $duration;

            $booking = Booking::create($validated);
            $booking->load('court');

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $booking
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/bookings/check-availability",
     *     tags={"Bookings"},
     *     summary="Cek ketersediaan lapangan",
     *     description="Mengecek apakah lapangan tersedia pada waktu tertentu",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="court_id", type="integer", example=1),
     *             @OA\Property(property="booking_date", type="string", format="date", example="2024-01-15"),
     *             @OA\Property(property="start_time", type="string", format="time", example="09:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="11:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ketersediaan berhasil dicek",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="available", type="boolean", example=true)
     *                     )
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'court_id' => 'required|exists:courts,id',
                'booking_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time'
            ]);

            $isAvailable = $this->isCourtAvailable(
                $validated['court_id'],
                $validated['booking_date'],
                $validated['start_time'],
                $validated['end_time']
            );

            return response()->json([
                'success' => true,
                'message' => 'Availability checked successfully',
                'data' => [
                    'available' => $isAvailable
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper methods (same as before)
    private function isCourtAvailable($courtId, $date, $startTime, $endTime, $excludeBookingId = null): bool
    {
        $query = Booking::where('court_id', $courtId)
                       ->where('booking_date', $date)
                       ->active()
                       ->where(function ($q) use ($startTime, $endTime) {
                           $q->whereBetween('start_time', [$startTime, $endTime])
                             ->orWhereBetween('end_time', [$startTime, $endTime])
                             ->orWhere(function ($q2) use ($startTime, $endTime) {
                                 $q2->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                             });
                       });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }

    private function calculateDuration($startTime, $endTime): float
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);
        
        return $end->diffInHours($start);
    }
}
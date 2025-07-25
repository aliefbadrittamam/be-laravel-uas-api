<?php

namespace App\Http\Controllers\API;

/**
 * @OA\Schema(
 *     schema="Court",
 *     type="object",
 *     title="Court",
 *     description="Model lapangan badminton",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Lapangan A"),
 *     @OA\Property(property="description", type="string", example="Lapangan premium dengan lantai kayu"),
 *     @OA\Property(property="price_per_hour", type="number", format="decimal", example=50000),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Schedule",
 *     type="object",
 *     title="Schedule",
 *     description="Model jadwal lapangan",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="court_id", type="integer", example=1),
 *     @OA\Property(property="date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="start_time", type="string", format="time", example="09:00"),
 *     @OA\Property(property="end_time", type="string", format="time", example="11:00"),
 *     @OA\Property(property="status", type="string", enum={"available", "booked"}, example="available"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
 *     @OA\Property(property="court", ref="#/components/schemas/Court")
 * )
 * 
 * @OA\Schema(
 *     schema="Booking",
 *     type="object",
 *     title="Booking",
 *     description="Model pemesanan lapangan",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="schedule_id", type="integer", example=1),
 *     @OA\Property(property="customer_name", type="string", example="John Doe"),
 *     @OA\Property(property="customer_phone", type="string", example="08123456789"),
 *     @OA\Property(property="customer_email", type="string", example="john@email.com"),
 *     @OA\Property(property="total_price", type="number", format="decimal", example=100000),
 *     @OA\Property(property="notes", type="string", example="Booking untuk latihan"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T10:00:00Z"),
 *     @OA\Property(
 *         property="schedule",
 *         allOf={
 *             @OA\Schema(ref="#/components/schemas/Schedule")
 *         }
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     title="API Response",
 *     description="Standard API response format",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operation completed successfully"),
 *     @OA\Property(property="data", type="object")
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Error Response",
 *     description="Error response format",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error occurred"),
 *     @OA\Property(property="error", type="string", example="Detailed error message")
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     title="Validation Error Response",
 *     description="Validation error response format",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\Property(
 *             property="field_name",
 *             type="array",
 *             @OA\Items(type="string", example="The field is required.")
 *         )
 *     )
 * )
 */
class SwaggerSchemas
{
    // This class is only for documentation purposes
}
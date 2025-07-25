<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Badminton Court Booking API",
 *     version="1.0.0",
 *     description="API untuk sistem pemesanan lapangan badminton",
 *     @OA\Contact(
 *         email="admin@badminton-booking.com",
 *         name="Badminton Booking Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Development Server"
 * )
 * 
 * @OA\PathItem(path="/api/v1")
 * 
 * @OA\Tag(
 *     name="Courts",
 *     description="Operasi yang berkaitan dengan lapangan badminton"
 * )
 * 
 * @OA\Tag(
 *     name="Schedules",
 *     description="Operasi yang berkaitan dengan jadwal lapangan"
 * )
 * 
 * @OA\Tag(
 *     name="Bookings",
 *     description="Operasi yang berkaitan dengan pemesanan lapangan"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
<?php

namespace App\Http\Resources;

/**
 * @OA\Info(
 *     title="Koumbaya Marketplace API",
 *     version="1.0.0",
 *     description="API pour la plateforme Koumbaya - Marketplace et loteries au Gabon"
 * )
 * @OA\Server(
 *     url="https://koumbaya.com/api",
 *     description="Serveur de production"
 * )
 * @OA\Server(
 *     url="http://localhost/koumbaya_web/api",
 *     description="Serveur de développement"
 * )
 */

/**
 * @OA\Schema(
 *     schema="ProductSummary",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="price", type="number", format="decimal"),
 *     @OA\Property(property="image", type="string", nullable=true),
 *     @OA\Property(property="category_id", type="integer")
 * )
 */

/**
 * @OA\Schema(
 *     schema="LotterySummary",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="lottery_number", type="string"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="ticket_price", type="number", format="decimal"),
 *     @OA\Property(property="max_tickets", type="integer"),
 *     @OA\Property(property="sold_tickets", type="integer")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Lottery",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="lottery_number", type="string"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="product_id", type="integer"),
 *     @OA\Property(property="ticket_price", type="number", format="decimal"),
 *     @OA\Property(property="max_tickets", type="integer"),
 *     @OA\Property(property="sold_tickets", type="integer"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="draw_date", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="winner_user_id", type="integer", nullable=true),
 *     @OA\Property(property="winning_ticket_number", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

/**
 * @OA\Schema(
 *     schema="LotteryDetails",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Lottery"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="product", ref="#/components/schemas/ProductSummary"),
 *             @OA\Property(property="winner", ref="#/components/schemas/Winner", nullable=true)
 *         )
 *     }
 * )
 */

/**
 * @OA\Schema(
 *     schema="Winner",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="first_name", type="string"),
 *     @OA\Property(property="last_name", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="phone", type="string", nullable=true)
 * )
 */

/**
 * @OA\Schema(
 *     schema="DrawHistory",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="lottery_id", type="integer"),
 *     @OA\Property(property="drawn_at", type="string", format="date-time"),
 *     @OA\Property(property="winning_ticket_number", type="string"),
 *     @OA\Property(property="winner_user_id", type="integer"),
 *     @OA\Property(property="draw_method", type="string"),
 *     @OA\Property(property="verification_hash", type="string", nullable=true)
 * )
 */

/**
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="data", type="object", nullable=true)
 * )
 */

/**
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="from", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="to", type="integer"),
 *     @OA\Property(property="total", type="integer")
 * )
 */

class ApiSchemas
{
    // Cette classe contient uniquement les annotations Swagger
}
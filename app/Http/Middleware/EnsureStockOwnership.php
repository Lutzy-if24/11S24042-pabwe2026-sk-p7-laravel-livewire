<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Features\Stock\Models\Stock;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureStockOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $stockParam = $request->route('stock');

        if ($stockParam !== null) {
            $stock = $stockParam instanceof Stock
                ? $stockParam
                : Stock::find($stockParam);

            if ($stock && $stock->user_id !== $user->id) {
                abort(403, 'Akses ditolak: Anda bukan pemilik stok ini.');
            }
        }

        return $next($request);
    }
}

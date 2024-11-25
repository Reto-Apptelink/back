<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function getOrders(Request $request)
    {
        try {
            $request->validate([
                'query' => 'nullable|string|max:255',
                'queryDate' => [
                    'nullable',
                    'string',
                    'regex:/^\d{2}\/\d{2}\/\d{2}(,\d{2}\/\d{2}\/\d{2})?$/',
                ],
                'pagination' => 'nullable|boolean',
                'per_page' => 'nullable|integer|min:1',
            ], [
                'query.string' => 'El parámetro de búsqueda debe ser una cadena de texto válida.',
                'queryDate.regex' => 'El parámetro de fechas debe estar en el formato dd/mm/yy o dd/mm/yy,dd/mm/yy.',
                'pagination.boolean' => 'El parámetro de paginación debe ser true o false.',
                'per_page.integer' => 'El número de elementos por página debe ser un entero positivo.',
            ]);

            $paginate = filter_var($request->input('pagination'), FILTER_VALIDATE_BOOLEAN);
            $query = Order::with(['customer', 'details.product'])->orderBy('id', 'desc');

            // Filtro por búsqueda general (order_id, customer_name, product_name)
            if ($request->filled('query')) {
                $searchQuery = $request->input('query');
                $query->where(function ($q) use ($searchQuery) {
                    // Validar si el valor es numérico antes de buscar en 'id'
                    if (is_numeric($searchQuery)) {
                        $q->where('id', $searchQuery); // Número de orden exacto
                    }

                    $q->orWhereHas('customer', function ($subQuery) use ($searchQuery) {
                        $subQuery->where('name', 'like', '%' . $searchQuery . '%');
                    })->orWhereHas('details.product', function ($subQuery) use ($searchQuery) {
                        $subQuery->where('name', 'like', '%' . $searchQuery . '%');
                    });
                });
            }

            // Filtro por rango de fechas
            if ($request->filled('queryDate')) {
                $dates = explode(',', $request->input('queryDate'));
                try {
                    if (count($dates) == 2) {
                        $startDate = Carbon::createFromFormat('d/m/y', trim($dates[0]))->startOfDay();
                        $endDate = Carbon::createFromFormat('d/m/y', trim($dates[1]))->endOfDay();
                    } else {
                        $startDate = Carbon::createFromFormat('d/m/y', trim($dates[0]))->startOfDay();
                        $endDate = $startDate->copy()->endOfDay();
                    }
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    throw ValidationException::withMessages([
                        'queryDate' => 'El rango de fechas es inválido.',
                    ]);
                }
            }

            // Aplicar paginación
            if ($paginate) {
                $perPage = $request->input('per_page', 15);
                $orders = $query->paginate($perPage);
            } else {
                $orders = $query->get();
            }

            // Mapear datos de las órdenes
            $mappedOrders = $orders->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'customer_name' => optional($order->customer)->name,
                    'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                    'details' => $order->details->map(function ($detail) {
                        return [
                            'detail_id' => $detail->id,
                            'product_id' => $detail->product_id,
                            'product_name' => optional($detail->product)->name,
                            'quantity' => $detail->quantity,
                            'unit_price' => $detail->unit_price,
                            'subtotal' => $detail->quantity * $detail->unit_price,
                        ];
                    }),
                ];
            });

            // Respuesta con o sin paginación
            $response = [
                'code' => 200,
                'success' => true,
                'message' => $orders->isNotEmpty() ? 'Órdenes encontradas.' : 'No se encontraron órdenes.',
                'data' => $mappedOrders,
            ];

            if ($paginate) {
                $response['pagination'] = [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'first_page_url' => $orders->url(1),
                    'last_page_url' => $orders->url($orders->lastPage()),
                    'next_page_url' => $orders->nextPageUrl(),
                    'prev_page_url' => $orders->previousPageUrl(),
                    'path' => $orders->path(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ];
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hubo un problema al obtener las órdenes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function create(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'order_date' => 'required|date|before_or_equal:today',
            'tax_rate' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'order_details' => 'required|array',
            'order_details.*.product_id' => 'required|exists:products,id',
            'order_details.*.quantity' => 'required|integer|min:1',
        ], [
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'order_date.before_or_equal' => 'La fecha del pedido no puede ser futura.',
            'order_details.*.product_id.exists' => 'El producto seleccionado no existe.',
            'order_details.*.quantity.min' => 'La cantidad debe ser al menos 1.',
        ]);

        DB::beginTransaction();

        try {

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'user_id' => $request->user_id,
                'order_date' => $request->order_date,
                'tax_rate' => $request->tax_rate,
                'discount' => $request->discount,
                'total_amount' => 0, // Valor temporal
            ]);

            $totalAmount = 0;

            // Crear los detalles de la orden
            foreach ($request->order_details as $detail) {
                $product = Product::findOrFail($detail['product_id']);

                // Verificar stock disponible
                if ($product->stock_quantity < $detail['quantity']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'La cantidad solicitada para el producto ' . $product->name . ' excede el stock disponible.',
                    ], 422);
                }

                // Crear el detalle de la orden
                $subtotal = $detail['quantity'] * $product->price;
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);

                // Actualizar el total de la orden
                $totalAmount += $subtotal;

                // Reducir el stock del producto
                $product->decrement('stock_quantity', $detail['quantity']);
            }

            // Calcular el total con impuestos y descuento
            $totalWithTax = $totalAmount * (1 + $request->tax_rate / 100);
            $totalFinal = $totalWithTax - $request->discount;

            // Actualizar el total en la orden
            $order->update(['total_amount' => $totalFinal]);

            DB::commit(); // Confirmar la transacción

            return response()->json([
                'status' => 'success',
                'message' => 'Orden registrada con éxito.',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al registrar la orden. Intente de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

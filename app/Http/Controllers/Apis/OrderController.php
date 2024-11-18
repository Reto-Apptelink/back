<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
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

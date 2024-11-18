<?php

namespace App\Http\Controllers\Apis;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProductController extends Controller
{
    public function getProductCatalog(Request $request)
    {
        try {
            // Validación de los parámetros de entrada
            $request->validate([
                'query' => 'nullable|string',
                'queryDate' => [
                    'nullable',
                    'string',
                    'regex:/^\d{2}\/\d{2}\/\d{2}(,\d{2}\/\d{2}\/\d{2})?$/',
                ],
                'pagination' => 'nullable|boolean',  // Validamos el parámetro de paginación como booleano
            ], [
                'query.string' => 'El parámetro de búsqueda debe ser una cadena de texto.',
                'queryDate.string' => 'El parámetro de fechas debe ser una cadena de texto válida.',
                'queryDate.regex' => 'El parámetro de fechas debe estar en el formato dd/mm/yy o dd/mm/yy,dd/mm/yy.',
                'pagination.boolean' => 'El parámetro de paginación debe ser un valor booleano (true o false).',
            ]);

            $query = Product::query();

            // Filtro por el parámetro 'query' (name, description, price, stock_quantity)
            if ($request->has('query') && !empty($request->input('query'))) {
                $queryParam = $request->input('query');
                $query->where(function ($q) use ($queryParam) {
                    $q->where('name', 'like', '%' . $queryParam . '%')
                        ->orWhere('description', 'like', '%' . $queryParam . '%')
                        ->orWhere('price', 'like', '%' . $queryParam . '%')
                        ->orWhere('stock_quantity', 'like', '%' . $queryParam . '%');
                });
            }

            // Filtro por el parámetro 'queryDate' (rango de fechas)
            if ($request->has('queryDate') && !empty($request->input('queryDate'))) {
                $dates = explode(',', $request->input('queryDate'));
                if (count($dates) == 2) {
                    $startDate = Carbon::createFromFormat('d/m/y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/y', trim($dates[1]))->endOfDay();
                } else {
                    $startDate = Carbon::createFromFormat('d/m/y', trim($dates[0]))->startOfDay();
                    $endDate = $startDate->copy()->endOfDay();
                }
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Comprobamos el parámetro de paginación y lo tratamos como booleano
            $pagination = filter_var($request->input('pagination', 'true'), FILTER_VALIDATE_BOOLEAN);

            if ($pagination) {
                $products = $query->paginate(10);  // Paginación activada
                $response = [
                    'status' => 'success',
                    'products' => [
                        'data' => $products->items(),
                        'pagination' => [
                            'current_page' => $products->currentPage(),
                            'total' => $products->total(),
                            'per_page' => $products->perPage(),
                            'last_page' => $products->lastPage(),
                            'first_page_url' => $products->url(1),
                            'last_page_url' => $products->url($products->lastPage()),
                            'next_page_url' => $products->nextPageUrl(),
                            'prev_page_url' => $products->previousPageUrl(),
                            'path' => $products->path(),
                            'from' => $products->firstItem(),
                            'to' => $products->lastItem(),
                        ],
                    ],
                ];
            } else {
                // Si la paginación está desactivada, devolvemos todos los productos sin paginar
                $products = $query->get();  // No paginamos
                $response = [
                    'status' => 'success',
                    'products' => $products,
                ];
            }

            return response()->json($response, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al procesar la solicitud.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


    public function create(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'price' => 'required|numeric|min:0.01',
                'stock_quantity' => 'required|integer|min:0',
                'description' => 'nullable|string|max:255',
            ], [
                'name.required' => 'El nombre del producto es obligatorio.',
                'name.string' => 'El nombre del producto debe ser una cadena de texto.',
                'name.max' => 'El nombre del producto no puede superar los 100 caracteres.',
                'price.required' => 'El precio del producto es obligatorio.',
                'price.numeric' => 'El precio debe ser un número.',
                'price.min' => 'El precio debe ser mayor a cero.',
                'stock_quantity.required' => 'La cantidad en stock es obligatoria.',
                'stock_quantity.integer' => 'La cantidad en stock debe ser un número entero.',
                'stock_quantity.min' => 'La cantidad en stock no puede ser negativa.',
                'description.string' => 'La descripción debe ser una cadena de texto.',
                'description.max' => 'La descripción no puede superar los 255 caracteres.',
            ]);

            // Creación del producto
            $product = Product::create([
                'name' => $request->name,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'description' => $request->description,
            ]);

            // Respuesta exitosa
            return response()->json([
                'status' => 'success',
                'message' => 'Producto creado con éxito.',
                'product' => $product,
            ], 201);
        } catch (ValidationException $e) {
            // Si ocurre un error de validación, se retornan los errores
            return response()->json([
                'status' => 'error',
                'message' => 'Error en la validación de los datos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Capturar cualquier otro error (por ejemplo, base de datos)
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al crear el producto. Intente de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validación personalizada
        try {
            // Validamos los datos recibidos
            $request->validate([
                'name' => 'required|string|max:100',
                'price' => 'required|numeric|min:0.01',
                'stock_quantity' => 'required|integer|min:0',
                'description' => 'nullable|string|max:255',
            ], [
                'name.required' => 'El nombre del producto es obligatorio.',
                'name.string' => 'El nombre del producto debe ser una cadena de texto.',
                'name.max' => 'El nombre del producto no puede superar los 100 caracteres.',
                'price.required' => 'El precio del producto es obligatorio.',
                'price.numeric' => 'El precio debe ser un número.',
                'price.min' => 'El precio debe ser mayor a cero.',
                'stock_quantity.required' => 'La cantidad en stock es obligatoria.',
                'stock_quantity.integer' => 'La cantidad en stock debe ser un número entero.',
                'stock_quantity.min' => 'La cantidad en stock no puede ser negativa.',
                'description.string' => 'La descripción debe ser una cadena de texto.',
                'description.max' => 'La descripción no puede superar los 255 caracteres.',
            ]);

            // Buscar el producto por su id
            $product = Product::find($id);

            // Si no se encuentra el producto, retornar un error
            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto no encontrado.',
                ], 404);
            }

            // Actualizar los campos del producto
            $product->update([
                'name' => $request->name,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'description' => $request->description,
            ]);

            // Respuesta exitosa
            return response()->json([
                'status' => 'success',
                'message' => 'Producto actualizado con éxito.',
                'product' => $product,
            ], 200);
        } catch (ValidationException $e) {
            // Si ocurre un error de validación, se retornan los errores
            return response()->json([
                'status' => 'error',
                'message' => 'Error en la validación de los datos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Capturar cualquier otro error (por ejemplo, base de datos)
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al actualizar el producto. Intente de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Buscar el producto por su id
            $product = Product::find($id);

            // Si no se encuentra el producto, retornar un error 404
            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto no encontrado.',
                ], 404);
            }

            // Eliminar el producto
            $product->delete();

            // Respuesta exitosa
            return response()->json([
                'status' => 'success',
                'message' => 'Producto eliminado con éxito.',
            ], 200);
        } catch (\Exception $e) {
            // Si ocurre un error inesperado, capturamos el error y retornamos un mensaje de error
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al eliminar el producto. Intente de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

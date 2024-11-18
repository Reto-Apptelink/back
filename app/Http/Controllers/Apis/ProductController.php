<?php

namespace App\Http\Controllers\Apis;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // Corregir
    /* public function getProductCatalog(Request $request)
    {
        try {
            $request->validate([
                'query' => 'nullable|string',
                'queryDate' => 'nullable|string',
            ], [
                'query.string' => 'El parámetro de búsqueda debe ser una cadena de texto.',
                'queryDate.string' => 'El parámetro de fechas debe ser una cadena de texto válida.',
            ]);

            // Consultar productos aplicando filtros
            $query = Product::query();

            // Filtro por el parámetro 'query' (name, description, price, stock_quantity)
            if ($request->has('query') && !empty($request->query)) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->query . '%')
                      ->orWhere('description', 'like', '%' . $request->query . '%')
                      ->orWhere('price', 'like', '%' . $request->query . '%')
                      ->orWhere('stock_quantity', 'like', '%' . $request->query . '%');
                });
            }

            // Filtro por el parámetro 'queryDate' (rango de fechas)
            if ($request->has('queryDate') && !empty($request->queryDate)) {
                // Decodificar y obtener el rango de fechas desde 'queryDate'
                $dates = json_decode($request->queryDate, true);
                if (isset($dates['start_date']) && isset($dates['end_date'])) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($dates['start_date'])->startOfDay(),
                        Carbon::parse($dates['end_date'])->endOfDay(),
                    ]);
                }
            }

            // Paginación: 10 resultados por página
            $products = $query->paginate(10);

            // Respuesta exitosa con productos paginados
            return response()->json([
                'status' => 'success',
                'products' => $products,
            ], 200);

        } catch (ValidationException $e) {
            // En caso de error de validación, retornar mensaje de error
            return response()->json([
                'status' => 'error',
                'message' => 'Error en la validación de los datos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // En caso de otros errores generales
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al obtener los productos. Intente de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    } */

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

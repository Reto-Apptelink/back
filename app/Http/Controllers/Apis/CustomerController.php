<?php

namespace App\Http\Controllers\Apis;

use App\Models\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CustomerController extends Controller
{
    public function getCustomers(Request $request)
    {
        try {
            // Validación de los parámetros de entrada
            $request->validate([
                'query' => 'nullable|string',
                'pagination' => 'nullable|boolean',
            ], [
                'query.string' => 'El parámetro de búsqueda debe ser una cadena de texto.',
                'pagination.boolean' => 'El parámetro de paginación debe ser un valor booleano (true 1 o false 0).',
            ]);

            $query = Customer::query();

            // Filtro por el parámetro 'query' (name, ruc)
            if ($request->has('query') && !empty($request->input('query'))) {
                $queryParam = $request->input('query');
                $query->where(function ($q) use ($queryParam) {
                    $q->where('name', 'like', '%' . $queryParam . '%')
                        ->orWhere('ruc', 'like', '%' . $queryParam . '%');
                });
            }

            // Comprobamos el parámetro de paginación y lo tratamos como booleano
            $pagination = filter_var($request->input('pagination', 'true'), FILTER_VALIDATE_BOOLEAN);

            if ($pagination) {
                $customers = $query->paginate(10);
                $response = [
                    'success' => true,
                    'data' => [
                        'customers' => $customers->items(),
                        'pagination' => [
                            'current_page' => $customers->currentPage(),
                            'total' => $customers->total(),
                            'per_page' => $customers->perPage(),
                            'last_page' => $customers->lastPage(),
                            'first_page_url' => $customers->url(1),
                            'last_page_url' => $customers->url($customers->lastPage()),
                            'next_page_url' => $customers->nextPageUrl(),
                            'prev_page_url' => $customers->previousPageUrl(),
                            'path' => $customers->path(),
                            'from' => $customers->firstItem(),
                            'to' => $customers->lastItem(),
                        ],
                    ],
                ];
            } else {
                $customers = $query->get();
                $response = [
                    'success' => true,
                    'customers' => $customers,
                ];
            }

            return response()->json($response, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
                'ruc' => 'required|numeric|unique:customers,ruc',
                'email' => 'required|email|max:255|unique:customers,email',
                'phone' => 'nullable|string|max:25',
            ], [
                'name.required' => 'El nombre del cliente es obligatorio.',
                'name.string' => 'El nombre del cliente debe ser una cadena de texto.',
                'name.max' => 'El nombre del cliente no puede superar los 100 caracteres.',
                'ruc.required' => 'El RUC del cliente es obligatorio.',
                'ruc.numeric' => 'El RUC debe ser un número.',
                'ruc.unique' => 'El RUC proporcionado ya está registrado.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico debe ser válido.',
                'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
                'email.unique' => 'El correo electrónico proporcionado ya está registrado.',
                'phone.string' => 'El teléfono debe ser una cadena de texto.',
                'phone.max' => 'El teléfono no puede superar los 25 caracteres.',
            ]);

            $product = Customer::create([
                'name' => $request->name,
                'ruc' => $request->ruc,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cliente creado con éxito.',
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
                'message' => 'Hubo un problema al crear el cliente. Intente de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validación de los datos de entrada
            $request->validate([
                'name' => 'required|string|max:100',
                'ruc' => 'required|string|unique:customers,ruc,' . $id,
                'email' => 'required|email|max:255|unique:customers,email,' . $id,
                'phone' => 'nullable|string|max:25',
            ], [
                'name.required' => 'El nombre del cliente es obligatorio.',
                'name.string' => 'El nombre del cliente debe ser una cadena de texto.',
                'name.max' => 'El nombre del cliente no puede superar los 100 caracteres.',
                'ruc.required' => 'El RUC del cliente es obligatorio.',
                'ruc.string' => 'El RUC debe ser una cadena de texto.',
                'ruc.unique' => 'El RUC proporcionado ya está registrado.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico debe ser válido.',
                'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
                'email.unique' => 'El correo electrónico proporcionado ya está registrado.',
                'phone.string' => 'El teléfono debe ser una cadena de texto.',
                'phone.max' => 'El teléfono no puede superar los 25 caracteres.',
            ]);

            // Buscar al cliente por ID
            $customer = Customer::findOrFail($id); // Retorna 404 si no se encuentra

            // Actualizar los datos del cliente
            $customer->update([
                'name' => $request->name,
                'ruc' => $request->ruc,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            // Responder con éxito
            return response()->json([
                'status' => 'success',
                'message' => 'Cliente actualizado con éxito.',
                'customer' => $customer,
            ], 200);
        } catch (ValidationException $e) {
            // Si ocurre un error de validación, retornar los errores
            return response()->json([
                'status' => 'error',
                'message' => 'Error en la validación de los datos.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Capturar cualquier otro error (por ejemplo, base de datos)
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al actualizar el cliente. Intente de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Buscar el cliente por su ID
            $customer = Customer::find($id);

            if (!$customer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cliente no encontrado.',
                ], 404);
            }

            // Eliminar el cliente
            $customer->delete();

            // Responder con éxito
            return response()->json([
                'status' => 'success',
                'message' => 'Cliente eliminado con éxito.',
            ], 200);
        } catch (\Exception $e) {
            // Capturar cualquier error (por ejemplo, cliente no encontrado)
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un problema al eliminar el cliente. Intente de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use InvalidArgumentException;

class CalcularController extends Controller
{
    public function calcular($operation, $num1, $num2)
    {
        $result = 0;
        $result = match ($operation) {
            'add' => $num1 + $num2,
            'sub' => $num1 - $num2,
            'mul' => $num1 * $num2,
            'div' => $num2 != 0 ? $num1 / $num2 : 'Error: Zero division',
            default => throw new InvalidArgumentException('Invalid operator')
        };
        return response()->json([
            'operation' => $operation,
            'num1' => $num1,
            'num2' => $num2,
            'result' => $result
        ]);
    }

    public function statistics()
    {
        $validated = request()->validate(
            [
                'numbers' => 'required|array|min:2',
                'numbers.*' => 'numeric',
                'operation' => 'required|in:sum,mean'
            ],
            [
                'numbers.required' => 'Debe proporcionar al menos 2 números.',
                'numbers.array' => 'Los números deben ser un array.',
                'numbers.min' => 'Debe proporcionar al menos 2 números.',
                'numbers.*.numeric' => 'Todos los elementos del array deben ser numéricos.',
                'operation.required' => 'La operación es obligatoria.',
                'operation.in' => 'La operación debe ser: sum o mean.'
            ]
        );

        $numbers = $validated['numbers'];
        $operation = $validated['operation'];
        $result = match ($operation) {
            'sum' => array_sum($numbers),
            'mean' => array_sum($numbers) / count($numbers),
        };
        return response()->json(['result' => $result]);
    }
}

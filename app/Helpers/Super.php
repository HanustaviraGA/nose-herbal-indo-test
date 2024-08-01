<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\DataTables;

/**
 * Display a table based on given module or query.
 * @return Renderable
 */
function select_table($queryOrModel)
{
    if (is_string($queryOrModel)) {
        // Execute the raw SQL query and convert the result to a collection
        $results = DB::select($queryOrModel);
        $results = collect($results)->map(function ($item, $key) {
            $item->id = $key + 1; // Add auto-increment ID
            return $item;
        });
        $query = $results;
    } elseif ($queryOrModel instanceof \Illuminate\Database\Eloquent\Model) {
        $query = $queryOrModel->newQuery();
    } elseif ($queryOrModel instanceof \Illuminate\Database\Eloquent\Builder) {
        $query = $queryOrModel;
    } elseif ($queryOrModel instanceof \Illuminate\Support\Collection) {
        if ($queryOrModel->isEmpty()) {
            return DataTables::of($queryOrModel)->make(true);
        }
        // Assuming all items in the collection are instances of the same model
        $model = $queryOrModel->first();
        $query = $model->newQuery()->whereIn($model->getKeyName(), $queryOrModel->pluck($model->getKeyName()));
    } else {
        throw new \InvalidArgumentException('Invalid query or model provided.');
    }

    return DataTables::of($query)
        ->addColumn('no', function ($data) {
            static $count = 1; // Initialize a static counter variable

            // Determine the primary key value
            $primaryKeyValue = null;
            if (method_exists($data, 'getKey')) {
                $primaryKeyValue = $data->getKey();
            } else if (property_exists($data, 'id')) {
                $primaryKeyValue = $data->id;
            }
            $primaryKeyValueEncoded = base64_encode(json_encode($primaryKeyValue));

            return '<td><span style="margin-left: 20px !important;">' . $count++ . '.</span><input type="checkbox" name="checkbox" data-record="' . $primaryKeyValueEncoded . '" style="display: none;"></td>';
        })
        ->rawColumns(['no']) // Include the new 'no' column in rawColumns
        ->make(true);
}

/**
 * Generate unique code.
 */
function generateCode() {
    $prefix = 'NSE';
    $date = date('ymd');
    $suffix = generateSuffix();
    return $prefix . '.' . $date . '.' . $suffix;
}

/**
 * Generate unique suffix.
 */
function generateSuffix() {
    $suffix = '';
    $length = 5; // Desired length of the suffix (5 characters)

    while (strlen($suffix) < $length) {
        $randType = rand(0, 2); // Randomly choose 0 for letter, 1 for number, 2 for alphanumeric

        if ($randType === 0) {
            $suffix .= chr(rand(65, 90)); // Random letter from A to Z
        } elseif ($randType === 1) {
            $suffix .= rand(0, 9); // Random number from 0 to 9
        } else {
            $suffix .= chr(rand(65, 90)) . rand(0, 9); // Random alphanumeric combination
        }
    }

    // Trim or pad the suffix to ensure it has exactly 5 characters
    $suffix = substr($suffix, 0, $length);

    return $suffix;
}

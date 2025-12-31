<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    private ?string $request;
    private ?string $detailsResource;
    private string $model;
    private ?string $resource;
    private $conditionTypes = ['where', 'whereNot', 'whereDateLess', 'whereDateMore', 'whereIn', 'whereNotIn', 'whereBetween', 'whereLike'];
    protected static $columnsCache = [];

    public function __construct(string $model, ?string $resource = null, ?string $detailsResource = null, ?string $request = null)
    {
        $this->model    = $model;
        $this->resource = $resource;
        $this->detailsResource  = $detailsResource;
        $this->request  = $request;
    }

    /**
     * Get all records.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $query = $this->model::query();

            if ($filters = request()->input('filters')) {
                foreach ($filters as $field => $value) {
                    $query->where($field, $value);
                }
            }

            if ($conditions = request()->input('conditions')) {
                $this->applyConditions($query, $conditions);
            }

            if ($orderBy = request()->input('orderBy')) {
                $direction = request()->input('direction', 'asc');
                $query->orderBy($orderBy, $direction);
            }


            if (request()->has('paginate')) {
                $perPage = request()->input('perPage', 15);
                $page = request()->input('page', 1);
                $collections = $query->latest()->paginate($perPage, ['*'], 'page', $page);
            } else {
                $collections = $query->latest()->get();
            }

            if ($this->detailsResource) {
                $data = $this->detailsResource::collection($collections);
                if (request()->has('paginate')) {
                    $data = $data->response()->getData(true);
                }
            } else {
                $data = $collections;
            }

            return $this->successResponse($data, 'Records retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([], 'An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function applyConditions($query, $conditions)
    {
        if (empty($conditions)) {
            return $query;
        }

        foreach ($conditions as $conditionType => $whereConditions) {
            if (empty($whereConditions)) {
                continue;
            }

            if (in_array($conditionType, $this->conditionTypes)) {
                $this->applyConditionType($query, $conditionType, $whereConditions);
            } elseif (env('APP_ENV') != 'production') {
                $allowedConditions = implode(', ', $this->conditionTypes);
                throw ValidationException::withMessages([
                    'conditionType' => "The condition type '{$conditionType}' is not supported. Allowed condition types are: {$allowedConditions}.",
                ]);
            }
        }

        return $query;
    }

    protected function applyConditionType($query, $conditionType, $whereConditions)
    {
        foreach ($whereConditions as $field => $value) {
            if (
                ! (
                    ($conditionType == 'whereLike' && method_exists($this->model, 'isTranslationAttribute') && $this->model->isTranslationAttribute($field)) ||
                    ($this->columnExists($field))
                )
            ) {
                if (env('APP_ENV') != 'production') {
                    throw ValidationException::withMessages([
                        'field' => "The field '{$field}' does not exist in the table.",
                    ]);
                }
                continue;
            }

            switch ($conditionType) {
                case 'where':
                    $query->where($field, $value);
                    break;

                case 'whereNot':
                    $query->where($field, '!=', $value);
                    break;

                case 'whereDateLess':
                    $query->whereDate($field, '<=', Carbon::parse($value));
                    break;

                case 'whereDateMore':
                    $query->whereDate($field, '>=', Carbon::parse($value));
                    break;

                case 'whereIn':
                    $query->whereIn($field, $value);
                    break;

                case 'whereNotIn':
                    $query->whereNotIn($field, $value);
                    break;

                case 'whereBetween':
                    if (is_array($value) && count($value) === 2) {
                        $query->whereBetween($field, $value);
                    }
                    break;

                case 'whereLike':
                    $query->whereHas('translations', function ($subQuery) use ($field, $value) {
                        $subQuery->where($field, 'like', '%' . $value . '%');
                    });
                    break;
            }
        }
    }

    public function getWithoutPagination()
    {
        try {
            $query = $this->model::query();

            if ($filters = request()->input('filters')) {
                foreach ($filters as $field => $value) {
                    $query->where($field, $value);
                }
            }

            if ($conditions = request()->input('conditions')) {
                $this->applyConditions($query, $conditions);
            }

            if ($orderBy = request()->input('orderBy')) {
                $direction = request()->input('direction', 'asc');
                $query->orderBy($orderBy, $direction);
            }

            if (request()->has('paginate')) {
                $perPage = request()->input('perPage', 15);
                $page = request()->input('page', 1);
                $collections = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $collections = $query->get();
            }

            $data = $this->resource ? $this->resource::collection($collections) : $collections;

            return $this->successResponse($data, 'Records retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([], 'An error occurred: ' . $e->getMessage(), 500);
        }
    }

    protected function columnExists($columnName)
    {
        $table = $this->model->getTable();

        if (!isset(self::$columnsCache[$table])) {
            self::$columnsCache[$table] = Schema::getColumnListing($table);
        }

        return in_array($columnName, self::$columnsCache[$table]);
    }

    /**
     * Create a new record.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        // Validate request
        $validatedData = resolve($this->request)->validated();
        try {
            // Create a new record
            $record = $this->model::create($validatedData);

            // Sync any relationships if method exists
            if (method_exists($this, 'syncRelation')) {
                $this->syncRelation($record);
            }

            // Format response with resource if available
            $data = $this->detailsResource ? new $this->detailsResource($record) : $record;

            return $this->successResponse($data, 'Record created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse([], 'An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific record by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $record = $this->model::find($id);
        if (!$record) {
            return $this->errorResponse([], 'Record not found', 404);
        } else {
            $data = $this->detailsResource ? new $this->detailsResource($record) : $record;
            return $this->successResponse($data, 'Record retrieved successfully');
        }
    }

    /**
     * Update a specific record by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id)
    {
        // Validate request
        $validatedData = resolve($this->request)->validated();
        $record = $this->model::findOrFail($id);
        try {
            // Update the record
            $record->update($validatedData);

            // Sync any relationships if method exists
            if (method_exists($this, 'syncRelation')) {
                $this->syncRelation($record);
            }

            // Format response with resource if available
            $data = $this->detailsResource ? new $this->detailsResource($record) : $record;

            return $this->successResponse($data, 'Record updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([], 'An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a specific record by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $record = $this->model::findOrFail($id);
        try {
            $record->delete();
            return $this->successResponse([], 'Record deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse([], 'An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle the 'active' status of a record.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleActive($id)
    {
        // Find the record by ID
        $record = $this->model::findOrFail($id);
        try {
            // Toggle the 'active' status
            $record->is_active = !$record->is_active;
            $record->save();

            // Format response with resource if available
            $data = $this->resource ? new $this->resource($record) : $record;

            return $this->successResponse($data, 'Record status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse([], 'An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Toggle the 'column' status of a record.
     *
     * @param int $id
     * @param string $column
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle($id, $column)
    {
        // Find the record by ID
        $record = $this->model::findOrFail($id);
        try {
            // Check if the column exists on the model
            if (!array_key_exists($column, $record->getAttributes())) {
                return $this->errorResponse([], 'Column does not exist on this model', 422);
            }

            // Ensure the column is a boolean value
            if (!is_bool($record->{$column})) {
                return $this->errorResponse([], 'The column is not a boolean value', 422);
            }

            // Toggle the 'column' status
            $record->{$column} = !$record->{$column};
            $record->save();

            // Use the resource class for a formatted response
            $resource = new $this->resource($record);

            // Return a successful response
            return $this->successResponse($resource, 'Record updated successfully');
        } catch (\Exception $e) {
            // Return an error response if something goes wrong
            return $this->errorResponse([], 'An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Return a successful response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Return a successful response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, string $message = 'Success', int $statusCode = 200)
    {
        return json($data, $message, 'success', $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(array $data = [], string $message = 'Error', int $statusCode = 400): JsonResponse
    {
        return json($data, $message, 'fail', $statusCode);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Repositories\AdScriptTaskRepositoryContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdScriptTask\StoreAdScriptTaskRequest;
use App\Http\Requests\AdScriptTask\IndexAdScriptTaskRequest;
use App\Http\Resources\AdScriptTaskResource;
use App\Actions\ListAdScriptTasksAction;
use Illuminate\Http\Resources\Json\JsonResource;

class AdScriptTaskController extends Controller
{
    /**
     * List Ad Script tasks
     *
     * @param IndexAdScriptTaskRequest $request
     * @param ListAdScriptTasksAction $action
     * @return JsonResource
     *
     * @OA\Get(
     *   path="/api/ad-scripts",
     *   summary="List ad script tasks",
     *   tags={"Ad Scripts"},
     *   @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"pending","completed","failed"})),
     *   @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Tasks list")
     * )
     */
    public function index(IndexAdScriptTaskRequest $request, ListAdScriptTasksAction $action)
    {
        $perPage = $request->integer('per_page', 10);
        $paginator = $action->execute(
            status: $request->string('status')->toString() ?: null,
            search: $request->string('search')->toString() ?: null,
            perPage: $perPage
        );

        return AdScriptTaskResource::collection($paginator);
    }

    /**
     * Create an Ad Script task
     *
     * @param StoreAdScriptTaskRequest $request
     * @param AdScriptTaskRepositoryContract $repository
     * @return AdScriptTaskResource
     *
     * @OA\Post(
     *   path="/api/ad-scripts",
     *   summary="Create ad script refactor task",
     *   tags={"Ad Scripts"},
     *   requestBody=@OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"reference_script","outcome_description"},
     *       @OA\Property(property="reference_script", type="string"),
     *       @OA\Property(property="outcome_description", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Task created")
     * )
     */
    public function store(StoreAdScriptTaskRequest $request, AdScriptTaskRepositoryContract $repository): AdScriptTaskResource
    {
        $task = $repository->createPending(
            $request->string('reference_script')->toString(),
            $request->string('outcome_description')->toString()
        );

        return new AdScriptTaskResource($task);
    }
}



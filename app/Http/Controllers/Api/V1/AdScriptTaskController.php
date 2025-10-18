<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Repositories\AdScriptTaskRepositoryContract;
use App\Events\TaskStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdScriptTask\StoreAdScriptTaskRequest;
use App\Http\Requests\AdScriptTask\IndexAdScriptTaskRequest;
use App\Http\Requests\AdScriptTask\AdScriptTaskFailedRequest;
use App\Http\Requests\AdScriptTask\AdScriptTaskResultRequest;
use App\Http\Resources\AdScriptTaskResource;
use App\Actions\ListAdScriptTasksAction;
use App\Jobs\DispatchAdScriptTaskToN8nJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

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

        DispatchAdScriptTaskToN8nJob::dispatch($task->id);

        return new AdScriptTaskResource($task);
    }

    /**
     * Receive n8n callback with result
     *
     * @param int $id
     * @param AdScriptTaskResultRequest $request
     * @param AdScriptTaskRepositoryContract $repository
     * @return JsonResponse
     *
     * @OA\Post(
     *    path="/api/ad-scripts/{id}/result",
     *    summary="Complete ad script task with AI result",
     *    tags={"Ad Scripts"},
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *    security={{"bearerAuth":{}}},
     *    requestBody=@OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *        required={"task_id","new_script","analysis"},
     *        @OA\Property(property="task_id", type="integer"),
     *        @OA\Property(property="new_script", type="string"),
     *        @OA\Property(property="analysis", type="string")
     *      )
     *    ),
     *    @OA\Response(response=200, description="Task completed"),
     *    @OA\Response(response=401, description="Unauthorized")
     *  )
     */
    public function result(int $id, AdScriptTaskResultRequest $request, AdScriptTaskRepositoryContract $repository): JsonResponse
    {
        $taskId = $request->integer('task_id');
        if ($taskId !== $id) {
            return response()->json(['message' => 'task_id mismatch'], 422);
        }

        $response = $request->input('response');

        try {
            $json = ltrim(trim($response, '`'), 'json');
            $parsed = json_decode($json, true);

            if (!Arr::has($parsed, ['new_script', 'analysis'])) {
                return response()->json(['message' => 'Invalid fields in the response'], 422);
            }

            $updated = $repository->markCompleted(
                $id,
                $parsed['new_script'],
                $parsed['analysis']
            );
        } catch (\Throwable $e) {
            $updated = $repository->markFailed($id, $e->getMessage());
        }

        // Broadcast the task status change to the frontend
        TaskStatusChanged::dispatch($updated);

        return (new AdScriptTaskResource($updated))->response();
    }

    /**
     * Receive n8n callback when workflow fails
     *
     * @param int $id
     * @param AdScriptTaskFailedRequest $request
     * @param AdScriptTaskRepositoryContract $repository
     * @return JsonResponse
     *
     * @OA\Post(
     *    path="/api/ad-scripts/{id}/failed",
     *    summary="Mark as failed ad script task",
     *    @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *    security={{"bearerAuth":{}}},
     *    requestBody=@OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *        required={"task_id","new_script","analysis"},
     *      )
     *    ),
     *    @OA\Response(response=200, description="Task failed"),
     *    @OA\Response(response=401, description="Unauthorized")
     *  )
     */
    public function failed(int $id, AdScriptTaskFailedRequest $request, AdScriptTaskRepositoryContract $repository): JsonResponse
    {
        $taskId = $request->integer('task_id');
        if ($taskId !== $id) {
            return response()->json(['message' => 'task_id mismatch'], 422);
        }

        $updated = $repository->markFailed($id, 'The n8n workflow failed.');
        // Broadcast the task status change to the frontend
        TaskStatusChanged::dispatch($updated);

        return (new AdScriptTaskResource($updated))->response();
    }
}



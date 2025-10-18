<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\ListAdScriptTasksAction;
use App\Contracts\Repositories\AdScriptTaskRepositoryContract;
use App\Jobs\DispatchAdScriptTaskToN8nJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url as LivewireUrl;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    #[LivewireUrl]
    public string $status = '';

    #[LivewireUrl]
    public string $search = '';

    public int $perPage = 10;

    // Form fields for creating new tasks
    public string $reference_script = '';
    public string $outcome_description = '';
    public bool $showCreateForm = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    // Task details modal
    public ?object $selectedTask = null;
    public bool $showTaskDetails = false;

    public function render()
    {
        $tasks = $this->fetchTasks();
        return view('livewire.task-list', [
            'tasks' => $tasks,
        ]);
    }

    public function getListeners(): array
    {
        return [
            "echo:tasks,task.status.changed" => "refreshTasks",
        ];
    }

    #[On('echo:tasks,task.status.changed')]
    public function refreshTasks(): void
    {
        // Log that we received the broadcast
        info('Livewire received broadcast event, refreshing tasks');

        // Force a complete refresh of the component
        $this->dispatch('$refresh');
    }

    /**
     * Display creation form
     */
    public function toggleCreateForm(): void
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->clearMessages();
    }

    /**
     * Store the task
     */
    public function createTask(): void
    {
        $this->clearMessages();

        // Validate the form data
        $this->validate([
            'reference_script' => 'required|string|min:1',
            'outcome_description' => 'required|string|min:1',
        ]);

        try {
            // Create the task directly using the repository
            $repository = app(AdScriptTaskRepositoryContract::class);
            $task = $repository->createPending(
                $this->reference_script,
                $this->outcome_description
            );

            // Dispatch the job to send to n8n
            DispatchAdScriptTaskToN8nJob::dispatch($task->id);

            $this->successMessage = 'Task created successfully!';
            $this->resetForm();
            $this->showCreateForm = false;
            // Refresh the task list
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred: ' . $e->getMessage();
        }
    }

    /**
     * Reset form
     */
    public function resetForm(): void
    {
        $this->reference_script = '';
        $this->outcome_description = '';
    }

    /**
     * Clear messages
     */
    public function clearMessages(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    /**
     * Open modal to display full details
     */
    public function openModal(int $taskId): void
    {
        try {
            $repository = app(AdScriptTaskRepositoryContract::class);
            $this->selectedTask = $repository->findById($taskId);
            $this->showTaskDetails = true;
        } catch (\Exception $e) {
            // Log the error for debugging
            $this->errorMessage = 'Error loading task details: ' . $e->getMessage();
        }
    }

    /**
     * Delete task
     */
    public function deleteTask(int $taskId): void
    {
        try {
            $repository = app(AdScriptTaskRepositoryContract::class);
            $this->selectedTask = $repository->findById($taskId);
            $this->selectedTask->delete();
        } catch (\Exception $e) {
            // Log the error for debugging
            $this->errorMessage = 'Error while delete task: ' . $e->getMessage();
        }
    }

    /**
     * Close modal of full details
     */
    public function closeTaskDetails(): void
    {
        $this->showTaskDetails = false;
        $this->selectedTask = null;
    }

    /**
     * Retry failed task
     *
     * @param int $taskId
     */
    public function retryTask(int $taskId): void
    {
        $this->clearMessages();

        try {
            $repository = app(AdScriptTaskRepositoryContract::class);

            // Reset the task to pending status
            $task = $repository->retryTask($taskId);

            // Dispatch the job to send to n8n again
            DispatchAdScriptTaskToN8nJob::dispatch($task->id);

            $this->successMessage = 'Task retry initiated successfully!';

            // Refresh the task list
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            $this->errorMessage = 'Error retrying task: ' . $e->getMessage();
        }
    }

    /**
     * Fetch tasks
     */
    public function fetchTasks(): LengthAwarePaginator
    {
        $action = app(ListAdScriptTasksAction::class);

        return $action->execute(
            status: $this->status !== '' ? $this->status : null,
            search: $this->search !== '' ? $this->search : null,
            perPage: $this->perPage
        );
    }
}



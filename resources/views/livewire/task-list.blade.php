<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Ad Script Tasks</h1>
        <button
            wire:click="toggleCreateForm"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
            {{ $showCreateForm ? 'Cancel' : 'Create New Task' }}
        </button>
    </div>

    <!-- Success/Error Messages -->
    @if($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Create Task Form -->
    @if($showCreateForm)
        <div class="bg-white border border-gray-300 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Create New Ad Script Task</h2>
            <form wire:submit.prevent="createTask">
                <div class="mb-4">
                    <label for="reference_script" class="block text-sm font-medium text-gray-700 mb-2">
                        Reference Script
                    </label>
                    <textarea
                        wire:model="reference_script"
                        id="reference_script"
                        rows="4"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter the original script that needs to be refactored..."
                    ></textarea>
                    @error('reference_script')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="outcome_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Outcome Description
                    </label>
                    <textarea
                        wire:model="outcome_description"
                        id="outcome_description"
                        rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Describe the desired outcome (tone, target audience, length, etc.)..."
                    ></textarea>
                    @error('outcome_description')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Create Task
                    </button>
                    <button
                        type="button"
                        wire:click="resetForm"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Clear Form
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="flex gap-3 mb-4">
        <input type="text" wire:model.debounce.400ms="search" placeholder="Search scripts or outcomes" class="border p-2 rounded w-64">
        <select wire:model="status" class="border p-2 rounded">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
        </select>
        <button
            wire:click="fetchTasks()"
            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
            title="Search"
        >Search</button>
    </div>

    <table class="w-full border text-sm">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2 text-left">ID</th>
                <th class="border p-2 text-left">Status</th>
                <th class="border p-2 text-left">Reference</th>
                <th class="border p-2 text-left">Outcome</th>
                <th class="border p-2 text-left">Refactored</th>
                <th class="border p-2 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="border p-2">{{ $task->id }}</td>
                    <td class="border p-2">
                        @php
                            $badgeClasses = match($task->status) {
                                'completed' => 'bg-green-100 text-green-800 border-green-200',
                                'failed' => 'bg-red-100 text-red-800 border-red-200',
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                default => 'bg-gray-100 text-gray-800 border-gray-200',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded text-xs border {{ $badgeClasses }}">{{ ucfirst($task->status) }}</span>
                    </td>
                    <td class="border p-2">{{ Str::limit($task->reference_script, 80) }}</td>
                    <td class="border p-2">{{ Str::limit($task->outcome_description, 80) }}</td>
                    <td class="border p-2">{{ $task->new_script }}</td>
                    <td class="border p-2">
                        <div class="flex gap-2">
                            <button
                                wire:click="openModal({{ $task->id }})"
                                class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded transition-colors duration-150"
                                title="View Details"
                            >VIEW</button>
                            <button
                                wire:click="deleteTask({{ $task->id }})"
                                class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded transition-colors duration-150"
                                title="View Details"
                            >DELETE</button>
                            @if($task->status === 'failed')
                                <button
                                    wire:click="retryTask({{ $task->id }})"
                                    class="text-orange-600 hover:text-orange-800 hover:bg-orange-50 p-2 rounded transition-colors duration-150"
                                    title="Retry Task"
                                >RETRY</button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $tasks->links() }}
    </div>

    <!-- Task Details Modal -->
    @if($showTaskDetails && $selectedTask)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="closeTaskDetails">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="flex justify-between items-center p-6 border-b">
                    <h2 class="text-xl font-semibold">Task Details #{{ $selectedTask->id }}</h2>
                    <button
                        wire:click="closeTaskDetails"
                        class="text-gray-400 hover:text-gray-600 text-2xl font-bold"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                @php
                                    $badgeClasses = match($selectedTask->status) {
                                        'completed' => 'bg-green-100 text-green-800 border-green-200',
                                        'failed' => 'bg-red-100 text-red-800 border-red-200',
                                        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200',
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded text-sm border {{ $badgeClasses }}">{{ ucfirst($selectedTask->status) }}</span>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Created At</label>
                                <p class="text-sm text-gray-900">{{ $selectedTask->created_at->format('M d, Y H:i:s') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Updated At</label>
                                <p class="text-sm text-gray-900">{{ $selectedTask->updated_at->format('M d, Y H:i:s') }}</p>
                            </div>

                            @if($selectedTask->error)
                                <div>
                                    <label class="block text-sm font-medium text-red-700 mb-2">Error</label>
                                    <div class="bg-red-50 border border-red-200 rounded-md p-3">
                                        <p class="text-sm text-red-800 whitespace-pre-wrap">{{ $selectedTask->error }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reference Script</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-md p-3">
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $selectedTask->reference_script }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Outcome Description</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-md p-3">
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $selectedTask->outcome_description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full Width Sections -->
                    @if($selectedTask->new_script)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Generated Script</label>
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $selectedTask->new_script }}</p>
                            </div>
                        </div>
                    @endif

                    @if($selectedTask->analysis)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Analysis</label>
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $selectedTask->analysis }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-between p-6 border-t bg-gray-50">
                    <button
                        wire:click="closeTaskDetails"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>



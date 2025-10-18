<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ad Script Tasks</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">Ad Script Tasks</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span id="user-email" class="text-sm text-gray-700"></span>
                    <button id="logout-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto mt-8">
        @livewire('task-list')
    </div>

    @livewireScripts

    <script>
        // Check authentication on page load
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token');
            const user = localStorage.getItem('user');
            
            if (!token || !user) {
                // Redirect to login if not authenticated
                window.location.href = '/login';
                return;
            }
            
            // Display user email
            try {
                const userData = JSON.parse(user);
                document.getElementById('user-email').textContent = userData.email;
            } catch (e) {
                console.error('Error parsing user data:', e);
                window.location.href = '/login';
            }
        });

        // Handle logout
        document.getElementById('logout-btn').addEventListener('click', async function() {
            const token = localStorage.getItem('auth_token');
            
            if (token) {
                try {
                    await fetch('/api/auth/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                } catch (error) {
                    console.error('Logout error:', error);
                }
            }
            
            // Clear local storage and redirect
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        });
    </script>
</body>
</html>



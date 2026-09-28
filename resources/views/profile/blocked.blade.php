<x-indexLay>
<div class="bg-brand-bg min-h-[calc(100vh-4rem)] flex">
    <!-- Sidebar -->
    @include('profile.partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 p-6 lg:p-12 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-white mb-1">Blocked users</h1>
                <p class="text-sm text-gray-500">0 members you have blocked.</p>
            </div>

            <!-- Content Container -->
            <div class="bg-brand-card rounded-xl p-16 flex flex-col items-center justify-center text-center border border-white/5 mt-8">
                <div class="w-12 h-12 rounded-full bg-brand-bg flex items-center justify-center mb-4">
                    <i class='bx bx-user text-gray-600 text-xl'></i>
                </div>
                <h3 class="text-white font-bold mb-2">You haven't blocked anyone.</h3>
                <p class="text-gray-500 text-sm">Open a member's profile or a comment menu to block them.</p>
            </div>
        </div>
    </main>
</div>
</x-indexLay>

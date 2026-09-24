<x-indexLay>
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-12 z-20 relative">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">User Management</h1>
                <p class="text-gray-400">Manage all registered users and their roles.</p>
            </div>
        </div>

        <div class="bg-brand-card rounded-2xl shadow-xl overflow-hidden border border-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800/50 text-gray-400 text-sm uppercase tracking-wider border-b border-gray-700">
                            <th class="px-6 py-4 font-medium">ID</th>
                            <th class="px-6 py-4 font-medium">User</th>
                            <th class="px-6 py-4 font-medium">Role</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach ($data as $row)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 text-gray-300 font-mono text-sm">{{ $row->id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0 bg-brand-bg rounded-full flex items-center justify-center text-brand font-bold uppercase shadow-inner">
                                        {{ substr($row->username, 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-white font-medium">{{ $row->username }}</div>
                                        <div class="text-gray-400 text-sm">{{ $row->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($row->role === 'admin' || $row->role === 'superadmin')
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-400 border border-purple-500/30 shadow-sm">
                                        {{ ucfirst($row->role) }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-brand/20 text-brand border border-brand/30 shadow-sm">
                                        {{ ucfirst($row->role) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('edit', ['id' => $row->id]) }}" class="text-gray-400 hover:text-brand transition-colors p-2 bg-gray-800 rounded-lg hover:bg-gray-700 border border-gray-700 hover:border-brand/50 shadow-sm" title="Edit User">
                                        <i class="bx bx-edit-alt text-lg"></i>
                                    </a>
                                    <form action="{{ route('delete-user', ['id' => $row->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors p-2 bg-gray-800 rounded-lg hover:bg-gray-700 border border-gray-700 hover:border-red-500/50 shadow-sm" title="Delete User">
                                            <i class="bx bx-trash text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($data->isEmpty())
            <div class="p-12 text-center flex flex-col items-center justify-center text-gray-500">
                <i class="bx bx-group text-5xl mb-3 text-gray-700"></i>
                <p>No users found in the database.</p>
            </div>
            @endif
        </div>
    </div>
</x-indexLay>

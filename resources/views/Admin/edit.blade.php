<x-indexLay>
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-12 flex justify-center items-center min-h-[70vh] z-20 relative">
        
        <div class="bg-brand-card w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-800 relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand to-cyan-400"></div>
            
            <div class="p-8 sm:p-10">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand/10 text-brand mb-4 shadow-inner">
                        <i class="bx bx-user-circle text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-white tracking-tight">Edit User</h2>
                    <p class="text-gray-400 mt-2">Update user account information.</p>
                </div>

                <form action="{{url('updated-data', ['id' => $data['id']]) }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="id" value="{{ $data['id'] }}">
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                        <div class="relative">
                            <i class="bx bx-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
                            <input type="text" name="username" id="username" value="{{ $data['username'] }}" required
                                class="w-full bg-brand-bg/50 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                        <div class="relative">
                            <i class="bx bx-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
                            <input type="email" name="email" id="email" value="{{ $data['email'] }}" required
                                class="w-full bg-brand-bg/50 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                        </div>
                    </div>

                    <div class="pt-6 flex gap-4">
                        <a href="{{ url('admin') }}" class="w-1/3 flex justify-center items-center bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white font-medium py-3 rounded-xl transition-all shadow-sm hover:shadow">
                            Cancel
                        </a>
                        <button type="submit" class="w-2/3 bg-brand hover:bg-cyan-400 text-white font-bold py-3 rounded-xl shadow-lg shadow-brand/30 transition-all hover:-translate-y-0.5">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-indexLay>
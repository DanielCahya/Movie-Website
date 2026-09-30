<x-indexLay>
<div class="bg-brand-bg min-h-[calc(100vh-4rem)] flex">
    <!-- Sidebar -->
    @include('profile.partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 p-6 lg:p-12 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            
            @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg mb-8 text-sm">
                {{ session('success') }}
            </div>
            @endif

            <div class="mb-10">
                <h1 class="text-2xl font-bold text-white mb-2">Edit Profile</h1>
                <p class="text-gray-400 text-sm">Update how you appear on the site. Email and password changes require confirmation.</p>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
                @csrf

                <!-- Avatar Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <h3 class="text-base font-bold text-white">Avatar</h3>
                        <p class="text-sm text-gray-500 mt-1">JPG, PNG or WebP, up to 2 MB.</p>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-6">
                        <div class="relative group cursor-pointer">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-[100px] h-[100px] rounded-full object-cover border border-white/10 bg-brand-card">
                            @else
                                <div class="w-[100px] h-[100px] rounded-full bg-brand-card border border-white/10 flex items-center justify-center text-4xl font-semibold text-white uppercase transition-colors">
                                    {{ substr($user->username, 0, 1) }}
                                </div>
                            @endif
                            <!-- Overlay for file input -->
                            <div class="absolute inset-0 bg-black/60 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class='bx bx-camera text-white text-2xl'></i>
                            </div>
                            <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/gif" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </div>
                        <div class="flex flex-col gap-2">
                            <span class="text-sm text-gray-300">Click picture to change</span>
                            @if($user->avatar)
                                <button type="button" onclick="document.getElementById('delete-avatar-form').submit();" class="text-xs text-red-400 hover:text-red-300 font-bold tracking-wider uppercase text-left transition-colors">
                                    <i class='bx bx-trash mr-1'></i> Remove Picture
                                </button>
                            @endif
                            @error('avatar')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-white/5">

                <!-- Identity Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <h3 class="text-base font-bold text-white">Identity</h3>
                        <p class="text-sm text-gray-500 mt-1">Shown on your profile and next to your reviews.</p>
                    </div>
                    <div class="md:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Display Name</label>
                                <input type="text" name="display_name" value="{{ $user->username }}" disabled class="w-full bg-brand-card border border-transparent rounded-lg px-4 py-3 text-white focus:outline-none cursor-not-allowed">
                                <p class="text-[11px] text-gray-500 mt-2">Cannot be changed right now.</p>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Username</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">@</span>
                                    <input type="text" name="username" value="{{ $user->username }}" class="w-full bg-brand-card border border-transparent rounded-lg pl-8 pr-4 py-3 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                                </div>
                                <p class="text-[11px] text-gray-500 mt-2">Lowercase letters, numbers, dot, underscore, hyphen.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-white/5">

                <!-- Bio Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <h3 class="text-base font-bold text-white">Bio</h3>
                        <p class="text-sm text-gray-500 mt-1">A short blurb shown on your public profile.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">About You</label>
                        <textarea name="bio" rows="4" class="w-full bg-brand-card border border-transparent rounded-lg p-4 text-white placeholder-gray-600 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors resize-none" placeholder="Tell readers a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="border-white/5">

                <!-- Email Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <h3 class="text-base font-bold text-white">Email</h3>
                        <p class="text-sm text-gray-500 mt-1">Used for sign-in and notifications.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Email Address</label>
                        <div class="relative">
                            <i class='bx bx-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-500'></i>
                            <input type="email" value="{{ $user->email }}" disabled class="w-full bg-brand-card border border-transparent rounded-lg pl-10 pr-4 py-3 text-gray-400 cursor-not-allowed">
                        </div>
                    </div>
                </div>
                
                <hr class="border-white/5">

                <!-- Password Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <h3 class="text-base font-bold text-white">Password</h3>
                        <p class="text-sm text-gray-500 mt-1">Leave blank to keep your current password. Minimum 6 characters.</p>
                    </div>
                    <div class="md:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">New Password</label>
                                <div class="relative">
                                    <i class='bx bx-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-500'></i>
                                    <input type="password" name="password" placeholder="••••••••" class="w-full bg-brand-card border border-transparent rounded-lg pl-10 pr-4 py-3 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2">Confirm New Password</label>
                                <div class="relative">
                                    <i class='bx bx-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-500'></i>
                                    <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full bg-brand-card border border-transparent rounded-lg pl-10 pr-4 py-3 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer / Save Button -->
                <div class="pt-8 pb-12 flex justify-end">
                    <button type="submit" class="bg-brand hover:bg-cyan-400 text-white font-bold py-2.5 px-6 rounded-lg transition-colors shadow-lg shadow-brand/20">
                        Save changes
                    </button>
                </div>
            </form>
            
            @if($user->avatar)
                <form id="delete-avatar-form" action="{{ route('profile.avatar.delete') }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </main>
</div>
</x-indexLay>

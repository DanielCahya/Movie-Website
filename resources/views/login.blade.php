<x-indexLay>
<style>
.perspective-1000 { perspective: 1000px; }
.preserve-3d { transform-style: preserve-3d; }
.backface-hidden { backface-visibility: hidden; -webkit-backface-visibility: hidden; }
.rotate-y-180 { transform: rotateY(180deg); }
</style>

<!-- Full screen background with overlay -->
<div class="fixed inset-0 z-0 pointer-events-none">
    <!-- Awesome movie-related background image (Interstellar backdrop) -->
    <img src="https://image.tmdb.org/t/p/original/rAiYTfKGqDCRIIqo664sY9XZIvQ.jpg" class="w-full h-full object-cover opacity-40" alt="Background">
    <div class="absolute inset-0 bg-brand-bg/80 backdrop-blur-md"></div>
</div>

<div class="relative z-10 w-full min-h-[85vh] flex items-center justify-center px-4 py-12">
    <div x-data="{ isFlipped: {{ request()->has('register') || old('email') ? 'true' : 'false' }} }" class="perspective-1000 w-full max-w-md h-[550px]">
        <div :class="isFlipped ? 'rotate-y-180' : ''" class="w-full h-full duration-700 preserve-3d relative transition-transform">
            
            <!-- FRONT: LOGIN -->
            <div class="absolute w-full h-full backface-hidden bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 sm:p-10 shadow-2xl flex flex-col justify-center">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-white tracking-tight">Welcome Back</h2>
                    <p class="text-gray-300 mt-2">Sign in to continue to Galiwe.</p>
                </div>

                @if(session('success'))
                <div class="bg-green-500/20 border border-green-500/50 text-green-200 px-4 py-3 rounded-xl mb-6 text-sm text-center">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any() && !old('email'))
                <div class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-xl mb-6 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ url('login') }}" method="post" class="space-y-5">
                    @csrf
                    <div class="relative">
                        <i class='bx bxs-user absolute left-4 top-1/2 -translate-y-1/2 text-white/50 text-xl'></i>
                        <input type="text" name="username" placeholder="Username" value="{{old('username')}}" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3.5 text-white placeholder-white/50 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                    </div>
                    
                    <div class="relative">
                        <i class='bx bxs-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-white/50 text-xl'></i>
                        <input type="password" name="password" placeholder="Password" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3.5 text-white placeholder-white/50 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                    </div>

                    <button type="submit" name="submit" class="w-full bg-brand hover:bg-cyan-400 text-white font-bold py-3.5 rounded-xl shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all hover:-translate-y-0.5 hover:shadow-[0_0_25px_rgba(6,182,212,0.6)] mt-4">
                        Login
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-gray-300">Don't have an account? 
                        <button type="button" @click="isFlipped = true" class="text-brand font-semibold hover:text-cyan-300 transition-colors ml-1 focus:outline-none">Register</button>
                    </p>
                </div>
            </div>

            <!-- BACK: REGISTER -->
            <div class="absolute w-full h-full backface-hidden rotate-y-180 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 sm:p-10 shadow-2xl flex flex-col justify-center">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-white tracking-tight">Create Account</h2>
                    <p class="text-gray-300 mt-2">Join Galiwe to start tracking movies.</p>
                </div>

                @if ($errors->any() && old('email'))
                <div class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-xl mb-6 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{url('/registerproses')}}" method="post" class="space-y-4">
                    @csrf
                    <div class="relative">
                        <i class='bx bxs-envelope absolute left-4 top-1/2 -translate-y-1/2 text-white/50 text-xl'></i>
                        <input type="email" name="email" placeholder="Email Address" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                    </div>

                    <div class="relative">
                        <i class='bx bxs-user absolute left-4 top-1/2 -translate-y-1/2 text-white/50 text-xl'></i>
                        <input type="text" name="username" placeholder="Username" value="{{old('username')}}" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                    </div>
                    
                    <div class="relative">
                        <i class='bx bxs-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-white/50 text-xl'></i>
                        <input type="password" name="password" placeholder="Password" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl pl-12 pr-4 py-3 text-white placeholder-white/50 focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors">
                    </div>

                    <button type="submit" name="submit" class="w-full bg-brand hover:bg-cyan-400 text-white font-bold py-3.5 rounded-xl shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all hover:-translate-y-0.5 hover:shadow-[0_0_25px_rgba(6,182,212,0.6)] mt-2">
                        Register
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-300">Already have an account? 
                        <button type="button" @click="isFlipped = false" class="text-brand font-semibold hover:text-cyan-300 transition-colors ml-1 focus:outline-none">Login</button>
                    </p>
                </div>
            </div>
            
        </div>
    </div>
</div>
</x-indexLay>
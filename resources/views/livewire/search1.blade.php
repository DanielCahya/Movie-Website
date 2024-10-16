<div class="relative">
    <input wire:model.debounce.300ms="search" type="text" class="bg-gray-800 rounded-full w-54 px-4 pl-8 py-1 focus:outline-none focus:shadow-outline" placeholder="Search">
    @if (strlen($search) >= 2)
        <div class="absolute bg-gray-800 text-sm rounded w-64">
            @if ($hasilcaritv->count() > 0)
                <ul>
                    @foreach ($hasilcaritv as $hasilcaritv)
                        <li class="border-b border-gray-700">
                            <a href="{{url('showdetailtv',$hasilcaritv['id'])}}" class="block hover:bg-gray text-xs flex item-center">
                                @if ($hasilcaritv['poster_path'])
                                    <img src="https://image.tmdb.org/t/p/w92/{{$hasilcaritv['poster_path']}}" alt="poster"
                                    class="w-8">
                                @else
                                    <img src="https://via.placeholder.com/32x42" alt="poster">
                                @endif

                                <span class="ml-4">{{ $hasilcaritv['name'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div>No Result for "{{ $search }}"</div>
            @endif
        </div>
    @endif
</div>

<x-tvshowlay>
        <div class="semua">
            @foreach ($top as $top)
            <article class="bs">
                <div class="bsx">
                    <a href="{{url('showdetailtv', $top['id'])}}" class="tip" target="_blank">
                        <div class="gmbr">
                            <img src="{{'https://image.tmdb.org/t/p/w500/'.$top['poster_path']}}" alt="Messi" width="225" height="31">
                        </div>
                        <div class="tt">
                            {{$top['name']}}
                        </div>
                    </a>
                </div>
            </article>
            @endforeach
        </div>
</x-tvshowlay>
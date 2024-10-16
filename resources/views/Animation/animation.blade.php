<x-indexLay>
    <div class="semua">
        @foreach ($topAn as $topAn)
        <article class="bs">
            <div class="bsx">
                <a href="{{url('showdetailtv', $topAn['id'])}}" class="tip" target="_blank">
                    <div class="gmbr">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$topAn['poster_path']}}" alt="Messi" width="225" height="31">
                    </div>
                    <div class="tt">
                        {{$topAn['name']}}
                    </div>
                </a>
            </div>
        </article>
        @endforeach
    </div>
    <div class="semua">
        @foreach ($anime as $anime)
        <article class="bs">
            <div class="bsx">
                <a href="{{url('show', $anime['id'])}}" class="tip" target="_blank">
                    <div class="gmbr">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$anime['poster_path']}}" alt="{{$anime['title']}}" width="225" height="31">
                    </div>
                    <div class="tt">
                        {{$anime['title']}}
                    </div>
                </a>
            </div>
        </article>
        @endforeach
    </div>
</x-indexLay>
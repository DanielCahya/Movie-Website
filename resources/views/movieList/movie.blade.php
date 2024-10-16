<x-indexLay>
    <div class="semua">
        @foreach ($topRated as $topRated)
        <article class="bs">
            <div class="bsx">
                <a href="{{url('movieshow', $topRated['id'])}}" class="tip" target="_blank">
                    <div class="gmbr">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$topRated['poster_path']}}" alt="{{$topRated['title']}}" width="225" height="31">
                    </div>
                    <div class="tt">
                        {{$topRated['title']}}
                    </div>
                </a>
            </div>
        </article>
        @endforeach
    </div>
</x-indexLay>
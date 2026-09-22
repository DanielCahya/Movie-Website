<x-indexLay>
    <div class="semua">
        @foreach ($topAn ?? [] as $item)
        <article class="bs">
            <div class="bsx">
                <a href="{{url('showdetailtv', $item['id'])}}" class="tip" target="_blank">
                    <div class="gmbr">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$item['poster_path']}}" alt="Messi" width="225" height="31">
                    </div>
                    <div class="tt">
                        {{$item['name']}}
                    </div>
                </a>
            </div>
        </article>
        @endforeach
    </div>
    <div class="semua">
        @foreach ($anime ?? [] as $item)
        <article class="bs">
            <div class="bsx">
                <a href="{{url('show', $item['id'])}}" class="tip" target="_blank">
                    <div class="gmbr">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$item['poster_path']}}" alt="{{$item['title']}}" width="225" height="31">
                    </div>
                    <div class="tt">
                        {{$item['title']}}
                    </div>
                </a>
            </div>
        </article>
        @endforeach
    </div>
</x-indexLay>
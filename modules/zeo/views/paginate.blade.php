<nav aria-label="...">
    <ul class="pagination">
        @if($paginator->previousPageUrl())
            <li class="page-item">
                <a class="page-link" href="{{$paginator->previousPageUrl()}}">Prev</a>
            </li>
        @endif
        @foreach($elements as $element)

            @if(is_array($element))
                @foreach($element as $key=>$element)
                    @if($paginator->currentPage() == $key)
                        <li class="page-item active">
                            <a class="page-link" href="#">{{$key}}</a>
                        </li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{$element}}">{{$key}}</a></li>
                    @endif
                @endforeach
            @else
                <li class="page-item disabled"><span>...</span></li>
            @endif

        @endforeach
        @if($paginator->nextPageUrl())
            <li class="page-item">
                <a class="page-link" href="{{$paginator->nextPageUrl()}}">Next</a>
            </li>
        @endif


    </ul>
</nav>

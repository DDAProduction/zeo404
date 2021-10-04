<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <link rel="stylesheet" type="text/css" href="media/style/{{$manager_theme}}/style.css"/>
</head>

<body style="background-color:#EEEEEE">

<h1>Zeo 404</h1>
<div id="actions">
    <ul class="actionButtons">
        <li id="Button1"><a onclick="document.location.href='index.php?a=106';" href="#"><img
                        src="media/style/MODxCarbon/images/icons/stop.png">Закрыть модуль</a></li>
    </ul>
</div>
<div class="sectionBody">


    <div id="modulePane" class="dynamic-tab-pane-control tab-pane">
        <div class="tab-row">
            <h2 id="page1" class="tab @if($tab == "main") selected @endif "><span
                        onClick="document.location.href='{{$module_url}}&tab=main'">ZEO</span></h2>
            @if($tab == 'all_error')
                <h2 id="page2" class="tab @if($tab == "all_error") selected @endif"><span
                            onClick="document.location.href='{{$_SERVER['REQUEST_URI']}}'">Error for url {{$url}}</span>
                </h2>
            @elseif($tab == 'redirect')
                <h2 id="page2" class="tab @if($tab == "redirect") selected @endif"><span
                            onClick="document.location.href='{{$_SERVER['REQUEST_URI']}}'">Redirect for url {{$url}}</span>
                </h2>
            @endif
        </div>

        <div id="tab-page1" class="tab-page" @if($tab == "main") style="display:block;"
             @else style="display:none;" @endif>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th  style="width: 50px;">#</th>
                    <th scope="col">Url</th>
                    <th scope="col">Count enter page</th>
                    <th scope="col">Exclude</th>
                    <th scope="col">Delete</th>
                    <th scope="col">Redirects</th>
                </tr>
                </thead>
                <tbody>
                @if(isset($links))
                    @foreach($links as $link)
                        <tr id="url_{{$link->id}}">
                            <td  style="width: 50px;">{{$loop->iteration}}</td>
                            <td>{{$link->url}}</td>
                            <td><a href="{{$module_url}}&tab=all_error&url_id={{$link->id}}">{{$link->count_error}}</a>
                            </td>
                            @if($link->exclude == 0)
                                <td>
                                    <button type="button" class="btn btn-secondary" onclick="switch_url({{$link->id}})">
                                        Disabled
                                    </button>
                                </td>
                            @else
                                <td>
                                    <button type="button" class="btn btn-success" onclick="switch_url({{$link->id}})">
                                        Enabled
                                    </button>
                                </td>
                            @endif
                            <td>
                                <a href="{{$module_url}}&tab=redirect&url_id={{$link->id}}">Redirects</a>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger" onclick="delete_url({{$link->id}})">Delete
                                </button>
                            </td>

                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
            @if(isset($links))
                {!! $links->links('Zeo::paginate')->toHtml() !!}
            @endif
        </div>

        @if($tab == 'all_error')

            <div id="tab-page2" class="tab-page" @if($tab == "all_error") style="display:block;"
                 @else style="display:none;" @endif >
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th  style="width: 50px;">#</th>
                        <th scope="col">Referer</th>
                        <th scope="col">User data</th>
                        <th scope="col">Date</th>
                        <th scope="col">Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($history))
                        @foreach($history as $link)
                            <tr>
                                <td  style="width: 50px;">{{$loop->iteration}}</td>
                                <td>{{$link->referer}}</td>
                                <td>{{$link->user_data}}</td>
                                <td>{{$link->created_at}}</td>
                                <td>
                                    <button type="button" class="btn btn-danger"
                                            onclick="delete_history({{$link->id}})">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        @elseif($tab == 'redirect')

            <div id="tab-page2" class="tab-page" @if($tab == "redirect") style="display:block;"
                 @else style="display:none;" @endif >
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th  style="width: 50px;">#</th>
                        <th scope="col">Page Id</th>
                        <th scope="col">Exclude</th>
                        <th scope="col">Date</th>
                        <th scope="col">Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($redirects))
                        @foreach($redirects as $link)
                            <tr>
                                <td  style="width: 50px;">{{$loop->iteration}}</td>
                                <td>
                                    <form class="form-inline" method="POST" action="{{$_SERVER['REQUEST_URI']}}">
                                        <div class="form-group mx-sm-3 mb-2">

                                            <input type="hidden" name="redirect_id" id="redirect_Id"
                                                   value="{{$link->id}}">
                                            <input type="hidden" name="zeo_url_id" id="zeo_url_id"
                                                   value="{{$_GET['url_id']}}">
                                            <label for="page_id" class="sr-only">Redirect ID</label>
                                            <input type="text" name="page_id" class="form-control" id="page_id"
                                                   placeholder="Enter Page ID For Redirect" value="{{$link->page_id}}">
                                        </div>
                                        <button type="submit" class="btn btn-primary mb-2">Edit redirect</button>
                                    </form>
                                </td>
                                @if($link->exclude == 0)
                                    <td>
                                        <button type="button" class="btn btn-secondary"
                                                onclick="switch_redirect_url({{$link->id}})">Disabled
                                        </button>
                                    </td>
                                @else
                                    <td>
                                        <button type="button" class="btn btn-success"
                                                onclick="switch_redirect_url({{$link->id}})">Enabled
                                        </button>
                                    </td>
                                @endif
                                <td>{{$link->created_at}}</td>
                                <td>
                                    <button type="button" class="btn btn-danger"
                                            onclick="delete_redirect({{$link->id}})">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <form class="form-inline" method="POST" action="{{$_SERVER['REQUEST_URI']}}">
                    <div class="form-group mx-sm-3 mb-2">
                        <input type="hidden" name="redirect_id" id="redirect_Id" value="">
                        <input type="hidden" name="zeo_url_id" id="zeo_url_id" value="{{$_GET['url_id']}}">
                        <label for="page_id" class="sr-only">Redirect ID</label>
                        <input type="text" name="page_id" class="form-control" id="page_id"
                               placeholder="Enter Page ID For Redirect">
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">Add redirect</button>
                </form>
            </div>
        @endif


    </div>
</div>
<script>
    document.querySelector('.form').addEventListener('submit', function () {
        parent.modx.main.work();
    });

    function delete_url(id) {
        baseUrl = '{!! $module_url !!}&action=delete_url&url_id=' + id;
        fetch(baseUrl,
            {
                method: "GET"
            })
            .then(function (data) {
                location.reload();
            })
            .catch((err) => console.log(err.message));
    }

    function delete_history(id) {
        baseUrl = '{!! $module_url !!}&action=delete_history&url_id=' + id;
        fetch(baseUrl,
            {
                method: "GET"
            })
            .then(function (data) {
                location.reload();
            })
            .catch((err) => console.log(err.message));
    }

    function delete_redirect(id) {
        baseUrl = '{!! $module_url !!}&action=delete_redirect&url_id=' + id;
        fetch(baseUrl,
            {
                method: "GET"
            })
            .then(function (data) {
                location.reload();
            })
            .catch((err) => console.log(err.message));
    }

    function switch_url(id) {
        baseUrl = '{!! $module_url !!}&action=switch_url&url_id=' + id;
        fetch(baseUrl,
            {
                method: "GET"
            })
            .then(function (data) {
                location.reload();
            })
            .catch((err) => console.log(err.message));
    }

    function switch_redirect_url(id) {
        baseUrl = '{!! $module_url !!}&action=switch_redirect_url&url_id=' + id;
        fetch(baseUrl,
            {
                method: "GET"
            })
            .then(function (data) {
                location.reload();
            })
            .catch((err) => console.log(err.message));
    }
</script>
</body>
</html>

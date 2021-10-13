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

            <h2 id="page3" class="tab @if($tab == "404info") selected @endif "><span
                        onClick="document.location.href='{{$module_url}}&tab=404info'">404 info</span></h2>

            @if($tab == '404info_task')
                <h2 id="page4" class="tab @if($tab == "404info_task") selected @endif"><span
                            onClick="document.location.href='{{$_SERVER['REQUEST_URI']}}'">Pages for task {{$task->name}} {{$task->date_end}}</span>
                </h2>
            @endif

            @if($tab == '404info_page')
                <h2 id="page4" class="tab @if($tab == "404info_page") selected @endif"><span
                            onClick="document.location.href='{{$_SERVER['REQUEST_URI']}}'">Pages for task {{$page->url}}</span>
                </h2>
            @endif
        </div>

        <div id="tab-page1" class="tab-page" @if($tab == "main") style="display:block;"
             @else style="display:none;" @endif>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th style="width: 50px;">#</th>
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
                            <td style="width: 50px;">{{$loop->iteration}}</td>
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
                        <th style="width: 50px;">#</th>
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
                                <td style="width: 50px;">{{$loop->iteration}}</td>
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
                        <th style="width: 50px;">#</th>
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
                                <td style="width: 50px;">{{$loop->iteration}}</td>
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

        @if($tab == '404info')
            <div id="tab-page3" class="tab-page" @if($tab == "404info") style="display:block;"
                 @else style="display:none;" @endif >
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th scope="col">Task name</th>
                        <th scope="col">Date task</th>
                        <th scope="col">Count Page</th>
                        <th scope="col">Count link</th>
                        <th scope="col">Count error link</th>
                        <th scope="col">Count js links</th>
                        <th scope="col">Count blank</th>
                        <th scope="col">Count empty links</th>
                        <th scope="col">Count phone links</th>
                        <th scope="col">Count image</th>
                        <th scope="col">Count error image</th>
                        <th scope="col">Count empty image</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($tasks))
                        @foreach($tasks as $task)
                            <tr>
                                <td style="width: 50px;">{{$loop->iteration}}</td>
                                <td>
                                    <a href="{{$module_url}}&tab=404info_task&task_id={{$task->id}}">{{$task->name}} {{$task->date_end}}</a>
                                </td>
                                <td>
                                    {{$task->created_at}}
                                </td>
                                <td>
                                    {{$task->count_page}}
                                </td>
                                <td>
                                    {{$task->count_link}}
                                </td>
                                <td>
                                    {{$task->count_error_link}}
                                </td>
                                <td>
                                    {{$task->count_js_links}}
                                </td>
                                <td>
                                    {{$task->count_blank}}
                                </td>
                                <td>
                                    {{$task->count_empty_links}}
                                </td>
                                <td>
                                    {{$task->count_phone_links}}
                                </td>
                                <td>
                                    {{$task->count_image}}
                                </td>
                                <td>
                                    {{$task->count_error_image}}
                                </td>
                                <td>
                                    {{$task->count_empty_image}}
                                </td>


                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                @if(isset($tasks))
                    {!! $tasks->links('Zeo::paginate')->toHtml() !!}
                @endif
            </div>
        @endif

        @if($tab == '404info_task')
            <div id="tab-page5" class="tab-page" @if($tab == "404info_task") style="display:block;"
                 @else style="display:none;" @endif >
                <form method="get" id="filter" name="filter">
                    <input type="hidden" name="a" value="{{$_GET['a']}}">
                    <input type="hidden" name="id" value="{{$_GET['id']}}">
                    <input type="hidden" name="tab" value="{{$_GET['tab']}}">
                    <input type="hidden" name="task_id" value="{{$_GET['task_id']}}">
                    <div class="form-group row">
                        <label for="inputPassword" class="col-sm-2 col-form-label">Order By:</label>
                        <div class="col-sm-10">
                            <select name="orderby" id="inputPassword" onchange="document.forms['filter'].submit();">
                                <option value="default" @if(!isset($_GET['orderby']))selected @endif>Default</option>
                                <option value="status"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'status')selected @endif>
                                    Status
                                </option>
                                <option value="count_link"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_link')selected @endif>
                                    Count link
                                </option>
                                <option value="count_error_link"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_error_link')selected @endif>
                                    Count error link
                                </option>
                                <option value="count_js_links"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_js_links')selected @endif>
                                    Count js links
                                </option>
                                <option value="count_blank"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_blank')selected @endif>
                                    Count blank
                                </option>
                                <option value="count_empty_links"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_empty_links')selected @endif>
                                    Count empty links
                                </option>
                                <option value="count_phone_links"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_phone_links')selected @endif>
                                    Count phone links
                                </option>
                                <option value="count_image"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_image')selected @endif>
                                    Count image
                                </option>
                                <option value="count_error_image"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_error_image')selected @endif>
                                    Count error image
                                </option>
                                <option value="count_empty_image"
                                        @if(isset($_GET['orderby']) && $_GET['orderby'] == 'count_empty_image')selected @endif>
                                    Count empty image
                                </option>
                            </select>
                        </div>
                    </div>
                </form>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th scope="col">Url</th>
                        <th scope="col">Status</th>
                        <th scope="col">Count link</th>
                        <th scope="col">Count error link</th>
                        <th scope="col">Count js links</th>
                        <th scope="col">Count blank</th>
                        <th scope="col">Count empty links</th>
                        <th scope="col">Count phone links</th>
                        <th scope="col">Count image</th>
                        <th scope="col">Count error image</th>
                        <th scope="col">Count empty image</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($pages))
                        @foreach($pages as $page)
                            <tr>
                                <td style="width: 50px;">{{$loop->iteration}}</td>
                                <td style="word-break: break-word;">
                                    <a href="{{$module_url}}&tab=404info_page&page_id={{$page->id}}">{{$page->url}}</a>
                                </td>

                                <td>
                                    {{$page->status}}
                                </td>

                                <td>
                                    {{$page->count_link}}
                                </td>
                                <td>
                                    {{$page->count_error_link}}
                                </td>
                                <td>
                                    {{$page->count_js_links}}
                                </td>
                                <td>
                                    {{$page->count_blank}}
                                </td>
                                <td>
                                    {{$page->count_empty_links}}
                                </td>
                                <td>
                                    {{$page->count_phone_links}}
                                </td>
                                <td>
                                    {{$page->count_image}}
                                </td>
                                <td>
                                    {{$page->count_error_image}}
                                </td>
                                <td>
                                    {{$page->count_empty_image}}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                @if(isset($pages))
                    {!! $pages->links('Zeo::paginate')->toHtml() !!}
                @endif
            </div>
        @endif


        @if($tab == '404info_page')
            <div id="tab-page5" class="tab-page" @if($tab == "404info_page") style="display:block;"
                 @else style="display:none;" @endif >
                <h3><a href="{{$page->url}}" target="_blank">{{$page->url}}</a></h3>
                <table>
                    <tr>

                        <td>
                            Links: {{$page->count_link}}
                        </td>
                        <td>
                            Error Links: {{$page->count_error_link}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Js Links: {{$page->count_js_links}}
                        </td>

                        <td>
                            Blank Links: {{$page->count_blank}}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Empty Links: {{$page->count_empty_links}}
                        </td>
                        <td>
                            Phone Links: {{$page->count_phone_links}}
                        </td>
                    </tr>
                </table>
                <br>
                <table>
                    <tr>
                        <td>
                            Images: {{$page->count_image}}
                        </td>
                        <td>
                            Error Images: {{$page->count_error_image}}
                        </td>

                    </tr>
                    <tr>
                        <td>
                            Empty Images: {{$page->count_empty_image}}
                        </td>
                    </tr>
                </table>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th scope="col">Url</th>
                        <th scope="col">Type</th>
                        <th scope="col">Code</th>
                        <th scope="col">Info</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($links))
                        @foreach($links as $link)
                            <tr>
                                <td style="width: 50px;">{{$loop->iteration}}</td>
                                <td style="word-break: break-word;">
                                    <a href="{{$link->url}}" target="_blank">{{$link->url}}</a>
                                </td>

                                <td>
                                    @switch($link->type)
                                        @case(1)
                                        Link
                                        @break
                                        @case(2)
                                        Image
                                        @break
                                    @endswitch
                                </td>
                                <td>
                                    @switch($link->code)
                                        @case(1)
                                        Blank
                                        @break
                                        @case(2)
                                        Empty Link
                                        @break
                                        @default
                                        {{$link->code }}
                                        @break
                                    @endswitch

                                </td>
                                <td>
                                    {{$link->info }}
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

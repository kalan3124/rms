<!doctype html>
<!--
                /  __ \|  ___|\ \ / /| |    |  _  || \ | |                    a8888b.
                | /  \/| |__   \ V / | |    | | | ||  \| |                   d888888b.
                | |    |  __|   \ /  | |    | | | || . ` |                   8P"YP"Y88
                | \__/\| |___   | |  | |____\ \_/ /| |\  |                   8|o||o|88
                 \____/\____/   \_/  \_____/ \___/ \_| \_/                   8'    .88
                                                                             8`._.' Y8.
| |    |_   _|| \ | || | | |\ \ / /                                         d/      `8b.
| |      | |  |  \| || | | | \ V /                                         dP   .    Y8b.
| |      | |  | . ` || | | | /   \                                        d8:'  "  `::88b
| |____ _| |_ | |\  || |_| |/ /^\ \                                      d8"         'Y88b
\_____/ \___/ \_| \_/ \___/ \/   \/                                     :8P    '      :888
                                                                         8a.   :     _a88P
                                                                       ._/"Yaa_:   .| 88P|
                                                                  jgs  \    YP"    `| 8P  `.
                                                                  a:f  /     \.___.d|    .'
                                                                       `--..__)8888P`._.'

Are you reading our source codes?
             - Join with us.
                     - http://www.ceylonlinux.com/careers.html
-->

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">

        <link rel="icon" type="image/x-icon" class="js-site-favicon" href="{{ url('/images/favicon.ico')}}">

        <!-- App Styles -->
        <link href="{{ url('/css/app.css?v='.config('gitlab.last_job_id'))}}" rel="stylesheet" type="text/css">

        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDwcGALDxWC1T-5fnGvlzxvIJIoghO0ZUc&amp;v=3.exp&amp;libraries=geometry,drawing,places"></script>

        <script src="{{ url('/js/app.js?v='.config('gitlab.last_job_id'))}}" defer></script>
    </head>
    <body>

        <div id="stage">
            <img id="spinner" src="{{ url('images/logo.jpg') }}" />
        </div>
        <div id="root">
        </div>
    </body>
</html>

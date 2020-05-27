<?php
return [
      /*
    |--------------------------------------------------------------------------
    | Git Lab Access Token
    |--------------------------------------------------------------------------
    |
    | This token is using to issue tracker. You can place sunshine git user's
    | access token for this variable
    |
    */
    'access_token'=>env('GIT_LAB_ACCESS_TOKEN','access'),
    /*
    |--------------------------------------------------------------------------
    | Our Git Lab Server URL
    |--------------------------------------------------------------------------
    |
    | You can change this variable to our gitlab serer url.
    |
    */
    "server"=>"http://git.ceylonlinux.lk/api/v4",
    /*
    |--------------------------------------------------------------------------
    | Git Lab Current developers
    |--------------------------------------------------------------------------
    |
    | If you set your gitlab user id to this variable you will be automatically
    | assigned to issues. visit
    | http://git.ceylonlinux.lk/api/v4/users?username=YOUR_USERNAME
    | to get your user id
    |
    */
    "current_devs"=>explode(",",env("GIT_LAB_CURRENT_DEVELOPERS","1")),
    /*
    |--------------------------------------------------------------------------
    | Git lab project id
    |--------------------------------------------------------------------------
    |
    | Currenlty our gitlab project id. You can find it in you project page's developer tool by
    | name="project_id" attribute
    |
    */
    'project_id'=>env('GIT_LAB_PROJECT_ID',142),
    /*
    |--------------------------------------------------------------------------
    | Git lab last job id
    |--------------------------------------------------------------------------
    |
    | This variable is using to clear cache after a git commit deployed to the server.
    | You dont want to manually edit this variable. It will automatically replacing with
    | the suitable value.
    |
    */
    "last_job_id"=>env('GIT_LAB_LAST_JOB_ID',0)
];
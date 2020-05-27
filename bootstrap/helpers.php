<?php
/**
 * Replacing a query string with assoc array values
 *
 * @param array $array data array
 * @param string $string
 * @return string
 */
function replaceStringWithAssocArray(array $array, string $string){
    $string = preg_replace_callback('/([a-zA-Z\_\.]+)/',function($var)use($array){
        $query = $var[0];
        $exploded = explode('.',$query);
        $arr1 = $array;
        foreach($exploded as $key=> $key_2){
            if(isset($arr1[$key_2])){
                $arr1 = $arr1[$key_2];
                if($key==count($exploded)-1) return $arr1;
            }
        }
    },$string);

    return $string;
}
/**
 * Getting parents from dot notation string
 * 
 * if you supplied a string like `name - parent.name` this function
 * will return a array like ['parent']
 * 
 * @param string $str
 * @return array
 */
function getParentsFromDotString($str){
    preg_match_all("/([a-zA-Z0-9\_\.]+)/",$str,$matches);

    $parents = array();
    
    foreach($matches[1] as $matched){
        $exploded = explode('.',$matched);
        array_pop($exploded);
        if(count($exploded)>0) $parents[] = implode('.',$exploded);
    }

    return $parents;
}

/**
 * Shortning a long name
 *
 * @param string $str
 * @param integer $length
 * @return void
 */
function shortString(string $str,int $length){
    if(strlen($str)<=$length){
        return $str;
    } else {
        return substr($str,0,$length).'...';
    }
}
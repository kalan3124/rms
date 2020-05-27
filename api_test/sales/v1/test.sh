#!/bin/bash

JQ_INSTALLED=$(command -v jq)
if [ -z "$JQ_INSTALLED" ] ; then
    sudo apt-get install jq
fi

SHL_DEFAULT_URL="http://shl.salespad.lk/healthcare"
SHL_REQUESTS_PATTERN='./requests/*.shlr'
SHL_OUT_RES_DIR='./out_responses/'
SHL_OUT_REQ_DIR='./out_requests/'
TODAY=$(date '+%Y_%m_%d')

check_response(){
    SHL_SUCCESS=$(echo $SHL_RESPONSE | jq '.result')

    if [ "$SHL_SUCCESS" == "false" ]; then
        SHL_MESSAGE=$(echo $SHL_RESPONSE | jq '.message')
        echo "ERROR: $SHL_MESSAGE"
        return 1
    else
        return 0
    fi
}

make_request(){
    SHL_DATA="${2:-'{}'}"
    SHL_RESPONSE=$(curl \
        --header "Content-Type: application/json" \
        --header "Accept: application/json" \
        --header "Authorization: Bearer $SHL_TOKEN" \
        --request POST \
        --data "$SHL_DATA" \
        "$SHL_URL/api/sales/v1/$1")
}

# If you run in your local server please put with ip
echo "Please enter the healthcare URL[$SHL_DEFAULT_URL]:-"
read -r SHL_URL

if [ -z "$SHL_URL" ] ; then
    SHL_URL="$SHL_DEFAULT_URL"
fi

# Make sure the user has an itinerary
echo "Please enter the username to test:-"
read -r SHL_USERNAME

echo "Please enter the password to test:-"
read -r SHL_PASSWORD

echo "+--------------------------------------------------------------------------+"
echo "| Sending login request to the sever..                                     |"
echo "+--------------------------------------------------------------------------+"

SHL_RESPONSE=$( curl \
        --header "Content-Type: application/json" \
        --header "Accept: application/json" \
        --request POST \
        --data '{"username":"'$SHL_USERNAME'","password":"'$SHL_PASSWORD'"}' \
        "$SHL_URL/api/sales/v1/login")

mkdir -p "$SHL_OUT_RES_DIR$TODAY"
mkdir -p "$SHL_OUT_REQ_DIR$TODAY"

echo $SHL_RESPONSE | jq . > "$SHL_OUT_RES_DIR$TODAY/login.json"

check_response

if [ "$?" -ne 0 ] ; then
    exit 1;
fi

SHL_TOKEN=$(echo $SHL_RESPONSE | jq -r '.token')

# If filenames given as arguments

if [ "$#" -ne 0 ]; then
    filenames="$@"
else 
    filenames=$SHL_REQUESTS_PATTERN
fi

# Here we start our web services

for filename in $filenames; do
    url=$(head -n 1 $filename)
    data=$(tail -n +3 $filename)
    filename_only=$(basename "$filename" .shlr)

    echo "--------------------------------------------------------------------------"
    echo "Are you want to check $filename_only Web Service?[y]"
    read -r SHL_NEXT
    echo "--------------------------------------------------------------------------"

    if [ -z "$SHL_NEXT" ] ; then
        SHL_NEXT="y"
    fi

    if [ "$SHL_NEXT" == "y" ];then
        make_request "$url" "$data"
        echo "--------------------------------------------------------------------------"
        echo "Response:-"
        echo "--------------------------------------------------------------------------"
        echo $SHL_RESPONSE|jq . > "$SHL_OUT_RES_DIR$TODAY/$filename_only.json"
        echo "POST $SHL_URL/api/sales/v1/$url HTTP/1.1">"$SHL_OUT_REQ_DIR$TODAY/$filename_only.http"
        echo "Content-Type: appliation/json">>"$SHL_OUT_REQ_DIR$TODAY/$filename_only.http"
        echo "Accept: appliation/json">>"$SHL_OUT_REQ_DIR$TODAY/$filename_only.http"
        echo "Authorization: Bearer $SHL_TOKEN">>"$SHL_OUT_REQ_DIR$TODAY/$filename_only.http"
        echo "">>"$SHL_OUT_REQ_DIR$TODAY/$filename_only.http"
        echo "$data"| jq . >>"$SHL_OUT_REQ_DIR$TODAY/$filename_only.http"
        check_response
        if [ $? -eq 0 ] ; then
            echo $SHL_RESPONSE| jq .
        fi
    fi
done

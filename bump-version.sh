#!/bin/bash

# works with a file called VERSION in the current directory,
# the contents of which should be a semantic version number
# such as "1.2.3"

# this script will display the current version, automatically
# suggest a "minor" version update, and ask for input to use
# the suggestion, or a newly entered value.

# once the new version number is determined, the script will
# pull a list of changes from git history, prepend this to
# a file called CHANGES (under the title of the new version
# number) and create a GIT tag.

if [[ -f VERSION ]]; then
    BASE_STRING=`cat VERSION`
    BASE_LIST=(`echo ${BASE_STRING} | tr '.' ' '`)
    V_MAJOR=${BASE_LIST[0]}
    V_MINOR=${BASE_LIST[1]}
    V_PATCH=${BASE_LIST[2]}
    echo "Current version : $BASE_STRING"
    V_MINOR=$((V_MINOR + 1))
    V_PATCH=0
    SUGGESTED_VERSION="$V_MAJOR.$V_MINOR.$V_PATCH"
    read -p "Enter a version number [$SUGGESTED_VERSION]: " INPUT_STRING
    if [[ "$INPUT_STRING" = "" ]]; then
        INPUT_STRING=${SUGGESTED_VERSION}
    fi
    echo "Will set new version to be $INPUT_STRING"
    echo ${INPUT_STRING} > VERSION
    tag_date=$(git log -1 --pretty=format:'%ad' --date=short)

    echo "## $INPUT_STRING - ${tag_date}" > tmpfile
    git log --pretty=format:" - %s" "v$BASE_STRING"...HEAD >> tmpfile
    echo "" >> tmpfile
    echo "" >> tmpfile
    cat CHANGELOG.md >> tmpfile
    mv tmpfile CHANGELOG.md
    echo "Version bump and update CHANGELOG file."
else
    echo "Could not find a VERSION file"
    read -p "Do you want to create a version file and start from scratch? [y]" RESPONSE
    if [[ "$RESPONSE" = "" ]]; then RESPONSE="y"; fi
    if [[ "$RESPONSE" = "Y" ]]; then RESPONSE="y"; fi
    if [[ "$RESPONSE" = "Yes" ]]; then RESPONSE="y"; fi
    if [[ "$RESPONSE" = "yes" ]]; then RESPONSE="y"; fi
    if [[ "$RESPONSE" = "YES" ]]; then RESPONSE="y"; fi
    if [[ "$RESPONSE" = "y" ]]; then
        tag_date=$(git log -1 --pretty=format:'%ad' --date=short)
        echo "0.1.0" > VERSION
        echo "## 0.1.0 - ${tag_date}" > CHANGELOG.md
        git log --pretty=format:" - %s" >> CHANGELOG.md
        echo "" >> CHANGELOG.md
        echo "" >> CHANGELOG.md
        echo "Version bump and make CHANGELOG file."
    fi
fi

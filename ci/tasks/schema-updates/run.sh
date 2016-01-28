#!/bin/bash

set -e
set -u

TASK_DIR="$PWD"

git clone "file://$TASK_DIR/repository" updated/repository

echo 'Logging in to Ravelry...'

curl -s --cookie-jar cookie.jar \
  -F "user[login]=$RAVELRY_LOGIN" \
  -F "user[password]=$RAVELRY_PASSWORD" \
  https://www.ravelry.com/account/login \
  > /dev/null

echo 'Downloading API documentation...'

curl -s --cookie cookie.jar \
  http://www.ravelry.com/api \
  > api.html

cd updated/repository

echo 'Generating API schema...'

cat "$TASK_DIR/api.html" | bin/schemagen.php > src/RavelryApi/schema.json

if git diff-files --quiet > /dev/null ; then
  exit
fi

git config user.email "$GIT_AUTHOR_EMAIL"
git config user.name "$GIT_AUTHOR_NAME"

echo "schema.json diff=schemajson" > .git/info/attributes
cat >> .git/config <<EOF
[diff "schemajson"]
  xfuncname = "^ {12}\"_cliname\": \"([^\"]+)\""
EOF

git commit -F - -- src/RavelryApi/schema.json <<EOF
Update from latest API documentation

Affected Methods:

$( git diff src/RavelryApi/schema.json | grep '^@@' | awk '{ print $5 }' | sed 's/^/ * /' )
EOF

git show HEAD | cat

cd ../

./repository/ci/tasks/run-tests/run.sh

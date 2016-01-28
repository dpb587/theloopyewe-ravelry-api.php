#!/bin/sh

set -e
set -u

if [ "" != "${PR_TITLE_DATA:-}" ] ; then
  cat > pr-title <<EOF
$PR_TITLE_DATA
EOF
  PR_TITLE_PATH=pr-title
fi

if [ "" != "${PR_BODY_DATA:-}" ] ; then
  cat > pr-body <<EOF
$PR_BODY_DATA
EOF
  PR_BODY_PATH=pr-body
fi

cat "$PR_TITLE_PATH" | jq -R -s '.' > pr-title.json
cat "$PR_BODY_PATH" | jq -R -s '.' > pr-body.json

jq -n \
  --arg base_branch "$GITHUB_BASE_BRANCH" \
  --arg head_owner "$GITHUB_HEAD_OWNER" \
  --arg head_branch "$GITHUB_HEAD_BRANCH" \
  --slurpfile title pr-title.json \
  --slurpfile body pr-body.json \
  '{
    "base": $base_branch,
    "body": ($body | join("\n")),
    "head": ($head_owner + ":" + $head_branch),
    "title": ($title | join("\n"))
  }' \
  | curl \
    --user "$GITHUB_AUTH_USER:$GITHUB_AUTH_TOKEN" \
    -X POST \
    --data '@-' \
    "https://api.github.com/repos/$GITHUB_BASE_OWNER/$GITHUB_BASE_REPO/pulls"

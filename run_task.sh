echo "CWD: $(pwd)" 

echo "BRANCH: $(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo '(detached)')"

git remote -v || true

echo

echo "GIT STATUS:"
git status --porcelain -b || true

echo

echo "-> Running: php bin/console tailwind:build --no-interaction"
php bin/console tailwind:build --no-interaction || echo "tailwind:build failed (non-zero exit)"

echo

echo "-> Running: php bin/console asset-map:compile --no-interaction"
php bin/console asset-map:compile --no-interaction || echo "asset-map:compile failed (non-zero exit)"

echo

echo "-> Staging all changes"
git add -A || true

echo "Changes (porcelain):"
git status --porcelain || true

echo

if [ -n "$(git status --porcelain)" ]; then
  echo "Committing changes..."
  git commit -m "chore: rebuild Tailwind CSS and compile assets" || echo "git commit failed or nothing to commit"
else
  echo "No changes to commit"
fi

echo "Pushing to remote"
git push || echo "git push failed (check remote/auth)"

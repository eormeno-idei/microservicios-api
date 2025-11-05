#!/bin/bash
git fetch --prune

for branch in $(git branch --format='%(refname:short)'); do
    remote_branch=$(git for-each-ref --format='%(upstream:short)' refs/heads/$branch)
    if [ -z "$remote_branch" ]; then
        echo "La rama '$branch' no tiene remoto. Eliminando..."
        git branch -d "$branch"
    fi
done
echo "Limpieza de ramas locales completada."
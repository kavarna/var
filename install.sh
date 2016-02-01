#! /bin/bash
. config.sh
if [ "$DBNAME" = "" ]; then
    echo "ERR: DBNAME non défini dans inc/config.sh";
    exit;
fi
if [ "$DBPASS" = "" ]; then
    echo "ERR: DBPASS non défini dans inc/config.sh";
    exit;
fi
if [ "$DBUSER" = "" ]; then
    echo "ERR: DBUSER non défini dans inc/config.sh";
    exit;
fi

FILES="pgproc/sql/*.sql pgdoc/sql/*.sql pgproc/plpgsql/*.sql pgproc/tests/tests.sql"
FILES="$FILES core/sql/*.sql core/plpgsql/*.sql"
echo 'Installing SQL from files:'
for i in $FILES; do 
    echo " - $i";
done
(echo 'BEGIN TRANSACTION; ' && cat $FILES && echo 'COMMIT; ' ) |  PGPASSWORD=$DBPASS PGOPTIONS="--client-min-messages=warning" psql -q -h localhost -U $DBUSER $DBNAME

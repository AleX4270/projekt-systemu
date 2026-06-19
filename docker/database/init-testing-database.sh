#!/usr/bin/env bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE USER system_project_testing WITH PASSWORD 'Iq621Ao?IP1L';
    CREATE DATABASE system_project_testing;
    GRANT ALL PRIVILEGES ON DATABASE system_project_testing TO system_project_testing;
EOSQL

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "system_project_testing" <<-EOSQL
    GRANT ALL ON SCHEMA public TO system_project_testing;
EOSQL
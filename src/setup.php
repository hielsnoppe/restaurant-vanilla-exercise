<?php

$db = new SQLite3('./data/db.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

$db->query('CREATE TABLE IF NOT EXISTS "menu_items" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" VARCHAR,
    "description" VARCHAR,
    "price" DECIMAL
)');

$db->query('INSERT INTO "menu_items" ("name", "description", "price") VALUES
("Vanille", "Vanillemilcheis", 1.5),
("Schokolade", "Schokoladensorbet", 1.5),
("Erdbeer", "Erdbeersorbet", 1.5)');

$db->query('CREATE TABLE IF NOT EXISTS "orders" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "created" DATETIME
)');

$db->query('CREATE TABLE IF NOT EXISTS "order_items" (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "order_id" INTEGER,
    "menu_item_id" INTEGER,
    "quantity" INTEGER
)');
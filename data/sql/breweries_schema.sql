DROP DATABASE IF EXISTS beers_api;

CREATE DATABASE beers_api;

USE beers_api;

DROP TABLE IF EXISTS user_reviews;
DROP TABLE IF EXISTS beer_distribution;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS beers;
DROP TABLE IF EXISTS distributors;
DROP TABLE IF EXISTS beer_styles;
DROP TABLE IF EXISTS breweries;

CREATE TABLE breweries (
    brewery_id INT AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    brewery_type TEXT NOT NULL,
    city TEXT NOT NULL,
    state TEXT,
    country TEXT NOT NULL,
    website_url TEXT,

    CONSTRAINT uq_breweries_name_city_country UNIQUE (name, city, country),
    CONSTRAINT chk_brewery_type CHECK (length(brewery_type) > 0)
);

CREATE TABLE beer_styles (
    style_id INT AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    origin_country TEXT,
    color TEXT,       
    CONSTRAINT uq_beer_styles_name UNIQUE (name)
);

CREATE TABLE beers (
    beer_id INT AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    brewery_id INT NOT NULL REFERENCES breweries(brewery_id) ON DELETE CASCADE,
    style_id INT REFERENCES beer_styles(style_id) ON DELETE SET NULL,
    abv NUMERIC(4,2) CHECK (abv IS NULL OR (abv >= 0 AND abv <= 100)),
    ibu INT CHECK (ibu IS NULL OR ibu >= 0),
    description TEXT,
    CONSTRAINT uq_beers_name_brewery UNIQUE (name, brewery_id)
);

CREATE TABLE locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    brewery_id INT NOT NULL REFERENCES breweries(brewery_id) ON DELETE CASCADE,
    address TEXT NOT NULL,
    latitude DOUBLE PRECISION,
    longitude DOUBLE PRECISION,
    open_hour TEXT
);

CREATE TABLE distributors (
    distributor_id INT AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    region TEXT,
    contact_email TEXT,
    phone_number TEXT,
    CONSTRAINT uq_distributors_name UNIQUE (name)
);

CREATE TABLE beer_distribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    beer_id INT NOT NULL REFERENCES beers(beer_id) ON DELETE CASCADE,
    distributor_id INT NOT NULL REFERENCES distributors(distributor_id) ON DELETE CASCADE,
    availability TEXT,
    packaging TEXT,
    CONSTRAINT uq_beer_distributor UNIQUE (beer_id, distributor_id)
);

CREATE TABLE user_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    beer_id INT NOT NULL REFERENCES beers(beer_id) ON DELETE CASCADE,
    username TEXT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_bodytext TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

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

-- =========================================================
-- BREWERIES
-- =========================================================
CREATE TABLE breweries (
    brewery_id INT AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    brewery_type TEXT NOT NULL,
    city TEXT NOT NULL,
    state TEXT,
    country TEXT NOT NULL,
    website_url TEXT,
    founded_year INT,
    owner_name TEXT,
    rating_avg DECIMAL(2,1),
    employee_count INT,

    CONSTRAINT uq_breweries_name_city_country UNIQUE (name, city, country),
    CONSTRAINT chk_brewery_type CHECK (length(brewery_type) > 0)
);

-- =========================================================
-- BEER STYLES
-- =========================================================
CREATE TABLE beer_styles (
    style_id INT AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    origin_country TEXT,
    color TEXT,
    typical_abv_range TEXT,
    glass_type TEXT,
    popularity_rank INT,
    pairing_foods TEXT,
    CONSTRAINT uq_beer_styles_name UNIQUE (name)
);

-- =========================================================
-- BEERS
-- =========================================================
CREATE TABLE beers (
    beer_id INT AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    brewery_id INT NOT NULL,
    style_id INT,
    abv NUMERIC(4,2) CHECK (abv IS NULL OR (abv >= 0 AND abv <= 100)),
    ibu INT CHECK (ibu IS NULL OR ibu >= 0),
    description TEXT,
    price DECIMAL(6,2),
    release_date DATE,
    calories INT,
    availability_status TEXT,

    CONSTRAINT uq_beers_name_brewery UNIQUE (name, brewery_id),
    CONSTRAINT fk_beer_brewery FOREIGN KEY (brewery_id) REFERENCES breweries(brewery_id) ON DELETE CASCADE,
    CONSTRAINT fk_beer_style FOREIGN KEY (style_id) REFERENCES beer_styles(style_id) ON DELETE SET NULL
);

-- =========================================================
-- LOCATIONS
-- =========================================================
CREATE TABLE locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    brewery_id INT NOT NULL,
    address TEXT NOT NULL,
    latitude DOUBLE PRECISION,
    longitude DOUBLE PRECISION,
    open_hour TEXT,
    phone_number TEXT,
    email TEXT,
    parking_available BOOLEAN,
    tour_available BOOLEAN,
    CONSTRAINT fk_location_brewery FOREIGN KEY (brewery_id) REFERENCES breweries(brewery_id) ON DELETE CASCADE
);

-- =========================================================
-- DISTRIBUTORS
-- =========================================================
CREATE TABLE distributors (
    distributor_id INT AUTO_INCREMENT PRIMARY KEY,
    name TEXT NOT NULL,
    region TEXT,
    contact_email TEXT,
    phone_number TEXT,
    founded_year INT,
    license_number TEXT,
    warehouse_count INT,
    rating_avg DECIMAL(2,1),
    CONSTRAINT uq_distributors_name UNIQUE (name)
);

-- =========================================================
-- BEER DISTRIBUTION
-- =========================================================
CREATE TABLE beer_distribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    beer_id INT NOT NULL,
    distributor_id INT NOT NULL,
    availability TEXT,
    packaging TEXT,
    price_per_unit DECIMAL(6,2),
    delivery_time_days INT,
    stock_level INT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_beer_distributor UNIQUE (beer_id, distributor_id),
    CONSTRAINT fk_distribution_beer FOREIGN KEY (beer_id) REFERENCES beers(beer_id) ON DELETE CASCADE,
    CONSTRAINT fk_distribution_distributor FOREIGN KEY (distributor_id) REFERENCES distributors(distributor_id) ON DELETE CASCADE
);

-- =========================================================
-- USER REVIEWS
-- =========================================================
CREATE TABLE user_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    beer_id INT NOT NULL,
    username TEXT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_bodytext TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    location TEXT,
    helpful_votes INT DEFAULT 0,
    verified_purchase BOOLEAN DEFAULT FALSE,
    photo_url TEXT,
    CONSTRAINT fk_review_beer FOREIGN KEY (beer_id) REFERENCES beers(beer_id) ON DELETE CASCADE
);

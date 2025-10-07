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
    CONSTRAINT fk_review_beer FOREIGN KEY (beer_id) REFERENCES beers(beer_id) ON DELETE CASCADE
);







-- Insertion of 10 records in each table.

/* ============================
   BEER STYLES (10)
   ============================ */
INSERT INTO beer_styles
(name, description, origin_country, color, typical_abv_range, glass_type, popularity_rank, pairing_foods)
VALUES
('Pilsner','Crisp pale lager with floral hop bite.','Czech Republic','Golden','4.4–5.4%','Pilsner flute',1,'sausages, pretzels'),
('North American Lager','Clean, highly carbonated pale lager.','Canada/USA','Pale gold','4.5–5.5%','Shaker pint',2,'wings, burgers'),
('Pale Ale','Balanced malt/hop profile.','England','Amber-gold','4.5–6.0%','Nonic pint',3,'fried chicken, tacos'),
('IPA','Hop-forward ale with firm bitterness.','England/USA','Gold to amber','5.5–7.5%','Nonic pint',4,'spicy food, pizza'),
('Imperial IPA','High gravity IPA, intense hops.','USA','Gold to deep gold','8.0–10.0%','Snifter',5,'BBQ, blue cheese'),
('Stout','Roasty, dark ale.','Ireland','Black','4.5–7.0%','Nonic pint',6,'oysters, desserts'),
('Porter','Chocolate/caramel malts, moderate roast.','England','Dark brown','4.5–6.5%','Nonic pint',7,'BBQ, stews'),
('Witbier','Belgian wheat ale with citrus/spice.','Belgium','Straw','4.5–5.5%','Weizen',8,'salads, goat cheese'),
('Saison','Dry, peppery farmhouse ale.','Belgium','Gold','5.0–7.5%','Tulip',9,'seafood, cheeses'),
('Tripel','Strong golden ale, fruity/spicy yeast.','Belgium','Deep gold','7.5–9.5%','Goblet',10,'shellfish, cheese');

/* ============================
   BREWERIES (10) — all Canadian
   ============================ */
INSERT INTO breweries (name, brewery_type, city, state, country, website_url, founded_year, owner_name, rating_avg, employee_count) VALUES
('Molson Brewery','Macro','Montréal','QC','Canada','https://www.molson.ca',1786,'Molson Coors Beverage Company',4.0,17000),
('Moosehead Breweries','Independent','Saint John','NB','Canada','https://moosehead.ca',1867,'Oland Family (Private Ownership)',4.1,300),
('Steam Whistle Brewing','Craft','Toronto','ON','Canada','https://steamwhistle.ca',2000,'Greg Taylor & Cam Heaps',4.2,300),
('Bellwoods Brewery','Craft','Toronto','ON','Canada','https://bellwoodsbrewery.com',2012,'Mike Clark & Luke Pestl',4.6,100),
('Unibroue','Craft','Chambly','QC','Canada','https://www.unibroue.com',1992,'Sapporo Holdings (via Sleeman Breweries Ltd.)',4.5,250),
('Dieu du Ciel!','Craft','Montréal','QC','Canada','https://dieuduciel.com',1998,'Jean-François Gravel & Stéphane Ostiguy',4.7,150),
('Phillips Brewing & Malting Co.','Craft','Victoria','BC','Canada','https://phillipsbeer.com',2001,'Matt Phillips',4.3,150),
('Driftwood Brewery','Craft','Victoria','BC','Canada','https://driftwoodbeer.com',2008,'Gary Lindsay & Kevin Yanush',4.4,60),
('Collective Arts Brewing','Craft','Hamilton','ON','Canada','https://collectiveartsbrewing.com',2013,'Matt Johnston & Bob Russell',4.3,200),
('Beau’s All Natural Brewing','Craft','Vankleek Hill','ON','Canada','https://beaus.ca',2006,'Steve & Tim Beauchesne',4.4,150);

/* ============================
   BEERS (10)
   ============================ */
INSERT INTO beers (name, brewery_id, style_id, abv, ibu, description, price, release_date, calories, availability_status) VALUES
('Molson Canadian',(SELECT brewery_id FROM breweries WHERE name='Molson Brewery'),(SELECT style_id FROM beer_styles WHERE name='North American Lager'),5.00,15,'Iconic Canadian pale lager brewed since 1959.',2.54,'1959-01-01',143,'Year-round'),
('Moosehead Lager',(SELECT brewery_id FROM breweries WHERE name='Moosehead Breweries'),(SELECT style_id FROM beer_styles WHERE name='North American Lager'),5.00,13,'Flagship lager from Canada’s oldest independent brewery.',2.70,'1933-01-01',144,'Year-round'),
('Steam Whistle Pilsner',(SELECT brewery_id FROM breweries WHERE name='Steam Whistle Brewing'),(SELECT style_id FROM beer_styles WHERE name='Pilsner'),5.00,22,'Bohemian-style Pilsner brewed with Saaz hops in Toronto.',3.75,'2000-05-01',165,'Year-round'),
('Jelly King',(SELECT brewery_id FROM breweries WHERE name='Bellwoods Brewery'),(SELECT style_id FROM beer_styles WHERE name='Pale Ale'),5.60,1,'Mixed-fermentation dry-hopped sour pale ale, refreshingly tart.',6.50,'2016-06-01',170,'Rotational'),
('Witchshark',(SELECT brewery_id FROM breweries WHERE name='Bellwoods Brewery'),(SELECT style_id FROM beer_styles WHERE name='Imperial IPA'),9.00,85,'Bold, hazy imperial IPA with juicy hop aroma.',5.40,'2012-06-01',270,'Rotational'),
('La Fin du Monde',(SELECT brewery_id FROM breweries WHERE name='Unibroue'),(SELECT style_id FROM beer_styles WHERE name='Tripel'),9.00,19,'Award-winning Belgian-style Tripel brewed in Québec since 1994.',11.99,'1994-02-01',270,'Year-round'),
('Blanche de Chambly',(SELECT brewery_id FROM breweries WHERE name='Unibroue'),(SELECT style_id FROM beer_styles WHERE name='Witbier'),5.00,10,'Belgian-style wheat ale brewed with coriander and orange peel.',2.98,'1992-01-01',150,'Year-round'),
('Péché Mortel',(SELECT brewery_id FROM breweries WHERE name='Dieu du Ciel!'),(SELECT style_id FROM beer_styles WHERE name='Stout'),9.50,76,'Coffee-infused imperial stout—rich, roasty, and intense.',3.88,'2001-01-01',270,'Seasonal'),
('Blue Buck',(SELECT brewery_id FROM breweries WHERE name='Phillips Brewing & Malting Co.'),(SELECT style_id FROM beer_styles WHERE name='Pale Ale'),5.00,23,'Approachable West Coast pale ale, a Phillips flagship.',2.75,'2001-06-01',126,'Year-round'),
('Fat Tug IPA',(SELECT brewery_id FROM breweries WHERE name='Driftwood Brewery'),(SELECT style_id FROM beer_styles WHERE name='IPA'),7.00,80,'Hop-saturated West Coast IPA with bold pine and citrus notes.',3.95,'2010-01-01',210,'Year-round');

/* ============================
   LOCATIONS (10) — one per brewery
   ============================ */
INSERT INTO locations
(brewery_id, address, latitude, longitude, open_hour, phone_number, email, parking_available, tour_available)
VALUES
((SELECT brewery_id FROM breweries WHERE name='Molson Brewery'),
 'Montréal, QC, Canada', 45.5188, -73.5470, 'See website',
 '+1-514-595-1786', 'info@molsoncoors.com', TRUE, FALSE),

((SELECT brewery_id FROM breweries WHERE name='Moosehead Breweries'),
 'Saint John, NB, Canada', 45.2572, -66.0959, 'See website',
 '+1-506-635-7000', 'info@moosehead.ca', TRUE, TRUE),

((SELECT brewery_id FROM breweries WHERE name='Steam Whistle Brewing'),
 '255 Bremner Blvd, Toronto, ON, Canada', 43.6411, -79.3853, 'See website',
 '+1-416-362-2337', 'info@steamwhistle.ca', TRUE, TRUE),

((SELECT brewery_id FROM breweries WHERE name='Bellwoods Brewery'),
 '124 Ossington Ave, Toronto, ON, Canada', 43.6480, -79.4212, 'See website',
 '+1-416-535-4586', 'info@bellwoodsbrewery.com', FALSE, FALSE),

((SELECT brewery_id FROM breweries WHERE name='Unibroue'),
 '80 Rue des Carrières, Chambly, QC, Canada', 45.4362, -73.2855, 'Mon–Fri',
 '+1-450-658-7653', 'info@unibroue.com', TRUE, FALSE),

((SELECT brewery_id FROM breweries WHERE name='Dieu du Ciel!'),
 '21 Av. Laurier O, Montréal, QC, Canada', 45.5246, -73.5973, 'Daily',
 '+1-514-490-9555', 'info@dieuduciel.com', FALSE, FALSE),

((SELECT brewery_id FROM breweries WHERE name='Phillips Brewing & Malting Co.'),
 '2010 Government St, Victoria, BC, Canada', 48.4335, -123.3604, 'See website',
 '+1-250-380-1912', 'info@phillipsbeer.com', TRUE, TRUE),

((SELECT brewery_id FROM breweries WHERE name='Driftwood Brewery'),
 '836 Viewfield Rd, Victoria, BC, Canada', 48.4516, -123.3690, 'See website',
 '+1-250-381-2739', 'info@driftwoodbeer.com', TRUE, FALSE),

((SELECT brewery_id FROM breweries WHERE name='Collective Arts Brewing'),
 '207 Burlington St E, Hamilton, ON, Canada', 43.2665, -79.8556, 'See website',
 '+1-289-389-1000', 'info@collectiveartsbrewing.com', TRUE, TRUE),

((SELECT brewery_id FROM breweries WHERE name='Beau’s All Natural Brewing'),
 '10 Terry Fox Dr, Vankleek Hill, ON, Canada', 45.5167, -74.6481, 'See website',
 '+1-866-585-2337', 'info@beaus.ca', TRUE, TRUE);

/* ============================
   DISTRIBUTORS (10) — Canadian provincial/territorial
   ============================ */
INSERT INTO distributors (name, region, contact_email, phone_number, founded_year, license_number, warehouse_count, rating_avg) VALUES
('LCBO (Liquor Control Board of Ontario)','Ontario','info@lcbo.com','+1-416-365-5900',1927,'ON-LCBO',5,4.3),
('The Beer Store (Brewers Retail Inc.)','Ontario','info@thebeerstore.ca','+1-905-361-1006',1927,'ON-TBS',3,4.0),
('SAQ (Société des alcools du Québec)','Québec','info@saq.com','+1-866-873-2020',1971,'QC-SAQ',3,4.2),
('BC Liquor Distribution Branch','British Columbia','info@bcldb.com','+1-604-252-7400',1921,'BC-LDB',2,4.1),
('AGLC (Alberta Gaming, Liquor and Cannabis)','Alberta','info@aglc.ca','+1-780-447-8600',1993,'AB-AGLC',3,4.0),
('MBLL (Manitoba Liquor & Lotteries)','Manitoba','info@mbll.ca','+1-204-957-2500',2013,'MB-MBLL',2,3.9),
('SLGA (Saskatchewan Liquor and Gaming Authority)','Saskatchewan','info@slga.gov.sk.ca','+1-306-787-5563',1947,'SK-SLGA',1,3.8),
('NSLC (Nova Scotia Liquor Corporation)','Nova Scotia','info@nslc.ca','+1-902-450-6752',1930,'NS-NSLC',1,4.0),
('NLC (Newfoundland & Labrador Liquor Corporation)','Newfoundland and Labrador','info@nlc.nl.ca','+1-709-724-1100',1954,'NL-NLC',1,3.9),
('PEILCC (Prince Edward Island Liquor Control Commission)','Prince Edward Island','info@peilcc.ca','+1-902-368-5710',1948,'PE-PEILCC',1,3.9);

/* ============================
   BEER DISTRIBUTION (10)
   ============================ */
INSERT INTO beer_distribution
(beer_id, distributor_id, availability, packaging, price_per_unit, delivery_time_days, stock_level)
VALUES
((SELECT beer_id FROM beers WHERE name='Molson Canadian'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'LCBO%'),
 'Province-wide','473mL can, 24-pack bottles', 2.95, 3, 1200),

((SELECT beer_id FROM beers WHERE name='Moosehead Lager'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'LCBO%'),
 'Province-wide','473mL can, 24-pack bottles', 3.05, 3, 900),

((SELECT beer_id FROM beers WHERE name='Steam Whistle Pilsner'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'LCBO%'),
 'Province-wide','473mL can, 6/12 bottles', 3.25, 3, 700),

((SELECT beer_id FROM beers WHERE name='La Fin du Monde'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'SAQ%'),
 'Province-wide','750mL bottle, 341mL bottle', 5.50, 4, 500),

((SELECT beer_id FROM beers WHERE name='Blanche de Chambly'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'SAQ%'),
 'Province-wide','341mL bottle, 473mL can', 3.50, 4, 800),

((SELECT beer_id FROM beers WHERE name='Péché Mortel'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'SAQ%'),
 'Selective','341mL bottle, 473mL can', 5.75, 5, 250),

((SELECT beer_id FROM beers WHERE name='Blue Buck'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'BC Liquor%'),
 'Province-wide','355mL can, 6-pack', 2.95, 3, 600),

((SELECT beer_id FROM beers WHERE name='Fat Tug IPA'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'BC Liquor%'),
 'Province-wide','473mL can, 4-pack', 4.25, 3, 650),

((SELECT beer_id FROM beers WHERE name='Jelly King'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'LCBO%'),
 'Rotational','473mL can, 500mL bottle', 6.50, 4, 300),

((SELECT beer_id FROM beers WHERE name='Witchshark'),
 (SELECT distributor_id FROM distributors WHERE name LIKE 'LCBO%'),
 'Rotational','473mL can', 6.95, 4, 180);

/* ============================
   USER REVIEWS (10)
   ============================ */
INSERT INTO user_reviews
(beer_id, username, rating, review_bodytext, location, helpful_votes, verified_purchase)
VALUES
((SELECT beer_id FROM beers WHERE name='Steam Whistle Pilsner'),'to_beaver',5,'Crisp & clean—perfect patio beer.','Toronto, ON',12,TRUE),
((SELECT beer_id FROM beers WHERE name='La Fin du Monde'),'qc_tripel_fan',5,'Fruity, spicy, beautifully balanced Tripel.','Montréal, QC',20,TRUE),
((SELECT beer_id FROM beers WHERE name='Péché Mortel'),'mtl_roast',5,'Huge coffee nose and rich body—dessert in a glass.','Montréal, QC',9,FALSE),
((SELECT beer_id FROM beers WHERE name='Jelly King'),'parkdale_pal',4,'Bright acidity + hop aroma; super refreshing.','Toronto, ON',6,TRUE),
((SELECT beer_id FROM beers WHERE name='Moosehead Lager'),'nb_local',4,'Solid everyday lager from a true independent.','Saint John, NB',5,TRUE),
((SELECT beer_id FROM beers WHERE name='Molson Canadian'),'habsandbeer',3,'Simple and clean—great for game nights.','Laval, QC',3,FALSE),
((SELECT beer_id FROM beers WHERE name='Blue Buck'),'island_hopper',4,'Malty, easy-drinking pale ale—great fridge staple.','Victoria, BC',4,TRUE),
((SELECT beer_id FROM beers WHERE name='Fat Tug IPA'),'westcoast_hops',5,'Piney, resinous, big bitterness—classic WC IPA.','Vancouver, BC',11,TRUE),
((SELECT beer_id FROM beers WHERE name='Blanche de Chambly'),'summer_wheat',4,'Soft citrus and spice—excellent with brunch.','Québec City, QC',7,FALSE),
((SELECT beer_id FROM beers WHERE name='Witchshark'),'ipa_addict',5,'Juicy, intense, yet balanced for the ABV.','Toronto, ON',8,TRUE);

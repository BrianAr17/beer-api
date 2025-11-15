POST /breweries   
    examples for requestBody:
              single:
                summary: Create a single brewery
                value:
                  name: "Jackson Brewery 1"
                  brewery_type: "Macro"
                  city: "Toronto"
                  state: "On"
                  country: "Canada"
                  website_url: "https://www.jackson1.ca"
                  founded_year: 1786
                  owner_name: "Jackson Beverage 1 Company"
                  rating_avg: 4.0
                  employee_count: 1000
              multiple:
                summary: Create multiple breweries
                value:
                  - name: "Jackson Brewery 2"
                    brewery_type: "Macro"
                    city: "Montréal"
                    state: "Qc"
                    country: "Canada"
                    website_url: "https://www.jackson2.ca"
                    founded_year: 1787
                    owner_name: "Jackson Beverage 2 Company"
                    rating_avg: 4.0
                    employee_count: 872

    examples for responses:
        examples:
                created:
                  value:
                    last_inserted_id: "13"


PUT /breweries
    examples for requestBody:
              multipleUpdate:
                summary: Update two breweries
                value:
                  - brewery_id: 9
                    name: "Jackson esdfdddddddddddgg"
                    brewery_type: "Macro"
                    city: "Montréal"
                    state: "Qc"
                    country: "Canada"
                    website_url: "https://www.jackson.ca"
                    founded_year: 1786
                    owner_name: "Jackson Beverage Company"
                    rating_avg: 4.0
                    employee_count: 1000
                  - brewery_id: 10
                    name: "Jackson Basdddddddddddddwr"
                    brewery_type: "Macro"
                    city: "Montréal"
                    state: "Qc"
                    country: "Canada"
                    website_url: "https://www.jackson.ca"
                    founded_year: 1786
                    owner_name: "Jackson Beverage Company"
                    rating_avg: 4.0
                    employee_count: 1000
                    
    examples for responses:
        examples:
                updated:
                  value:
                    status: "ok"
                    total_rows_affected: 2
                    details:
                      - brewery_id: 9
                        status: "ok"
                      - brewery_id: 10
                        status: "ok"


DELETE /breweries
    examples for requestBody:
              deleteMultiple:
                summary: Delete multiple breweries
                value: [16, 18]

    examples for responses:
                deleted:
                    value:
                    rows_deleted: 2

# Packages and Places API

These public, read-only endpoints return JSON. The production base URL is `https://booking.voyantaservices.com/api`. No authentication is required. Browser requests from `https://voyantaservices.com` are allowed by CORS.

Example browser request from the HTML website:

```html
<script>
  fetch('https://booking.voyantaservices.com/api/packages')
    .then((response) => response.json())
    .then(({ data }) => console.log(data));
</script>
```

All list endpoints use Laravel pagination. The `data` property contains the records, `links` contains navigation URLs, and `meta` describes the current page. Image fields are absolute URLs built from the application's storage URL.

## Packages

### List packages

`GET /api/packages`

Only active packages are returned. Each package includes its itinerary grouped by day and a `places` array with full details for every unique place linked as a destination or itinerary stop. Each itinerary stop includes its linked place details, and hotel or inclusion data when configured. Optional query parameters:

| Parameter | Type | Description |
| --- | --- | --- |
| `search` | string | Match package name |
| `destination` | integer | Filter by a destination place ID |
| `duration` | integer | Filter by number of days |
| `min_price` | number | Minimum adult base price (`price.adult.base_price`) |
| `max_price` | number | Maximum adult base price (`price.adult.base_price`) |
| `per_page` | integer | Results per page, from 1 to 100 (default 12) |
| `page` | integer | Page number |

Example request: `GET /api/packages?search=Kerala&duration=5&per_page=10`

The adult price object reports the same per-adult package estimate used on the package detail page: base package price, linked hotel and itinerary costs, state daily costs, discount, tax, and final estimated total. Place prices in this estimate use the India adult rate. Child and infant base prices are separate and are not included in the adult estimate. Amounts are numeric INR values rounded to two decimal places. `pre_discount_total` is before the package discount; `discounted_total` is after discount and before tax; `estimated_total` is after tax.

Example response:

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "name": "Kerala Backwaters Escape",
      "slug": "kerala-backwaters-escape",
      "category": "Family",
      "tour_type": "Domestic",
      "description": "Explore the backwaters and hill country.",
      "departure_city": "Kochi",
      "price": {
        "currency": "INR",
        "adult": {
          "base_price": 25000.00,
          "hotel_cost": 0.00,
          "state_daily_cost": 0.00,
          "itinerary_cost": 500.00,
          "pre_discount_total": 25500.00,
          "discount_percentage": 10.00,
          "discount_amount": 2550.00,
          "discounted_total": 22950.00,
          "tax_percentage": 5.00,
          "tax_amount": 1147.50,
          "estimated_total": 24097.50
        },
        "child": "18000.00",
        "infant": null
      },
      "duration": { "days": 5, "nights": 4 },
      "image": "https://example.com/storage/packages/kerala.jpg",
      "gallery": ["https://example.com/storage/packages/kerala-1.jpg"],
      "views": 0,
      "availability": [
        { "departure_date": "2026-12-10", "seat_limit": 20, "adult_price_override": null }
      ],
      "destinations": [
        { "id": 7, "name": "Alleppey Backwaters", "slug": "alleppey-backwaters" }
      ],
      "itinerary": [
        {
          "day": 1,
          "stops": [
            {
              "id": 81,
              "type": "place",
              "start_time": "09:00:00",
              "additional_price": "0.00",
              "nights": null,
              "place": {
                "id": 7,
                "name": "Alleppey Backwaters",
                "slug": "alleppey-backwaters",
                "place_description": "A peaceful network of canals and lagoons.",
                "description": "Enjoy a boat ride through Kerala's famous backwaters.",
                "city": { "id": 3, "name": "Alappuzha", "state": "Kerala", "country": "India" },
                "images": {
                  "primary": "https://example.com/storage/places/alleppey.jpg",
                  "secondary": ["https://example.com/storage/places/alleppey-2.jpg"]
                },
                "visit_duration_hours": "3.0",
                "best_time_to_visit": "morning",
                "prices": {
                  "per_person": "500.00",
                  "child": "250.00",
                  "indian": "500.00",
                  "foreigner": "1200.00",
                  "student": "300.00"
                },
                "note": "Boat charges may vary by season."
              },
              "hotel": null,
              "inclusion": null
            }
          ]
        }
      ]
    }
  ],
  "links": {
    "first": "https://example.com/api/packages?page=1",
    "last": "https://example.com/api/packages?page=1",
    "prev": null,
    "next": null
  },
  "meta": { "current_page": 1, "per_page": 10, "total": 1, "last_page": 1 }
}
```

### Get a package

`GET /api/packages/{slug}`

Example request: `GET /api/packages/kerala-backwaters-escape`

The detail response includes the same complete package fields, itinerary, full `places` array, hotels, inclusions, and availability as each item in the list response.

Example response:

```json
{
  "success": true,
  "data": {
    "id": 12,
    "name": "Kerala Backwaters Escape",
    "slug": "kerala-backwaters-escape",
    "category": "Family",
    "tour_type": "Domestic",
    "description": "Explore the backwaters and hill country.",
    "departure_city": "Kochi",
    "price": {
        "currency": "INR",
        "adult": {
          "base_price": 25000.00,
          "hotel_cost": 0.00,
          "state_daily_cost": 0.00,
          "itinerary_cost": 500.00,
          "pre_discount_total": 25500.00,
          "discount_percentage": 10.00,
          "discount_amount": 2550.00,
          "discounted_total": 22950.00,
          "tax_percentage": 5.00,
          "tax_amount": 1147.50,
          "estimated_total": 24097.50
        },
        "child": "18000.00",
        "infant": null
      },
    "duration": { "days": 5, "nights": 4 },
    "image": "https://example.com/storage/packages/kerala.jpg",
    "gallery": ["https://example.com/storage/packages/kerala-1.jpg"],
    "destinations": [
      { "id": 7, "name": "Alleppey Backwaters", "slug": "alleppey-backwaters" }
    ],
    "views": 0,
    "availability": [
      { "departure_date": "2026-12-10", "seat_limit": 20, "adult_price_override": null }
    ],
    "itinerary": [
      {
        "day": 1,
        "stops": [
          {
            "id": 81,
            "type": "place",
            "start_time": "09:00:00",
            "additional_price": "0.00",
            "nights": null,
            "place": {
              "id": 7,
              "name": "Alleppey Backwaters",
              "slug": "alleppey-backwaters",
              "place_description": "A peaceful network of canals and lagoons.",
              "description": "Enjoy a boat ride through Kerala's famous backwaters.",
              "city": { "id": 3, "name": "Alappuzha", "state": "Kerala", "country": "India" },
              "images": {
                "primary": "https://example.com/storage/places/alleppey.jpg",
                "secondary": ["https://example.com/storage/places/alleppey-2.jpg"]
              },
              "visit_duration_hours": "3.0",
              "best_time_to_visit": "morning",
              "prices": {
                "per_person": "500.00",
                "child": "250.00",
                "indian": "500.00",
                "foreigner": "1200.00",
                "student": "300.00"
              },
              "note": "Boat charges may vary by season."
            },
            "hotel": null,
            "inclusion": null
          }
        ]
      }
    ]
  }
}
```

The `availability` array contains configured departure dates, seat limits, and optional adult price overrides. Each itinerary entry is a day with ordered stops. Stops may contain a `place`, `hotel`, and/or `inclusion`; fields that do not apply are `null`. Missing or inactive package slugs return HTTP `404`.

## Places

### List places

`GET /api/places`

Optional query parameters:

| Parameter | Type | Description |
| --- | --- | --- |
| `search` | string | Match place name |
| `city_id` | integer | Filter by city ID |
| `best_time_to_visit` | string | One of `morning`, `afternoon`, `evening`, or `night` |
| `per_page` | integer | Results per page, from 1 to 100 (default 12) |
| `page` | integer | Page number |

Example request: `GET /api/places?city_id=3&best_time_to_visit=morning`

Example response:

```json
{
  "success": true,
  "data": [
    {
      "id": 7,
      "name": "Alleppey Backwaters",
      "slug": "alleppey-backwaters",
      "place_description": "A peaceful network of canals and lagoons.",
      "description": "Enjoy a boat ride through Kerala's famous backwaters.",
      "city": { "id": 3, "name": "Alappuzha", "state": "Kerala", "country": "India" },
      "images": {
        "primary": "https://example.com/storage/places/alleppey.jpg",
        "secondary": ["https://example.com/storage/places/alleppey-2.jpg"]
      },
      "visit_duration_hours": "3.0",
      "best_time_to_visit": "morning",
      "prices": {
        "per_person": "500.00",
        "child": "250.00",
        "indian": "500.00",
        "foreigner": "1200.00",
        "student": "300.00"
      },
      "note": "Boat charges may vary by season."
    }
  ],
  "links": {
    "first": "https://example.com/api/places?page=1",
    "last": "https://example.com/api/places?page=1",
    "prev": null,
    "next": null
  },
  "meta": { "current_page": 1, "per_page": 12, "total": 1, "last_page": 1 }
}
```

### Get a place

`GET /api/places/{slug}`

Example request: `GET /api/places/alleppey-backwaters`

Example response:

```json
{
  "success": true,
  "data": {
    "id": 7,
    "name": "Alleppey Backwaters",
    "slug": "alleppey-backwaters",
    "place_description": "A peaceful network of canals and lagoons.",
    "description": "Enjoy a boat ride through Kerala's famous backwaters.",
    "city": { "id": 3, "name": "Alappuzha", "state": "Kerala", "country": "India" },
    "images": {
      "primary": "https://example.com/storage/places/alleppey.jpg",
      "secondary": ["https://example.com/storage/places/alleppey-2.jpg"]
    },
    "visit_duration_hours": "3.0",
    "best_time_to_visit": "morning",
    "prices": {
      "per_person": "500.00",
      "child": "250.00",
      "indian": "500.00",
      "foreigner": "1200.00",
      "student": "300.00"
    },
    "note": "Boat charges may vary by season."
  }
}
```

Unknown slugs return HTTP `404`. Invalid query parameters return HTTP `422` with Laravel's JSON validation error format.

## Locations

These read-only endpoints expose the location hierarchy used by destinations and package itineraries. Each list endpoint is paginated and returns the standard `success`, `data`, `links`, and `meta` fields. No authentication is required.

### Countries

- `GET /api/countries` — list countries, alphabetically.
- `GET /api/countries/{id}` — get a country.

Optional list query parameters:

| Parameter | Type | Description |
| --- | --- | --- |
| `search` | string | Match country name |
| `per_page` | integer | Results per page, from 1 to 100 (default 12) |
| `page` | integer | Page number |

Example: `GET /api/countries?search=India`

Each country record has this shape:

```json
{
  "id": 1,
  "name": "India",
  "states_count": 28
}
```

### States

- `GET /api/states` — list states.
- `GET /api/states/{id}` — get a state.

Optional list query parameters:

| Parameter | Type | Description |
| --- | --- | --- |
| `country_id` | integer | Filter by country ID |
| `search` | string | Match state name |
| `per_page` | integer | Results per page, from 1 to 100 (default 12) |
| `page` | integer | Page number |

Example: `GET /api/states?country_id=1&per_page=50`

Each state record has this shape:

```json
{
  "id": 5,
  "name": "Himachal Pradesh",
  "price_per_day": "0.00",
  "cities_count": 12,
  "country": { "id": 1, "name": "India" }
}
```

### Cities

- `GET /api/cities` — list cities.
- `GET /api/cities/{slug}` — get a city by slug.

Optional list query parameters:

| Parameter | Type | Description |
| --- | --- | --- |
| `state_id` | integer | Filter by state ID |
| `country_id` | integer | Filter by country ID |
| `search` | string | Match city name |
| `per_page` | integer | Results per page, from 1 to 100 (default 12) |
| `page` | integer | Page number |

Example: `GET /api/cities?state_id=5`

Each city record has this shape:

```json
{
  "id": 20,
  "name": "Manali",
  "slug": "manali",
  "description": "A high-altitude resort town in Himachal Pradesh.",
  "images": {
    "primary": "https://example.com/storage/cities/manali.jpg",
    "secondary": ["https://example.com/storage/cities/manali-2.jpg"]
  },
  "places_count": 8,
  "state": {
    "id": 5,
    "name": "Himachal Pradesh",
    "country": { "id": 1, "name": "India" }
  }
}
```

Invalid filter values return HTTP `422` with Laravel's JSON validation error format. Unknown country, state, or city identifiers return HTTP `404`.

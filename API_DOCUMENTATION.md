# API Documentation - Laravel Company Contacts

## Overview

This document provides comprehensive documentation for all API endpoints available in the Laravel Company Contacts application. The API allows for searching, filtering, and managing contacts and tags.

**Version:** 1.0.0  
**Last Updated:** July 21, 2025  
**Base URL:** `https://your-domain.com/api`

## Authentication

Most API endpoints that modify data require authentication using Laravel Sanctum. Public endpoints for viewing and searching contacts are available without authentication.

### Authentication Headers

For protected endpoints, include the following header:

```
Authorization: Bearer {your_api_token}
```

To obtain an API token, use the login endpoint.

## Response Format

All API responses follow a consistent JSON format:

```json
{
  "success": true|false,
  "data": {...},  // For successful responses
  "error": "Error message"  // For error responses
}
```

## Error Codes

- `401` - Unauthorized (Authentication required)
- `403` - Forbidden (Insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Endpoints

### Authentication

#### Login

```
POST /api/auth/login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "device_name": "Browser" // Optional
}
```

**Response:**
```json
{
  "success": true,
  "token": "your_api_token",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com"
  }
}
```

#### Logout

```
POST /api/auth/logout
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Contacts

#### List Contacts

```
GET /api/contacts
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Results per page (default: 15)
- `sort_by` - Field to sort by (default: last_name)
- `sort_dir` - Sort direction: asc or desc (default: asc)

**Response:**
```json
{
  "success": true,
  "contacts": [
    {
      "id": 1,
      "first_name": "Jan",
      "last_name": "Kowalski",
      "full_name": "Jan Kowalski",
      "email": "jan.kowalski@example.com",
      "phone": "123456789",
      "company": "ABC Corp",
      "position": "Manager",
      "tags": [
        {
          "id": 1,
          "name": "VIP",
          "color": "#FF0000"
        }
      ],
      "created_at": "2025-07-21T12:00:00.000000Z",
      "updated_at": "2025-07-21T12:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4
  }
}
```

#### Search Contacts

```
GET /api/search
```

**Query Parameters:**
- `query` - Search text (optional)
- `company` - Filter by company (optional)
- `position` - Filter by position (optional)
- `tags` - Array of tag IDs (optional)
- `tag_search_mode` - "any" or "all" (default: "any")
- `page` - Page number (default: 1)
- `per_page` - Results per page (default: 15)

**Response:**
```json
{
  "success": true,
  "contacts": [
    {
      "id": 1,
      "first_name": "Jan",
      "last_name": "Kowalski",
      "full_name": "Jan Kowalski",
      "email": "jan.kowalski@example.com",
      "phone": "123456789",
      "company": "ABC Corp",
      "position": "Manager",
      "tags": [
        {
          "id": 1,
          "name": "VIP",
          "color": "#FF0000"
        }
      ],
      "initials": "JK"
    }
  ],
  "total": 1,
  "per_page": 15,
  "current_page": 1,
  "last_page": 1
}
```

#### Get Contact

```
GET /api/contacts/{id}
```

**Response:**
```json
{
  "success": true,
  "contact": {
    "id": 1,
    "first_name": "Jan",
    "last_name": "Kowalski",
    "full_name": "Jan Kowalski",
    "email": "jan.kowalski@example.com",
    "phone": "123456789",
    "company": "ABC Corp",
    "position": "Manager",
    "tags": [
      {
        "id": 1,
        "name": "VIP",
        "color": "#FF0000"
      }
    ],
    "created_at": "2025-07-21T12:00:00.000000Z",
    "updated_at": "2025-07-21T12:00:00.000000Z"
  }
}
```

#### Create Contact (Protected)

```
POST /api/contacts
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Request Body:**
```json
{
  "first_name": "Anna",
  "last_name": "Nowak",
  "email": "anna.nowak@example.com",
  "phone": "987654321",
  "company": "XYZ Ltd",
  "position": "Developer",
  "tags": [1, 2]
}
```

**Response:**
```json
{
  "success": true,
  "contact": {
    "id": 2,
    "first_name": "Anna",
    "last_name": "Nowak",
    "full_name": "Anna Nowak",
    "email": "anna.nowak@example.com",
    "phone": "987654321",
    "company": "XYZ Ltd",
    "position": "Developer",
    "tags": [
      {
        "id": 1,
        "name": "VIP",
        "color": "#FF0000"
      },
      {
        "id": 2,
        "name": "Partner",
        "color": "#0000FF"
      }
    ],
    "created_at": "2025-07-21T12:00:00.000000Z",
    "updated_at": "2025-07-21T12:00:00.000000Z"
  }
}
```

#### Update Contact (Protected)

```
PUT /api/contacts/{id}
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Request Body:**
```json
{
  "first_name": "Jan",
  "last_name": "Kowalski",
  "email": "jan.kowalski@example.com",
  "phone": "123456789",
  "company": "Updated Company",
  "position": "Senior Manager",
  "tags": [1, 2]
}
```

**Response:**
```json
{
  "success": true,
  "contact": {
    "id": 1,
    "first_name": "Jan",
    "last_name": "Kowalski",
    "full_name": "Jan Kowalski",
    "email": "jan.kowalski@example.com",
    "phone": "123456789",
    "company": "Updated Company",
    "position": "Senior Manager",
    "tags": [
      {
        "id": 1,
        "name": "VIP",
        "color": "#FF0000"
      },
      {
        "id": 2,
        "name": "Partner",
        "color": "#0000FF"
      }
    ],
    "created_at": "2025-07-21T12:00:00.000000Z",
    "updated_at": "2025-07-21T12:00:00.000000Z"
  }
}
```

#### Delete Contact (Protected)

```
DELETE /api/contacts/{id}
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Contact deleted successfully"
}
```

#### Export Contacts (Protected)

```
GET /api/contacts/export
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Query Parameters:**
- `query` - Search text (optional)
- `company` - Filter by company (optional)
- `position` - Filter by position (optional)
- `tags` - Array of tag IDs (optional)
- `tag_search_mode` - "any" or "all" (default: "any")

**Response:**
CSV file download with contacts data.

#### Generate QR Code

```
GET /api/contacts/{id}/qr
```

**Response:**
SVG image of QR code containing vCard data.

### Search Features

#### Search Suggestions

```
GET /api/search/suggestions
```

**Query Parameters:**
- `query` - Search text (minimum 2 characters)

**Response:**
```json
{
  "success": true,
  "suggestions": [
    {
      "type": "name",
      "value": "Jan Kowalski",
      "label": "Jan Kowalski"
    },
    {
      "type": "company",
      "value": "ABC Corp",
      "label": "ABC Corp (firma)"
    },
    {
      "type": "position",
      "value": "Manager",
      "label": "Manager (stanowisko)"
    },
    {
      "type": "tag",
      "value": "VIP",
      "label": "VIP (tag)",
      "id": 1,
      "color": "#FF0000"
    }
  ]
}
```

#### Group By Company

```
GET /api/search/group-by-company
```

**Query Parameters:**
- `query` - Search text (optional)
- `company` - Filter by company (optional)
- `position` - Filter by position (optional)
- `tags` - Array of tag IDs (optional)
- `tag_search_mode` - "any" or "all" (default: "any")

**Response:**
```json
{
  "success": true,
  "groups": [
    {
      "company": "ABC Corp",
      "count": 1,
      "contacts": [
        {
          "id": 1,
          "first_name": "Jan",
          "last_name": "Kowalski",
          "full_name": "Jan Kowalski",
          "email": "jan.kowalski@example.com",
          "phone": "123456789",
          "company": "ABC Corp",
          "position": "Manager",
          "tags": [
            {
              "id": 1,
              "name": "VIP",
              "color": "#FF0000"
            }
          ],
          "initials": "JK"
        }
      ]
    },
    {
      "company": "XYZ Ltd",
      "count": 1,
      "contacts": [
        {
          "id": 2,
          "first_name": "Anna",
          "last_name": "Nowak",
          "full_name": "Anna Nowak",
          "email": "anna.nowak@example.com",
          "phone": "987654321",
          "company": "XYZ Ltd",
          "position": "Developer",
          "tags": [
            {
              "id": 1,
              "name": "VIP",
              "color": "#FF0000"
            },
            {
              "id": 2,
              "name": "Partner",
              "color": "#0000FF"
            }
          ],
          "initials": "AN"
        }
      ]
    }
  ],
  "total": 2
}
```

#### Group By Position

```
GET /api/search/group-by-position
```

**Query Parameters:**
- `query` - Search text (optional)
- `company` - Filter by company (optional)
- `position` - Filter by position (optional)
- `tags` - Array of tag IDs (optional)
- `tag_search_mode` - "any" or "all" (default: "any")

**Response:**
```json
{
  "success": true,
  "groups": [
    {
      "position": "Developer",
      "count": 1,
      "contacts": [
        {
          "id": 2,
          "first_name": "Anna",
          "last_name": "Nowak",
          "full_name": "Anna Nowak",
          "email": "anna.nowak@example.com",
          "phone": "987654321",
          "company": "XYZ Ltd",
          "position": "Developer",
          "tags": [
            {
              "id": 1,
              "name": "VIP",
              "color": "#FF0000"
            },
            {
              "id": 2,
              "name": "Partner",
              "color": "#0000FF"
            }
          ],
          "initials": "AN"
        }
      ]
    },
    {
      "position": "Manager",
      "count": 1,
      "contacts": [
        {
          "id": 1,
          "first_name": "Jan",
          "last_name": "Kowalski",
          "full_name": "Jan Kowalski",
          "email": "jan.kowalski@example.com",
          "phone": "123456789",
          "company": "ABC Corp",
          "position": "Manager",
          "tags": [
            {
              "id": 1,
              "name": "VIP",
              "color": "#FF0000"
            }
          ],
          "initials": "JK"
        }
      ]
    }
  ],
  "total": 2
}
```

#### Search By Tags

```
GET /api/search/by-tags
```

**Query Parameters:**
- `tag_ids` - Array of tag IDs (required)
- `search_mode` - "any" or "all" (default: "any")

**Response:**
```json
{
  "success": true,
  "contacts": [
    {
      "id": 1,
      "first_name": "Jan",
      "last_name": "Kowalski",
      "full_name": "Jan Kowalski",
      "email": "jan.kowalski@example.com",
      "phone": "123456789",
      "company": "ABC Corp",
      "position": "Manager",
      "tags": [
        {
          "id": 1,
          "name": "VIP",
          "color": "#FF0000"
        }
      ],
      "initials": "JK"
    },
    {
      "id": 2,
      "first_name": "Anna",
      "last_name": "Nowak",
      "full_name": "Anna Nowak",
      "email": "anna.nowak@example.com",
      "phone": "987654321",
      "company": "XYZ Ltd",
      "position": "Developer",
      "tags": [
        {
          "id": 1,
          "name": "VIP",
          "color": "#FF0000"
        },
        {
          "id": 2,
          "name": "Partner",
          "color": "#0000FF"
        }
      ],
      "initials": "AN"
    }
  ],
  "total": 2,
  "search_mode": "any"
}
```

#### Get Available Tags

```
GET /api/search/available-tags
```

**Response:**
```json
{
  "success": true,
  "tags": [
    {
      "id": 1,
      "name": "VIP",
      "color": "#FF0000",
      "contacts_count": 2
    },
    {
      "id": 2,
      "name": "Partner",
      "color": "#0000FF",
      "contacts_count": 1
    }
  ]
}
```

### Tags (All Protected)

#### List Tags

```
GET /api/tags
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Response:**
```json
{
  "success": true,
  "tags": [
    {
      "id": 1,
      "name": "VIP",
      "color": "#FF0000",
      "contacts_count": 2,
      "created_at": "2025-07-21T12:00:00.000000Z",
      "updated_at": "2025-07-21T12:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Partner",
      "color": "#0000FF",
      "contacts_count": 1,
      "created_at": "2025-07-21T12:00:00.000000Z",
      "updated_at": "2025-07-21T12:00:00.000000Z"
    }
  ]
}
```

#### Get Tag

```
GET /api/tags/{id}
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Response:**
```json
{
  "success": true,
  "tag": {
    "id": 1,
    "name": "VIP",
    "color": "#FF0000",
    "contacts_count": 2,
    "contacts": [
      {
        "id": 1,
        "first_name": "Jan",
        "last_name": "Kowalski",
        "full_name": "Jan Kowalski",
        "email": "jan.kowalski@example.com"
      },
      {
        "id": 2,
        "first_name": "Anna",
        "last_name": "Nowak",
        "full_name": "Anna Nowak",
        "email": "anna.nowak@example.com"
      }
    ],
    "created_at": "2025-07-21T12:00:00.000000Z",
    "updated_at": "2025-07-21T12:00:00.000000Z"
  }
}
```

#### Create Tag

```
POST /api/tags
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Request Body:**
```json
{
  "name": "Client",
  "color": "#00FF00"
}
```

**Response:**
```json
{
  "success": true,
  "tag": {
    "id": 3,
    "name": "Client",
    "color": "#00FF00",
    "contacts_count": 0,
    "created_at": "2025-07-21T12:00:00.000000Z",
    "updated_at": "2025-07-21T12:00:00.000000Z"
  }
}
```

#### Update Tag

```
PUT /api/tags/{id}
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Request Body:**
```json
{
  "name": "VIP Client",
  "color": "#FF0000"
}
```

**Response:**
```json
{
  "success": true,
  "tag": {
    "id": 1,
    "name": "VIP Client",
    "color": "#FF0000",
    "contacts_count": 2,
    "created_at": "2025-07-21T12:00:00.000000Z",
    "updated_at": "2025-07-21T12:00:00.000000Z"
  }
}
```

#### Delete Tag

```
DELETE /api/tags/{id}
```

**Headers:**
```
Authorization: Bearer {your_api_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Tag deleted successfully"
}
```

## Rate Limiting

API requests are limited to 60 requests per minute per IP address. When the limit is exceeded, a 429 Too Many Requests response will be returned with the following headers:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
Retry-After: 60
```

## Security

### Authentication
The API uses Laravel Sanctum for token-based authentication. To authenticate:

1. Obtain a token via the login endpoint
2. Include the token in the Authorization header for all protected requests
3. Tokens expire after 24 hours of inactivity

### CORS
Cross-Origin Resource Sharing (CORS) is enabled for the API. The following headers are allowed:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
```

### Data Validation
All input data is validated before processing. Common validation rules include:

- Email addresses must be valid and unique
- Names cannot be empty
- Phone numbers must follow a valid format
- Colors must be valid hex codes

## Error Handling

All errors return a JSON response with an error message:

```json
{
  "success": false,
  "error": "Error message",
  "details": {
    "field": ["Validation error message"]
  }
}
```

### Common Error Codes

| Status Code | Description                                           |
|-------------|-------------------------------------------------------|
| 400         | Bad Request - Invalid input data                      |
| 401         | Unauthorized - Authentication required                |
| 403         | Forbidden - Insufficient permissions                  |
| 404         | Not Found - Resource not found                        |
| 422         | Unprocessable Entity - Validation failed              |
| 429         | Too Many Requests - Rate limit exceeded               |
| 500         | Internal Server Error - Server-side error occurred    |

## Versioning

The current API version is v1. All endpoints are prefixed with `/api`. Future versions will be available at `/api/v2`, `/api/v3`, etc.

## Pagination

List endpoints support pagination with the following query parameters:

- `page` - Page number (default: 1)
- `per_page` - Results per page (default: 15, max: 100)

Pagination metadata is included in the response:

```json
{
  "pagination": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4,
    "from": 1,
    "to": 15
  }
}
```

## Support

For API support, please contact the development team at support@example.com.

## Changelog

### Version 1.0.0 (July 21, 2025)
- Initial release of the API
- Implemented all CRUD operations for contacts and tags
- Added search and filtering capabilities
- Implemented authentication with Sanctum